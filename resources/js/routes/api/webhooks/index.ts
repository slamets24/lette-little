import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\ShopeeWebhookController::shopee
 * @see app/Http/Controllers/Api/ShopeeWebhookController.php:21
 * @route '/api/webhooks/shopee'
 */
export const shopee = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: shopee.url(options),
    method: 'post',
})

shopee.definition = {
    methods: ["post"],
    url: '/api/webhooks/shopee',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\ShopeeWebhookController::shopee
 * @see app/Http/Controllers/Api/ShopeeWebhookController.php:21
 * @route '/api/webhooks/shopee'
 */
shopee.url = (options?: RouteQueryOptions) => {
    return shopee.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\ShopeeWebhookController::shopee
 * @see app/Http/Controllers/Api/ShopeeWebhookController.php:21
 * @route '/api/webhooks/shopee'
 */
shopee.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: shopee.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\ShopeeWebhookController::shopee
 * @see app/Http/Controllers/Api/ShopeeWebhookController.php:21
 * @route '/api/webhooks/shopee'
 */
    const shopeeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: shopee.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\ShopeeWebhookController::shopee
 * @see app/Http/Controllers/Api/ShopeeWebhookController.php:21
 * @route '/api/webhooks/shopee'
 */
        shopeeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: shopee.url(options),
            method: 'post',
        })
    
    shopee.form = shopeeForm
/**
* @see \App\Http\Controllers\Api\TikTokWebhookController::tiktok
 * @see app/Http/Controllers/Api/TikTokWebhookController.php:21
 * @route '/api/webhooks/tiktok'
 */
export const tiktok = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: tiktok.url(options),
    method: 'post',
})

tiktok.definition = {
    methods: ["post"],
    url: '/api/webhooks/tiktok',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\TikTokWebhookController::tiktok
 * @see app/Http/Controllers/Api/TikTokWebhookController.php:21
 * @route '/api/webhooks/tiktok'
 */
tiktok.url = (options?: RouteQueryOptions) => {
    return tiktok.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TikTokWebhookController::tiktok
 * @see app/Http/Controllers/Api/TikTokWebhookController.php:21
 * @route '/api/webhooks/tiktok'
 */
tiktok.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: tiktok.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\TikTokWebhookController::tiktok
 * @see app/Http/Controllers/Api/TikTokWebhookController.php:21
 * @route '/api/webhooks/tiktok'
 */
    const tiktokForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: tiktok.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\TikTokWebhookController::tiktok
 * @see app/Http/Controllers/Api/TikTokWebhookController.php:21
 * @route '/api/webhooks/tiktok'
 */
        tiktokForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: tiktok.url(options),
            method: 'post',
        })
    
    tiktok.form = tiktokForm
const webhooks = {
    shopee: Object.assign(shopee, shopee),
tiktok: Object.assign(tiktok, tiktok),
}

export default webhooks