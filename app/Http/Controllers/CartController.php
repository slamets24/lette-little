<?php

namespace App\Http\Controllers;

use App\Services\Cart\CartManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Display the customer cart.
     */
    public function show(Request $request, CartManager $cartManager): Response
    {
        $cart = $cartManager->resolve($request);
        $adjustments = $cart ? $cartManager->sync($cart) : [];

        return Inertia::render('Cart', [
            'cart' => $cartManager->present($cart?->fresh()),
            'cartAdjustments' => $adjustments,
        ]);
    }

    /**
     * Clear the customer cart.
     */
    public function destroy(Request $request, CartManager $cartManager): RedirectResponse
    {
        $cartManager->clear($request);
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Cart cleared.')]);

        return back();
    }
}
