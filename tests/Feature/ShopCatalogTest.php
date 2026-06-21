<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShopCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_displays_seeded_catalog(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->get(route('shop'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Shop')
                ->has('categories', 4)
                ->has('products', 5)
                ->has('sizes')
                ->where('products.0.name', 'Cloud Dress')
                ->where('products.0.category.name', 'Dresses')
                ->where('products.0.price', 249000)
            );
    }

    public function test_shop_filters_by_category_and_size(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->get(route('shop', ['category' => 'sleepwear']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Shop')
                ->has('products', 1)
                ->where('products.0.name', 'Cloud Sleep Set')
                ->where('filters.category', 'sleepwear')
            );

        $this->get(route('shop', ['size' => '9-12M']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Shop')
                ->has('products', 1)
                ->where('products.0.name', 'Morning Set')
                ->where('filters.size', '9-12M')
            );
    }

    public function test_inactive_products_are_hidden_from_shop(): void
    {
        $this->seed(CatalogSeeder::class);

        Product::query()->where('slug', 'cloud-dress')->update(['is_active' => false]);

        $this->get(route('shop'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Shop')
                ->has('products', 4)
                ->where('products.0.name', 'Little Bloom Romper')
            );
    }

    public function test_product_detail_displays_active_product(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->get(route('shop.show', 'cloud-dress'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Product')
                ->where('product.name', 'Cloud Dress')
                ->where('product.category.name', 'Dresses')
                ->has('product.images', 1)
                ->has('product.variants', 3)
                ->has('relatedProducts', 1)
            );
    }

    public function test_cart_page_displays_checkout_shell(): void
    {
        $this->get(route('cart'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cart')
            );
    }

    public function test_inactive_product_detail_returns_not_found(): void
    {
        $this->seed(CatalogSeeder::class);

        Product::query()->where('slug', 'cloud-dress')->update(['is_active' => false]);

        $this->get(route('shop.show', 'cloud-dress'))->assertNotFound();
    }
}
