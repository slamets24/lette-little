import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AiChatController::chat
 * @see app/Http/Controllers/AiChatController.php:18
 * @route '/chat'
 */
export const chat = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: chat.url(options),
    method: 'post',
})

chat.definition = {
    methods: ["post"],
    url: '/chat',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AiChatController::chat
 * @see app/Http/Controllers/AiChatController.php:18
 * @route '/chat'
 */
chat.url = (options?: RouteQueryOptions) => {
    return chat.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AiChatController::chat
 * @see app/Http/Controllers/AiChatController.php:18
 * @route '/chat'
 */
chat.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: chat.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AiChatController::chat
 * @see app/Http/Controllers/AiChatController.php:18
 * @route '/chat'
 */
    const chatForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: chat.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AiChatController::chat
 * @see app/Http/Controllers/AiChatController.php:18
 * @route '/chat'
 */
        chatForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: chat.url(options),
            method: 'post',
        })
    
    chat.form = chatForm
const AiChatController = { chat }

export default AiChatController