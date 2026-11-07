<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['product_id', 'sku', 'size', 'color', 'color_hex', 'price', 'stock', 'stock_reserved', 'is_active'];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * @return HasMany<MarketplaceMapping, $this>
     */
    public function marketplaceMappings(): HasMany
    {
        return $this->hasMany(MarketplaceMapping::class);
    }

    /**
     * Calculate available stock after deducting reserved buffer.
     */
    public function getAvailableStockAttribute(): int
    {
        return max(0, (int) $this->stock - (int) ($this->stock_reserved ?? 0));
    }

    /**
     * Get price with backward-compatible fallback to parent product base_price.
     */
    public function getPriceAttribute(?int $value): int
    {
        if ($value !== null && $value > 0) {
            return $value;
        }

        return (int) ($this->product?->base_price ?? $value ?? 0);
    }

    /**
     * @param  Builder<ProductVariant>  $query
     * @return Builder<ProductVariant>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('product_variants.is_active', true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
            'stock_reserved' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
