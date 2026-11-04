<?php

use Illuminate\Support\Facades\Route;
use Modules\Authors\Controllers\AuthorController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::resource('authors', AuthorController::class);
});
