<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Order\OrderManager;
use App\Services\Seo\PageMeta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Display the authenticated customer's order history.
     */
    public function index(Request $request, OrderManager $orderManager): Response
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->with(['items', 'payment'])
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Order $order) => $orderManager->presentSummary($order));

        return Inertia::render('Orders', [
            'orders' => $orders,
            'meta' => PageMeta::make(
                title: 'My Orders',
                description: 'Track the status of every Lette Little order you have placed.',
                noindex: true,
            ),
        ]);
    }

    /**
     * Display a customer order confirmation page.
     */
    public function show(Request $request, Order $order, OrderManager $orderManager): Response
    {
        abort_unless($this->canView($request, $order), 404);

        return Inertia::render('OrderConfirmation', [
            'order' => $orderManager->present($order),
            'meta' => PageMeta::make(
                title: 'Order confirmation',
                description: 'Your Lette Little order has been received.',
                noindex: true,
            ),
        ]);
    }

    private function canView(Request $request, Order $order): bool
    {
        if ($request->hasValidSignature()) {
            return true;
        }

        if ($request->user()) {
            return $order->user_id === $request->user()->id;
        }

        return in_array($order->id, $request->session()->get('completed_order_ids', []), true);
    }
}
