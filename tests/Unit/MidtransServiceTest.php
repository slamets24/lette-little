<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Payment\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransServiceTest extends TestCase
{
    use RefreshDatabase;

    private MidtransService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.midtrans.server_key' => 'test-server-key',
            'services.midtrans.client_key' => 'test-client-key',
            'services.midtrans.is_production' => false,
        ]);

        $this->service = new MidtransService;
    }

    public function test_create_snap_token_returns_token_and_url_on_success(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '081234567890',
        ]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'order_number' => 'LL-202606-0001',
            'subtotal' => 250000,
            'shipping_cost' => 25000,
            'total' => 275000,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_name' => 'Cloud Dress',
            'variant_sku' => 'LL-CDR-03-IVY',
            'variant_size' => 'M',
            'variant_color' => 'Ivy',
            'unit_price' => 250000,
            'quantity' => 1,
            'line_total' => 250000,
        ]);

        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'test-snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/test-snap-token-123',
            ], 201),
        ]);

        $result = $this->service->createSnapToken($order);

        $this->assertSame('test-snap-token-123', $result['snap_token']);
        $this->assertSame('https://app.sandbox.midtrans.com/snap/v4/redirection/test-snap-token-123', $result['snap_url']);

        Http::assertSent(function ($request) {
            $payload = $request->data();

            return $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
                && $request->hasHeader('Authorization')
                && $payload['transaction_details']['order_id'] === 'LL-202606-0001'
                && $payload['transaction_details']['gross_amount'] === 275000
                && $payload['customer_details']['first_name'] === 'John Doe'
                && $payload['customer_details']['email'] === 'john@example.com'
                && $payload['customer_details']['phone'] === '081234567890'
                && count($payload['item_details']) === 2
                && $payload['item_details'][0]['id'] === 'LL-CDR-03-IVY'
                && $payload['item_details'][0]['price'] === 250000
                && $payload['item_details'][0]['quantity'] === 1
                && $payload['item_details'][0]['name'] === 'Cloud Dress - M Ivy'
                && $payload['item_details'][1]['id'] === 'SHIPPING'
                && $payload['item_details'][1]['price'] === 25000
                && $payload['item_details'][1]['quantity'] === 1;
        });
    }

    public function test_create_snap_token_throws_exception_on_midtrans_error(): void
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'order_number' => 'LL-202606-0002',
            'total' => 100000,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
        ]);

        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'error_messages' => ['Gross amount is invalid'],
            ], 400),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to create Snap token: Gross amount is invalid');

        $this->service->createSnapToken($order);
    }

    public function test_create_snap_token_excludes_shipping_item_when_zero(): void
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'shipping_cost' => 0,
            'total' => 150000,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'unit_price' => 150000,
            'quantity' => 1,
            'line_total' => 150000,
        ]);

        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'test-token',
                'redirect_url' => 'https://snap.test',
            ], 201),
        ]);

        $this->service->createSnapToken($order);

        Http::assertSent(function ($request) {
            $payload = $request->data();

            return count($payload['item_details']) === 1
                && $payload['item_details'][0]['id'] !== 'SHIPPING';
        });
    }

    public function test_verify_signature_returns_true_for_valid_signature(): void
    {
        $orderId = 'LL-202606-0001';
        $statusCode = '200';
        $grossAmount = '150000.00';
        $serverKey = 'test-server-key';

        $validSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        $result = $this->service->verifySignature($orderId, $statusCode, $grossAmount, $validSignature);

        $this->assertTrue($result);
    }

    public function test_verify_signature_returns_false_for_invalid_signature(): void
    {
        $orderId = 'LL-202606-0001';
        $statusCode = '200';
        $grossAmount = '150000.00';

        $invalidSignature = 'invalid-signature-hash';

        $result = $this->service->verifySignature($orderId, $statusCode, $grossAmount, $invalidSignature);

        $this->assertFalse($result);
    }

    public function test_uses_production_url_when_configured(): void
    {
        config(['services.midtrans.is_production' => true]);
        $service = new MidtransService;

        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'total' => 100000,
        ]);

        OrderItem::factory()->create(['order_id' => $order->id]);

        Http::fake([
            'app.midtrans.com/*' => Http::response([
                'token' => 'prod-token',
                'redirect_url' => 'https://app.midtrans.com/snap/v4/redirection/prod-token',
            ], 201),
        ]);

        $service->createSnapToken($order);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://app.midtrans.com/snap/v1/transactions';
        });
    }
}
