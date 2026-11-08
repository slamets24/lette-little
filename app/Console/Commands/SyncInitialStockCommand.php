<?php

namespace App\Console\Commands;

use App\Jobs\SyncStockAcrossChannelsJob;
use App\Models\MarketplaceMapping;
use App\Services\Marketplace\MarketplaceManager;
use Illuminate\Console\Command;

class SyncInitialStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:sync-initial-stock
                            {--marketplace= : Specific marketplace to sync (shopee|tiktok)}
                            {--queue : Queue the sync jobs instead of running synchronously}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize initial warehouse stock from website database to connected marketplaces';

    /**
     * Execute the console command.
     */
    public function handle(MarketplaceManager $manager): int
    {
        $marketplace = $this->option('marketplace');
        $asQueue = (bool) $this->option('queue');

        $query = MarketplaceMapping::query()->with('variant.product');

        if ($marketplace) {
            $query->where('marketplace', strtolower($marketplace));
        }

        $mappings = $query->get();

        if ($mappings->isEmpty()) {
            $this->info('No marketplace mappings found to sync.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Found %d mapping(s) to synchronize.', $mappings->count()));

        $bar = $this->output->createProgressBar($mappings->count());
        $bar->start();

        $successCount = 0;
        $failCount = 0;

        foreach ($mappings as $mapping) {
            $variant = $mapping->variant;

            if (! $variant || ! $variant->is_active) {
                $this->newLine();
                $this->warn(sprintf('Skipping mapping ID %d: Variant is missing or inactive.', $mapping->id));
                $bar->advance();

                continue;
            }

            $availableStock = $variant->available_stock;

            if ($asQueue) {
                SyncStockAcrossChannelsJob::dispatch($variant->id, $availableStock);
                $successCount++;
            } else {
                try {
                    $driver = $manager->driver($mapping->marketplace);
                    $success = $driver->updateStock($mapping, $availableStock);

                    if ($success) {
                        $mapping->update([
                            'is_synced' => true,
                            'last_synced_at' => now(),
                        ]);
                        $successCount++;
                    } else {
                        $mapping->update(['is_synced' => false]);
                        $failCount++;
                    }
                } catch (\Throwable $e) {
                    $this->newLine();
                    $this->error(sprintf('Error syncing mapping ID %d (%s): %s', $mapping->id, $mapping->marketplace, $e->getMessage()));
                    $mapping->update(['is_synced' => false]);
                    $failCount++;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $mappings->count()],
                ['Succeeded / Queued', $successCount],
                ['Failed', $failCount],
            ]
        );

        return $failCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}
