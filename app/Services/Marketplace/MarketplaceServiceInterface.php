<?php

namespace App\Services\Marketplace;

use App\Models\MarketplaceMapping;
use Illuminate\Http\Request;

interface MarketplaceServiceInterface
{
    /**
     * Get the marketplace identifier name.
     */
    public function getMarketplaceName(): string;

    /**
     * Update inventory/stock on the marketplace for a given mapping.
     */
    public function updateStock(MarketplaceMapping $mapping, int $stock): bool;

    /**
     * Verify the incoming webhook request signature.
     */
    public function verifyWebhookSignature(Request $request): bool;

    /**
     * Extract line items from the webhook payload.
     *
     * @return list<array{product_id: string|null, variant_id: string|null, sku: string|null, quantity: int}>
     */
    public function extractOrderItems(Request $request): array;
}
