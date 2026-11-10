<?php

namespace Modules\Catalog\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Catalog\Services\BookService;
use Modules\Shared\Contracts\BookServiceInterface;

class rviceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Binding contract -> implementasi (Catalog Context)
        $this->app->bind(BookServiceInterface::class, BookService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}
