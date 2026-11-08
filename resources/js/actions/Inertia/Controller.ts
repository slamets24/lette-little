import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/about'
 */
const Controller535fd093ca1d5254af5dc12ac208e8d5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller535fd093ca1d5254af5dc12ac208e8d5.url(options),
    method: 'get',
})

Controller535fd093ca1d5254af5dc12ac208e8d5.definition = {
    methods: ["get","head"],
    url: '/about',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/about'
 */
Controller535fd093ca1d5254af5dc12ac208e8d5.url = (options?: RouteQueryOptions) => {
    return Controller535fd093ca1d5254af5dc12ac208e8d5.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/about'
 */
Controller535fd093ca1d5254af5dc12ac208e8d5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller535fd093ca1d5254af5dc12ac208e8d5.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/about'
 */
Controller535fd093ca1d5254af5dc12ac208e8d5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller535fd093ca1d5254af5dc12ac208e8d5.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/about'
 */
    const Controller535fd093ca1d5254af5dc12ac208e8d5Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller535fd093ca1d5254af5dc12ac208e8d5.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/about'
 */
        Controller535fd093ca1d5254af5dc12ac208e8d5Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller535fd093ca1d5254af5dc12ac208e8d5.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/about'
 */
        Controller535fd093ca1d5254af5dc12ac208e8d5Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller535fd093ca1d5254af5dc12ac208e8d5.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller535fd093ca1d5254af5dc12ac208e8d5.form = Controller535fd093ca1d5254af5dc12ac208e8d5Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/journal'
 */
const Controllerab0cf61a51a9ec85dc2f83c8befd80d5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerab0cf61a51a9ec85dc2f83c8befd80d5.url(options),
    method: 'get',
})

Controllerab0cf61a51a9ec85dc2f83c8befd80d5.definition = {
    methods: ["get","head"],
    url: '/journal',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/journal'
 */
Controllerab0cf61a51a9ec85dc2f83c8befd80d5.url = (options?: RouteQueryOptions) => {
    return Controllerab0cf61a51a9ec85dc2f83c8befd80d5.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/journal'
 */
Controllerab0cf61a51a9ec85dc2f83c8befd80d5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerab0cf61a51a9ec85dc2f83c8befd80d5.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/journal'
 */
Controllerab0cf61a51a9ec85dc2f83c8befd80d5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerab0cf61a51a9ec85dc2f83c8befd80d5.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/journal'
 */
    const Controllerab0cf61a51a9ec85dc2f83c8befd80d5Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllerab0cf61a51a9ec85dc2f83c8befd80d5.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/journal'
 */
        Controllerab0cf61a51a9ec85dc2f83c8befd80d5Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerab0cf61a51a9ec85dc2f83c8befd80d5.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/journal'
 */
        Controllerab0cf61a51a9ec85dc2f83c8befd80d5Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerab0cf61a51a9ec85dc2f83c8befd80d5.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllerab0cf61a51a9ec85dc2f83c8befd80d5.form = Controllerab0cf61a51a9ec85dc2f83c8befd80d5Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/size-guide'
 */
const Controllerde9b04be48bfa2845d30c25784f0eea7 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerde9b04be48bfa2845d30c25784f0eea7.url(options),
    method: 'get',
})

Controllerde9b04be48bfa2845d30c25784f0eea7.definition = {
    methods: ["get","head"],
    url: '/size-guide',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/size-guide'
 */
Controllerde9b04be48bfa2845d30c25784f0eea7.url = (options?: RouteQueryOptions) => {
    return Controllerde9b04be48bfa2845d30c25784f0eea7.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/size-guide'
 */
Controllerde9b04be48bfa2845d30c25784f0eea7.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerde9b04be48bfa2845d30c25784f0eea7.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/size-guide'
 */
Controllerde9b04be48bfa2845d30c25784f0eea7.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerde9b04be48bfa2845d30c25784f0eea7.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/size-guide'
 */
    const Controllerde9b04be48bfa2845d30c25784f0eea7Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllerde9b04be48bfa2845d30c25784f0eea7.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/size-guide'
 */
        Controllerde9b04be48bfa2845d30c25784f0eea7Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerde9b04be48bfa2845d30c25784f0eea7.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/size-guide'
 */
        Controllerde9b04be48bfa2845d30c25784f0eea7Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerde9b04be48bfa2845d30c25784f0eea7.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllerde9b04be48bfa2845d30c25784f0eea7.form = Controllerde9b04be48bfa2845d30c25784f0eea7Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/care-guide'
 */
const Controller608d210194fb672a50efe711ede9165c = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller608d210194fb672a50efe711ede9165c.url(options),
    method: 'get',
})

Controller608d210194fb672a50efe711ede9165c.definition = {
    methods: ["get","head"],
    url: '/care-guide',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/care-guide'
 */
Controller608d210194fb672a50efe711ede9165c.url = (options?: RouteQueryOptions) => {
    return Controller608d210194fb672a50efe711ede9165c.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/care-guide'
 */
Controller608d210194fb672a50efe711ede9165c.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller608d210194fb672a50efe711ede9165c.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/care-guide'
 */
Controller608d210194fb672a50efe711ede9165c.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller608d210194fb672a50efe711ede9165c.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/care-guide'
 */
    const Controller608d210194fb672a50efe711ede9165cForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller608d210194fb672a50efe711ede9165c.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/care-guide'
 */
        Controller608d210194fb672a50efe711ede9165cForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller608d210194fb672a50efe711ede9165c.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/care-guide'
 */
        Controller608d210194fb672a50efe711ede9165cForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller608d210194fb672a50efe711ede9165c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller608d210194fb672a50efe711ede9165c.form = Controller608d210194fb672a50efe711ede9165cForm
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/shipping-returns'
 */
const Controllerb6d3b768e613fb622a28ae3e1897f5d4 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerb6d3b768e613fb622a28ae3e1897f5d4.url(options),
    method: 'get',
})

Controllerb6d3b768e613fb622a28ae3e1897f5d4.definition = {
    methods: ["get","head"],
    url: '/shipping-returns',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/shipping-returns'
 */
Controllerb6d3b768e613fb622a28ae3e1897f5d4.url = (options?: RouteQueryOptions) => {
    return Controllerb6d3b768e613fb622a28ae3e1897f5d4.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/shipping-returns'
 */
Controllerb6d3b768e613fb622a28ae3e1897f5d4.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerb6d3b768e613fb622a28ae3e1897f5d4.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/shipping-returns'
 */
Controllerb6d3b768e613fb622a28ae3e1897f5d4.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerb6d3b768e613fb622a28ae3e1897f5d4.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/shipping-returns'
 */
    const Controllerb6d3b768e613fb622a28ae3e1897f5d4Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllerb6d3b768e613fb622a28ae3e1897f5d4.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/shipping-returns'
 */
        Controllerb6d3b768e613fb622a28ae3e1897f5d4Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerb6d3b768e613fb622a28ae3e1897f5d4.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/shipping-returns'
 */
        Controllerb6d3b768e613fb622a28ae3e1897f5d4Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerb6d3b768e613fb622a28ae3e1897f5d4.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllerb6d3b768e613fb622a28ae3e1897f5d4.form = Controllerb6d3b768e613fb622a28ae3e1897f5d4Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/privacy'
 */
const Controllera2c058616aeb0c9393ca03a98bc05c02 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllera2c058616aeb0c9393ca03a98bc05c02.url(options),
    method: 'get',
})

Controllera2c058616aeb0c9393ca03a98bc05c02.definition = {
    methods: ["get","head"],
    url: '/privacy',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/privacy'
 */
Controllera2c058616aeb0c9393ca03a98bc05c02.url = (options?: RouteQueryOptions) => {
    return Controllera2c058616aeb0c9393ca03a98bc05c02.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/privacy'
 */
Controllera2c058616aeb0c9393ca03a98bc05c02.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllera2c058616aeb0c9393ca03a98bc05c02.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/privacy'
 */
Controllera2c058616aeb0c9393ca03a98bc05c02.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllera2c058616aeb0c9393ca03a98bc05c02.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/privacy'
 */
    const Controllera2c058616aeb0c9393ca03a98bc05c02Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllera2c058616aeb0c9393ca03a98bc05c02.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/privacy'
 */
        Controllera2c058616aeb0c9393ca03a98bc05c02Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllera2c058616aeb0c9393ca03a98bc05c02.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/privacy'
 */
        Controllera2c058616aeb0c9393ca03a98bc05c02Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllera2c058616aeb0c9393ca03a98bc05c02.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllera2c058616aeb0c9393ca03a98bc05c02.form = Controllera2c058616aeb0c9393ca03a98bc05c02Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/terms'
 */
const Controller619dc3a99425f668ea9cab64e6648cb4 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller619dc3a99425f668ea9cab64e6648cb4.url(options),
    method: 'get',
})

Controller619dc3a99425f668ea9cab64e6648cb4.definition = {
    methods: ["get","head"],
    url: '/terms',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/terms'
 */
Controller619dc3a99425f668ea9cab64e6648cb4.url = (options?: RouteQueryOptions) => {
    return Controller619dc3a99425f668ea9cab64e6648cb4.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/terms'
 */
Controller619dc3a99425f668ea9cab64e6648cb4.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller619dc3a99425f668ea9cab64e6648cb4.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/terms'
 */
Controller619dc3a99425f668ea9cab64e6648cb4.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller619dc3a99425f668ea9cab64e6648cb4.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/terms'
 */
    const Controller619dc3a99425f668ea9cab64e6648cb4Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller619dc3a99425f668ea9cab64e6648cb4.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/terms'
 */
        Controller619dc3a99425f668ea9cab64e6648cb4Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller619dc3a99425f668ea9cab64e6648cb4.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/terms'
 */
        Controller619dc3a99425f668ea9cab64e6648cb4Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller619dc3a99425f668ea9cab64e6648cb4.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller619dc3a99425f668ea9cab64e6648cb4.form = Controller619dc3a99425f668ea9cab64e6648cb4Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
const Controllere19ee86e9cf603ce1a59a1ec5d21dec5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'get',
})

Controllere19ee86e9cf603ce1a59a1ec5d21dec5.definition = {
    methods: ["get","head"],
    url: '/settings/appearance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url = (options?: RouteQueryOptions) => {
    return Controllere19ee86e9cf603ce1a59a1ec5d21dec5.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
Controllere19ee86e9cf603ce1a59a1ec5d21dec5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
Controllere19ee86e9cf603ce1a59a1ec5d21dec5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
    const Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
        Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
        Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllere19ee86e9cf603ce1a59a1ec5d21dec5.form = Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form

/**
* Multiple routes resolve to \Inertia\Controller::Controller, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `Controller['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const Controller = {
    '/about': Controller535fd093ca1d5254af5dc12ac208e8d5,
    '/journal': Controllerab0cf61a51a9ec85dc2f83c8befd80d5,
    '/size-guide': Controllerde9b04be48bfa2845d30c25784f0eea7,
    '/care-guide': Controller608d210194fb672a50efe711ede9165c,
    '/shipping-returns': Controllerb6d3b768e613fb622a28ae3e1897f5d4,
    '/privacy': Controllera2c058616aeb0c9393ca03a98bc05c02,
    '/terms': Controller619dc3a99425f668ea9cab64e6648cb4,
    '/settings/appearance': Controllere19ee86e9cf603ce1a59a1ec5d21dec5,
}

export default Controller