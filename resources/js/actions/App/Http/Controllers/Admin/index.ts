import DashboardController from './DashboardController'
import OrderController from './OrderController'
import ProductController from './ProductController'
import AdminUserController from './AdminUserController'
const Admin = {
    DashboardController: Object.assign(DashboardController, DashboardController),
OrderController: Object.assign(OrderController, OrderController),
ProductController: Object.assign(ProductController, ProductController),
AdminUserController: Object.assign(AdminUserController, AdminUserController),
}

export default Admin