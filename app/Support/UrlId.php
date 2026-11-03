<?php

namespace App\Support;

use Illuminate\Contracts\Routing\UrlRoutable;

class UrlId
{
    private const CIPHER = 'AES-128-CTR';

    private const IV = '1234567891011121';

    private const KEY = '$2y$10$sCCllRntt8aTf3M5lxqyXeabehx7Z5UyIHo8ENeHV2H8BumxFbdKi';

    /** @var list<string> */
    private const SKIP_KEYS = [
        'step', 'page', 'per_page', 'tab', 'days', 'duration', 'year',
        'date', 'filter', 'status', 'q', 'search', 'sort', 'type',
        'view', 'field', 'role', 'format', 'billing', 'token', 'signature',
        'expires',
    ];

    public static function encrypt(mixed $id): string
    {
        $encryption = openssl_encrypt(
            (string) $id,
            self::CIPHER,
            self::KEY,
            0,
            self::IV
        );

        return $encryption === false ? (string) $id : self::toUrl($encryption);
    }

    public static function decrypt(mixed $encryption): mixed
    {
        if ($encryption === null || $encryption === '' || is_bool($encryption) || is_array($encryption) || is_object($encryption)) {
            return $encryption;
        }

        if (is_int($encryption) || (is_string($encryption) && ctype_digit($encryption))) {
            return (int) $encryption;
        }

        $plain = openssl_decrypt(
            self::fromUrl((string) $encryption),
            self::CIPHER,
            self::KEY,
            0,
            self::IV
        );

        if ($plain === false || ! preg_match('/^\d+$/', $plain)) {
            return null;
        }

        return (int) $plain;
    }

    public static function shouldHide(?string $name): bool
    {
        if (! is_string($name) || $name === '') {
            return false;
        }

        if (str_starts_with($name, 'admin.')) {
            return false;
        }

        return str_starts_with($name, 'mentor.')
            || str_starts_with($name, 'mentee.')
            || str_starts_with($name, 'sessions.')
            || str_starts_with($name, 'mock-interviews.')
            || str_starts_with($name, 'notifications.');
    }

    public static function isQueryIdKey(string $key): bool
    {
        return $key === 'id' || str_ends_with($key, '_id');
    }

    public static function encodeParameters(mixed $parameters): array
    {
        if ($parameters instanceof UrlRoutable) {
            $parameters = [$parameters];
        } elseif (! is_array($parameters)) {
            $parameters = $parameters === null ? [] : [$parameters];
        }

        $out = [];
        foreach ($parameters as $key => $value) {
            if ($value instanceof UrlRoutable) {
                $routeKey = $value->getRouteKey();
                $out[$key] = self::shouldEncode($key, $routeKey)
                    ? self::encrypt($routeKey)
                    : $value;
                continue;
            }

            $out[$key] = self::shouldEncode($key, $value)
                ? self::encrypt($value)
                : $value;
        }

        return $out;
    }

    public static function modelPayload(object|array $model): array
    {
        $data = is_array($model) ? $model : $model->toArray();
        if (isset($data['id']) && self::isIdValue($data['id'])) {
            $data['id'] = self::encrypt($data['id']);
        }

        return $data;
    }

    public static function publicId(?array $payload): ?array
    {
        if ($payload === null) {
            return null;
        }

        if (isset($payload['id']) && self::isIdValue($payload['id'])) {
            $payload['id'] = self::encrypt($payload['id']);
        }

        return $payload;
    }

    private static function shouldEncode(mixed $key, mixed $value): bool
    {
        if (is_string($key) && in_array($key, self::SKIP_KEYS, true)) {
            return false;
        }

        return self::isIdValue($value);
    }

    private static function isIdValue(mixed $value): bool
    {
        if (is_int($value)) {
            return $value > 0;
        }

        return is_string($value) && ctype_digit($value) && (int) $value > 0;
    }

    private static function toUrl(string $value): string
    {
        return rtrim(strtr($value, '+/', '-_'), '=');
    }

    private static function fromUrl(string $value): string
    {
        $value = strtr($value, '-_', '+/');
        $pad = strlen($value) % 4;

        return $pad ? $value.str_repeat('=', 4 - $pad) : $value;
    }
}
