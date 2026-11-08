<?php

namespace App\Jobs;

use App\Models\ProductVariant;
use App\Services\Marketplace\MarketplaceManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SyncStockAcrossChannelsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Timeout in seconds.
     */
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $variantId,
        public int $newStock,
        public ?string $sourceMarketplace = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MarketplaceManager $manager): void
    {
        $variant = ProductVariant::query()
            ->with('marketplaceMappings')
            ->find($this->variantId);

        if (! $variant) {
            Log::warning('SyncStockAcrossChannelsJob: Variant not found', [
                'variant_id' => $this->variantId,
            ]);

            return;
        }

        $mappings = $variant->marketplaceMappings;

        if ($mappings->isEmpty()) {
            return;
        }

        $failedMappings = [];

        foreach ($mappings as $mapping) {
            // Skip the channel that initiated the stock change to avoid redundant sync
            if ($this->sourceMarketplace !== null && strtolower($mapping->marketplace) === strtolower($this->sourceMarketplace)) {
                continue;
            }

            try {
                $driver = $manager->driver($mapping->marketplace);
                $success = $driver->updateStock($mapping, $this->newStock);

                if ($success) {
                    $mapping->update([
                        'is_synced' => true,
                        'last_synced_at' => now(),
                    ]);
                } else {
                    $mapping->update([
                        'is_synced' => false,
                    ]);
                    $failedMappings[] = $mapping->id;
                }
            } catch (Throwable $e) {
                Log::error('Exception while syncing stock to marketplace', [
                    'mapping_id' => $mapping->id,
                    'marketplace' => $mapping->marketplace,
                    'error' => $e->getMessage(),
                ]);

                $mapping->update([
                    'is_synced' => false,
                ]);

                $failedMappings[] = $mapping->id;
            }
        }

        if (! empty($failedMappings)) {
            throw new RuntimeException(
                sprintf('Failed syncing stock for mappings [%s] on variant [%d]', implode(', ', $failedMappings), $this->variantId)
            );
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('SyncStockAcrossChannelsJob permanently failed after retries', [
            'variant_id' => $this->variantId,
            'new_stock' => $this->newStock,
            'source_marketplace' => $this->sourceMarketplace,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
