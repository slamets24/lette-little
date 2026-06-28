<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Services\Cart\CartManager;
use App\Services\Order\OrderManager;
use App\Services\Payment\MidtransService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class CheckoutController extends Controller
{
    /**
     * Display the checkout form.
     */
    public function show(Request $request, CartManager $cartManager): Response|RedirectResponse
    {
        $cart = $cartManager->resolve($request);
        $adjustments = $cart ? $cartManager->sync($cart) : [];
        $cartPayload = $cartManager->present($cart?->fresh());

        if ($cartPayload['count'] === 0) {
            return redirect()->route('cart')->withErrors([
                'cart' => __('Add an item to your cart before checkout.'),
            ]);
        }

        return Inertia::render('Checkout', [
            'cart' => $cartPayload,
            'cartAdjustments' => $adjustments,
        ]);
    }

    /**
     * Create an order from the current cart.
     */
    public function store(StoreOrderRequest $request, OrderManager $orderManager, MidtransService $midtransService): RedirectResponse|SymfonyResponse
    {
        $order = $orderManager->checkout($request, $request->validated());
        $completedOrderIds = $request->session()->get('completed_order_ids', []);
        $completedOrderIds[] = $order->id;

        $request->session()->put('completed_order_ids', array_values(array_unique($completedOrderIds)));

        try {
            $snap = $midtransService->createSnapToken($order);

            $order->payment()->update([
                'status' => PaymentStatus::Pending,
                'external_reference' => $snap['snap_token'],
                'raw_payload' => $snap,
            ]);

            return Inertia::location($snap['snap_url']);
        } catch (Throwable $exception) {
            Log::error('Midtrans Snap redirect creation failed', [
                'order_number' => $order->order_number,
                'exception' => $exception,
            ]);

            return redirect()->route('orders.show', $order)->withErrors([
                'payment' => __('Payment is temporarily unavailable. Please contact us to continue your order.'),
            ]);
        }
    }
}
