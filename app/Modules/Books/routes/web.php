<?php

use Illuminate\Support\Facades\Route;
use Modules\Books\Controllers\BookController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::resource('books', BookController::class);
    Route::post('books/{book}/borrow', [BookController::class, 'borrow'])->name('books.borrow');
});