<?php

namespace App\Providers;

use App\Services\PublicFileStorage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            PublicFileStorage::ensureStorageReady();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
