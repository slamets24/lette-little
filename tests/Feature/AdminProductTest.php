<?php

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_admin_products(): void
    {
        $this->get(route('admin.products.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_view_admin_products(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_admin_users_can_view_admin_products(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/products/Index')
                ->has('products', 5)
                ->where('stats.products', 5)
                ->where('stats.active_products', 5)
            );
    }

    public function test_admin_users_can_create_products_with_variants(): void
    {
        $category = Category::query()->create([
            'name' => 'Sets',
            'slug' => 'sets',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), $this->payload([
                'category_id' => $category->id,
            ]));

        $product = Product::query()->where('slug', 'linen-baby-set')->firstOrFail();
        $variant = $product->variants()->firstOrFail();

        $response->assertRedirect(route('admin.products.edit', $product));
        $this->assertModelExists($product);
        $this->assertSame('LL-LBS-03-CRM', $variant->sku);
        $this->assertSame(12, $variant->stock);
        $this->assertTrue($product->is_active);
        $this->assertSame(1, StockMovement::query()
            ->where('product_variant_id', $variant->id)
            ->where('type', StockMovementType::Adjustment->value)
            ->where('quantity', 12)
            ->count());
    }

    public function test_admin_users_can_update_products_and_stock(): void
    {
        $category = Category::query()->create([
            'name' => 'Dresses',
            'slug' => 'dresses',
            'sort_order' => 10,
            'is_active' => true,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Cloud Dress',
            'slug' => 'cloud-dress',
            'base_price' => 249000,
            'is_active' => true,
            'is_featured' => false,
        ]);
        $keptVariant = $product->variants()->create([
            'sku' => 'LL-CDR-03-IVY',
            'size' => '0-3M',
            'color' => 'Ivory',
            'color_hex' => '#f5eadc',
            'price' => 249000,
            'stock' => 4,
            'is_active' => true,
        ]);
        $removedVariant = $product->variants()->create([
            'sku' => 'LL-CDR-36-IVY',
            'size' => '3-6M',
            'color' => 'Ivory',
            'color_hex' => '#f5eadc',
            'price' => 249000,
            'stock' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.products.update', $product), $this->payload([
                'category_id' => $category->id,
                'name' => 'Cloud Dress Updated',
                'slug' => 'cloud-dress-updated',
                'variants' => [
                    [
                        'id' => $keptVariant->id,
                        'sku' => 'LL-CDR-03-IVY',
                        'size' => '0-3M',
                        'color' => 'Ivory',
                        'color_hex' => '#f5eadc',
                        'price' => 259000,
                        'stock' => 9,
                        'is_active' => true,
                    ],
                    [
                        'sku' => 'LL-CDR-69-SND',
                        'size' => '6-9M',
                        'color' => 'Sand',
                        'color_hex' => '#d8c2a9',
                        'price' => 269000,
                        'stock' => 3,
                        'is_active' => true,
                    ],
                ],
            ]));

        $product->refresh();
        $keptVariant->refresh();
        $removedVariant->refresh();

        $response->assertRedirect(route('admin.products.edit', $product));
        $this->assertSame('Cloud Dress Updated', $product->name);
        $this->assertSame(9, $keptVariant->stock);
        $this->assertFalse($removedVariant->is_active);
        $this->assertSame(3, $product->variants()->count());
        $this->assertSame(2, $product->variants()->where('is_active', true)->count());
        $this->assertSame(1, StockMovement::query()
            ->where('product_variant_id', $keptVariant->id)
            ->where('quantity', 5)
            ->count());
    }

    public function test_product_skus_must_be_unique(): void
    {
        $category = Category::query()->create([
            'name' => 'Rompers',
            'slug' => 'rompers',
            'sort_order' => 10,
            'is_active' => true,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Existing Romper',
            'slug' => 'existing-romper',
            'base_price' => 199000,
            'is_active' => true,
            'is_featured' => false,
        ]);
        $product->variants()->create([
            'sku' => 'LL-EXISTING',
            'size' => '0-3M',
            'color' => 'Ivory',
            'price' => 199000,
            'stock' => 2,
            'is_active' => true,
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $this->payload([
                'category_id' => $category->id,
                'variants' => [
                    [
                        'sku' => 'LL-EXISTING',
                        'size' => '3-6M',
                        'color' => 'Oat',
                        'color_hex' => '#efe2d2',
                        'price' => 209000,
                        'stock' => 5,
                        'is_active' => true,
                    ],
                ],
            ]))
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('variants.0.sku');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'category_id' => null,
            'name' => 'Linen Baby Set',
            'slug' => 'linen-baby-set',
            'description' => 'A light coordinated set for warm days.',
            'materials' => 'Cotton linen blend.',
            'care_instructions' => 'Machine wash cold.',
            'base_price' => 299000,
            'is_active' => true,
            'is_featured' => false,
            'variants' => [
                [
                    'sku' => 'LL-LBS-03-CRM',
                    'size' => '0-3M',
                    'color' => 'Cream',
                    'color_hex' => '#f0dfca',
                    'price' => 299000,
                    'stock' => 12,
                    'is_active' => true,
                ],
            ],
        ], $overrides);
    }
}
