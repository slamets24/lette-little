import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\StorefrontPageController::collection
 * @see app/Http/Controllers/StorefrontPageController.php:16
 * @route '/collection'
 */
export const collection = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: collection.url(options),
    method: 'get',
})

collection.definition = {
    methods: ["get","head"],
    url: '/collection',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\StorefrontPageController::collection
 * @see app/Http/Controllers/StorefrontPageController.php:16
 * @route '/collection'
 */
collection.url = (options?: RouteQueryOptions) => {
    return collection.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\StorefrontPageController::collection
 * @see app/Http/Controllers/StorefrontPageController.php:16
 * @route '/collection'
 */
collection.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: collection.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\StorefrontPageController::collection
 * @see app/Http/Controllers/StorefrontPageController.php:16
 * @route '/collection'
 */
collection.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: collection.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\StorefrontPageController::collection
 * @see app/Http/Controllers/StorefrontPageController.php:16
 * @route '/collection'
 */
    const collectionForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: collection.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\StorefrontPageController::collection
 * @see app/Http/Controllers/StorefrontPageController.php:16
 * @route '/collection'
 */
        collectionForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: collection.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\StorefrontPageController::collection
 * @see app/Http/Controllers/StorefrontPageController.php:16
 * @route '/collection'
 */
        collectionForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: collection.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    collection.form = collectionForm
/**
* @see \App\Http\Controllers\StorefrontPageController::faq
 * @see app/Http/Controllers/StorefrontPageController.php:46
 * @route '/faq'
 */
export const faq = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: faq.url(options),
    method: 'get',
})

faq.definition = {
    methods: ["get","head"],
    url: '/faq',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\StorefrontPageController::faq
 * @see app/Http/Controllers/StorefrontPageController.php:46
 * @route '/faq'
 */
faq.url = (options?: RouteQueryOptions) => {
    return faq.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\StorefrontPageController::faq
 * @see app/Http/Controllers/StorefrontPageController.php:46
 * @route '/faq'
 */
faq.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: faq.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\StorefrontPageController::faq
 * @see app/Http/Controllers/StorefrontPageController.php:46
 * @route '/faq'
 */
faq.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: faq.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\StorefrontPageController::faq
 * @see app/Http/Controllers/StorefrontPageController.php:46
 * @route '/faq'
 */
    const faqForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: faq.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\StorefrontPageController::faq
 * @see app/Http/Controllers/StorefrontPageController.php:46
 * @route '/faq'
 */
        faqForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: faq.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\StorefrontPageController::faq
 * @see app/Http/Controllers/StorefrontPageController.php:46
 * @route '/faq'
 */
        faqForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: faq.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    faq.form = faqForm
const StorefrontPageController = { collection, faq }

export default StorefrontPageController