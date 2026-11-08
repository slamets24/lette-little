<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShopCategoryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_page_renders_shop_with_category_preselected(): void
    {
        $this->seed(CatalogSeeder::class);
        $category = Category::query()->where('slug', 'dresses')->firstOrFail();
        $expectedCount = Product::query()->active()->where('category_id', $category->id)->count();

        $this->get(route('shop.category', 'dresses'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Shop')
                ->where('filters.category', 'dresses')
                ->has('products', $expectedCount)
                ->where('products.0.category.slug', 'dresses')
                ->has('categories')
                ->has('sizes')
            );
    }

    public function test_category_page_url_is_not_captured_as_a_product_slug(): void
    {
        $this->assertSame(url('/shop/category/dresses'), route('shop.category', 'dresses'));

        $this->seed(CatalogSeeder::class);

        $this->get('/shop/category/dresses')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Shop'));
    }

    public function test_category_page_has_its_own_metadata(): void
    {
        $this->seed(CatalogSeeder::class);
        $category = Category::query()->where('slug', 'dresses')->firstOrFail();

        $this->get(route('shop.category', 'dresses'))
            ->assertSee('<title>Dresses - '.config('app.name').'</title>', false)
            ->assertSee('<meta name="description" content="'.e($category->description).'">', false)
            ->assertSee('<link rel="canonical" href="'.route('shop.category', 'dresses').'">', false)
            ->assertSee('<meta name="robots" content="index, follow">', false)
            ->assertSee('"@type":"ItemList"', false)
            ->assertSee('"url":"'.str_replace('/', '\/', route('shop.show', 'cloud-dress')).'"', false);
    }

    public function test_category_page_generates_a_description_when_the_category_has_none(): void
    {
        Category::query()->create([
            'name' => 'Accessories',
            'slug' => 'accessories',
            'description' => null,
            'sort_order' => 50,
            'is_active' => true,
        ]);

        $this->get(route('shop.category', 'accessories'))
            ->assertOk()
            ->assertSee('<meta name="description" content="Shop Lette Little accessories: soft, breathable pieces for little ones, shipped across Indonesia.">', false);
    }

    public function test_category_page_keeps_its_category_and_self_canonical_when_filtered(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->get(route('shop.category', ['category' => 'dresses', 'size' => '0-3M']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.category', 'dresses')
                ->where('filters.size', '0-3M')
            )
            ->assertSee('<link rel="canonical" href="'.route('shop.category', 'dresses').'">', false);
    }

    public function test_inactive_category_page_returns_not_found(): void
    {
        $this->seed(CatalogSeeder::class);
        Category::query()->where('slug', 'dresses')->update(['is_active' => false]);

        $this->get(route('shop.category', 'dresses'))->assertNotFound();
    }

    public function test_unknown_category_page_returns_not_found(): void
    {
        $this->get('/shop/category/does-not-exist')->assertNotFound();
    }
}
