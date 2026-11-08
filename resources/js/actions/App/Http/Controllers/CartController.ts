import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\CartController::show
 * @see app/Http/Controllers/CartController.php:17
 * @route '/cart'
 */
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/cart',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CartController::show
 * @see app/Http/Controllers/CartController.php:17
 * @route '/cart'
 */
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartController::show
 * @see app/Http/Controllers/CartController.php:17
 * @route '/cart'
 */
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\CartController::show
 * @see app/Http/Controllers/CartController.php:17
 * @route '/cart'
 */
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\CartController::show
 * @see app/Http/Controllers/CartController.php:17
 * @route '/cart'
 */
    const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\CartController::show
 * @see app/Http/Controllers/CartController.php:17
 * @route '/cart'
 */
        showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\CartController::show
 * @see app/Http/Controllers/CartController.php:17
 * @route '/cart'
 */
        showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
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
const CartController = { show, destroy }

export default CartController