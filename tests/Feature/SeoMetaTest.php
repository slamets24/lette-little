<?php

namespace Tests\Feature;

use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_indexable_metadata(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('<title>'.config('app.name').'</title>', false)
            ->assertSee('<meta name="robots" content="index, follow">', false)
            ->assertSee('<link rel="canonical" href="'.route('home').'">', false)
            ->assertSee('name="description" content="Lette Little makes soft', false)
            ->assertSee('property="og:image"', false)
            ->assertSee('"@type":"Store"', false);
    }

    public function test_shop_renders_its_own_description(): void
    {
        $this->get(route('shop'))
            ->assertOk()
            ->assertSee('<title>Shop - '.config('app.name').'</title>', false)
            ->assertSee('Browse every Lette Little piece', false);
    }

    public function test_product_page_renders_product_structured_data(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->get(route('shop.show', 'cloud-dress'))
            ->assertOk()
            ->assertSee('<title>Cloud Dress - '.config('app.name').'</title>', false)
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"name":"Cloud Dress"', false)
            ->assertSee('"priceCurrency":"IDR"', false)
            ->assertSee('"availability":"https:\/\/schema.org\/InStock"', false);
    }

    public function test_static_storefront_pages_describe_themselves(): void
    {
        $this->get(route('size-guide'))
            ->assertOk()
            ->assertSee('<title>Size Guide - '.config('app.name').'</title>', false)
            ->assertSee('Find the right Lette Little size', false);
    }

    public function test_cart_and_checkout_are_not_indexed(): void
    {
        $this->get(route('cart'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_descriptions_are_trimmed_for_search_results(): void
    {
        $this->seed(CatalogSeeder::class);

        $response = $this->get(route('shop.show', 'cloud-dress'));

        preg_match('/<meta name="description" content="([^"]*)">/', $response->getContent(), $matches);

        $this->assertNotEmpty($matches, 'The product page is missing a meta description.');
        $this->assertLessThanOrEqual(160, mb_strlen(html_entity_decode($matches[1])));
    }
}
