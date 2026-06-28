<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Services\Payment\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    /**
     * Handle Midtrans payment notifications.
     */
    public function __invoke(Request $request, MidtransService $midtransService): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'string'],
            'status_code' => ['required', 'string'],
            'gross_amount' => ['required', 'string'],
            'signature_key' => ['required', 'string'],
            'transaction_status' => ['required', 'string'],
            'transaction_id' => ['nullable', 'string'],
            'fraud_status' => ['nullable', 'string'],
            'payment_type' => ['nullable', 'string'],
        ]);

        if (! $midtransService->verifySignature($data['order_id'], $data['status_code'], $data['gross_amount'], $data['signature_key'])) {
            Log::warning('Rejected Midtrans webhook with invalid signature', [
                'order_id' => $data['order_id'],
                'transaction_status' => $data['transaction_status'],
            ]);

            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        $order = Order::query()
            ->where('order_number', $data['order_id'])
            ->with(['items', 'payment'])
            ->first();

        if (! $order || ! $order->payment) {
            Log::warning('Received Midtrans webhook for unknown order or payment', [
                'order_id' => $data['order_id'],
                'transaction_status' => $data['transaction_status'],
            ]);

            return response()->json(['message' => 'Order not found.'], 404);
        }

        if (! $this->grossAmountMatches($data['gross_amount'], $order->payment->amount)) {
            Log::warning('Rejected Midtrans webhook with mismatched gross amount', [
                'order_id' => $data['order_id'],
                'gross_amount' => $data['gross_amount'],
                'expected_amount' => $order->payment->amount,
            ]);

            return response()->json(['message' => 'Gross amount does not match.'], 422);
        }

        DB::transaction(function () use ($order, $data, $request): void {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $payment = Payment::query()->where('order_id', $order->id)->lockForUpdate()->firstOrFail();
            $status = $data['transaction_status'];

            $payment->forceFill([
                'external_reference' => $data['transaction_id'] ?? $payment->external_reference,
                'raw_payload' => $request->all(),
            ]);

            if ($this->isSuccessfulPayment($status, $data['fraud_status'] ?? null)) {
                $payment->forceFill([
                    'status' => PaymentStatus::Completed,
                    'settled_at' => $payment->settled_at ?? now(),
                ])->save();

                if ($order->status !== OrderStatus::Paid) {
                    $order->forceFill(['status' => OrderStatus::Paid])->save();
                }

                return;
            }

            if ($payment->status === PaymentStatus::Completed || $order->status === OrderStatus::Paid) {
                $payment->save();

                return;
            }

            if ($status === 'pending') {
                $payment->forceFill(['status' => PaymentStatus::Pending])->save();
                $order->forceFill(['status' => OrderStatus::PendingPayment])->save();

                return;
            }

            if (in_array($status, ['deny', 'cancel', 'expire', 'failure'], true)) {
                $payment->forceFill(['status' => PaymentStatus::Failed])->save();
                $order->forceFill(['status' => OrderStatus::Cancelled])->save();
                $this->releaseStock($order);

                return;
            }

            if (in_array($status, ['refund', 'partial_refund', 'chargeback'], true)) {
                $payment->forceFill(['status' => PaymentStatus::Refunded])->save();

                return;
            }

            $payment->save();
        });

        return response()->json(['message' => 'Webhook handled.']);
    }

    private function isSuccessfulPayment(string $transactionStatus, ?string $fraudStatus): bool
    {
        if ($transactionStatus === 'settlement') {
            return true;
        }

        return $transactionStatus === 'capture' && in_array($fraudStatus, [null, 'accept'], true);
    }

    private function grossAmountMatches(string $grossAmount, int $expectedAmount): bool
    {
        return (int) round((float) $grossAmount) === $expectedAmount;
    }

    private function releaseStock(Order $order): void
    {
        $alreadyReleased = StockMovement::query()
            ->where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->where('type', StockMovementType::Cancellation->value)
            ->exists();

        if ($alreadyReleased) {
            return;
        }

        $order->loadMissing('items.variant');

        foreach ($order->items as $item) {
            $item->variant()->increment('stock', $item->quantity);

            StockMovement::query()->create([
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'type' => StockMovementType::Cancellation,
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'notes' => "Cancelled order {$order->order_number} item {$item->id}",
            ]);
        }
    }
}
