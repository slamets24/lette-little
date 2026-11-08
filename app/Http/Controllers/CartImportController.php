<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportCartRequest;
use App\Services\Cart\CartManager;
use Illuminate\Http\JsonResponse;

class CartImportController extends Controller
{
    /**
     * Import localStorage cart items to backend cart.
     */
    public function store(ImportCartRequest $request, CartManager $cartManager): JsonResponse
    {
        $adjustments = $cartManager->import(
            $request,
            $request->validated('items'),
        );

        return response()->json([
            'success' => true,
            'adjustments' => $adjustments,
            'cart' => $cartManager->present($cartManager->resolve($request)),
        ]);
    }
}
