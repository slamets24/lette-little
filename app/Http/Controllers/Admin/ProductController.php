<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\Admin\ProductCatalogManager;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display the admin product list.
     */
    public function index(): Response
    {
        $products = Product::query()
            ->with('category:id,name,slug')
            ->withCount('variants')
            ->withSum('variants as total_stock', 'stock')
            ->orderByDesc('updated_at')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/products/Index', [
            'products' => $products->map(fn (Product $product) => $this->presentProduct($product)),
            'stats' => [
                'products' => $products->count(),
                'active_products' => $products->where('is_active', true)->count(),
                'total_stock' => $products->sum(fn (Product $product) => (int) ($product->total_stock ?? 0)),
            ],
        ]);
    }

    /**
     * Show the create product form.
     */
    public function create(): Response
    {
        return Inertia::render('admin/products/Form', [
            'categories' => $this->categoryOptions(),
            'product' => null,
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request, ProductCatalogManager $products): RedirectResponse
    {
        $product = $products->create($request->validated());

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Product created.');
    }

    /**
     * Show the edit product form.
     */
    public function edit(Product $product): Response
    {
        $product->load([
            'images:id,product_id,path,alt_text,sort_order,is_primary',
            'variants' => fn ($query) => $query->orderBy('size')->orderBy('color'),
        ]);

        return Inertia::render('admin/products/Form', [
            'categories' => $this->categoryOptions(),
            'product' => $this->presentProductDetail($product),
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, Product $product, ProductCatalogManager $products): RedirectResponse
    {
        $product = $products->update($product, $request->validated());

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Product updated.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function categoryOptions(): array
    {
        return Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function presentProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'category' => [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ],
            'variants_count' => $product->variants_count,
            'total_stock' => (int) ($product->total_stock ?? 0),
            'is_active' => $product->is_active,
            'is_featured' => $product->is_featured,
            'created_at' => $product->created_at?->toIso8601String(),
            'updated_at' => $product->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentProductDetail(Product $product): array
    {
        return [
            'id' => $product->id,
            'category_id' => $product->category_id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'materials' => $product->materials,
            'care_instructions' => $product->care_instructions,
            'base_price' => $product->base_price,
            'is_active' => $product->is_active,
            'is_featured' => $product->is_featured,
            'images' => $product->images->map(fn ($image) => [
                'id' => $image->id,
                'product_id' => $image->product_id,
                'path' => $image->path,
                'alt_text' => $image->alt_text,
                'sort_order' => $image->sort_order,
                'is_primary' => $image->is_primary,
            ])->values(),
            'variants' => $product->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'product_id' => $variant->product_id,
                'sku' => $variant->sku,
                'size' => $variant->size,
                'color' => $variant->color,
                'color_hex' => $variant->color_hex,
                'price' => $variant->price,
                'stock' => $variant->stock,
                'is_active' => $variant->is_active,
            ])->values(),
            'created_at' => $product->created_at?->toIso8601String(),
            'updated_at' => $product->updated_at?->toIso8601String(),
        ];
    }
}
