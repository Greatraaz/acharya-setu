<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

final class RegistrationData
{
    public const TERMS_VERSION = '1.0';

    public static function normalize(Request $request): void
    {
        $name = trim((string) preg_replace('/\s+/u', ' ', (string) $request->input('name', '')));
        $email = strtolower(trim((string) $request->input('email', '')));
        $phone = trim((string) $request->input('phone', ''));

        $request->merge([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
        ]);
    }

    public static function emailTaken(string $email): bool
    {
        $email = strtolower(trim($email));

        return User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->exists();
    }

    public static function phoneTaken(?string $phone): bool
    {
        return User::findByPhone($phone) !== null;
    }
}
