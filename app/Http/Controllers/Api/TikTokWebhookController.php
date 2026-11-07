<?php

namespace App\Http\Controllers\Api;

use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Jobs\SyncStockAcrossChannelsJob;
use App\Models\MarketplaceMapping;
use App\Models\ProductVariant;
use App\Services\Marketplace\TikTokService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TikTokWebhookController extends Controller
{
    /**
     * Handle incoming TikTok Shop order/inventory webhooks.
     */
    public function handle(Request $request, TikTokService $tiktokService): JsonResponse
    {
        // 1. Validate webhook signature
        if (! $tiktokService->verifyWebhookSignature($request)) {
            Log::warning('TikTok webhook invalid signature', [
                'headers' => $request->headers->all(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Invalid signature',
            ], 401);
        }

        // 2. Extract line items from notification payload
        $items = $tiktokService->extractOrderItems($request);

        foreach ($items as $item) {
            $variant = null;

            if (! empty($item['variant_id'])) {
                $mapping = MarketplaceMapping::query()
                    ->where('marketplace', 'tiktok')
                    ->where('marketplace_variant_id', (string) $item['variant_id'])
                    ->first();

                $variant = $mapping?->variant;
            }

            if (! $variant && ! empty($item['sku'])) {
                $variant = ProductVariant::query()->where('sku', $item['sku'])->first();
            }

            if (! $variant) {
                Log::warning('TikTok webhook variant not found for item', [
                    'item' => $item,
                ]);

                continue;
            }

            $quantity = max(1, $item['quantity']);

            // 3. Cut local stock and record stock movement
            DB::transaction(function () use ($variant, $quantity, $item): void {
                $variant->decrement('stock', $quantity);

                $variant->stockMovements()->create([
                    'quantity' => -$quantity,
                    'type' => StockMovementType::Sale,
                    'reference_type' => MarketplaceMapping::class,
                    'reference_id' => $variant->id,
                    'notes' => sprintf('TikTok webhook stock reduction (sku_id: %s, qty: %d)', $item['variant_id'] ?? 'N/A', $quantity),
                ]);
            });

            // 4. Dispatch async stock sync to other marketplace channels
            $freshVariant = $variant->fresh();
            if ($freshVariant) {
                SyncStockAcrossChannelsJob::dispatch(
                    $freshVariant->id,
                    $freshVariant->available_stock,
                    'tiktok'
                );
            }
        }

        // Fast HTTP 200 response
        return response()->json([
            'code' => 0,
            'message' => 'Webhook received successfully',
        ], 200);
    }
}
