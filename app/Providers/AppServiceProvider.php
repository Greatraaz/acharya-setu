<?php

namespace App\Providers;

use App\Models\ConsultationSession;
use App\Models\InsightEvent;
use App\Observers\ConsultationSessionObserver;
use App\Routing\DashboardUrlGenerator;
use App\Services\PublicFileStorage;
use App\Support\UrlId;
use Carbon\Carbon;
use App\Mail\RedactingLogTransport;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Log\LogManager;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend('url', function ($url, $app) {
            $generator = new DashboardUrlGenerator(
                $app['router']->getRoutes(),
                $url->getRequest(),
                $app['config']['app.asset_url']
            );

            $generator->setSessionResolver(fn () => $app['session'] ?? null);
            $generator->setKeyResolver(function () use ($app) {
                $config = $app->make('config');

                return [$config->get('app.key'), ...($config->get('app.previous_keys') ?? [])];
            });

            return $generator;
        });
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

        $this->app->booted(function () {
            AbstractPaginator::queryStringResolver(function () {
                $query = request()->query();
                $name = request()->route()?->getName();

                return UrlId::shouldHide($name) ? UrlId::encodeParameters($query) : $query;
            });
        });

        if (! $this->app->runningInConsole()) {
            try {
                if (\Illuminate\Support\Facades\Cache::add('session_reminders_tick', 1, now()->addMinutes(5))) {
                    app(\App\Services\NotificationService::class)->sendDueSessionReminders();
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
