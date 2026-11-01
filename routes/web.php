<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/our-story', [HomeController::class, 'story'])->name('story');
Route::get('/collection', [ShopController::class, 'index'])->name('collection');
Route::get('/perfume/{product}', [ShopController::class, 'show'])->name('product');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:5,1');

Route::get('/bag', [CartController::class, 'index'])->name('cart');
Route::post('/bag/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/bag/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/bag/{product}', [CartController::class, 'remove'])->name('cart.remove');

Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/order/{order}/thank-you', [CheckoutController::class, 'thanks'])->name('order.thanks');

// Payment gateways
Route::get('/payment/stripe/{order}/success', [PaymentController::class, 'stripeSuccess'])->name('payment.stripe.success');
Route::get('/payment/paymob/callback', [PaymentController::class, 'paymobCallback'])->name('payment.paymob.callback');
Route::get('/payment/{order}/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('/webhooks/paymob', [WebhookController::class, 'paymob'])->name('webhooks.paymob');

/*
|--------------------------------------------------------------------------
| Admin dashboard  (/admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [Admin\AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');

    Route::middleware('admin')->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::resource('products', Admin\ProductController::class)->except('show');
        Route::resource('orders', Admin\OrderController::class)->only('index', 'show', 'update', 'destroy');
        Route::resource('customers', Admin\CustomerController::class)->only('index', 'show', 'update', 'destroy');
        Route::resource('messages', Admin\MessageController::class)->only('index', 'show', 'destroy');

        Route::get('accounting', [Admin\AccountingController::class, 'index'])->name('accounting');
        Route::get('accounting/export', [Admin\AccountingController::class, 'export'])->name('accounting.export');
        Route::post('expenses', [Admin\AccountingController::class, 'storeExpense'])->name('expenses.store');
        Route::delete('expenses/{expense}', [Admin\AccountingController::class, 'destroyExpense'])->name('expenses.destroy');
    });
});
