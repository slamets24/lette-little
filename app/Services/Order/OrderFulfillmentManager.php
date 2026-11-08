<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderFulfillmentManager
{
    public function __construct(private readonly OrderMailer $orderMailer) {}

    /**
     * @return list<array{label: string, value: string}>
     */
    public function availableTransitions(Order $order): array
    {
        return collect($this->targetsFor($order->status))
            ->map(fn (OrderStatus $status): array => [
                'label' => $status === OrderStatus::Paid
                    ? 'Mark as paid'
                    : str($status->value)->replace('_', ' ')->title()->toString(),
                'value' => $status->value,
            ])
            ->values()
            ->all();
    }

    public function transition(Order $order, OrderStatus $targetStatus, array $tracking = []): Order
    {
        return DB::transaction(function () use ($order, $targetStatus, $tracking): Order {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status === $targetStatus) {
                return $lockedOrder->fresh(['customer', 'address', 'items', 'payment']);
            }

            if (! $this->canTransition($lockedOrder->status, $targetStatus)) {
                throw ValidationException::withMessages([
                    'status' => __('This order cannot be moved from :from to :to.', [
                        'from' => str($lockedOrder->status->value)->replace('_', ' ')->title(),
                        'to' => str($targetStatus->value)->replace('_', ' ')->title(),
                    ]),
                ]);
            }

            $attributes = ['status' => $targetStatus];

            if ($targetStatus === OrderStatus::Shipped) {
                $attributes['courier'] = $tracking['courier'] ?? $lockedOrder->courier;
                $attributes['tracking_number'] = $tracking['tracking_number'] ?? $lockedOrder->tracking_number;
            }

            $lockedOrder->forceFill($attributes)->save();

            if ($targetStatus === OrderStatus::Paid) {
                $lockedOrder->payment()->where('status', PaymentStatus::Pending)->update([
                    'status' => PaymentStatus::Completed,
                    'settled_at' => now(),
                ]);
            }

            if ($targetStatus === OrderStatus::Cancelled) {
                $lockedOrder->payment()->where('status', PaymentStatus::Pending)->update([
                    'status' => PaymentStatus::Failed,
                ]);

                $this->releaseStockOnce($lockedOrder);
            }

            $freshOrder = $lockedOrder->fresh(['customer', 'address', 'items', 'payment']);

            DB::afterCommit(function () use ($freshOrder, $targetStatus): void {
                if ($targetStatus === OrderStatus::Paid) {
                    $this->orderMailer->paid($freshOrder);
                }

                if ($targetStatus === OrderStatus::Shipped) {
                    $this->orderMailer->shipped($freshOrder);
                }
            });

            return $freshOrder;
        }, attempts: 3);
    }

    public function releaseStockOnce(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $alreadyReleased = StockMovement::query()
                ->where('reference_type', Order::class)
                ->where('reference_id', $order->id)
                ->where('type', StockMovementType::Cancellation->value)
                ->exists();

            if ($alreadyReleased) {
                return;
            }

            $order->loadMissing('items');

            foreach ($order->items as $item) {
                ProductVariant::query()
                    ->whereKey($item->product_variant_id)
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->increment('stock', $item->quantity);

                StockMovement::query()->create([
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'type' => StockMovementType::Cancellation,
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'notes' => "Cancelled order {$order->order_number} item {$item->id}",
                ]);
            }
        }, attempts: 3);
    }

    public function canTransition(OrderStatus $from, OrderStatus $to): bool
    {
        return in_array($to, $this->targetsFor($from), true);
    }

    /**
     * @return list<OrderStatus>
     */
    private function targetsFor(OrderStatus $status): array
    {
        return match ($status) {
            OrderStatus::PendingPayment => [OrderStatus::Paid, OrderStatus::Cancelled],
            OrderStatus::Paid => [OrderStatus::Packed],
            OrderStatus::Packed => [OrderStatus::Shipped],
            OrderStatus::Shipped => [OrderStatus::Completed],
            default => [],
        };
    }
}
