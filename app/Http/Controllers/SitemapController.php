<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Route names of the indexable static storefront pages.
     *
     * @var list<string>
     */
    private const STATIC_ROUTES = [
        'shop',
        'collection',
        'about',
        'journal',
        'size-guide',
        'care-guide',
        'shipping-returns',
        'faq',
        'privacy',
        'terms',
    ];

    /**
     * Render the XML sitemap of every indexable storefront URL.
     */
    public function __invoke(): Response
    {
        $urls = [
            ['loc' => rtrim(route('home'), '/').'/', 'lastmod' => null],
        ];

        foreach (self::STATIC_ROUTES as $routeName) {
            $urls[] = ['loc' => route($routeName), 'lastmod' => null];
        }

        Category::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'slug', 'updated_at'])
            ->each(function (Category $category) use (&$urls): void {
                $urls[] = [
                    'loc' => route('shop.category', $category->slug),
                    'lastmod' => $category->updated_at?->toAtomString(),
                ];
            });

        Product::query()
            ->active()
            ->whereHas('category', fn ($query) => $query->active())
            ->orderBy('name')
            ->get(['id', 'slug', 'updated_at'])
            ->each(function (Product $product) use (&$urls): void {
                $urls[] = [
                    'loc' => route('shop.show', $product),
                    'lastmod' => $product->updated_at?->toAtomString(),
                ];
            });

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
