<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Services\Cart\CartManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderManager
{
    public function __construct(private readonly CartManager $cartManager) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function checkout(Request $request, array $data): Order
    {
        $cart = $this->cartManager->resolve($request);

        if (! $cart) {
            throw ValidationException::withMessages([
                'cart' => __('Your cart is empty.'),
            ]);
        }

        $cart->load('items.variant.product.category');

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => __('Your cart is empty.'),
            ]);
        }

        return DB::transaction(function () use ($request, $data, $cart) {
            $customer = $this->customer($data);
            $address = $this->address($customer, $data);
            $order = $this->createOrder($request, $customer, $address, $data, $cart);

            foreach ($cart->items as $item) {
                $this->createOrderItem($order, $item);
            }

            $order->payment()->create([
                'provider' => 'midtrans',
                'status' => PaymentStatus::Pending,
                'amount' => $order->total,
            ]);

            $cart->items()->delete();

            return $order->fresh(['customer', 'address', 'items', 'payment']);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function present(Order $order): array
    {
        $order->loadMissing(['customer', 'address', 'items', 'payment']);

        $paymentPayload = $order->payment?->raw_payload;
        $snapUrl = is_array($paymentPayload) ? ($paymentPayload['snap_url'] ?? null) : null;

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status->value,
            'subtotal' => $order->subtotal,
            'shipping_cost' => $order->shipping_cost,
            'total' => $order->total,
            'notes' => $order->notes,
            'created_at' => $order->created_at?->toISOString(),
            'customer' => [
                'id' => $order->customer->id,
                'name' => $order->customer->name,
                'email' => $order->customer->email,
                'phone' => $order->customer->phone,
            ],
            'address' => [
                'id' => $order->address->id,
                'address_line_1' => $order->address->address_line_1,
                'address_line_2' => $order->address->address_line_2,
                'city' => $order->address->city,
                'province' => $order->address->province,
                'postal_code' => $order->address->postal_code,
                'country' => $order->address->country,
            ],
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'variant_sku' => $item->variant_sku,
                'variant_size' => $item->variant_size,
                'variant_color' => $item->variant_color,
                'unit_price' => $item->unit_price,
                'quantity' => $item->quantity,
                'line_total' => $item->line_total,
            ])->values(),
            'payment' => [
                'provider' => $order->payment?->provider,
                'status' => $order->payment?->status->value,
                'amount' => $order->payment?->amount,
                'snap_url' => $snapUrl,
            ],
        ];
    }

    public function generateOrderNumber(): string
    {
        $prefix = 'LL-'.now()->format('Ym');
        $sequence = Order::query()->where('order_number', 'like', "{$prefix}-%")->count() + 1;

        do {
            $orderNumber = sprintf('%s-%04d', $prefix, $sequence);
            $sequence++;
        } while (Order::query()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function customer(array $data): Customer
    {
        return Customer::query()->updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'phone' => $data['phone'],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function address(Customer $customer, array $data): Address
    {
        return $customer->addresses()->create([
            'address_line_1' => $data['address_line_1'],
            'address_line_2' => $data['address_line_2'] ?? null,
            'city' => $data['city'],
            'province' => $data['province'],
            'postal_code' => $data['postal_code'],
            'country' => $data['country'] ?? 'ID',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createOrder(Request $request, Customer $customer, Address $address, array $data, Cart $cart): Order
    {
        $subtotal = $cart->items->sum(fn (CartItem $item) => $item->variant->price * $item->quantity);
        $shippingCost = 0;

        return Order::query()->create([
            'order_number' => $this->generateOrderNumber(),
            'user_id' => $request->user()?->id,
            'customer_id' => $customer->id,
            'address_id' => $address->id,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total' => $subtotal + $shippingCost,
            'notes' => $data['notes'] ?? null,
            'status' => OrderStatus::PendingPayment,
        ]);
    }

    private function createOrderItem(Order $order, CartItem $item): void
    {
        $variant = ProductVariant::query()
            ->with('product.category')
            ->lockForUpdate()
            ->findOrFail($item->product_variant_id);

        if (! $variant->is_active || ! $variant->product->is_active || ! $variant->product->category->is_active) {
            throw ValidationException::withMessages([
                'cart' => __('One of your cart items is no longer available.'),
            ]);
        }

        if ($variant->stock < $item->quantity) {
            throw ValidationException::withMessages([
                'cart' => __('Not enough stock for :product.', ['product' => $variant->product->name]),
            ]);
        }

        $orderItem = $order->items()->create([
            'product_variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'variant_sku' => $variant->sku,
            'variant_size' => $variant->size,
            'variant_color' => $variant->color,
            'unit_price' => $variant->price,
            'quantity' => $item->quantity,
            'line_total' => $variant->price * $item->quantity,
        ]);

        $variant->decrement('stock', $item->quantity);

        StockMovement::query()->create([
            'product_variant_id' => $variant->id,
            'quantity' => -$item->quantity,
            'type' => StockMovementType::Sale,
            'reference_type' => Order::class,
            'reference_id' => $order->id,
            'notes' => "Order {$order->order_number} item {$orderItem->id}",
        ]);
    }
}
