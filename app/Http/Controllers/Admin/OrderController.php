<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListOrdersRequest;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Display the admin order inbox.
     */
    public function index(ListOrdersRequest $request): Response
    {
        $filters = $this->filters($request->validated());

        $orders = $this->baseOrderQuery($filters)
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Order $order): array => $this->presentOrder($order));

        return Inertia::render('admin/orders/Index', [
            'orders' => $orders,
            'filters' => $filters,
            'stats' => $this->stats(),
            'statusOptions' => $this->orderStatusOptions(),
            'paymentStatusOptions' => $this->paymentStatusOptions(),
        ]);
    }

    /**
     * Display an admin order detail page.
     */
    public function show(ListOrdersRequest $request, Order $order): Response
    {
        $order->load([
            'address:id,customer_id,address_line_1,address_line_2,city,province,postal_code,country',
            'customer:id,name,email,phone',
            'items:id,order_id,product_name,variant_sku,variant_size,variant_color,unit_price,quantity,line_total',
            'payment:id,order_id,provider,status,amount,external_reference,settled_at,raw_payload',
        ]);

        return Inertia::render('admin/orders/Show', [
            'order' => $this->presentOrderDetail($order),
            'filters' => $this->filters($request->validated()),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Order>
     */
    private function baseOrderQuery(array $filters): Builder
    {
        return Order::query()
            ->with([
                'customer:id,name,email,phone',
                'payment:id,order_id,status',
            ])
            ->when($filters['q'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['q'];

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function (Builder $query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] !== '', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when($filters['payment_status'] !== '', function (Builder $query) use ($filters): void {
                $query->whereHas('payment', fn (Builder $query) => $query->where('status', $filters['payment_status']));
            })
            ->when($filters['date_from'] !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $filters['date_to']));
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{q: string, status: string, payment_status: string, date_from: string, date_to: string}
     */
    private function filters(array $validated): array
    {
        return [
            'q' => (string) ($validated['q'] ?? ''),
            'status' => (string) ($validated['status'] ?? ''),
            'payment_status' => (string) ($validated['payment_status'] ?? ''),
            'date_from' => (string) ($validated['date_from'] ?? ''),
            'date_to' => (string) ($validated['date_to'] ?? ''),
        ];
    }

    /**
     * @return array{orders: int, pending_payment: int, paid: int, fulfillment: int}
     */
    private function stats(): array
    {
        return [
            'orders' => Order::query()->count(),
            'pending_payment' => Order::query()->where('status', OrderStatus::PendingPayment)->count(),
            'paid' => Order::query()->where('status', OrderStatus::Paid)->count(),
            'fulfillment' => Order::query()->whereIn('status', [
                OrderStatus::Paid,
                OrderStatus::Packed,
                OrderStatus::Shipped,
            ])->count(),
        ];
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function orderStatusOptions(): array
    {
        return collect(OrderStatus::cases())
            ->map(fn (OrderStatus $status): array => [
                'label' => str($status->value)->replace('_', ' ')->title()->toString(),
                'value' => $status->value,
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function paymentStatusOptions(): array
    {
        return collect(PaymentStatus::cases())
            ->map(fn (PaymentStatus $status): array => [
                'label' => str($status->value)->replace('_', ' ')->title()->toString(),
                'value' => $status->value,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function presentOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer' => [
                'name' => $order->customer->name,
                'email' => $order->customer->email,
                'phone' => $order->customer->phone,
            ],
            'status' => $order->status->value,
            'payment_status' => $order->payment?->status->value,
            'total' => $order->total,
            'created_at' => $order->created_at?->toIso8601String(),
            'updated_at' => $order->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentOrderDetail(Order $order): array
    {
        return [
            ...$this->presentOrder($order),
            'address' => [
                'address_line_1' => $order->address->address_line_1,
                'address_line_2' => $order->address->address_line_2,
                'city' => $order->address->city,
                'province' => $order->address->province,
                'postal_code' => $order->address->postal_code,
                'country' => $order->address->country,
            ],
            'items' => $order->items->map(fn ($item): array => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'variant_sku' => $item->variant_sku,
                'variant_size' => $item->variant_size,
                'variant_color' => $item->variant_color,
                'unit_price' => $item->unit_price,
                'quantity' => $item->quantity,
                'line_total' => $item->line_total,
            ])->all(),
            'subtotal' => $order->subtotal,
            'shipping_cost' => $order->shipping_cost,
            'total' => $order->total,
            'notes' => $order->notes,
            'payment' => $order->payment ? [
                'provider' => $order->payment->provider,
                'status' => $order->payment->status->value,
                'amount' => $order->payment->amount,
                'external_reference' => $order->payment->external_reference,
                'settled_at' => $order->payment->settled_at?->toIso8601String(),
            ] : null,
        ];
    }
}
