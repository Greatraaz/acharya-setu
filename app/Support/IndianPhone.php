<?php

namespace App\Support;

use App\Rules\IndianMobile;

/**
 * Indian mobile numbers: 10 digits starting with 6–9, optional +91 / 91 / 0 prefix.
 * Canonical storage format: +91XXXXXXXXXX
 */
final class IndianPhone
{
    public static function localTenDigits(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone) ?? '';

        if (strlen($digits) >= 12 && str_starts_with($digits, '91')) {
            return substr($digits, -10);
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return substr($digits, -10);
        }

        return substr($digits, -10);
    }

    public static function isValid(?string $phone): bool
    {
        if ($phone === null || trim((string) $phone) === '') {
            return false;
        }

        $local = self::localTenDigits($phone);

        return (bool) preg_match('/^[6-9]\d{9}$/', $local);
    }

    /**
     * Normalize to +91XXXXXXXXXX, or null when empty/invalid.
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null || trim((string) $phone) === '') {
            return null;
        }

        if (! self::isValid($phone)) {
            return null;
        }

        return '+91'.self::localTenDigits($phone);
    }

    /**
     * Laravel validation rule list for phone fields.
     *
     * @return list<mixed>
     */
    public static function rules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'max:20',
            new IndianMobile(),
        ];
    }
}
