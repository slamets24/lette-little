<?php

namespace App\Services\Marketplace;

use App\Models\MarketplaceMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TikTokService implements MarketplaceServiceInterface
{
    private string $baseUrl;

    private ?string $appKey;

    private ?string $appSecret;

    private ?string $shopCipher;

    private ?string $accessToken;

    private ?string $webhookSecret;

    public function __construct()
    {
        $this->baseUrl = (string) config('marketplace.tiktok.base_url', 'https://open-api.tiktokglobalshop.com');
        $this->appKey = config('marketplace.tiktok.app_key') ? (string) config('marketplace.tiktok.app_key') : null;
        $this->appSecret = config('marketplace.tiktok.app_secret') ? (string) config('marketplace.tiktok.app_secret') : null;
        $this->shopCipher = config('marketplace.tiktok.shop_cipher') ? (string) config('marketplace.tiktok.shop_cipher') : null;
        $this->accessToken = config('marketplace.tiktok.access_token') ? (string) config('marketplace.tiktok.access_token') : null;
        $this->webhookSecret = config('marketplace.tiktok.webhook_secret') ? (string) config('marketplace.tiktok.webhook_secret') : $this->appSecret;
    }

    public function getMarketplaceName(): string
    {
        return 'tiktok';
    }

    /**
     * Generate HMAC-SHA256 signature for TikTok Shop Open API.
     *
     * @param  array<string, mixed>  $params
     */
    public function generateSign(string $path, array $params, string $body = ''): string
    {
        if (! $this->appSecret) {
            return '';
        }

        // Exclude sign and access_token from signature calculation
        unset($params['sign'], $params['access_token']);
        ksort($params);

        $paramString = '';
        foreach ($params as $key => $val) {
            $paramString .= "{$key}{$val}";
        }

        $baseString = sprintf('%s%s%s%s%s', $this->appSecret, $path, $paramString, $body, $this->appSecret);

        return hash_hmac('sha256', $baseString, $this->appSecret);
    }

    /**
     * Update stock on TikTok Shop for the given variant mapping.
     */
    public function updateStock(MarketplaceMapping $mapping, int $stock): bool
    {
        if (! $this->appKey || ! $this->appSecret) {
            Log::warning('TikTok Shop API credentials missing, skipping sync', [
                'mapping_id' => $mapping->id,
            ]);

            return false;
        }

        $productId = $mapping->marketplace_product_id;
        $skuId = $mapping->marketplace_variant_id;
        $path = "/product/202309/products/{$productId}/inventory/update";
        $timestamp = time();

        $params = [
            'app_key' => $this->appKey,
            'timestamp' => (string) $timestamp,
        ];

        if ($this->shopCipher) {
            $params['shop_cipher'] = $this->shopCipher;
        }

        $payload = [
            'skus' => [
                [
                    'id' => $skuId,
                    'inventory' => [
                        [
                            'quantity' => max(0, $stock),
                        ],
                    ],
                ],
            ],
        ];

        $jsonBody = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '';
        $params['sign'] = $this->generateSign($path, $params, $jsonBody);

        $url = sprintf('%s%s?%s', rtrim($this->baseUrl, '/'), $path, http_build_query($params));

        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ($this->accessToken) {
            $headers['x-tts-access-token'] = $this->accessToken;
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->withBody($jsonBody, 'application/json')
                ->post($url);

            if ($response->successful()) {
                $data = $response->json();
                $code = $data['code'] ?? null;
                if ($code === 0 || $code === null) {
                    return true;
                }

                Log::error('TikTok Shop stock update returned API error', [
                    'mapping_id' => $mapping->id,
                    'response' => $data,
                ]);

                return false;
            }

            Log::error('TikTok Shop stock update HTTP failed', [
                'mapping_id' => $mapping->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('TikTok Shop stock update exception', [
                'mapping_id' => $mapping->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Verify incoming webhook signature from TikTok Shop.
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $signature = $request->header('Authorization')
            ?? $request->header('X-Tts-Signature')
            ?? $request->header('signature')
            ?? $request->input('sign');

        if (! $signature) {
            return false;
        }

        $secret = $this->webhookSecret ?? $this->appSecret;
        if (! $secret) {
            return false;
        }

        $content = $request->getContent();

        // Standard TikTok webhook verification:
        // hash_hmac('sha256', appSecret . content . appSecret, appSecret) or hash_hmac('sha256', content, secret)
        $expectedWrapped = hash_hmac('sha256', "{$secret}{$content}{$secret}", $secret);
        if (hash_equals($expectedWrapped, (string) $signature)) {
            return true;
        }

        $expectedDirect = hash_hmac('sha256', $content, $secret);

        return hash_equals($expectedDirect, (string) $signature);
    }

    /**
     * Extract line items from TikTok webhook payload.
     *
     * @return list<array{product_id: string|null, variant_id: string|null, sku: string|null, quantity: int}>
     */
    public function extractOrderItems(Request $request): array
    {
        $data = $request->all();
        $items = [];

        $lineList = $data['data']['order_line_list']
            ?? $data['data']['order_lines']
            ?? $data['order_line_list']
            ?? $data['data']['skus']
            ?? [];

        foreach ($lineList as $line) {
            $items[] = [
                'product_id' => isset($line['product_id']) ? (string) $line['product_id'] : null,
                'variant_id' => isset($line['sku_id']) ? (string) $line['sku_id'] : (isset($line['id']) ? (string) $line['id'] : null),
                'sku' => $line['seller_sku'] ?? $line['sku'] ?? null,
                'quantity' => (int) ($line['quantity'] ?? $line['item_count'] ?? 1),
            ];
        }

        return $items;
    }
}
