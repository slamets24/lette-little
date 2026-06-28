<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin operations dashboard.
     */
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => $this->stats(),
            'needsAttention' => $this->needsAttention(),
            'recentOrders' => $this->recentOrders(),
            'lowStockVariants' => $this->lowStockVariants(),
        ]);
    }

    /**
     * @return array{orders: int, pending_payment: int, ready_to_pack: int, in_fulfillment: int, completed: int, paid_revenue: int}
     */
    private function stats(): array
    {
        return [
            'orders' => Order::query()->count(),
            'pending_payment' => Order::query()->where('status', OrderStatus::PendingPayment)->count(),
            'ready_to_pack' => Order::query()->where('status', OrderStatus::Paid)->count(),
            'in_fulfillment' => Order::query()->whereIn('status', [
                OrderStatus::Packed,
                OrderStatus::Shipped,
            ])->count(),
            'completed' => Order::query()->where('status', OrderStatus::Completed)->count(),
            'paid_revenue' => (int) Payment::query()->where('status', PaymentStatus::Completed)->sum('amount'),
        ];
    }

    /**
     * @return array{ready_to_pack: int, pending_payment: int, low_stock: int}
     */
    private function needsAttention(): array
    {
        return [
            'ready_to_pack' => Order::query()->where('status', OrderStatus::Paid)->count(),
            'pending_payment' => Order::query()->where('status', OrderStatus::PendingPayment)->count(),
            'low_stock' => ProductVariant::query()->active()->where('stock', '<=', 3)->count(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentOrders(): array
    {
        return Order::query()
            ->with(['customer:id,name,email', 'payment:id,order_id,status,amount'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Order $order): array => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer->name,
                'customer_email' => $order->customer->email,
                'status' => $order->status->value,
                'payment_status' => $order->payment?->status->value,
                'total' => $order->total,
                'created_at' => $order->created_at?->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function lowStockVariants(): array
    {
        return ProductVariant::query()
            ->active()
            ->with('product:id,name')
            ->where('stock', '<=', 3)
            ->orderBy('stock')
            ->limit(6)
            ->get(['id', 'product_id', 'sku', 'size', 'color', 'stock'])
            ->map(fn (ProductVariant $variant): array => [
                'id' => $variant->id,
                'product_name' => $variant->product->name,
                'sku' => $variant->sku,
                'size' => $variant->size,
                'color' => $variant->color,
                'stock' => $variant->stock,
            ])
            ->values()
            ->all();
    }
}
