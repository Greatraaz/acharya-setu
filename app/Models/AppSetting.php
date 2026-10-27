<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing  = false;
    protected $keyType    = 'string';
 
    protected $fillable = ['key', 'value'];
 
    /**
     * Get a config value by key (with optional default).
     * Results are cached for 60 minutes.
     */
    public static function allCached(): array
    {
        return Cache::remember('app_configurations', 3600, function () {
            return static::all()->pluck('value', 'key')->toArray();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::allCached();

        return $all[$key] ?? $default;
    }
 
    /**
     * Set a config value and clear cache.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('app_configurations');
    }

    public static function isMaintenanceMode(): bool 
    {
         return static::get('maintenance_mode', '0') === '1'; 
    }
    
    public static function isTwoFactorEnabled(): bool 
    {
         return static::get('two_factor_auth', '0') === '1'; 
    }
    
    public static function isEmailVerificationEnabled(): bool 
    {
         return static::get('email_verification', '1') === '1'; 
    }
    
    public static function isUserRegistrationEnabled(): bool 
    {
         return static::get('user_registration', '1') === '1'; 
    }
    


    // ── Helpers for specific setting groups ───────────────────
 
    /** Mail / SMTP settings */
    public static function mail(): array
    {
        $s = static::allCached();

        $host = trim((string) ($s['mail_host'] ?? $s['smtp_host'] ?? ''));
        $username = trim((string) ($s['mail_username'] ?? $s['smtp_username'] ?? ''));
        $fromAddress = trim((string) ($s['mail_from_address'] ?? ''));
        $password = static::decryptIfNeeded($s['mail_password'] ?? null)
            ?? static::decryptIfNeeded($s['smtp_password'] ?? null)
            ?? '';
        $password = is_string($password) ? trim($password) : '';
        if ($password !== '' && (str_contains(strtolower($host), 'gmail') || str_contains(strtolower($host), 'google'))) {
            $password = str_replace(' ', '', $password);
        }

        // Hostinger and most SMTP hosts only relay mail when From matches the
        // authenticated mailbox. A mismatched fallback like hello@vedrix.com
        // is rejected with 550 "Sender is not allowed to relay emails".
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            if ($fromAddress === '' || strcasecmp($fromAddress, $username) !== 0) {
                $fromAddress = $username;
            }
        } elseif ($fromAddress === '' || filter_var($fromAddress, FILTER_VALIDATE_EMAIL) === false) {
            $fromAddress = 'hello@vedrix.online';
        }

        return [
            'driver'       => $s['mail_driver'] ?? $s['mail_mailer'] ?? 'smtp',
            'host'         => $host,
            'port'         => (int) ($s['mail_port'] ?? $s['smtp_port'] ?? 587),
            'encryption'   => $s['mail_encryption'] ?? $s['smtp_encryption'] ?? 'tls',
            'username'     => $username,
            'password'     => $password,
            'from_address' => $fromAddress,
            'from_name'    => $s['mail_from_name'] ?? ($s['app_name'] ?? 'Vedrix'),
        ];
    }
 
    /** Razorpay settings */
    public static function razorpay(): array
    {
        $s = static::allCached();
        $mode = strtolower((string) ($s['razorpay_mode'] ?? 'test')) === 'live' ? 'live' : 'test';
        $prefix = $mode === 'live' ? 'rzp_live_' : 'rzp_test_';

        $pairs = [
            [
                $s["razorpay_{$mode}_key"] ?? null,
                static::decryptIfNeeded($s["razorpay_{$mode}_secret"] ?? null),
            ],
            [
                $s['razorpay_key_id'] ?? null,
                static::decryptIfNeeded($s['razorpay_key_secret'] ?? null),
            ],
            [
                $s['razorpay_key'] ?? null,
                static::decryptIfNeeded($s['razorpay_secret'] ?? null),
            ],
            [
                config('services.razorpay.key'),
                config('services.razorpay.secret'),
            ],
            [
                env('RAZORPAY_KEY_ID'),
                env('RAZORPAY_KEY_SECRET'),
            ],
        ];

        $key = '';
        $secret = '';
        $sawOtherMode = false;

        foreach ($pairs as [$candidateKey, $candidateSecret]) {
            $candidateKey = trim((string) $candidateKey);
            $candidateSecret = trim((string) $candidateSecret);
            if ($candidateKey === '') {
                continue;
            }
            if (! str_starts_with($candidateKey, $prefix)) {
                $sawOtherMode = true;
                continue;
            }
            if ($candidateSecret === '') {
                continue;
            }
            $key = $candidateKey;
            $secret = $candidateSecret;
            break;
        }

        $message = null;
        if ($key === '' || $secret === '') {
            $message = $sawOtherMode
                ? 'Razorpay mode is '.ucfirst($mode).', but the saved key is not a '.$prefix.' key. Paste the '.ucfirst($mode).' Key ID and secret in admin settings.'
                : 'Payment gateway is not configured.';
        }

        return [
            'mode'    => $mode,
            'enabled' => static::toBool($s['razorpay_enabled'] ?? false),
            'key'     => $key,
            'secret'  => $secret,
            'message' => $message,
        ];
    }

    private static function firstNonEmpty(array $values): string
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return '';
    }

    private static function decryptIfNeeded(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $current = $value;

        // Secrets may have been encrypted more than once when re-saving settings.
        for ($i = 0; $i < 3; $i++) {
            $looksEncrypted = strlen($current) > 80 || str_starts_with($current, 'eyJpdiI6');
            if (! $looksEncrypted) {
                return $current;
            }

            try {
                $plain = decrypt($current);
                if (! is_string($plain) || $plain === '') {
                    return null;
                }
                $current = $plain;
            } catch (\Throwable) {
                // Undecryptable ciphertext — force re-entry in admin settings.
                return null;
            }
        }

        return strlen($current) > 80 ? null : $current;
    }

    private static function toBool(mixed $value): bool
    {
        return in_array((string) $value, ['1', 'true', 'yes', 'on'], true);
    }
 
    /** MSG91 SMS settings */
    public static function msg91(): array
    {
        $s = static::allCached();
        return [
            'auth_key'    => $s['msg91_auth_key']        ?? '',
            'sender_id'   => $s['msg91_sender_id']       ?? 'VEDRIX',
            'route'       => $s['msg91_route']            ?? '4',
            'template_id' => $s['msg91_otp_template_id'] ?? '',
        ];
    }
 
    /** Fast2SMS settings */
    public static function fast2sms(): array
    {
        $s = static::allCached();
        return [
            'api_key' => $s['fast2sms_api_key'] ?? '',
            'route'   => $s['fast2sms_route']   ?? 'dlt_manual',
        ];
    }
 
    /** Active SMS provider name */
    public static function smsProvider(): string
    {
        return static::get('sms_active_provider', 'msg91');
    }
 
    /** Agora video settings */
    public static function agora(): array
    {
        $s = static::allCached();

        $appId = static::firstNonEmpty([
            $s['agora_app_id'] ?? null,
            config('services.agora.app_id'),
            env('AGORA_APP_ID'),
        ]);

        $appCert = static::firstNonEmpty([
            static::decryptIfNeeded($s['agora_app_certificate'] ?? null),
            config('services.agora.app_certificate'),
            env('AGORA_APP_CERTIFICATE'),
        ]);

        return [
            'app_id'       => $appId,
            'app_cert'     => $appCert,
            'token_expiry' => (int) ($s['agora_token_expiry'] ?? config('services.agora.token_expiry', 3600)),
        ];
    }
 
    /** App general settings */
    public static function app(): array
    {
        $s = static::allCached();
        return [
            'name'              => $s['app_name']      ?? 'Vedrix',
            'url'               => $s['app_url']       ?? config('app.url'),
            'timezone'          => $s['timezone']      ?? 'Asia/Kolkata',
            'currency'          => $s['default_currency'] ?? 'INR',
            'currency_symbol'   => $s['currency_symbol']  ?? '₹',
            'date_format'       => $s['date_format']   ?? 'd/m/Y',
            'maintenance_mode'  => (bool)($s['maintenance_mode'] ?? false),
        ];
    }

    /** Firebase Cloud Messaging server key (legacy HTTP). Empty when not configured. */
    public static function fcmServerKey(): string
    {
        $s = static::allCached();
        $raw = static::firstNonEmpty([
            $s['fcm_server_key'] ?? null,
            config('services.fcm.server_key'),
            env('FCM_SERVER_KEY'),
        ]);

        return (string) (static::decryptIfNeeded($raw) ?: '');
    }

    /**
     * Firebase service-account JSON for FCM HTTP v1.
     *
     * @return array<string, mixed>|null
     */
    public static function fcmServiceAccount(): ?array
    {
        $s = static::allCached();
        $raw = static::firstNonEmpty([
            $s['fcm_service_account'] ?? null,
            config('services.fcm.service_account'),
            env('FCM_SERVICE_ACCOUNT'),
            env('GOOGLE_APPLICATION_CREDENTIALS'),
        ]);
        $raw = (string) (static::decryptIfNeeded($raw) ?: '');
        if ($raw === '') {
            return null;
        }

        if (is_file($raw) && is_readable($raw)) {
            $raw = (string) file_get_contents($raw);
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || empty($decoded['client_email']) || empty($decoded['private_key']) || empty($decoded['project_id'])) {
            return null;
        }

        return $decoded;
    }

    /** Career + mock interview paid add-on prices. */
    public static function careerAddons(): array
    {
        return [
            'resume_price'              => round((float) static::get('addon_resume_price', 499), 2),
            'linkedin_price'            => round((float) static::get('addon_linkedin_price', 499), 2),
            'mock_interview_per_minute' => round((float) static::get('addon_mock_interview_rate_per_minute', 15), 2),
            'currency'                  => 'INR',
        ];
    }

    /** Seller / invoice billing details */
    public static function billing(): array
    {
        $s = static::allCached();
        $app = static::app();

        return [
            'company_name' => $s['invoice_company_name'] ?? ($app['name'] ?? 'Vedrix'),
            'gstin'        => $s['invoice_gstin'] ?? '',
            'address'      => $s['invoice_address'] ?? '',
            'email'        => $s['invoice_email'] ?? ($s['contact_email'] ?? ''),
            'phone'        => $s['invoice_phone'] ?? ($s['support_phone'] ?? ''),
            'prefix'       => $s['invoice_prefix'] ?? 'INV',
        ];
    }
}
