<?php

namespace App\Listeners;

use App\Services\ActivityLogger;
use Illuminate\Auth\Events\{Login, Logout, Failed};
 
class LogAuthEvents
{
    public function handleLogin(Login $event): void
    {
        ActivityLogger::login($event->user);
    }
 
    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            ActivityLogger::logout($event->user);
        }
    }
 
    public function handleFailed(Failed $event): void
    {
        ActivityLogger::loginFailed($event->credentials['email'] ?? 'unknown');
    }
}
