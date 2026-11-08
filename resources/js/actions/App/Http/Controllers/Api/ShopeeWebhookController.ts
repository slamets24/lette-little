import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\ShopeeWebhookController::handle
 * @see app/Http/Controllers/Api/ShopeeWebhookController.php:21
 * @route '/api/webhooks/shopee'
 */
export const handle = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handle.url(options),
    method: 'post',
})

handle.definition = {
    methods: ["post"],
    url: '/api/webhooks/shopee',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\ShopeeWebhookController::handle
 * @see app/Http/Controllers/Api/ShopeeWebhookController.php:21
 * @route '/api/webhooks/shopee'
 */
handle.url = (options?: RouteQueryOptions) => {
    return handle.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\ShopeeWebhookController::handle
 * @see app/Http/Controllers/Api/ShopeeWebhookController.php:21
 * @route '/api/webhooks/shopee'
 */
handle.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handle.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\ShopeeWebhookController::handle
 * @see app/Http/Controllers/Api/ShopeeWebhookController.php:21
 * @route '/api/webhooks/shopee'
 */
    const handleForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: handle.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\ShopeeWebhookController::handle
 * @see app/Http/Controllers/Api/ShopeeWebhookController.php:21
 * @route '/api/webhooks/shopee'
 */
        handleForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: handle.url(options),
            method: 'post',
        })
    
    handle.form = handleForm
const ShopeeWebhookController = { handle }

export default ShopeeWebhookController