import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\MidtransWebhookController::__invoke
 * @see app/Http/Controllers/MidtransWebhookController.php:22
 * @route '/payments/midtrans/webhook'
 */
const MidtransWebhookController = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: MidtransWebhookController.url(options),
    method: 'post',
})

MidtransWebhookController.definition = {
    methods: ["post"],
    url: '/payments/midtrans/webhook',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\MidtransWebhookController::__invoke
 * @see app/Http/Controllers/MidtransWebhookController.php:22
 * @route '/payments/midtrans/webhook'
 */
MidtransWebhookController.url = (options?: RouteQueryOptions) => {
    return MidtransWebhookController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\MidtransWebhookController::__invoke
 * @see app/Http/Controllers/MidtransWebhookController.php:22
 * @route '/payments/midtrans/webhook'
 */
MidtransWebhookController.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: MidtransWebhookController.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\MidtransWebhookController::__invoke
 * @see app/Http/Controllers/MidtransWebhookController.php:22
 * @route '/payments/midtrans/webhook'
 */
    const MidtransWebhookControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: MidtransWebhookController.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\MidtransWebhookController::__invoke
 * @see app/Http/Controllers/MidtransWebhookController.php:22
 * @route '/payments/midtrans/webhook'
 */
        MidtransWebhookControllerForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: MidtransWebhookController.url(options),
            method: 'post',
        })
    
    MidtransWebhookController.form = MidtransWebhookControllerForm
export default MidtransWebhookController