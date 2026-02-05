<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MarketplaceMerchantController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\MarketplaceOrderController;

Route::get('/', function () {
    return view('welcome');
});
// Login Routes
Route::get('/login', [LoginController::class, 'showForm']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout']); // Add this for the button

// marketplace-merchant Route
Route::get('/marketplace-merchant', [MarketplaceMerchantController::class, 'index']);
// Route to load the form HTML via AJAX
Route::get('/merchants/create', [MerchantController::class, 'create']);

// Route to save the data
Route::post('/merchants/store', [MerchantController::class, 'store']);
Route::get('/shopify/callback', [MerchantController::class, 'callback']);
Route::post('/merchants/authorize', [MerchantController::class, 'authorize'])->name('merchants.authorize');

Route::get('/marketplace-order', [MarketplaceOrderController::class, 'index']);
Route::post('/marketplace/orders/sync', [MarketplaceOrderController::class, 'syncOrders']);