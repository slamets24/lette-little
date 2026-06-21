<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    /**
     * Display the customer product catalog.
     */
    public function index(Request $request): Response
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
        ]);
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
        ]);
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
