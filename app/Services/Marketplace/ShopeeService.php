<?php

namespace App\Services\Marketplace;

use App\Models\MarketplaceMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopeeService implements MarketplaceServiceInterface
{
    private string $baseUrl;

    private ?string $partnerId;

    private ?string $partnerKey;

    private ?string $shopId;

    private ?string $accessToken;

    private ?string $webhookSecret;

    public function __construct()
    {
        $this->baseUrl = (string) config('marketplace.shopee.base_url', 'https://partner.shopeemobile.com');
        $this->partnerId = config('marketplace.shopee.partner_id') ? (string) config('marketplace.shopee.partner_id') : null;
        $this->partnerKey = config('marketplace.shopee.partner_key') ? (string) config('marketplace.shopee.partner_key') : null;
        $this->shopId = config('marketplace.shopee.shop_id') ? (string) config('marketplace.shopee.shop_id') : null;
        $this->accessToken = config('marketplace.shopee.access_token') ? (string) config('marketplace.shopee.access_token') : null;
        $this->webhookSecret = config('marketplace.shopee.webhook_secret') ? (string) config('marketplace.shopee.webhook_secret') : $this->partnerKey;
    }

    public function getMarketplaceName(): string
    {
        return 'shopee';
    }

    /**
     * Generate HMAC-SHA256 signature for Shopee Open API v2.
     */
    public function generateSign(string $path, int $timestamp, ?string $accessToken = null, ?string $shopId = null): string
    {
        if (! $this->partnerKey || ! $this->partnerId) {
            return '';
        }

        $baseString = sprintf('%s%s%d%s%s', $this->partnerId, $path, $timestamp, $accessToken ?? '', $shopId ?? '');

        return hash_hmac('sha256', $baseString, $this->partnerKey);
    }

    /**
     * Update stock on Shopee for the given variant mapping.
     */
    public function updateStock(MarketplaceMapping $mapping, int $stock): bool
    {
        if (! $this->partnerId || ! $this->partnerKey) {
            Log::warning('Shopee API credentials missing, skipping sync', [
                'mapping_id' => $mapping->id,
            ]);

            return false;
        }

        $path = '/api/v2/product/update_stock';
        $timestamp = time();
        $shopId = $this->shopId;
        $accessToken = $this->accessToken;

        $sign = $this->generateSign($path, $timestamp, $accessToken, $shopId);

        $url = sprintf('%s%s', rtrim($this->baseUrl, '/'), $path);

        $itemId = (int) $mapping->marketplace_product_id;
        $modelId = (int) $mapping->marketplace_variant_id;

        $payload = [
            'item_id' => $itemId,
            'stock_list' => [
                [
                    'model_id' => $modelId,
                    'normal_stock' => max(0, $stock),
                ],
            ],
        ];

        try {
            $response = Http::timeout(10)->post($url, $payload, [
                'partner_id' => $this->partnerId,
                'timestamp' => $timestamp,
                'access_token' => $accessToken,
                'shop_id' => $shopId,
                'sign' => $sign,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (empty($data['error'])) {
                    return true;
                }

                Log::error('Shopee stock update returned API error', [
                    'mapping_id' => $mapping->id,
                    'response' => $data,
                ]);

                return false;
            }

            Log::error('Shopee stock update HTTP failed', [
                'mapping_id' => $mapping->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Shopee stock update exception', [
                'mapping_id' => $mapping->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Verify incoming webhook signature from Shopee.
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $signature = $request->header('Authorization')
            ?? $request->header('X-Shopee-Signature')
            ?? $request->input('sign');

        if (! $signature) {
            return false;
        }

        $secret = $this->webhookSecret ?? $this->partnerKey;
        if (! $secret) {
            return false;
        }

        $content = $request->getContent();
        $fullUrl = $request->fullUrl();

        // Shopee standard: hash_hmac('sha256', url . '|' . content, secret)
        $expectedUrlPipeContent = hash_hmac('sha256', "{$fullUrl}|{$content}", $secret);
        if (hash_equals($expectedUrlPipeContent, (string) $signature)) {
            return true;
        }

        // Alternative Shopee format: hash_hmac('sha256', content, secret)
        $expectedContentOnly = hash_hmac('sha256', $content, $secret);

        return hash_equals($expectedContentOnly, (string) $signature);
    }

    /**
     * Extract order line items from Shopee webhook payload.
     *
     * @return list<array{product_id: string|null, variant_id: string|null, sku: string|null, quantity: int}>
     */
    public function extractOrderItems(Request $request): array
    {
        $data = $request->all();
        $items = [];

        // Direct item list or order detail data
        $itemList = $data['data']['item_list']
            ?? $data['item_list']
            ?? $data['data']['items']
            ?? [];

        foreach ($itemList as $item) {
            $items[] = [
                'product_id' => isset($item['item_id']) ? (string) $item['item_id'] : null,
                'variant_id' => isset($item['model_id']) ? (string) $item['model_id'] : null,
                'sku' => $item['model_sku'] ?? $item['item_sku'] ?? null,
                'quantity' => (int) ($item['model_quantity_purchased'] ?? $item['quantity'] ?? 1),
            ];
        }

        return $items;
    }
}
