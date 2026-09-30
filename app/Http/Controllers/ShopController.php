<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\Seo\PageMeta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    /**
     * The return window stated on the Shipping & Returns page, in days.
     */
    public const RETURN_WINDOW_DAYS = 7;

    /**
     * Display the storefront home page.
     */
    public function home(): Response
    {
        $featuredProducts = Product::query()
            ->active()
            ->where('is_featured', true)
            ->whereHas('category', fn ($query) => $query->active())
            ->with([
                'category:id,name,slug',
                'primaryImage:id,product_id,path,alt_text',
                'variants' => fn ($query) => $query->active()->orderBy('size')->orderBy('color'),
            ])
            ->orderBy('name')
            ->limit(8)
            ->get();

        return Inertia::render('Welcome', [
            'featuredProducts' => $featuredProducts->map(
                fn (Product $product) => $this->presentProduct($product),
            ),
            'meta' => PageMeta::make(
                jsonLd: $this->homeJsonLd(),
                canonical: $this->homeUrl(),
            ),
        ]);
    }

    /**
     * Display the customer product catalog.
     */
    public function index(Request $request): Response
    {
        return $this->renderCatalog($request);
    }

    /**
     * Display the catalog preselected to a single active category.
     */
    public function category(Request $request, Category $category): Response
    {
        abort_unless($category->is_active, 404);

        return $this->renderCatalog($request, $category);
    }

    /**
     * Display a single customer product detail page.
     */
    public function show(Product $product): Response
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'category:id,name,slug,is_active',
            'images:id,product_id,path,alt_text,sort_order,is_primary',
            'variants' => fn ($query) => $query->active()->orderBy('size')->orderBy('color'),
        ]);

        abort_unless($product->category->is_active, 404);

        $relatedProducts = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with([
                'category:id,name,slug',
                'primaryImage:id,product_id,path,alt_text',
                'variants' => fn ($query) => $query->active()->orderBy('size')->orderBy('color'),
            ])
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->limit(4)
            ->get();

        return Inertia::render('Product', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'materials' => $product->materials,
                'care_instructions' => $product->care_instructions,
                'category' => [
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ],
                'images' => $product->images->map(fn ($image) => [
                    'id' => $image->id,
                    'path' => $image->path,
                    'alt_text' => $image->alt_text ?? $product->name,
                    'is_primary' => $image->is_primary,
                ])->values(),
                'variants' => $product->variants->map(fn ($variant) => [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'color_hex' => $variant->color_hex,
                    'price' => $variant->price,
                    'stock' => $variant->stock,
                    'is_available' => $variant->stock > 0,
                ])->values(),
            ],
            'relatedProducts' => $relatedProducts->map(fn (Product $product) => $this->presentProduct($product)),
            'meta' => PageMeta::make(
                title: $product->name,
                description: $product->description,
                image: $product->images->first()?->path,
                jsonLd: $this->productJsonLd($product),
            ),
        ]);
    }

    /**
     * Render the Shop page, optionally scoped to a category page.
     */
    private function renderCatalog(Request $request, ?Category $categoryPage = null): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'size' => ['nullable', 'string', 'max:24'],
            'in_stock' => ['nullable', 'boolean'],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0'],
        ]);

        $filters = [
            'q' => $filters['q'] ?? '',
            'category' => $filters['category'] ?? '',
            'size' => $filters['size'] ?? '',
            'in_stock' => $request->boolean('in_stock'),
            'min_price' => $filters['min_price'] ?? null,
            'max_price' => $filters['max_price'] ?? null,
        ];

        if ($categoryPage) {
            $filters['category'] = $categoryPage->slug;
        }

        $products = Product::query()
            ->active()
            ->whereHas('category', fn ($query) => $query->active())
            ->with([
                'category:id,name,slug',
                'primaryImage:id,product_id,path,alt_text',
                'variants' => fn ($query) => $query->active()->orderBy('size')->orderBy('color'),
            ])
            ->when($filters['q'], function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('materials', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'], function ($query, string $category) {
                $query->whereHas('category', fn ($query) => $query->where('slug', $category));
            })
            ->when($filters['size'], function ($query, string $size) {
                $query->whereHas('variants', fn ($query) => $query->active()->where('size', $size));
            })
            ->when($filters['in_stock'], function ($query) {
                $query->whereHas('variants', fn ($query) => $query->active()->where('stock', '>', 0));
            })
            ->when($filters['min_price'] !== null, function ($query) use ($filters) {
                $query->whereHas('variants', fn ($query) => $query->active()->where('price', '>=', $filters['min_price']));
            })
            ->when($filters['max_price'] !== null, function ($query) use ($filters) {
                $query->whereHas('variants', fn ($query) => $query->active()->where('price', '<=', $filters['max_price']));
            })
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->get();

        $categories = Category::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ]);

        $sizes = Product::query()
            ->active()
            ->whereHas('category', fn ($query) => $query->active())
            ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->where('product_variants.is_active', true)
            ->distinct()
            ->orderBy('product_variants.size')
            ->pluck('product_variants.size')
            ->values();

        return Inertia::render('Shop', [
            'categories' => $categories,
            'products' => $products->map(fn (Product $product) => $this->presentProduct($product)),
            'sizes' => $sizes,
            'filters' => $filters,
            'meta' => $this->catalogMeta($request, $products, $categoryPage),
        ]);
    }

    /**
     * Build the metadata for the shop listing or a category page.
     *
     * @param  Collection<int, Product>  $products
     * @return array<string, mixed>
     */
    private function catalogMeta(Request $request, Collection $products, ?Category $categoryPage): array
    {
        if ($categoryPage) {
            $name = $categoryPage->name;
            $canonical = route('shop.category', $categoryPage->slug);
            $lowercaseName = str($name)->lower()->value();

            return PageMeta::make(
                title: $name,
                description: $categoryPage->description
                    ?: "Shop Lette Little {$lowercaseName}: soft, breathable pieces for little ones, shipped across Indonesia.",
                jsonLd: $this->productListJsonLd($name, $canonical, $products),
                canonical: $canonical,
            );
        }

        $canonical = $this->shopCanonical($request);

        return PageMeta::make(
            title: 'Shop',
            description: 'Browse every Lette Little piece: calm, breathable dresses, rompers, sets, and sleepwear for newborn gifting and daily dressing.',
            jsonLd: $this->productListJsonLd('Shop', route('shop'), $products),
            canonical: $canonical,
        );
    }

    /**
     * Resolve the canonical URL of a /shop request.
     *
     * Filtered listings canonicalise to /shop, except a listing filtered by
     * category alone, which points to that category's own page.
     */
    private function shopCanonical(Request $request): string
    {
        $query = $request->query();

        if (array_keys($query) === ['category'] && is_string($query['category']) && $query['category'] !== '') {
            $category = Category::query()->active()->where('slug', $query['category'])->first();

            if ($category) {
                return route('shop.category', $category->slug);
            }
        }

        return route('shop');
    }

    /**
     * Build the schema.org ItemList for a product listing.
     *
     * @param  Collection<int, Product>  $products
     * @return array<string, mixed>
     */
    private function productListJsonLd(string $name, string $url, Collection $products): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $name,
            'url' => $url,
            'numberOfItems' => $products->count(),
            'itemListElement' => $products->values()->map(fn (Product $product, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $product->name,
                'url' => route('shop.show', $product),
            ])->all(),
        ];
    }

    /**
     * The homepage URL with its trailing slash.
     */
    private function homeUrl(): string
    {
        return rtrim(route('home'), '/').'/';
    }

    /**
     * Build the Organization and WebSite schema for the homepage.
     *
     * @return array<string, mixed>
     */
    private function homeJsonLd(): array
    {
        $homeUrl = $this->homeUrl();

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => $homeUrl.'#organization',
                    'name' => config('app.name'),
                    'url' => $homeUrl,
                    'description' => PageMeta::DEFAULT_DESCRIPTION,
                    'sameAs' => ['https://instagram.com/lettelittle.id'],
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'contactType' => 'customer service',
                        'email' => 'hello@lettelittle.id',
                        'areaServed' => 'ID',
                    ],
                    'areaServed' => 'ID',
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $homeUrl.'#website',
                    'name' => config('app.name'),
                    'url' => $homeUrl,
                    'publisher' => ['@id' => $homeUrl.'#organization'],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => [
                            '@type' => 'EntryPoint',
                            'urlTemplate' => route('shop').'?q={search_term_string}',
                        ],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ];
    }

    /**
     * Build the schema.org Product and BreadcrumbList graph for a product page.
     *
     * @return array<string, mixed>
     */
    private function productJsonLd(Product $product): array
    {
        $variants = $product->variants;
        $prices = $variants->pluck('price');
        $lowPrice = $prices->min() ?? $product->base_price;
        $highPrice = $prices->max() ?? $product->base_price;
        $inStock = $variants->sum('stock') > 0;
        $productUrl = route('shop.show', $product);

        $productNode = array_filter([
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $product->description,
            'category' => $product->category->name,
            'image' => $product->images->map(fn ($image) => url($image->path))->values()->all(),
            'sku' => $variants->first()?->sku,
            'brand' => [
                '@type' => 'Brand',
                'name' => config('app.name'),
            ],
            'itemCondition' => 'https://schema.org/NewCondition',
            'offers' => [
                '@type' => 'AggregateOffer',
                'url' => $productUrl,
                'lowPrice' => (string) $lowPrice,
                'highPrice' => (string) $highPrice,
                'offerCount' => max($variants->count(), 1),
                'priceCurrency' => 'IDR',
                'priceValidUntil' => now()->addYear()->toDateString(),
                'availability' => $inStock
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
            ],
            'hasMerchantReturnPolicy' => [
                '@type' => 'MerchantReturnPolicy',
                'applicableCountry' => 'ID',
                'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                'merchantReturnDays' => self::RETURN_WINDOW_DAYS,
                'returnMethod' => 'https://schema.org/ReturnByMail',
                'returnFees' => 'https://schema.org/ReturnFeesCustomerResponsibility',
                'merchantReturnLink' => route('shipping-returns'),
            ],
        ], fn ($value) => $value !== null);

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                $productNode,
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $this->homeUrl()],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Shop', 'item' => route('shop')],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => $product->category->name, 'item' => route('shop.category', $product->category->slug)],
                        ['@type' => 'ListItem', 'position' => 4, 'name' => $product->name, 'item' => $productUrl],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentProduct(Product $product): array
    {
        $variants = $product->variants;
        $stock = $variants->sum('stock');
        $price = $variants->min('price') ?? $product->base_price;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'category' => [
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ],
            'price' => $price,
            'href' => route('shop.show', $product),
            'image' => $product->primaryImage?->path ?? '/images/lette/stitch-product-dress.jpg',
            'swatches' => $variants->pluck('color_hex')->filter()->unique()->values(),
            'available_sizes' => $variants->where('stock', '>', 0)->pluck('size')->unique()->values(),
            'stock' => $stock,
            'stock_label' => match (true) {
                $stock === 0 => 'Sold out',
                $stock <= 3 => 'Low stock',
                default => 'In stock',
            },
            'is_low_stock' => $stock > 0 && $stock <= 3,
        ];
    }
}
