<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

class LoginThrottle
{
    public const MAX_ATTEMPTS = 5;

    public const DECAY_SECONDS = 900;

    public static function key(string $email, string $ip): string
    {
        return 'login:'.sha1(strtolower(trim($email)).'|'.$ip);
    }

    public static function availableIn(string $email, string $ip): ?int
    {
        $key = static::key($email, $ip);

        if (! RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return null;
        }

        return RateLimiter::availableIn($key);
    }

    public static function hit(string $email, string $ip): void
    {
        RateLimiter::hit(static::key($email, $ip), self::DECAY_SECONDS);
    }

    public static function clear(string $email, string $ip): void
    {
        RateLimiter::clear(static::key($email, $ip));
    }

    public static function lockedMessage(int $seconds): string
    {
        $minutes = max(1, (int) ceil($seconds / 60));

        return "Too many login attempts. Try again in {$minutes} minute".($minutes === 1 ? '' : 's').'.';
    }

    public static function isDisabled(User $user): bool
    {
        return $user->is_active === false || $user->is_active === 0 || $user->is_active === '0';
    }
}
