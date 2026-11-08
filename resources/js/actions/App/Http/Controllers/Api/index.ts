import ShopeeWebhookController from './ShopeeWebhookController'
import TikTokWebhookController from './TikTokWebhookController'
const Api = {
    ShopeeWebhookController: Object.assign(ShopeeWebhookController, ShopeeWebhookController),
TikTokWebhookController: Object.assign(TikTokWebhookController, TikTokWebhookController),
}

export default Api