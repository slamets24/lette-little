<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Marketplace API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for omnichannel synchronization with external
    | marketplaces (Shopee, TikTok Shop, Tokopedia).
    |
    */

    'shopee' => [
        'partner_id' => env('SHOPEE_PARTNER_ID'),
        'partner_key' => env('SHOPEE_PARTNER_KEY'),
        'shop_id' => env('SHOPEE_SHOP_ID'),
        'access_token' => env('SHOPEE_ACCESS_TOKEN'),
        'base_url' => env('SHOPEE_BASE_URL', 'https://partner.shopeemobile.com'),
        'webhook_secret' => env('SHOPEE_WEBHOOK_SECRET'),
    ],

    'tiktok' => [
        'app_key' => env('TIKTOK_APP_KEY'),
        'app_secret' => env('TIKTOK_APP_SECRET'),
        'shop_cipher' => env('TIKTOK_SHOP_CIPHER'),
        'access_token' => env('TIKTOK_ACCESS_TOKEN'),
        'base_url' => env('TIKTOK_BASE_URL', 'https://open-api.tiktokglobalshop.com'),
        'webhook_secret' => env('TIKTOK_WEBHOOK_SECRET'),
    ],

    'tokopedia' => [
        'client_id' => env('TOKOPEDIA_CLIENT_ID'),
        'client_secret' => env('TOKOPEDIA_CLIENT_SECRET'),
        'fs_id' => env('TOKOPEDIA_FS_ID'),
        'shop_id' => env('TOKOPEDIA_SHOP_ID'),
        'base_url' => env('TOKOPEDIA_BASE_URL', 'https://fs.tokopedia.net'),
        'webhook_secret' => env('TOKOPEDIA_WEBHOOK_SECRET'),
    ],

];
