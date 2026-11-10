<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Controllers\BookController;
use Modules\Shared\Http\Middleware\EnsureAdmin;

Route::middleware(['web', 'auth'])->prefix('library')->name('library.')->group(function (): void {
    Route::get('books', [BookController::class, 'index'])->name('books.index');

    Route::middleware(EnsureAdmin::class)->group(function (): void {
        Route::delete('books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
        Route::patch('books/{id}/restore', [BookController::class, 'restore'])
            ->whereNumber('id')
            ->name('books.restore');
    });
});
