<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            );
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
