<?php

use App\Http\Controllers\Checkout\PaypalCheckoutController;
use App\Http\Controllers\Checkout\PaypalWebhookController;
use App\Http\Controllers\Checkout\StripeCheckoutController;
use App\Http\Controllers\Checkout\StripeWebhookController;
use App\Livewire\Cart\CartPage;
use App\Livewire\Checkout\CheckoutPage;
use App\Livewire\Checkout\ManualPaymentPage;
use Illuminate\Support\Facades\Route;

Route::get('/carrinho', CartPage::class)->name('cart.index');

Route::get('/checkout', CheckoutPage::class)->name('checkout.index');
Route::get('/checkout/{order:order_number}/pagamento-manual', ManualPaymentPage::class)
    ->name('checkout.manual');

// --- Stripe ---
Route::get('/checkout/{order:order_number}/stripe', [StripeCheckoutController::class, 'start'])
    ->name('checkout.stripe.start');
Route::get('/checkout/{order:order_number}/stripe/sucesso', [StripeCheckoutController::class, 'success'])
    ->name('checkout.stripe.success');
Route::get('/checkout/{order:order_number}/stripe/cancelado', [StripeCheckoutController::class, 'cancel'])
    ->name('checkout.stripe.cancel');

// --- PayPal ---
Route::get('/checkout/{order:order_number}/paypal', [PaypalCheckoutController::class, 'start'])
    ->name('checkout.paypal.start');
Route::get('/checkout/{order:order_number}/paypal/retorno', [PaypalCheckoutController::class, 'return'])
    ->name('checkout.paypal.return');
Route::get('/checkout/{order:order_number}/paypal/cancelado', [PaypalCheckoutController::class, 'cancel'])
    ->name('checkout.paypal.cancel');

// --- Webhooks (sem sessão/CSRF — ver nota sobre bootstrap/app.php) ---
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');
Route::post('/webhooks/paypal', [PaypalWebhookController::class, 'handle'])->name('webhooks.paypal');