<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Mail\OrderPaidMail;
use App\Mail\OrderShippedMail;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_admin_orders(): void
    {
        $this->get(route('admin.orders.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_view_admin_orders(): void
    {
        $order = $this->createOrder();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.orders.show', $order))
            ->assertForbidden();
    }

    public function test_non_admin_users_cannot_update_admin_orders(): void
    {
        $order = $this->createOrder(['status' => OrderStatus::Paid], [], PaymentStatus::Completed);

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.orders.update', $order), ['status' => OrderStatus::Packed->value])
            ->assertForbidden();
    }

    public function test_admin_users_can_view_order_inbox(): void
    {
        $this->createOrder([
            'order_number' => 'LL-202606-0101',
            'status' => OrderStatus::Paid,
            'total' => 349000,
            'created_at' => now()->subDay(),
        ], [
            'name' => 'Maya Putri',
            'email' => 'maya@example.com',
        ], PaymentStatus::Completed);
        $this->createOrder([
            'order_number' => 'LL-202606-0102',
            'status' => OrderStatus::PendingPayment,
            'total' => 219000,
            'created_at' => now(),
        ], [
            'name' => 'Raka Senja',
            'email' => 'raka@example.com',
        ], PaymentStatus::Pending);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
                ->has('orders.data', 2)
                ->where('stats.orders', 2)
                ->where('stats.pending_payment', 1)
                ->where('stats.paid', 1)
                ->where('filters.q', '')
                ->where('statusOptions.0.value', 'draft')
                ->where('paymentStatusOptions.0.value', 'pending')
            );
    }

    public function test_admin_users_can_filter_orders(): void
    {
        $matching = $this->createOrder([
            'order_number' => 'LL-202606-0201',
            'status' => OrderStatus::Paid,
            'total' => 420000,
            'created_at' => now()->setDate(2026, 6, 20),
        ], [
            'name' => 'Maya Putri',
            'email' => 'maya@example.com',
        ], PaymentStatus::Completed);
        $this->createOrder([
            'order_number' => 'LL-202606-0202',
            'status' => OrderStatus::PendingPayment,
            'total' => 210000,
            'created_at' => now()->setDate(2026, 6, 21),
        ], [
            'name' => 'Raka Senja',
            'email' => 'raka@example.com',
        ], PaymentStatus::Pending);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.orders.index', [
                'q' => 'maya@example.com',
                'status' => OrderStatus::Paid->value,
                'payment_status' => PaymentStatus::Completed->value,
                'date_from' => '2026-06-19',
                'date_to' => '2026-06-20',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
                ->has('orders.data', 1)
                ->where('orders.data.0.order_number', $matching->order_number)
                ->where('filters.q', 'maya@example.com')
                ->where('filters.status', 'paid')
                ->where('filters.payment_status', 'completed')
                ->where('filters.date_from', '2026-06-19')
                ->where('filters.date_to', '2026-06-20')
            );
    }

    public function test_admin_users_can_view_order_detail(): void
    {
        $order = $this->createOrder([
            'order_number' => 'LL-202606-0301',
            'status' => OrderStatus::Paid,
            'subtotal' => 498000,
            'shipping_cost' => 12000,
            'total' => 510000,
            'notes' => 'Please pack as a gift.',
        ], [
            'name' => 'Nadia Lestari',
            'email' => 'nadia@example.com',
            'phone' => '081234567890',
        ], PaymentStatus::Completed);

        OrderItem::factory()->for($order)->create([
            'product_name' => 'Cloud Dress',
            'variant_sku' => 'LL-CDR-03-IVY',
            'variant_size' => '0-3M',
            'variant_color' => 'Ivory',
            'unit_price' => 249000,
            'quantity' => 2,
            'line_total' => 498000,
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.orders.show', [
                'order' => $order,
                'q' => 'nadia@example.com',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Show')
                ->where('order.order_number', 'LL-202606-0301')
                ->where('order.customer.email', 'nadia@example.com')
                ->where('order.address.city', 'Bandung')
                ->where('order.payment.status', 'completed')
                ->where('order.notes', 'Please pack as a gift.')
                ->has('order.items', 1)
                ->where('order.items.0.variant_sku', 'LL-CDR-03-IVY')
                ->where('filters.q', 'nadia@example.com')
                ->where('availableTransitions.0.value', 'packed')
            );
    }

    public function test_admin_can_progress_paid_order_through_fulfillment_steps(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $order = $this->createOrder(['status' => OrderStatus::Paid], [], PaymentStatus::Completed);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), ['status' => OrderStatus::Packed->value])
            ->assertRedirect(route('admin.orders.show', $order));
        $this->assertSame(OrderStatus::Packed, $order->fresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), [
                'status' => OrderStatus::Shipped->value,
                'courier' => 'JNE',
                'tracking_number' => 'JT1234567890',
            ])
            ->assertRedirect(route('admin.orders.show', $order));
        $order->refresh();
        $this->assertSame(OrderStatus::Shipped, $order->status);
        $this->assertSame('JNE', $order->courier);
        $this->assertSame('JT1234567890', $order->tracking_number);
        Mail::assertQueued(OrderShippedMail::class);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), ['status' => OrderStatus::Completed->value])
            ->assertRedirect(route('admin.orders.show', $order));
        $this->assertSame(OrderStatus::Completed, $order->fresh()->status);
    }

    public function test_admin_can_mark_pending_payment_as_paid(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $pending = $this->createOrder(['status' => OrderStatus::PendingPayment], [], PaymentStatus::Pending);
        $pending->payment()->update(['provider' => 'bank_transfer']);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $pending), ['status' => OrderStatus::Paid->value])
            ->assertRedirect(route('admin.orders.show', $pending));

        $pending->refresh();
        $payment = $pending->payment()->firstOrFail();

        $this->assertSame(OrderStatus::Paid, $pending->status);
        $this->assertSame(PaymentStatus::Completed, $payment->status);
        $this->assertNotNull($payment->settled_at);
        Mail::assertQueued(OrderPaidMail::class);
    }

    public function test_admin_cannot_ship_without_tracking_details(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->createOrder(['status' => OrderStatus::Packed], [], PaymentStatus::Completed);

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.update', $order), ['status' => OrderStatus::Shipped->value])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHasErrors(['courier', 'tracking_number']);
        $this->assertSame(OrderStatus::Packed, $order->fresh()->status);
    }

    public function test_admin_cannot_skip_fulfillment_steps(): void
    {
        $admin = User::factory()->admin()->create();
        $paid = $this->createOrder(['status' => OrderStatus::Paid], ['email' => 'paid@example.com'], PaymentStatus::Completed);

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $paid))
            ->patch(route('admin.orders.update', $paid), [
                'status' => OrderStatus::Shipped->value,
                'courier' => 'JNE',
                'tracking_number' => 'JT123',
            ])
            ->assertRedirect(route('admin.orders.show', $paid))
            ->assertSessionHasErrors('status');
        $this->assertSame(OrderStatus::Paid, $paid->fresh()->status);
    }

    public function test_admin_cannot_move_terminal_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $cancelled = $this->createOrder(['status' => OrderStatus::Cancelled], [], PaymentStatus::Failed);
        $completed = $this->createOrder(['status' => OrderStatus::Completed], ['email' => 'completed@example.com'], PaymentStatus::Completed);

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $cancelled))
            ->patch(route('admin.orders.update', $cancelled), ['status' => OrderStatus::Packed->value])
            ->assertRedirect(route('admin.orders.show', $cancelled))
            ->assertSessionHasErrors('status');
        $this->assertSame(OrderStatus::Cancelled, $cancelled->fresh()->status);

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $completed))
            ->patch(route('admin.orders.update', $completed), [
                'status' => OrderStatus::Shipped->value,
                'courier' => 'JNE',
                'tracking_number' => 'JT123',
            ])
            ->assertRedirect(route('admin.orders.show', $completed))
            ->assertSessionHasErrors('status');
        $this->assertSame(OrderStatus::Completed, $completed->fresh()->status);
    }

    public function test_admin_can_cancel_pending_order_and_release_stock_once(): void
    {
        [$order, $variant] = $this->createReservedOrder();
        $reservedStock = $variant->fresh()->stock;
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), ['status' => OrderStatus::Cancelled->value])
            ->assertRedirect(route('admin.orders.show', $order));
        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), ['status' => OrderStatus::Cancelled->value])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Failed, $order->payment()->firstOrFail()->status);
        $this->assertSame($reservedStock + 2, $variant->fresh()->stock);
        $this->assertSame(1, $variant->stockMovements()
            ->where('type', StockMovementType::Cancellation)
            ->where('reference_id', $order->id)
            ->count());
    }

    /**
     * @return array{0: Order, 1: ProductVariant}
     */
    private function createReservedOrder(): array
    {
        $this->seed(CatalogSeeder::class);
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();
        $variant->decrement('stock', 2);

        $order = $this->createOrder([
            'order_number' => 'LL-202606-0401',
            'subtotal' => 498000,
            'shipping_cost' => 0,
            'total' => 498000,
            'status' => OrderStatus::PendingPayment,
        ], [], PaymentStatus::Pending);

        OrderItem::factory()->for($order)->create([
            'product_variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'variant_sku' => $variant->sku,
            'variant_size' => $variant->size,
            'variant_color' => $variant->color,
            'unit_price' => $variant->price,
            'quantity' => 2,
            'line_total' => 498000,
        ]);

        return [$order, $variant];
    }

    /**
     * @param  array<string, mixed>  $orderOverrides
     * @param  array<string, mixed>  $customerOverrides
     */
    private function createOrder(array $orderOverrides = [], array $customerOverrides = [], PaymentStatus $paymentStatus = PaymentStatus::Pending): Order
    {
        $customer = Customer::factory()->create(array_replace([
            'name' => 'Customer Name',
            'email' => fake()->unique()->safeEmail(),
            'phone' => '081111111111',
        ], $customerOverrides));
        $address = Address::factory()->for($customer)->create([
            'address_line_1' => 'Jl. Mawar No. 10',
            'address_line_2' => null,
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40111',
            'country' => 'ID',
        ]);

        $order = Order::factory()
            ->for($customer)
            ->for($address)
            ->create(array_replace([
                'subtotal' => 249000,
                'shipping_cost' => 0,
                'total' => 249000,
                'status' => OrderStatus::PendingPayment,
            ], $orderOverrides));

        Payment::query()->create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'status' => $paymentStatus,
            'amount' => $order->total,
            'external_reference' => 'snap-'.$order->order_number,
            'raw_payload' => ['snap_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/test'],
            'settled_at' => $paymentStatus === PaymentStatus::Completed ? now() : null,
        ]);

        return $order;
    }
}
