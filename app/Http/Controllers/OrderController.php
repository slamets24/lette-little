<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Order\OrderManager;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Display a customer order confirmation page.
     */
    public function show(Request $request, Order $order, OrderManager $orderManager): Response
    {
        abort_unless($this->canView($request, $order), 404);

        return Inertia::render('OrderConfirmation', [
            'order' => $orderManager->present($order),
        ]);
    }

    private function canView(Request $request, Order $order): bool
    {
        if ($request->user()) {
            return $order->user_id === $request->user()->id;
        }

        return in_array($order->id, $request->session()->get('completed_order_ids', []), true);
    }
}
