<?php

use App\Http\Controllers\Admin\ProofDownloadController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Payments\ManualPaymentQueue;
use App\Livewire\Admin\Payments\ManualPaymentReview;
use App\Livewire\Admin\Products\ProductForm;
use App\Livewire\Admin\Products\ProductIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');

    Route::get('/produtos', ProductIndex::class)->name('products.index');
    Route::get('/produtos/novo', ProductForm::class)->name('products.create');
    Route::get('/produtos/{product}/editar', ProductForm::class)->name('products.edit');

    Route::get('/pagamentos', ManualPaymentQueue::class)->name('payments.index');
    Route::get('/pagamentos/{order:order_number}', ManualPaymentReview::class)->name('payments.review');
    Route::get('/pagamentos/{order:order_number}/comprovativo', ProofDownloadController::class)
        ->name('payments.proof-download');
});