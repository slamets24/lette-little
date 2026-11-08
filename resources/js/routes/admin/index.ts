import orders from './orders'
import products from './products'
import users from './users'
const admin = {
    orders: Object.assign(orders, orders),
products: Object.assign(products, products),
users: Object.assign(users, users),
}

export default admin