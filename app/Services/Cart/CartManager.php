<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartManager
{
    private const CookieName = 'lette_little_cart';

    private const CookieMinutes = 43200;

    public function resolve(Request $request, bool $create = false): ?Cart
    {
        if ($request->user()) {
            return $this->resolveUserCart($request, $create);
        }

        return $this->resolveGuestCart($request, $create);
    }

    public function count(Request $request): int
    {
        $cart = $this->resolve($request);

        if (! $cart) {
            return 0;
        }

        return (int) $cart->items()->sum('quantity');
    }

    /**
     * @return array<int, string>
     */
    public function sync(Cart $cart): array
    {
        $adjustments = [];

        $cart->loadMissing('items.variant.product.category');

        foreach ($cart->items as $item) {
            $variant = $item->variant;
            $product = $variant->product;
            $category = $product->category;

            if (! $variant->is_active || ! $product->is_active || ! $category->is_active || $variant->stock < 1) {
                $adjustments[] = "{$product->name} is no longer available and was removed from your cart.";
                $item->delete();

                continue;
            }

            if ($item->quantity > $variant->stock) {
                $item->forceFill(['quantity' => $variant->stock])->save();
                $adjustments[] = "{$product->name} quantity was adjusted to {$variant->stock} based on current stock.";
            }
        }

        return $adjustments;
    }

    public function addItem(Request $request, int $productVariantId, int $quantity): CartItem
    {
        return DB::transaction(function () use ($request, $productVariantId, $quantity) {
            $cart = $this->resolve($request, create: true);
            $variant = $this->availableVariant($productVariantId);
            $item = $cart->items()->firstOrNew(['product_variant_id' => $variant->id]);
            $nextQuantity = (int) $item->quantity + $quantity;

            $this->ensureStock($variant, $nextQuantity);

            $item->quantity = $nextQuantity;
            $item->save();
            $this->touch($cart);

            return $item->load('variant.product.primaryImage');
        });
    }

    public function updateItem(Request $request, CartItem $item, int $quantity): CartItem
    {
        return DB::transaction(function () use ($request, $item, $quantity) {
            $this->ensureCartItemBelongsToRequest($request, $item);

            $variant = $this->availableVariant($item->product_variant_id);
            $this->ensureStock($variant, $quantity);

            $item->forceFill(['quantity' => $quantity])->save();
            $this->touch($item->cart);

            return $item->load('variant.product.primaryImage');
        });
    }

    public function removeItem(Request $request, CartItem $item): void
    {
        DB::transaction(function () use ($request, $item) {
            $this->ensureCartItemBelongsToRequest($request, $item);
            $cart = $item->cart;

            $item->delete();
            $this->touch($cart);
        });
    }

    public function clear(Request $request): void
    {
        $cart = $this->resolve($request);

        if (! $cart) {
            return;
        }

        $cart->items()->delete();
        $this->touch($cart);
    }

    /**
     * @param  array<int, array{product_variant_id: int, quantity: int}>  $items
     * @return array<int, string>
     */
    public function import(Request $request, array $items): array
    {
        $adjustments = [];

        foreach ($items as $item) {
            try {
                $this->addItem($request, $item['product_variant_id'], $item['quantity']);
            } catch (ValidationException) {
                $adjustments[] = 'One unavailable cart item was skipped.';
            }
        }

        return $adjustments;
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, count: int, subtotal: int}
     */
    public function present(?Cart $cart): array
    {
        if (! $cart) {
            return [
                'items' => [],
                'count' => 0,
                'subtotal' => 0,
            ];
        }

        $cart->load([
            'items' => fn ($query) => $query->oldest('id'),
            'items.variant.product.primaryImage',
        ]);

        $items = $cart->items->map(function (CartItem $item) {
            $variant = $item->variant;
            $product = $variant->product;
            $image = $product->primaryImage?->path ?? '/images/lette/stitch-product-dress.jpg';

            return [
                'id' => $item->id,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'image' => $image,
                ],
                'variant' => [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'price' => $variant->price,
                    'stock' => $variant->stock,
                ],
                'quantity' => $item->quantity,
                'line_total' => $variant->price * $item->quantity,
            ];
        })->values();

        return [
            'items' => $items->all(),
            'count' => (int) $items->sum('quantity'),
            'subtotal' => (int) $items->sum('line_total'),
        ];
    }

    public function ensureCartItemBelongsToRequest(Request $request, CartItem $item): void
    {
        $cart = $this->resolve($request);

        abort_if(! $cart || $item->cart_id !== $cart->id, 404);
    }

    private function resolveUserCart(Request $request, bool $create): ?Cart
    {
        $cart = Cart::query()->where('user_id', $request->user()->id)->first();

        if (! $cart && $create) {
            $cart = Cart::query()->create([
                'user_id' => $request->user()->id,
                'last_activity_at' => now(),
            ]);
        }

        return $cart;
    }

    private function resolveGuestCart(Request $request, bool $create): ?Cart
    {
        $token = $request->cookie(self::CookieName) ?? ($request->hasSession() ? $request->session()->get(self::CookieName) : null);
        $cart = $token ? Cart::query()->where('guest_token', $token)->first() : null;

        if (! $cart && $create) {
            $token = (string) Str::uuid();
            $cart = Cart::query()->create([
                'guest_token' => $token,
                'last_activity_at' => now(),
            ]);

            if ($request->hasSession()) {
                $request->session()->put(self::CookieName, $token);
            }

            $secure = config('session.secure') ?? $request->isSecure();

            Cookie::queue(Cookie::make(self::CookieName, $token, self::CookieMinutes, '/', null, (bool) $secure, true, false, 'lax'));
        }

        return $cart;
    }

    private function availableVariant(int $productVariantId): ProductVariant
    {
        $variant = ProductVariant::query()
            ->with('product.category')
            ->find($productVariantId);

        if (! $variant || ! $variant->is_active || ! $variant->product->is_active || ! $variant->product->category->is_active) {
            throw ValidationException::withMessages([
                'product_variant_id' => __('This product is not available.'),
            ]);
        }

        if ($variant->stock < 1) {
            throw ValidationException::withMessages([
                'product_variant_id' => __('This product is out of stock.'),
            ]);
        }

        return $variant;
    }

    private function ensureStock(ProductVariant $variant, int $quantity): void
    {
        if ($quantity > $variant->stock) {
            throw ValidationException::withMessages([
                'quantity' => __('Only :count items are available.', ['count' => $variant->stock]),
            ]);
        }
    }

    private function touch(Cart $cart): void
    {
        $cart->forceFill(['last_activity_at' => now()])->save();
    }
}
