<?php

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Jobs\SyncStockAcrossChannelsJob;
use App\Models\Category;
use App\Models\MarketplaceMapping;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Marketplace\ShopeeService;
use App\Services\Marketplace\TikTokService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MarketplaceSyncTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    private Product $product;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'marketplace.shopee.partner_id' => '123456',
            'marketplace.shopee.partner_key' => 'shopee_partner_secret_key',
            'marketplace.shopee.shop_id' => '998877',
            'marketplace.shopee.access_token' => 'shopee_token_123',
            'marketplace.shopee.base_url' => 'https://partner.test-stable.shopeemobile.com',
            'marketplace.shopee.webhook_secret' => 'shopee_webhook_secret',

            'marketplace.tiktok.app_key' => 'tiktok_app_123',
            'marketplace.tiktok.app_secret' => 'tiktok_secret_key',
            'marketplace.tiktok.shop_cipher' => 'cipher_123',
            'marketplace.tiktok.access_token' => 'tiktok_token_456',
            'marketplace.tiktok.base_url' => 'https://open-api.tiktokglobalshop.com',
            'marketplace.tiktok.webhook_secret' => 'tiktok_webhook_secret',
        ]);

        $this->category = Category::query()->create([
            'name' => 'Sets',
            'slug' => 'sets',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->product = Product::query()->create([
            'category_id' => $this->category->id,
            'name' => 'Floral Summer Set',
            'slug' => 'floral-summer-set',
            'base_price' => 150000,
            'is_active' => true,
        ]);

        $this->variant = $this->product->variants()->create([
            'sku' => 'SET-FLORAL-M-PNK',
            'size' => 'M',
            'color' => 'Pink Floral',
            'price' => 165000,
            'stock' => 15,
            'stock_reserved' => 2,
            'is_active' => true,
        ]);
    }

    public function test_product_variant_has_stock_reserved_and_available_stock_accessor(): void
    {
        $this->assertSame(2, $this->variant->stock_reserved);
        // available_stock = stock (15) - stock_reserved (2) = 13
        $this->assertSame(13, $this->variant->available_stock);

        // Fallback price test
        $fallbackVariant = new ProductVariant([
            'price' => 0,
            'stock' => 10,
        ]);
        $fallbackVariant->setRelation('product', $this->product);
        $this->assertSame(150000, $fallbackVariant->price);
    }

    public function test_marketplace_mapping_relation_and_attributes(): void
    {
        $mapping = MarketplaceMapping::query()->create([
            'product_variant_id' => $this->variant->id,
            'marketplace' => 'shopee',
            'marketplace_product_id' => '10001',
            'marketplace_variant_id' => '20001',
            'marketplace_sku' => 'SET-FLORAL-M-PNK-SP',
            'is_synced' => true,
        ]);

        $this->assertTrue($mapping->variant->is($this->variant));
        $this->assertTrue($this->variant->marketplaceMappings->first()->is($mapping));
    }

    public function test_shopee_service_updates_stock_with_correct_signature_and_payload(): void
    {
        Http::fake([
            'https://partner.test-stable.shopeemobile.com/api/v2/product/update_stock*' => Http::response([
                'error' => '',
                'message' => 'success',
                'response' => [
                    'item_id' => 10001,
                ],
            ], 200),
        ]);

        $mapping = MarketplaceMapping::query()->create([
            'product_variant_id' => $this->variant->id,
            'marketplace' => 'shopee',
            'marketplace_product_id' => '10001',
            'marketplace_variant_id' => '20001',
            'marketplace_sku' => 'SET-FLORAL-M-PNK-SP',
            'is_synced' => false,
        ]);

        $service = app(ShopeeService::class);
        $result = $service->updateStock($mapping, 10);

        $this->assertTrue($result);

        Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            $data = $request->data();

            return str_contains($request->url(), '/api/v2/product/update_stock')
                && $data['item_id'] === 10001
                && $data['stock_list'][0]['model_id'] === 20001
                && $data['stock_list'][0]['normal_stock'] === 10;
        });
    }

    public function test_tiktok_service_updates_stock_with_correct_signature_and_payload(): void
    {
        Http::fake([
            'https://open-api.tiktokglobalshop.com/product/202309/products/9001/inventory/update*' => Http::response([
                'code' => 0,
                'message' => 'Success',
            ], 200),
        ]);

        $mapping = MarketplaceMapping::query()->create([
            'product_variant_id' => $this->variant->id,
            'marketplace' => 'tiktok',
            'marketplace_product_id' => '9001',
            'marketplace_variant_id' => '9002',
            'marketplace_sku' => 'SET-FLORAL-M-PNK-TT',
            'is_synced' => false,
        ]);

        $service = app(TikTokService::class);
        $result = $service->updateStock($mapping, 10);

        $this->assertTrue($result);

        Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            $data = $request->data();

            return str_contains($request->url(), '/product/202309/products/9001/inventory/update')
                && $data['skus'][0]['id'] === '9002'
                && $data['skus'][0]['inventory'][0]['quantity'] === 10;
        });
    }

    public function test_sync_stock_job_syncs_to_all_marketplaces_except_source(): void
    {
        Http::fake([
            'https://partner.test-stable.shopeemobile.com/*' => Http::response(['error' => ''], 200),
            'https://open-api.tiktokglobalshop.com/*' => Http::response(['code' => 0], 200),
        ]);

        $shopeeMapping = MarketplaceMapping::query()->create([
            'product_variant_id' => $this->variant->id,
            'marketplace' => 'shopee',
            'marketplace_product_id' => '10001',
            'marketplace_variant_id' => '20001',
            'is_synced' => false,
        ]);

        $tiktokMapping = MarketplaceMapping::query()->create([
            'product_variant_id' => $this->variant->id,
            'marketplace' => 'tiktok',
            'marketplace_product_id' => '9001',
            'marketplace_variant_id' => '9002',
            'is_synced' => false,
        ]);

        // When source is Shopee, Shopee mapping should be skipped, only TikTok synced
        $job = new SyncStockAcrossChannelsJob($this->variant->id, 8, 'shopee');
        app()->call([$job, 'handle']);

        $tiktokMapping->refresh();
        $this->assertTrue($tiktokMapping->is_synced);
        $this->assertNotNull($tiktokMapping->last_synced_at);

        $shopeeMapping->refresh();
        $this->assertFalse($shopeeMapping->is_synced); // not touched
    }

    public function test_shopee_webhook_controller_validates_signature_and_decrements_stock_and_dispatches_sync(): void
    {
        Queue::fake();

        MarketplaceMapping::query()->create([
            'product_variant_id' => $this->variant->id,
            'marketplace' => 'shopee',
            'marketplace_product_id' => '10001',
            'marketplace_variant_id' => '20001',
            'is_synced' => true,
        ]);

        $payload = [
            'code' => 3,
            'data' => [
                'order_sn' => '261027998811',
                'item_list' => [
                    [
                        'item_id' => 10001,
                        'model_id' => 20001,
                        'model_quantity_purchased' => 2,
                    ],
                ],
            ],
        ];

        $jsonPayload = json_encode($payload);
        $signature = hash_hmac('sha256', $jsonPayload, config('marketplace.shopee.webhook_secret'));

        $response = $this->call(
            'POST',
            route('api.webhooks.shopee'),
            [],
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $jsonPayload
        );

        $response->assertStatus(200)
            ->assertJson(['code' => 0]);

        $this->variant->refresh();
        // 15 - 2 = 13
        $this->assertSame(13, $this->variant->stock);

        $this->assertDatabaseHas('stock_movements', [
            'product_variant_id' => $this->variant->id,
            'quantity' => -2,
            'type' => StockMovementType::Sale->value,
        ]);

        Queue::assertPushed(SyncStockAcrossChannelsJob::class, function (SyncStockAcrossChannelsJob $job) {
            return $job->variantId === $this->variant->id
                && $job->newStock === 11 // 13 stock - 2 reserved
                && $job->sourceMarketplace === 'shopee';
        });
    }

    public function test_shopee_webhook_rejects_invalid_signature(): void
    {
        $payload = json_encode(['foo' => 'bar']);

        $response = $this->call(
            'POST',
            route('api.webhooks.shopee'),
            [],
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'invalid_signature_string',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid signature']);
    }

    public function test_tiktok_webhook_controller_validates_signature_and_decrements_stock_and_dispatches_sync(): void
    {
        Queue::fake();

        MarketplaceMapping::query()->create([
            'product_variant_id' => $this->variant->id,
            'marketplace' => 'tiktok',
            'marketplace_product_id' => '9001',
            'marketplace_variant_id' => '9002',
            'is_synced' => true,
        ]);

        $payload = [
            'type' => 1,
            'data' => [
                'order_id' => 'TT-987654321',
                'order_line_list' => [
                    [
                        'product_id' => '9001',
                        'sku_id' => '9002',
                        'quantity' => 3,
                    ],
                ],
            ],
        ];

        $jsonPayload = json_encode($payload);
        $signature = hash_hmac('sha256', $jsonPayload, config('marketplace.tiktok.webhook_secret'));

        $response = $this->call(
            'POST',
            route('api.webhooks.tiktok'),
            [],
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $jsonPayload
        );

        $response->assertStatus(200)
            ->assertJson(['code' => 0]);

        $this->variant->refresh();
        // 15 - 3 = 12
        $this->assertSame(12, $this->variant->stock);

        $this->assertDatabaseHas('stock_movements', [
            'product_variant_id' => $this->variant->id,
            'quantity' => -3,
            'type' => StockMovementType::Sale->value,
        ]);

        Queue::assertPushed(SyncStockAcrossChannelsJob::class, function (SyncStockAcrossChannelsJob $job) {
            return $job->variantId === $this->variant->id
                && $job->newStock === 10 // 12 stock - 2 reserved
                && $job->sourceMarketplace === 'tiktok';
        });
    }

    public function test_web_checkout_dispatches_sync_stock_job_without_blocking(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'name' => 'Aditya Pratama',
            'email' => 'aditya@example.com',
            'phone' => '081234567890',
            'address_line_1' => 'Jl. Pahlawan No. 45',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12190',
            'country' => 'ID',
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertSessionHasNoErrors();
        $this->variant->refresh();
        $this->assertSame(14, $this->variant->stock);

        Queue::assertPushed(SyncStockAcrossChannelsJob::class, function (SyncStockAcrossChannelsJob $job) {
            return $job->variantId === $this->variant->id
                && $job->newStock === 12; // 14 stock - 2 reserved
        });
    }

    public function test_sync_initial_stock_artisan_command(): void
    {
        Http::fake([
            'https://partner.test-stable.shopeemobile.com/*' => Http::response(['error' => ''], 200),
        ]);

        $mapping = MarketplaceMapping::query()->create([
            'product_variant_id' => $this->variant->id,
            'marketplace' => 'shopee',
            'marketplace_product_id' => '10001',
            'marketplace_variant_id' => '20001',
            'is_synced' => false,
        ]);

        $this->artisan('marketplace:sync-initial-stock', ['--marketplace' => 'shopee'])
            ->expectsOutputToContain('Found 1 mapping(s) to synchronize.')
            ->assertSuccessful();

        $mapping->refresh();
        $this->assertTrue($mapping->is_synced);
        $this->assertNotNull($mapping->last_synced_at);
    }
}
