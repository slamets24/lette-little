<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartImportController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StorefrontPageController;
use App\Services\Seo\PageMeta;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShopController::class, 'home'])->name('home');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/shop/category/{category:slug}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/shop/{product:slug}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/cart', [CartController::class, 'show'])->name('cart');
Route::post('/cart/items', [CartItemController::class, 'store'])->name('cart.items.store');
Route::patch('/cart/items/{cartItem}', [CartItemController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{cartItem}', [CartItemController::class, 'destroy'])->name('cart.items.destroy');
Route::delete('/cart', [CartController::class, 'destroy'])->name('cart.destroy');
Route::post('/cart/import', [CartImportController::class, 'store'])->name('cart.import');
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::post('/payments/midtrans/webhook', MidtransWebhookController::class)->name('payments.midtrans.webhook');
Route::get('/orders', [OrderController::class, 'index'])->middleware(['auth', 'verified'])->name('orders.index');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::get('/collection', [StorefrontPageController::class, 'collection'])->name('collection');
Route::get('/faq', [StorefrontPageController::class, 'faq'])->name('faq');

$storefrontPages = [
    ['/about', 'About', 'about', 'About', 'Lette Little was born from one idea: clothing that feels as gentle as a mother\'s touch and as light as a cloud.'],
    ['/journal', 'Journal', 'journal', 'Journal', 'Notes on dressing little ones: fabric care, gifting ideas, and slow family mornings from the Lette Little studio.'],
    ['/size-guide', 'SizeGuide', 'size-guide', 'Size Guide', 'Find the right Lette Little size by your baby\'s height and weight, with room to grow.'],
    ['/care-guide', 'CareGuide', 'care-guide', 'Care Guide', 'How to wash, dry, and store Lette Little pieces so they stay soft wash after wash.'],
    ['/shipping-returns', 'ShippingReturns', 'shipping-returns', 'Shipping & Returns', 'Courier options, delivery times, and how returns work for Lette Little orders across Indonesia.'],
    ['/privacy', 'Privacy', 'privacy', 'Privacy Policy', 'How Lette Little collects, uses, and protects your personal data.'],
    ['/terms', 'Terms', 'terms', 'Terms & Conditions', 'The terms that apply when you shop with Lette Little.'],
];

foreach ($storefrontPages as [$uri, $component, $name, $title, $description]) {
    Route::inertia($uri, $component, [
        'meta' => PageMeta::make(title: $title, description: $description),
    ])->name($name);
}

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('admin/orders', AdminOrderController::class)
        ->names('admin.orders')
        ->only(['index', 'show', 'update']);
    Route::resource('admin/products', AdminProductController::class)
        ->names('admin.products')
        ->except(['show', 'destroy']);
    Route::resource('admin/users', AdminUserController::class)
        ->middleware('superadmin')
        ->names('admin.users')
        ->only(['index', 'create', 'store']);
});

require __DIR__.'/settings.php';
