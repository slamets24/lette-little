<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Services\Cart\CartManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartItemController extends Controller
{
    /**
     * Add an item to the customer cart.
     */
    public function store(StoreCartItemRequest $request, CartManager $cartManager): RedirectResponse
    {
        $cartManager->addItem(
            $request,
            (int) $request->validated('product_variant_id'),
            (int) $request->validated('quantity'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Added to cart.')]);

        return back();
    }

    /**
     * Update a cart item quantity.
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem, CartManager $cartManager): RedirectResponse
    {
        $cartManager->updateItem($request, $cartItem, (int) $request->validated('quantity'));

        return back();
    }

    /**
     * Remove a cart item.
     */
    public function destroy(Request $request, CartItem $cartItem, CartManager $cartManager): RedirectResponse
    {
        $cartManager->removeItem($request, $cartItem);

        return back();
    }
}
