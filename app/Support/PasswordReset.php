<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordReset
{
    public const GENERIC_SENT = 'If an account exists for this email or mobile, a reset code has been sent.';

    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public static function rules(): array
    {
        return ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->numbers()];
    }

    /**
     * missing, expired, invalid, or ok.
     */
    public static function inspect(string $email, string $token): string
    {
        $record = DB::table('password_reset_tokens')->where('email', static::normalizeEmail($email))->first();

        if (! $record) {
            return 'missing';
        }

        $minutes = (int) config('auth.passwords.users.expire', 60);
        if (Carbon::parse($record->created_at)->addMinutes($minutes)->isPast()) {
            return 'expired';
        }

        if (! Hash::check($token, $record->token)) {
            return 'invalid';
        }

        return 'ok';
    }

    public static function message(string $outcome): string
    {
        return match ($outcome) {
            'expired' => 'This reset link has expired. Request a new one.',
            'missing' => 'This reset link is invalid or has already been used.',
            'invalid' => 'This reset link is invalid or has already been used.',
            default   => 'This reset link is invalid. Please request a new one.',
        };
    }

    public static function apply(User $user, string $password): void
    {
        $user->forceFill([
            'password'       => Hash::make($password),
            'remember_token' => \Illuminate\Support\Str::random(60),
        ])->save();

        $user->tokens()->delete();
    }
}
