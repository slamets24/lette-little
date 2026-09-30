<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Mail\OrderPaidMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.midtrans.server_key' => 'test-server-key',
            'services.midtrans.client_key' => 'test-client-key',
            'services.midtrans.is_production' => false,
        ]);
    }

    public function test_settlement_webhook_marks_payment_completed_and_order_paid(): void
    {
        Mail::fake();
        $order = $this->createPendingOrder();

        $this->postJson(route('payments.midtrans.webhook'), $this->payload($order, 'settlement'))
            ->assertOk()
            ->assertJson(['message' => 'Webhook handled.']);

        $order->refresh();
        $payment = $order->payment()->firstOrFail();

        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertSame(PaymentStatus::Completed, $payment->status);
        $this->assertNotNull($payment->settled_at);
        $this->assertSame('midtrans-transaction-123', $payment->external_reference);
        $this->assertSame('settlement', $payment->raw_payload['transaction_status']);
        Mail::assertQueued(OrderPaidMail::class);
    }

    public function test_capture_webhook_with_accepted_fraud_status_marks_payment_completed(): void
    {
        $order = $this->createPendingOrder();

        $this->postJson(route('payments.midtrans.webhook'), $this->payload($order, 'capture', [
            'fraud_status' => 'accept',
        ]))->assertOk();

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Completed, $order->payment()->firstOrFail()->status);
    }

    public function test_pending_webhook_keeps_order_waiting_for_payment(): void
    {
        $order = $this->createPendingOrder();

        $this->postJson(route('payments.midtrans.webhook'), $this->payload($order, 'pending'))
            ->assertOk();

        $this->assertSame(OrderStatus::PendingPayment, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Pending, $order->payment()->firstOrFail()->status);
    }

    public function test_invalid_signature_does_not_mutate_order_or_payment(): void
    {
        $order = $this->createPendingOrder();
        $payload = $this->payload($order, 'settlement');
        $payload['signature_key'] = 'invalid-signature';

        $this->postJson(route('payments.midtrans.webhook'), $payload)
            ->assertForbidden();

        $this->assertSame(OrderStatus::PendingPayment, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Pending, $order->payment()->firstOrFail()->status);
    }

    public function test_expired_webhook_marks_failed_cancels_order_and_releases_stock_once(): void
    {
        [$order, $variant] = $this->createReservedOrder();
        $reservedStock = $variant->fresh()->stock;

        $this->postJson(route('payments.midtrans.webhook'), $this->payload($order, 'expire'))
            ->assertOk();
        $this->postJson(route('payments.midtrans.webhook'), $this->payload($order, 'expire'))
            ->assertOk();

        $order->refresh();
        $payment = $order->payment()->firstOrFail();

        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertSame(PaymentStatus::Failed, $payment->status);
        $this->assertSame($reservedStock + 2, $variant->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 1);
        $this->assertDatabaseHas('stock_movements', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'type' => StockMovementType::Cancellation->value,
            'reference_type' => Order::class,
            'reference_id' => $order->id,
        ]);
    }

    public function test_completed_payment_cannot_be_downgraded_by_late_failed_webhook(): void
    {
        [$order, $variant] = $this->createReservedOrder();
        $reservedStock = $variant->fresh()->stock;

        $this->postJson(route('payments.midtrans.webhook'), $this->payload($order, 'settlement'))
            ->assertOk();
        $this->postJson(route('payments.midtrans.webhook'), $this->payload($order, 'expire'))
            ->assertOk();

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Completed, $order->payment()->firstOrFail()->status);
        $this->assertSame($reservedStock, $variant->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_late_success_webhook_does_not_downgrade_fulfilled_order(): void
    {
        $order = $this->createPendingOrder();
        $order->forceFill(['status' => OrderStatus::Packed])->save();
        $order->payment()->firstOrFail()->forceFill([
            'status' => PaymentStatus::Completed,
            'settled_at' => now()->subDay(),
        ])->save();

        $this->postJson(route('payments.midtrans.webhook'), $this->payload($order, 'settlement'))
            ->assertOk();

        $order->refresh();
        $payment = $order->payment()->firstOrFail();

        $this->assertSame(OrderStatus::Packed, $order->status);
        $this->assertSame(PaymentStatus::Completed, $payment->status);
        $this->assertSame('settlement', $payment->raw_payload['transaction_status']);
    }

    public function test_late_success_webhook_does_not_resurrect_cancelled_order(): void
    {
        [$order, $variant] = $this->createReservedOrder();
        $reservedStock = $variant->fresh()->stock;

        $this->postJson(route('payments.midtrans.webhook'), $this->payload($order, 'expire'))
            ->assertOk();
        $this->postJson(route('payments.midtrans.webhook'), $this->payload($order, 'settlement'))
            ->assertOk();

        $order->refresh();
        $payment = $order->payment()->firstOrFail();

        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertSame(PaymentStatus::Failed, $payment->status);
        $this->assertSame($reservedStock + 2, $variant->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 1);
        $this->assertSame('settlement', $payment->raw_payload['transaction_status']);
    }

    /**
     * @return array{0: Order, 1: ProductVariant}
     */
    private function createReservedOrder(): array
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();
        $variant->decrement('stock', 2);
        $order = $this->createPendingOrder($variant);

        return [$order, $variant];
    }

    private function createPendingOrder(?ProductVariant $variant = null): Order
    {
        if (! $variant) {
            $this->seed(CatalogSeeder::class);
            $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();
        }

        $order = Order::factory()->create([
            'order_number' => 'LL-202606-0001',
            'subtotal' => 498000,
            'shipping_cost' => 0,
            'total' => 498000,
            'status' => OrderStatus::PendingPayment,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'variant_sku' => $variant->sku,
            'variant_size' => $variant->size,
            'variant_color' => $variant->color,
            'unit_price' => $variant->price,
            'quantity' => 2,
            'line_total' => 498000,
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'status' => PaymentStatus::Pending,
            'amount' => $order->total,
            'external_reference' => 'test-snap-token',
        ]);

        return $order;
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function payload(Order $order, string $transactionStatus, array $overrides = []): array
    {
        $payload = [
            'order_id' => $order->order_number,
            'status_code' => '200',
            'gross_amount' => '498000.00',
            'transaction_status' => $transactionStatus,
            'transaction_id' => 'midtrans-transaction-123',
            'payment_type' => 'bank_transfer',
        ];

        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('services.midtrans.server_key'),
        );

        return array_merge($payload, $overrides);
    }
}
