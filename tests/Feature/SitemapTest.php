<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_is_served_as_xml(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringStartsWith('application/xml', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $response->getContent());
        $this->assertNotFalse(simplexml_load_string($response->getContent()));
    }

    public function test_sitemap_lists_every_indexable_storefront_url(): void
    {
        $this->seed(CatalogSeeder::class);

        $locations = $this->sitemapLocations();

        $expected = [
            rtrim(route('home'), '/').'/',
            route('shop'),
            route('collection'),
            route('about'),
            route('journal'),
            route('size-guide'),
            route('care-guide'),
            route('shipping-returns'),
            route('faq'),
            route('privacy'),
            route('terms'),
        ];

        foreach ($expected as $url) {
            $this->assertContains($url, $locations);
        }

        Category::query()->active()->each(
            fn (Category $category) => $this->assertContains(route('shop.category', $category->slug), $locations),
        );

        Product::query()->active()->each(
            fn (Product $product) => $this->assertContains(route('shop.show', $product), $locations),
        );
    }

    public function test_product_entries_use_updated_at_as_lastmod(): void
    {
        $this->seed(CatalogSeeder::class);
        $product = Product::query()->where('slug', 'cloud-dress')->firstOrFail();

        $this->get('/sitemap.xml')
            ->assertSee('<loc>'.route('shop.show', $product).'</loc>', false)
            ->assertSee('<lastmod>'.$product->updated_at->toAtomString().'</lastmod>', false);
    }

    public function test_sitemap_excludes_inactive_products_and_categories(): void
    {
        $this->seed(CatalogSeeder::class);
        Product::query()->where('slug', 'cloud-dress')->update(['is_active' => false]);
        $category = Category::query()->where('slug', 'rompers')->firstOrFail();
        $category->update(['is_active' => false]);
        $rompers = Product::query()->where('category_id', $category->id)->get();

        $locations = $this->sitemapLocations();

        $this->assertNotContains(route('shop.show', 'cloud-dress'), $locations);
        $this->assertNotContains(route('shop.category', 'rompers'), $locations);

        foreach ($rompers as $product) {
            $this->assertNotContains(route('shop.show', $product), $locations);
        }
    }

    public function test_sitemap_excludes_private_pages(): void
    {
        $locations = $this->sitemapLocations();

        foreach (['cart', 'checkout', 'orders', 'login', 'register', 'dashboard', 'admin', 'settings'] as $path) {
            $this->assertNotContains(url($path), $locations);
        }

        foreach ($locations as $location) {
            $this->assertDoesNotMatchRegularExpression('#/(cart|checkout|orders|admin|settings|login|register|dashboard)(/|$)#', $location);
        }
    }

    /**
     * @return list<string>
     */
    private function sitemapLocations(): array
    {
        $xml = simplexml_load_string($this->get('/sitemap.xml')->assertOk()->getContent());

        $locations = [];

        foreach ($xml->url as $url) {
            $locations[] = (string) $url->loc;
        }

        return $locations;
    }
}
