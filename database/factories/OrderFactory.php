<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(100_000, 500_000);
        $shippingCost = fake()->numberBetween(0, 50_000);
        $customer = Customer::factory();

        return [
            'order_number' => 'LL-'.now()->format('Ym').'-'.fake()->unique()->numberBetween(1000, 9999),
            'customer_id' => $customer,
            'address_id' => Address::factory()->for($customer),
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total' => $subtotal + $shippingCost,
            'notes' => fake()->optional()->sentence(),
            'status' => OrderStatus::PendingPayment,
        ];
    }
}
