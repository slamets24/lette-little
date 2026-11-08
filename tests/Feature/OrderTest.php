<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrderTest extends TestCase
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

        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'test-snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/test-snap-token',
            ], 201),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function checkoutPayload(): array
    {
        return [
            'name' => 'Maya Lestari',
            'email' => 'maya@example.com',
            'phone' => '081234567890',
            'address_line_1' => 'Jl. Melati No. 10',
            'address_line_2' => 'Rumah warna krem',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40111',
            'country' => 'ID',
            'notes' => 'Tolong bungkus rapi untuk hadiah.',
            'payment_method' => 'midtrans',
        ];
    }

    public function test_checkout_page_displays_cart_summary(): void
    {
        $this->seed(CatalogSeeder::class);
        $user = User::factory()->create();
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)->get(route('checkout'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Checkout')
                ->where('cart.count', 2)
                ->where('cart.subtotal', 498000)
                ->has('cart.items', 1)
            );
    }

    public function test_authenticated_user_can_complete_checkout(): void
    {
        Mail::fake();
        $this->seed(CatalogSeeder::class);
        $user = User::factory()->create();
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)->post(route('checkout.store'), $this->checkoutPayload())
            ->assertRedirect('https://app.sandbox.midtrans.com/snap/v4/redirection/test-snap-token');

        $order = Order::query()->firstOrFail();

        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('pending_payment', $order->status->value);
        $this->assertSame(498000, $order->subtotal);
        $this->assertSame(498000, $order->total);
        $this->assertStringStartsWith('LL-', $order->order_number);
        Mail::assertQueued(OrderPlacedMail::class);
        $this->assertDatabaseHas('customers', [
            'email' => 'maya@example.com',
            'phone' => '081234567890',
        ]);
        $this->assertDatabaseHas('addresses', [
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'country' => 'ID',
        ]);
    }

    public function test_inertia_checkout_redirects_to_midtrans_snap(): void
    {
        $this->seed(CatalogSeeder::class);
        $user = User::factory()->create();
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)
            ->withHeaders(['X-Inertia' => 'true'])
            ->post(route('checkout.store'), $this->checkoutPayload())
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', 'https://app.sandbox.midtrans.com/snap/v4/redirection/test-snap-token');

        $order = Order::query()->with('payment')->firstOrFail();

        $this->assertSame('test-snap-token', $order->payment->external_reference);
        $this->assertSame('test-snap-token', $order->payment->raw_payload['snap_token']);
        $this->assertSame('https://app.sandbox.midtrans.com/snap/v4/redirection/test-snap-token', $order->payment->raw_payload['snap_url']);
    }

    public function test_bank_transfer_checkout_skips_midtrans_and_shows_order(): void
    {
        config([
            'services.bank_transfer.bank' => 'BCA',
            'services.bank_transfer.account_name' => 'Lette Little',
            'services.bank_transfer.account_number' => '1234567890',
        ]);

        $this->seed(CatalogSeeder::class);
        $user = User::factory()->create();
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            ...$this->checkoutPayload(),
            'payment_method' => 'bank_transfer',
        ]);

        $order = Order::query()->with('payment')->firstOrFail();

        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame('bank_transfer', $order->payment->provider);
        $this->assertSame('pending', $order->payment->status->value);
        $this->assertNull($order->payment->external_reference);
        Http::assertNothingSent();

        $this->actingAs($user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OrderConfirmation')
                ->where('order.payment.provider', 'bank_transfer')
                ->where('order.payment.snap_url', null)
                ->where('order.payment.bank_transfer.bank', 'BCA')
                ->where('order.payment.bank_transfer.account_number', '1234567890')
            );
    }

    public function test_checkout_snapshots_items_decrements_stock_creates_payment_and_clears_cart(): void
    {
        $this->seed(CatalogSeeder::class);
        $user = User::factory()->create();
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();
        $startingStock = $variant->stock;

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::query()->with(['items', 'payment'])->firstOrFail();

        $this->assertSame('Cloud Dress', $order->items->first()->product_name);
        $this->assertSame('LL-CDR-03-IVY', $order->items->first()->variant_sku);
        $this->assertSame(249000, $order->items->first()->unit_price);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertSame(498000, $order->items->first()->line_total);
        $this->assertSame('midtrans', $order->payment->provider);
        $this->assertSame('pending', $order->payment->status->value);
        $this->assertSame(498000, $order->payment->amount);
        $this->assertSame('test-snap-token', $order->payment->external_reference);
        $this->assertSame('test-snap-token', $order->payment->raw_payload['snap_token']);
        $this->assertSame($startingStock - 2, $variant->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_variant_id' => $variant->id,
            'quantity' => -2,
            'type' => 'sale',
            'reference_type' => Order::class,
            'reference_id' => $order->id,
        ]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_fails_when_cart_is_empty(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('checkout.store'), $this->checkoutPayload())
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_fails_when_stock_drops_before_submit(): void
    {
        $this->seed(CatalogSeeder::class);
        $user = User::factory()->create();
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $variant->forceFill(['stock' => 1])->save();

        $this->actingAs($user)->post(route('checkout.store'), $this->checkoutPayload())
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $variant->fresh()->stock);
    }

    public function test_guest_is_redirected_from_order_history(): void
    {
        $this->get(route('orders.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_only_their_order_history(): void
    {
        $this->seed(CatalogSeeder::class);
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $ownOrder = Order::factory()
            ->for($owner, 'user')
            ->create([
                'order_number' => 'LL-202606-1001',
                'status' => OrderStatus::PendingPayment,
                'subtotal' => 498000,
                'shipping_cost' => 0,
                'total' => 498000,
            ]);
        $ownOrder->items()->createMany([
            [
                'product_variant_id' => $variant->id,
                'product_name' => 'Cloud Dress',
                'variant_sku' => 'LL-CDR-03-IVY',
                'variant_size' => '3-6M',
                'variant_color' => 'Ivory',
                'unit_price' => 249000,
                'quantity' => 2,
                'line_total' => 498000,
            ],
        ]);
        $ownOrder->payment()->create([
            'provider' => 'midtrans',
            'status' => PaymentStatus::Pending,
            'amount' => 498000,
            'raw_payload' => [
                'snap_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/customer-token',
            ],
        ]);

        $otherOrder = Order::factory()
            ->for($otherUser, 'user')
            ->create([
                'order_number' => 'LL-202606-1002',
                'status' => OrderStatus::Paid,
            ]);
        $otherOrder->items()->create([
            'product_variant_id' => $variant->id,
            'product_name' => 'Luna Set',
            'variant_sku' => 'LL-SET-01-LUN',
            'variant_size' => '6-9M',
            'variant_color' => 'Sage',
            'unit_price' => 199000,
            'quantity' => 1,
            'line_total' => 199000,
        ]);

        $this->actingAs($owner)->get(route('orders.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders')
                ->has('orders.data', 1)
                ->where('orders.data.0.order_number', 'LL-202606-1001')
                ->where('orders.data.0.status', 'pending_payment')
                ->where('orders.data.0.items_count', 2)
                ->where('orders.data.0.items_summary', 'Cloud Dress')
                ->where('orders.data.0.payment.status', 'pending')
                ->where('orders.data.0.payment.snap_url', 'https://app.sandbox.midtrans.com/snap/v4/redirection/customer-token')
                ->where('orders.total', 1)
            );
    }

    public function test_authenticated_user_sees_empty_order_history(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('orders.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders')
                ->has('orders.data', 0)
                ->where('orders.total', 0)
            );
    }

    public function test_order_confirmation_page_displays_order_details_for_owner(): void
    {
        $this->seed(CatalogSeeder::class);
        $user = User::factory()->create();
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);
        $this->actingAs($user)->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::query()->firstOrFail();

        $this->actingAs($user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OrderConfirmation')
                ->where('order.order_number', $order->order_number)
                ->where('order.payment.provider', 'midtrans')
                ->where('order.payment.status', 'pending')
                ->where('order.payment.snap_url', 'https://app.sandbox.midtrans.com/snap/v4/redirection/test-snap-token')
                ->has('order.items', 1)
            );
    }

    public function test_guest_can_view_order_confirmation_after_checkout_in_same_session(): void
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::query()->firstOrFail();

        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OrderConfirmation')
                ->where('order.order_number', $order->order_number)
            );
    }

    public function test_guest_cannot_view_order_confirmation_without_completed_order_session(): void
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::query()->firstOrFail();

        $this->flushSession();

        $this->get(route('orders.show', $order))->assertNotFound();
    }

    public function test_guest_can_view_order_with_signed_link(): void
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::query()->firstOrFail();
        $this->flushSession();

        $this->get(URL::temporarySignedRoute('orders.show', now()->addDays(1), [
            'order' => $order,
        ]))->assertOk();
    }

    public function test_user_cannot_view_another_users_order(): void
    {
        $this->seed(CatalogSeeder::class);
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $this->actingAs($owner)->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);
        $this->actingAs($owner)->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::query()->firstOrFail();

        $this->actingAs($otherUser)->get(route('orders.show', $order))->assertNotFound();
    }
}
