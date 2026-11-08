import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import items from './items'
/**
* @see \App\Http\Controllers\CartController::destroy
 * @see app/Http/Controllers/CartController.php:36
 * @route '/cart'
 */
export const destroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/cart',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\CartController::destroy
 * @see app/Http/Controllers/CartController.php:36
 * @route '/cart'
 */
destroy.url = (options?: RouteQueryOptions) => {
    return destroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartController::destroy
 * @see app/Http/Controllers/CartController.php:36
 * @route '/cart'
 */
destroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\CartController::destroy
 * @see app/Http/Controllers/CartController.php:36
 * @route '/cart'
 */
    const destroyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\CartController::destroy
 * @see app/Http/Controllers/CartController.php:36
 * @route '/cart'
 */
        destroyForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
/**
* @see \App\Http\Controllers\CartImportController::importMethod
 * @see app/Http/Controllers/CartImportController.php:14
 * @route '/cart/import'
 */
export const importMethod = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(options),
    method: 'post',
})

importMethod.definition = {
    methods: ["post"],
    url: '/cart/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CartImportController::importMethod
 * @see app/Http/Controllers/CartImportController.php:14
 * @route '/cart/import'
 */
importMethod.url = (options?: RouteQueryOptions) => {
    return importMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartImportController::importMethod
 * @see app/Http/Controllers/CartImportController.php:14
 * @route '/cart/import'
 */
importMethod.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\CartImportController::importMethod
 * @see app/Http/Controllers/CartImportController.php:14
 * @route '/cart/import'
 */
    const importMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: importMethod.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\CartImportController::importMethod
 * @see app/Http/Controllers/CartImportController.php:14
 * @route '/cart/import'
 */
        importMethodForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: importMethod.url(options),
            method: 'post',
        })
    
    importMethod.form = importMethodForm
const cart = {
    items: Object.assign(items, items),
destroy: Object.assign(destroy, destroy),
import: Object.assign(importMethod, importMethod),
}

export default cart