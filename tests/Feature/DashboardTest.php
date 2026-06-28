<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_visit_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_admin_users_can_visit_the_operations_dashboard(): void
    {
        $this->seed(CatalogSeeder::class);
        ProductVariant::query()->update(['stock' => 8]);
        $lowStockVariant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();
        $lowStockVariant->forceFill(['stock' => 2])->save();

        $paid = $this->createDashboardOrder(OrderStatus::Paid, PaymentStatus::Completed, 498000, 'LL-202606-0901');
        $this->createDashboardOrder(OrderStatus::PendingPayment, PaymentStatus::Pending, 219000, 'LL-202606-0902');
        $this->createDashboardOrder(OrderStatus::Packed, PaymentStatus::Completed, 329000, 'LL-202606-0903');
        $this->createDashboardOrder(OrderStatus::Completed, PaymentStatus::Completed, 179000, 'LL-202606-0904');

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('stats.orders', 4)
                ->where('stats.pending_payment', 1)
                ->where('stats.ready_to_pack', 1)
                ->where('stats.in_fulfillment', 1)
                ->where('stats.completed', 1)
                ->where('stats.paid_revenue', 1006000)
                ->where('needsAttention.ready_to_pack', 1)
                ->where('needsAttention.pending_payment', 1)
                ->where('needsAttention.low_stock', 1)
                ->has('recentOrders', 4)
                ->where('recentOrders.0.order_number', 'LL-202606-0904')
                ->where('recentOrders.3.order_number', $paid->order_number)
                ->has('lowStockVariants', 1)
                ->where('lowStockVariants.0.sku', 'LL-CDR-03-IVY')
            );
    }

    private function createDashboardOrder(OrderStatus $orderStatus, PaymentStatus $paymentStatus, int $total, string $orderNumber): Order
    {
        $variant = ProductVariant::query()->where('sku', 'LL-CDR-03-IVY')->firstOrFail();

        $order = Order::factory()->create([
            'order_number' => $orderNumber,
            'subtotal' => $total,
            'shipping_cost' => 0,
            'total' => $total,
            'status' => $orderStatus,
            'created_at' => now()->addMinutes((int) substr($orderNumber, -1)),
            'updated_at' => now()->addMinutes((int) substr($orderNumber, -1)),
        ]);

        $order->items()->create([
            'product_variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'variant_sku' => $variant->sku,
            'variant_size' => $variant->size,
            'variant_color' => $variant->color,
            'unit_price' => $variant->price,
            'quantity' => 1,
            'line_total' => $total,
        ]);

        $order->payment()->create([
            'provider' => 'midtrans',
            'status' => $paymentStatus,
            'amount' => $total,
            'settled_at' => $paymentStatus === PaymentStatus::Completed ? now() : null,
        ]);

        return $order;
    }
}
