<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentSettingController;
use Illuminate\Support\Facades\Route;

// ─── Public / Guest ─────────────────────────────────────
Route::get('/', fn() => redirect('/menu'));

// Catalog publik (bisa diakses tanpa login)
Route::get('/menu', [MenuController::class, 'index'])->name('menu');

// Tambah ke keranjang (guest akan di-redirect ke login)
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Webhook (no auth, no CSRF) ────────────────────────
Route::post('/webhook/midtrans', [WebhookController::class, 'midtransCallback']);

// ─── Buyer: Profile Completion (auth only) ──────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile/complete', [ProfileController::class, 'showCompleteForm'])->name('profile.complete');
    Route::post('/profile/complete', [ProfileController::class, 'updateProfile']);
});

// ─── Buyer: Protected Routes (auth + profile complete) ──
Route::middleware(['auth', 'profile.complete'])->group(function () {
    // Shopping Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::patch('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])->name('checkout.place');
    Route::get('/checkout/payment/{order}', [CheckoutController::class, 'showPayment'])->name('checkout.payment');
    Route::get('/checkout/manual/{order}', [CheckoutController::class, 'showManualPayment'])->name('checkout.manual');
    Route::post('/checkout/manual/{order}/proof', [CheckoutController::class, 'uploadProof'])->name('checkout.manual.proof');

    // Order Tracking
    Route::get('/orders/history', [OrderTrackingController::class, 'history'])->name('orders.history');
    Route::get('/orders/{order}/tracking', [OrderTrackingController::class, 'show'])->name('orders.tracking');
});

// ─── Admin / Seller Dashboard ───────────────────────────
Route::middleware(['auth', 'role.seller'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Product CRUD
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::patch('/products/{product}/toggle', [ProductController::class, 'toggleAvailability'])->name('products.toggle');

    // Order Management
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{order}/confirm-payment', [OrderController::class, 'confirmPayment'])->name('orders.confirm-payment');
    Route::post('/orders/{order}/start-processing', [OrderController::class, 'startProcessing'])->name('orders.start-processing');

    Route::get('/payment-settings', [PaymentSettingController::class, 'edit'])->name('payment-settings.edit');
    Route::put('/payment-settings', [PaymentSettingController::class, 'update'])->name('payment-settings.update');
});
