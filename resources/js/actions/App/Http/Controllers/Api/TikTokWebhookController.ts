import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\TikTokWebhookController::handle
 * @see app/Http/Controllers/Api/TikTokWebhookController.php:21
 * @route '/api/webhooks/tiktok'
 */
export const handle = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handle.url(options),
    method: 'post',
})

handle.definition = {
    methods: ["post"],
    url: '/api/webhooks/tiktok',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\TikTokWebhookController::handle
 * @see app/Http/Controllers/Api/TikTokWebhookController.php:21
 * @route '/api/webhooks/tiktok'
 */
handle.url = (options?: RouteQueryOptions) => {
    return handle.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TikTokWebhookController::handle
 * @see app/Http/Controllers/Api/TikTokWebhookController.php:21
 * @route '/api/webhooks/tiktok'
 */
handle.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handle.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\TikTokWebhookController::handle
 * @see app/Http/Controllers/Api/TikTokWebhookController.php:21
 * @route '/api/webhooks/tiktok'
 */
    const handleForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: handle.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\TikTokWebhookController::handle
 * @see app/Http/Controllers/Api/TikTokWebhookController.php:21
 * @route '/api/webhooks/tiktok'
 */
        handleForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: handle.url(options),
            method: 'post',
        })
    
    handle.form = handleForm
const TikTokWebhookController = { handle }

export default TikTokWebhookController