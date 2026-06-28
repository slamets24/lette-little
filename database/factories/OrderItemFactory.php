<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productName = fake()->words(2, true);
        $size = fake()->randomElement(['0-3M', '3-6M', '6-9M', '9-12M']);
        $color = fake()->safeColorName();
        $unitPrice = fake()->numberBetween(150_000, 350_000);
        $quantity = fake()->numberBetween(1, 3);

        return [
            'order_id' => Order::factory(),
            'product_variant_id' => fn () => $this->variant($productName, $size, $color, $unitPrice)->id,
            'product_name' => $productName,
            'variant_sku' => 'LL-'.Str::upper(fake()->unique()->bothify('???-###')),
            'variant_size' => $size,
            'variant_color' => $color,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $unitPrice * $quantity,
        ];
    }

    private function variant(string $productName, string $size, string $color, int $unitPrice): ProductVariant
    {
        $category = Category::query()->create([
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => $productName,
            'slug' => fake()->unique()->slug(),
            'base_price' => $unitPrice,
            'is_active' => true,
        ]);

        return ProductVariant::query()->create([
            'product_id' => $product->id,
            'sku' => 'LL-'.Str::upper(fake()->unique()->bothify('???-###')),
            'size' => $size,
            'color' => $color,
            'price' => $unitPrice,
            'stock' => 10,
            'is_active' => true,
        ]);
    }
}
