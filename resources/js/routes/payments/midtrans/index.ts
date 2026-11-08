import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\MidtransWebhookController::__invoke
 * @see app/Http/Controllers/MidtransWebhookController.php:22
 * @route '/payments/midtrans/webhook'
 */
export const webhook = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: webhook.url(options),
    method: 'post',
})

webhook.definition = {
    methods: ["post"],
    url: '/payments/midtrans/webhook',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\MidtransWebhookController::__invoke
 * @see app/Http/Controllers/MidtransWebhookController.php:22
 * @route '/payments/midtrans/webhook'
 */
webhook.url = (options?: RouteQueryOptions) => {
    return webhook.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\MidtransWebhookController::__invoke
 * @see app/Http/Controllers/MidtransWebhookController.php:22
 * @route '/payments/midtrans/webhook'
 */
webhook.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: webhook.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\MidtransWebhookController::__invoke
 * @see app/Http/Controllers/MidtransWebhookController.php:22
 * @route '/payments/midtrans/webhook'
 */
    const webhookForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: webhook.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\MidtransWebhookController::__invoke
 * @see app/Http/Controllers/MidtransWebhookController.php:22
 * @route '/payments/midtrans/webhook'
 */
        webhookForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: webhook.url(options),
            method: 'post',
        })
    
    webhook.form = webhookForm
const midtrans = {
    webhook: Object.assign(webhook, webhook),
}

export default midtrans