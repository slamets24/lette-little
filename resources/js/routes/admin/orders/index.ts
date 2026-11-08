import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:22
 * @route '/admin/orders'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/orders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:22
 * @route '/admin/orders'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:22
 * @route '/admin/orders'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:22
 * @route '/admin/orders'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:22
 * @route '/admin/orders'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:22
 * @route '/admin/orders'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:22
 * @route '/admin/orders'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:44
 * @route '/admin/orders/{order}'
 */
export const show = (args: { order: string | number | { order_number: string | number } } | [order: string | number | { order_number: string | number } ] | string | number | { order_number: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/orders/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:44
 * @route '/admin/orders/{order}'
 */
show.url = (args: { order: string | number | { order_number: string | number } } | [order: string | number | { order_number: string | number } ] | string | number | { order_number: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'order_number' in args) {
            args = { order: args.order_number }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.order_number
                : args.order,
                }

    return show.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:44
 * @route '/admin/orders/{order}'
 */
show.get = (args: { order: string | number | { order_number: string | number } } | [order: string | number | { order_number: string | number } ] | string | number | { order_number: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:44
 * @route '/admin/orders/{order}'
 */
show.head = (args: { order: string | number | { order_number: string | number } } | [order: string | number | { order_number: string | number } ] | string | number | { order_number: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:44
 * @route '/admin/orders/{order}'
 */
    const showForm = (args: { order: string | number | { order_number: string | number } } | [order: string | number | { order_number: string | number } ] | string | number | { order_number: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:44
 * @route '/admin/orders/{order}'
 */
        showForm.get = (args: { order: string | number | { order_number: string | number } } | [order: string | number | { order_number: string | number } ] | string | number | { order_number: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:44
 * @route '/admin/orders/{order}'
 */
        showForm.head = (args: { order: string | number | { order_number: string | number } } | [order: string | number | { order_number: string | number } ] | string | number | { order_number: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Admin\OrderController::update
 * @see app/Http/Controllers/Admin/OrderController.php:63
 * @route '/admin/orders/{order}'
 */
export const update = (args: { order: string | number | { order_number: string | number } } | [order: string | number | { order_number: string | number } ] | string | number | { order_number: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/orders/{order}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::update
 * @see app/Http/Controllers/Admin/OrderController.php:63
 * @route '/admin/orders/{order}'
 */
update.url = (args: { order: string | number | { order_number: string | number } } | [order: string | number | { order_number: string | number } ] | string | number | { order_number: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'order_number' in args) {
            args = { order: args.order_number }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.order_number
                : args.order,
                }

    return update.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::update
 * @see app/Http/Controllers/Admin/OrderController.php:63
 * @route '/admin/orders/{order}'
 */
update.put = (args: { order: string | number | { order_number: string | number } } | [order: string | number | { order_number: string | number } ] | string | number | { order_number: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\OrderController::update
 * @see app/Http/Controllers/Admin/OrderController.php:63
 * @route '/admin/orders/{order}'
 */
update.patch = (args: { order: string | number | { order_number: string | number } } | [order: string | number | { order_number: string | number } ] | string | number | { order_number: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::update
 * @see app/Http/Controllers/Admin/OrderController.php:63
 * @route '/admin/orders/{order}'
 */
    const updateForm = (args: { order: string | number | { order_number: string | number } } | [order: string | number | { order_number: string | number } ] | string | number | { order_number: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::update
 * @see app/Http/Controllers/Admin/OrderController.php:63
 * @route '/admin/orders/{order}'
 */
        updateForm.put = (args: { order: string | number | { order_number: string | number } } | [order: string | number | { order_number: string | number } ] | string | number | { order_number: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\OrderController::update
 * @see app/Http/Controllers/Admin/OrderController.php:63
 * @route '/admin/orders/{order}'
 */
        updateForm.patch = (args: { order: string | number | { order_number: string | number } } | [order: string | number | { order_number: string | number } ] | string | number | { order_number: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
const orders = {
    index: Object.assign(index, index),
show: Object.assign(show, show),
update: Object.assign(update, update),
}

export default orders