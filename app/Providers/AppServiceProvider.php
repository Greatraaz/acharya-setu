<?php

namespace App\Providers;

use App\Services\PublicFileStorage;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force PHP runtime timezone to IST
        $tz = config('app.timezone', 'Asia/Kolkata');
        date_default_timezone_set($tz);
        Carbon::setLocale(config('app.locale', 'en'));

        try {
            PublicFileStorage::ensureStorageReady();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
