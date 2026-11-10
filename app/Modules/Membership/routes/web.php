<?php

use Illuminate\Support\Facades\Route;
use Modules\Membership\Controllers\MemberController;
use Modules\Shared\Http\Middleware\EnsureAdmin;

Route::middleware(['web', 'auth'])->prefix('library')->name('library.')->group(function (): void {
    Route::get('members', [MemberController::class, 'index'])->name('members.index');

    Route::middleware(EnsureAdmin::class)->group(function (): void {
        Route::delete('members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
        Route::patch('members/{id}/restore', [MemberController::class, 'restore'])
            ->whereNumber('id')
            ->name('members.restore');
    });
});
