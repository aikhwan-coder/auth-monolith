<?php

use Illuminate\Support\Facades\Route;
use Modules\Circulation\Controllers\LoanController;
use Modules\Shared\Http\Middleware\EnsureAdmin;

Route::middleware(['web', 'auth'])->prefix('library')->name('library.')->group(function (): void {
    Route::get('loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::post('loans', [LoanController::class, 'store'])->name('loans.store');
    Route::get('loans/{loan}', [LoanController::class, 'show'])->whereNumber('loan')->name('loans.show');
    Route::patch('loans/{loan}/return', [LoanController::class, 'returnBook'])
        ->whereNumber('loan')
        ->name('loans.return');

    Route::middleware(EnsureAdmin::class)->group(function (): void {
        Route::delete('loans/{loan}', [LoanController::class, 'destroy'])->whereNumber('loan')->name('loans.destroy');
        Route::patch('loans/{id}/restore', [LoanController::class, 'restore'])
            ->whereNumber('id')
            ->name('loans.restore');
    });
});
