import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\CartImportController::store
 * @see app/Http/Controllers/CartImportController.php:14
 * @route '/cart/import'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/cart/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CartImportController::store
 * @see app/Http/Controllers/CartImportController.php:14
 * @route '/cart/import'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartImportController::store
 * @see app/Http/Controllers/CartImportController.php:14
 * @route '/cart/import'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\CartImportController::store
 * @see app/Http/Controllers/CartImportController.php:14
 * @route '/cart/import'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\CartImportController::store
 * @see app/Http/Controllers/CartImportController.php:14
 * @route '/cart/import'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const CartImportController = { store }

export default CartImportController