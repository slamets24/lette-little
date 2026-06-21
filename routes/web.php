<?php

use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/shop/{product:slug}', [ShopController::class, 'show'])->name('shop.show');
Route::inertia('/cart', 'Cart')->name('cart');
Route::inertia('/collection', 'Collection')->name('collection');
Route::inertia('/about', 'About')->name('about');
Route::inertia('/journal', 'Journal')->name('journal');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
