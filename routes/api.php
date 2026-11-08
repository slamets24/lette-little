<?php

use App\Http\Controllers\Api\ShopeeWebhookController;
use App\Http\Controllers\Api\TikTokWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Marketplace Webhooks and API endpoints.
|
*/

Route::prefix('webhooks')->group(function (): void {
    Route::post('/shopee', [ShopeeWebhookController::class, 'handle'])->name('api.webhooks.shopee');
    Route::post('/tiktok', [TikTokWebhookController::class, 'handle'])->name('api.webhooks.tiktok');
});
