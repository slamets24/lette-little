import Api from './Api'
import ShopController from './ShopController'
import SitemapController from './SitemapController'
import RobotsController from './RobotsController'
import CartController from './CartController'
import CartItemController from './CartItemController'
import CartImportController from './CartImportController'
import CheckoutController from './CheckoutController'
import MidtransWebhookController from './MidtransWebhookController'
import OrderController from './OrderController'
import StorefrontPageController from './StorefrontPageController'
import AiChatController from './AiChatController'
import Admin from './Admin'
import Settings from './Settings'
const Controllers = {
    Api: Object.assign(Api, Api),
ShopController: Object.assign(ShopController, ShopController),
SitemapController: Object.assign(SitemapController, SitemapController),
RobotsController: Object.assign(RobotsController, RobotsController),
CartController: Object.assign(CartController, CartController),
CartItemController: Object.assign(CartItemController, CartItemController),
CartImportController: Object.assign(CartImportController, CartImportController),
CheckoutController: Object.assign(CheckoutController, CheckoutController),
MidtransWebhookController: Object.assign(MidtransWebhookController, MidtransWebhookController),
OrderController: Object.assign(OrderController, OrderController),
StorefrontPageController: Object.assign(StorefrontPageController, StorefrontPageController),
AiChatController: Object.assign(AiChatController, AiChatController),
Admin: Object.assign(Admin, Admin),
Settings: Object.assign(Settings, Settings),
}

export default Controllers