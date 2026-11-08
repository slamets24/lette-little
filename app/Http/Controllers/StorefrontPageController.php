<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\Seo\FaqContent;
use App\Services\Seo\PageMeta;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontPageController extends Controller
{
    /**
     * Display the collection overview with an ItemList of the categories.
     */
    public function collection(): Response
    {
        $categories = Category::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Collection', [
            'meta' => PageMeta::make(
                title: 'Collection',
                description: 'Meet the Cloud Beginning collection: dresses, rompers, sets, and sleepwear built around soft, breathable fabric.',
                jsonLd: [
                    '@context' => 'https://schema.org',
                    '@type' => 'ItemList',
                    'name' => 'Collection',
                    'itemListElement' => $categories->values()->map(fn (Category $category, int $index): array => [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $category->name,
                        'url' => route('shop.category', $category->slug),
                    ])->all(),
                ],
            ),
        ]);
    }

    /**
     * Display the FAQ page from the shared FAQ content.
     */
    public function faq(): Response
    {
        return Inertia::render('Faq', [
            'faqs' => FaqContent::items(),
            'meta' => PageMeta::make(
                title: 'FAQ',
                description: 'Answers to the questions we hear most about Lette Little sizing, fabric, payment, and delivery.',
                jsonLd: FaqContent::jsonLd(),
            ),
        ]);
    }
}
