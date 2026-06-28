<?php

use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartImportController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
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
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::inertia('/collection', 'Collection')->name('collection');
Route::inertia('/about', 'About')->name('about');
Route::inertia('/journal', 'Journal')->name('journal');

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::resource('admin/orders', AdminOrderController::class)
        ->names('admin.orders')
        ->only(['index', 'show']);
    Route::resource('admin/products', AdminProductController::class)
        ->names('admin.products')
        ->except(['show', 'destroy']);
});

require __DIR__.'/settings.php';
