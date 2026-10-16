<?php

namespace App\Providers;

use App\Models\ConsultationSession;
use App\Models\InsightEvent;
use App\Observers\ConsultationSessionObserver;
use App\Services\PublicFileStorage;
use Carbon\Carbon;
use App\Mail\RedactingLogTransport;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

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

        // Keep "remember me" cookies aligned with 1-week session lifetime.
        Auth::guard('web')->setRememberDuration((int) config('session.lifetime', 10080));

        try {
            PublicFileStorage::ensureStorageReady();
        } catch (\Throwable $e) {
            report($e);
        }

        Mail::extend('log', function (array $config = []) {
            $logger = $this->app->make(LoggerInterface::class);

            if ($logger instanceof LogManager) {
                $logger = $logger->channel($config['channel'] ?? config('mail.log_channel'));
            }

            return new RedactingLogTransport($logger);
        });

        ResetPassword::createUrlUsing(function (object $user, string $token) {
            return url('/reset-password/'.$token).'?'.http_build_query([
                'email' => $user->getEmailForPasswordReset(),
            ]);
        });

        Route::bind('events_webinar', fn ($value) => InsightEvent::findOrFail($value));

        ConsultationSession::observe(ConsultationSessionObserver::class);
    }
}
