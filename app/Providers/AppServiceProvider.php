<?php

namespace App\Providers;

use App\Models\ConsultationSession;
use App\Models\InsightEvent;
use App\Observers\ConsultationSessionObserver;
use App\Services\PublicFileStorage;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Route;
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

        ResetPassword::createUrlUsing(function (object $user, string $token) {
            return url('/reset-password/'.$token).'?'.http_build_query([
                'email' => $user->getEmailForPasswordReset(),
            ]);
        });

        Route::bind('events_webinar', fn ($value) => InsightEvent::findOrFail($value));

        ConsultationSession::observe(ConsultationSessionObserver::class);
    }
}
