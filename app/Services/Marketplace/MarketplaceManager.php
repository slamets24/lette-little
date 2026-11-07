<?php

namespace App\Services\Marketplace;

use InvalidArgumentException;

class MarketplaceManager
{
    /**
     * @param  array<string, MarketplaceServiceInterface>  $services
     */
    public function __construct(
        private readonly ShopeeService $shopeeService,
        private readonly TikTokService $tiktokService,
    ) {}

    /**
     * Resolve the marketplace service adapter by marketplace name.
     */
    public function driver(string $marketplace): MarketplaceServiceInterface
    {
        return match (strtolower($marketplace)) {
            'shopee' => $this->shopeeService,
            'tiktok' => $this->tiktokService,
            default => throw new InvalidArgumentException("Unsupported marketplace [{$marketplace}]."),
        };
    }

    /**
     * Get all supported marketplace services.
     *
     * @return array<string, MarketplaceServiceInterface>
     */
    public function all(): array
    {
        return [
            'shopee' => $this->shopeeService,
            'tiktok' => $this->tiktokService,
        ];
    }
}
