<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Buyer\AddressController as BuyerAddressController;
use App\Http\Controllers\Buyer\CartController as BuyerCartController;
use App\Http\Controllers\Buyer\CheckoutController as BuyerCheckoutController;
use App\Http\Controllers\Buyer\MotorController as BuyerMotorController;
use App\Http\Controllers\Buyer\OrderController as BuyerOrderController;
use App\Http\Controllers\Buyer\PageController as BuyerPageController;
use App\Http\Controllers\Buyer\PartController as BuyerPartController;
use App\Http\Controllers\Buyer\WishlistController as BuyerWishlistController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BuyerPageController::class, 'home'])->name('buyer.home');
Route::get('/about', [BuyerPageController::class, 'about'])->name('buyer.about');
Route::get('/produk', [BuyerPageController::class, 'products'])->name('buyer.products');

Route::get('/motors', [BuyerMotorController::class, 'index'])->name('buyer.motors.index');
Route::get('/motors/{motor:slug}', [BuyerMotorController::class, 'show'])->name('buyer.motors.show');

Route::get('/parts', [BuyerPartController::class, 'index'])->name('buyer.parts.index');
Route::get('/parts/{part:slug}', [BuyerPartController::class, 'show'])->name('buyer.parts.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('auth.login');
    Route::post('/login', [LoginController::class, 'store'])->name('auth.login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('auth.register');
    Route::post('/register', [RegisterController::class, 'store'])->name('auth.register.store');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('auth.password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('auth.password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('auth.password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', LogoutController::class)->name('auth.logout');
    Route::get('/dashboard', DashboardController::class)->name('buyer.dashboard');

    Route::get('/wishlist', [BuyerWishlistController::class, 'index'])->name('buyer.wishlist.index');
    Route::post('/wishlist/{part}', [BuyerWishlistController::class, 'toggle'])->name('buyer.wishlist.toggle');

    Route::get('/cart', [BuyerCartController::class, 'index'])->name('buyer.cart.index');
    Route::post('/cart/items', [BuyerCartController::class, 'store'])->name('buyer.cart.store');
    Route::patch('/cart/items/{cartItem}', [BuyerCartController::class, 'update'])->name('buyer.cart.update');
    Route::delete('/cart/items/{cartItem}', [BuyerCartController::class, 'destroy'])->name('buyer.cart.destroy');
    Route::delete('/cart', [BuyerCartController::class, 'clear'])->name('buyer.cart.clear');
    Route::post('/cart/checkout-selected', [BuyerCartController::class, 'checkoutSelected'])->name('buyer.cart.checkoutSelected');

    Route::get('/account/addresses', [BuyerAddressController::class, 'index'])->name('buyer.addresses.index');
    Route::get('/account/addresses/create', [BuyerAddressController::class, 'create'])->name('buyer.addresses.create');
    Route::post('/account/addresses', [BuyerAddressController::class, 'store'])->name('buyer.addresses.store');
    Route::get('/account/addresses/{address}/edit', [BuyerAddressController::class, 'edit'])->name('buyer.addresses.edit');
    Route::put('/account/addresses/{address}', [BuyerAddressController::class, 'update'])->name('buyer.addresses.update');
    Route::delete('/account/addresses/{address}', [BuyerAddressController::class, 'destroy'])->name('buyer.addresses.destroy');

    Route::get('/checkout', [BuyerCheckoutController::class, 'address'])->name('buyer.checkout.address');
    Route::post('/checkout/address', [BuyerCheckoutController::class, 'setAddress'])->name('buyer.checkout.setAddress');
    Route::get('/checkout/shipping', [BuyerCheckoutController::class, 'shipping'])->name('buyer.checkout.shipping');
    Route::post('/checkout/shipping', [BuyerCheckoutController::class, 'setShipping'])->name('buyer.checkout.setShipping');
    Route::get('/checkout/payment', [BuyerCheckoutController::class, 'payment'])->name('buyer.checkout.payment');
    Route::post('/checkout/place', [BuyerCheckoutController::class, 'placeOrder'])->name('buyer.checkout.place');
    Route::get('/checkout/finish/{order}', [BuyerCheckoutController::class, 'finish'])->name('buyer.checkout.finish');

    Route::get('/my/orders', [BuyerOrderController::class, 'index'])->name('buyer.orders.index');
    Route::get('/my/orders/{order:order_no}', [BuyerOrderController::class, 'show'])->name('buyer.orders.show');
    Route::post('/my/orders/{order:order_no}/simulate-payment', [BuyerOrderController::class, 'simulatePayment'])->name('buyer.orders.simulatePayment');
    Route::post('/my/orders/{order:order_no}/confirm-received', [BuyerOrderController::class, 'confirmReceived'])->name('buyer.orders.confirmReceived');
});
