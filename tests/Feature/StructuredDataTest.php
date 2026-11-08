<?php

namespace Tests\Feature;

use App\Http\Controllers\ShopController;
use App\Models\Category;
use App\Models\Product;
use App\Services\Seo\FaqContent;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StructuredDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_organization_and_website_graph(): void
    {
        $jsonLd = $this->jsonLd($this->get(route('home')));
        $homeUrl = rtrim(route('home'), '/').'/';

        $organization = $this->node($jsonLd, 'Organization');
        $website = $this->node($jsonLd, 'WebSite');

        $this->assertSame(config('app.name'), $organization['name']);
        $this->assertSame($homeUrl, $organization['url']);
        $this->assertSame(['https://instagram.com/lettelittle.id'], $organization['sameAs']);
        $this->assertSame('hello@lettelittle.id', $organization['contactPoint']['email']);
        $this->assertSame('ID', $organization['areaServed']);
        $this->assertArrayNotHasKey('logo', $organization);

        $this->assertSame('SearchAction', $website['potentialAction']['@type']);
        $this->assertSame(route('shop').'?q={search_term_string}', $website['potentialAction']['target']['urlTemplate']);
        $this->assertSame('required name=search_term_string', $website['potentialAction']['query-input']);

        $this->assertNull($this->node($jsonLd, 'Store', required: false));
    }

    public function test_product_page_renders_rich_product_schema(): void
    {
        Carbon::setTestNow('2026-09-30 10:00:00');
        $this->seed(CatalogSeeder::class);
        $product = Product::query()->where('slug', 'cloud-dress')->firstOrFail();
        $variants = $product->variants()->where('is_active', true)->orderBy('size')->orderBy('color')->get();

        $node = $this->node($this->jsonLd($this->get(route('shop.show', 'cloud-dress'))), 'Product');

        $this->assertSame(['@type' => 'Brand', 'name' => config('app.name')], $node['brand']);
        $this->assertSame($variants->first()->sku, $node['sku']);
        $this->assertSame('https://schema.org/NewCondition', $node['itemCondition']);

        $offers = $node['offers'];
        $this->assertSame('AggregateOffer', $offers['@type']);
        $this->assertSame((string) $variants->min('price'), $offers['lowPrice']);
        $this->assertSame((string) $variants->max('price'), $offers['highPrice']);
        $this->assertSame($variants->count(), $offers['offerCount']);
        $this->assertSame('IDR', $offers['priceCurrency']);
        $this->assertSame('https://schema.org/InStock', $offers['availability']);
        $this->assertSame(route('shop.show', 'cloud-dress'), $offers['url']);
        $this->assertSame('2027-09-30', $offers['priceValidUntil']);

        $policy = $node['hasMerchantReturnPolicy'];
        $this->assertSame('MerchantReturnPolicy', $policy['@type']);
        $this->assertSame(7, ShopController::RETURN_WINDOW_DAYS);
        $this->assertSame(ShopController::RETURN_WINDOW_DAYS, $policy['merchantReturnDays']);
        $this->assertSame('https://schema.org/MerchantReturnFiniteReturnWindow', $policy['returnPolicyCategory']);
        $this->assertSame('ID', $policy['applicableCountry']);
    }

    public function test_return_window_matches_the_shipping_and_returns_page(): void
    {
        $page = file_get_contents(resource_path('js/pages/ShippingReturns.vue'));

        $this->assertMatchesRegularExpression('/within\s+'.ShopController::RETURN_WINDOW_DAYS.'\s+days of delivery/', $page);
    }

    public function test_sold_out_product_offer_is_out_of_stock(): void
    {
        $this->seed(CatalogSeeder::class);
        $product = Product::query()->where('slug', 'cloud-dress')->firstOrFail();
        $product->variants()->update(['stock' => 0]);

        $node = $this->node($this->jsonLd($this->get(route('shop.show', 'cloud-dress'))), 'Product');

        $this->assertSame('https://schema.org/OutOfStock', $node['offers']['availability']);
    }

    public function test_product_page_renders_breadcrumbs(): void
    {
        $this->seed(CatalogSeeder::class);
        $category = Product::query()->where('slug', 'cloud-dress')->firstOrFail()->category;

        $breadcrumbs = $this->node($this->jsonLd($this->get(route('shop.show', 'cloud-dress'))), 'BreadcrumbList');

        $this->assertSame([
            [1, 'Home', rtrim(route('home'), '/').'/'],
            [2, 'Shop', route('shop')],
            [3, $category->name, route('shop.category', $category->slug)],
            [4, 'Cloud Dress', route('shop.show', 'cloud-dress')],
        ], array_map(fn (array $item) => [$item['position'], $item['name'], $item['item']], $breadcrumbs['itemListElement']));
    }

    public function test_faq_page_renders_faqs_from_the_shared_source(): void
    {
        $response = $this->get(route('faq'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Faq')
                ->where('faqs', FaqContent::items())
            );

        $faqPage = $this->node($this->jsonLd($response), 'FAQPage');

        $this->assertCount(count(FaqContent::items()), $faqPage['mainEntity']);

        foreach (FaqContent::items() as $index => $item) {
            $this->assertSame($item['question'], $faqPage['mainEntity'][$index]['name']);
            $this->assertSame(FaqContent::plainAnswer($item['answer']), $faqPage['mainEntity'][$index]['acceptedAnswer']['text']);
        }
    }

    public function test_faq_answers_keep_their_guide_links(): void
    {
        $hrefs = collect(FaqContent::items())
            ->flatMap(fn (array $item) => $item['answer'])
            ->filter(fn (array $part) => ($part['internal'] ?? false) === true)
            ->pluck('href')
            ->all();

        $this->assertContains(route('size-guide', absolute: false), $hrefs);
        $this->assertContains(route('care-guide', absolute: false), $hrefs);
        $this->assertContains(route('shipping-returns', absolute: false), $hrefs);
        $this->assertStringContainsString('size guide', FaqContent::plainAnswer(FaqContent::items()[0]['answer']));
    }

    public function test_shop_renders_item_list_of_listed_products(): void
    {
        $this->seed(CatalogSeeder::class);

        $response = $this->get(route('shop', ['category' => 'dresses', 'size' => '0-3M']));
        $listed = $response->viewData('page')['props']['products'];

        $itemList = $this->node($this->jsonLd($response), 'ItemList');

        $this->assertSame(count($listed), $itemList['numberOfItems']);
        $this->assertSame(
            collect($listed)->pluck('href')->all(),
            array_column($itemList['itemListElement'], 'url'),
        );
        $this->assertSame(1, $itemList['itemListElement'][0]['position']);
    }

    public function test_collection_renders_item_list_of_category_pages(): void
    {
        $this->seed(CatalogSeeder::class);

        $itemList = $this->node($this->jsonLd($this->get(route('collection'))), 'ItemList');
        $categories = Category::query()->active()->orderBy('sort_order')->get();

        $this->assertCount(4, $itemList['itemListElement']);
        $this->assertSame(
            $categories->map(fn (Category $category) => route('shop.category', $category->slug))->all(),
            array_column($itemList['itemListElement'], 'url'),
        );
    }

    /**
     * Decode the JSON-LD script rendered in the page head.
     *
     * @return array<string, mixed>
     */
    private function jsonLd(TestResponse $response): array
    {
        $response->assertOk();

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $response->getContent(), $matches);

        $this->assertNotEmpty($matches, 'The page is missing JSON-LD.');

        return json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Find a node of the given type in a JSON-LD document or graph.
     *
     * @param  array<string, mixed>  $jsonLd
     * @return array<string, mixed>|null
     */
    private function node(array $jsonLd, string $type, bool $required = true): ?array
    {
        $nodes = $jsonLd['@graph'] ?? [$jsonLd];

        foreach ($nodes as $node) {
            if (($node['@type'] ?? null) === $type) {
                return $node;
            }
        }

        if ($required) {
            $this->fail("The JSON-LD has no {$type} node.");
        }

        return null;
    }
}
