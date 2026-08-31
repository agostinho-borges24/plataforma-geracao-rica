<?php

use App\Livewire\Cart\CartPage;
use App\Livewire\Checkout\CheckoutPage;
use App\Livewire\Checkout\ManualPaymentPage;
use Illuminate\Support\Facades\Route;

Route::get('/carrinho', CartPage::class)->name('cart.index');

Route::get('/checkout', CheckoutPage::class)->name('checkout.index');
Route::get('/checkout/{order:order_number}/pagamento-manual', ManualPaymentPage::class)
    ->name('checkout.manual');

// Placeholders — serão substituídos pela integração real de Stripe/PayPal
// na próxima etapa. Por ora só evitam erro 404 no redirect do CheckoutPage.
Route::get('/checkout/{order:order_number}/stripe', fn () => abort(501, 'Integração Stripe ainda não implementada.'))
    ->name('checkout.stripe.start');
Route::get('/checkout/{order:order_number}/paypal', fn () => abort(501, 'Integração PayPal ainda não implementada.'))
    ->name('checkout.paypal.start');