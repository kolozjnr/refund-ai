<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminRefundController;
use App\Http\Controllers\RefundController;

Route::redirect('/', '/refunds');
Route::get('/refunds', [RefundController::class, 'index'])->name('refunds.index');
Route::post('/refunds', [RefundController::class, 'store'])->name('refunds.store');
Route::get('/refunds/{refund}', [RefundController::class, 'show'])->name('refunds.show');
Route::prefix('admin/refunds')->name('admin.refunds.')->group(function () {
    Route::get('/', [AdminRefundController::class, 'index'])->name('index');
    Route::get('/{refund}', [AdminRefundController::class, 'show'])->name('show');
});
