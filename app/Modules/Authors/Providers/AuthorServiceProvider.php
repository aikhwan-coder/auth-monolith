<?php

namespace Modules\Authors\Providers;

use Illuminate\Support\ServiceProvider;

class AuthorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }
}
