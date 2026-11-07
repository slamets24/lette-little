<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('marketplace_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->enum('marketplace', ['shopee', 'tiktok', 'tokopedia']);
            $table->string('marketplace_product_id'); // ID induk di marketplace (Shopee item_id / TikTok product_id)
            $table->string('marketplace_variant_id'); // ID varian di marketplace (Shopee model_id / TikTok sku_id)
            $table->string('marketplace_sku')->nullable();
            $table->boolean('is_synced')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['marketplace', 'marketplace_variant_id'], 'mk_variant_unique');
            $table->index(['product_variant_id', 'marketplace']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_mappings');
    }
};
