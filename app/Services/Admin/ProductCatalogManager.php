<?php

namespace App\Services\Admin;

use App\Enums\StockMovementType;
use App\Jobs\SyncStockAcrossChannelsJob;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProductCatalogManager
{
    /**
     * Create a product with its variants.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            $variants = $data['variants'];
            $product = Product::query()->create(Arr::except($data, ['variants']));

            foreach ($variants as $variantData) {
                $variant = $product->variants()->create(Arr::except($variantData, ['id']));
                $this->recordStockAdjustment($variant, $variant->stock, 'Initial admin stock');
                SyncStockAcrossChannelsJob::dispatch($variant->id, $variant->available_stock)->afterCommit();
            }

            return $product->refresh();
        });
    }

    /**
     * Update a product and reconcile its variants.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            $variants = $data['variants'];
            $submittedVariantIds = [];

            $product->update(Arr::except($data, ['variants']));

            foreach ($variants as $variantData) {
                $variantId = $variantData['id'] ?? null;
                $payload = Arr::except($variantData, ['id']);

                if ($variantId) {
                    $variant = $product->variants()->whereKey($variantId)->firstOrFail();
                    $previousStock = $variant->stock;
                    $variant->update($payload);
                    $difference = $variant->stock - $previousStock;
                    $this->recordStockAdjustment($variant, $difference, 'Admin stock update');
                    if ($difference !== 0) {
                        SyncStockAcrossChannelsJob::dispatch($variant->id, $variant->available_stock)->afterCommit();
                    }
                } else {
                    $variant = $product->variants()->create($payload);
                    $this->recordStockAdjustment($variant, $variant->stock, 'Initial admin stock');
                    SyncStockAcrossChannelsJob::dispatch($variant->id, $variant->available_stock)->afterCommit();
                }

                $submittedVariantIds[] = $variant->id;
            }

            $product->variants()
                ->whereNotIn('id', $submittedVariantIds)
                ->update(['is_active' => false]);

            return $product->refresh();
        });
    }

    private function recordStockAdjustment(ProductVariant $variant, int $quantity, string $notes): void
    {
        if ($quantity === 0) {
            return;
        }

        $variant->stockMovements()->create([
            'quantity' => $quantity,
            'type' => StockMovementType::Adjustment,
            'reference_type' => Product::class,
            'reference_id' => $variant->product_id,
            'notes' => $notes,
        ]);
    }
}
