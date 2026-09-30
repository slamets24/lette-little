<?php

namespace Tests\Feature;

use App\Models\User;
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
            ->assertSee('<link rel="canonical" href="'.rtrim(route('home'), '/').'/">', false)
            ->assertSee('name="description" content="Lette Little makes soft', false)
            ->assertSee('property="og:image"', false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"@type":"WebSite"', false);
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

    public function test_shop_canonicalises_filtered_listings_to_shop(): void
    {
        $this->seed(CatalogSeeder::class);

        $queries = [
            [],
            ['q' => 'dress'],
            ['size' => '0-3M'],
            ['category' => 'dresses', 'size' => '0-3M'],
            ['category' => 'unknown'],
        ];

        foreach ($queries as $query) {
            $this->get(route('shop', $query))
                ->assertOk()
                ->assertSee('<link rel="canonical" href="'.route('shop').'">', false);
        }
    }

    public function test_shop_filtered_by_category_only_canonicalises_to_the_category_page(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->get(route('shop', ['category' => 'dresses']))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('shop.category', 'dresses').'">', false)
            ->assertSee('<meta property="og:url" content="'.route('shop.category', 'dresses').'">', false);
    }

    public function test_pages_without_explicit_canonical_use_the_current_url_without_query(): void
    {
        $this->get(route('size-guide', ['utm_source' => 'newsletter']))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('size-guide').'">', false);
    }

    public function test_login_and_register_are_noindexed_with_their_own_titles(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<title>Log in - '.config('app.name').'</title>', false)
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('<title>Register - '.config('app.name').'</title>', false)
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_guest_auth_pages_are_noindexed(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->get(route('password.reset', 'token'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_authenticated_account_pages_are_noindexed(): void
    {
        $this->actingAs(User::factory()->unverified()->create())
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $user = User::factory()->create();

        foreach (['password.confirm', 'profile.edit', 'appearance.edit'] as $routeName) {
            $this->actingAs($user)
                ->get(route($routeName))
                ->assertOk()
                ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        }
    }

    public function test_admin_pages_are_noindexed(): void
    {
        $admin = User::factory()->admin()->create();

        foreach (['dashboard', 'admin.orders.index', 'admin.products.index'] as $routeName) {
            $this->actingAs($admin)
                ->get(route($routeName))
                ->assertOk()
                ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        }
    }
}
