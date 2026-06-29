<?php

namespace App\Models;

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
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::remember('app_configurations', 3600, function () {
            return static::all()->pluck('value', 'key')->toArray();
        });
 
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
        return [
            'driver'     => $s['mail_driver']     ?? $s['mail_mailer']  ?? 'smtp',
            'host'       => $s['smtp_host']        ?? '',
            'port'       => (int) ($s['smtp_port'] ?? 587),
            'encryption' => $s['smtp_encryption']  ?? 'tls',
            'username'   => $s['smtp_username']    ?? '',
            'password'   => $s['smtp_password']    ?? '',
            'from_address' => $s['mail_from_address'] ?? 'hello@acharyasetu.com',
            'from_name'    => $s['mail_from_name']    ?? ($s['app_name'] ?? 'AcharyaSetu'),
        ];
    }
 
    /** Razorpay settings */
    public static function razorpay(): array
    {
        $s = static::allCached();
        $mode = $s['razorpay_mode'] ?? 'test';
        return [
            'mode'   => $mode,
            'key'    => $s["razorpay_{$mode}_key"]    ?? $s['razorpay_key']    ?? '',
            'secret' => $s["razorpay_{$mode}_secret"] ?? $s['razorpay_secret'] ?? '',
        ];
    }
 
    /** MSG91 SMS settings */
    public static function msg91(): array
    {
        $s = static::allCached();
        return [
            'auth_key'    => $s['msg91_auth_key']        ?? '',
            'sender_id'   => $s['msg91_sender_id']       ?? 'ACHSETU',
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
        return [
            'app_id'      => $s['agora_app_id']          ?? '',
            'app_cert'    => $s['agora_app_certificate']  ?? '',
            'token_expiry'=> (int)($s['agora_token_expiry'] ?? 3600),
        ];
    }
 
    /** App general settings */
    public static function app(): array
    {
        $s = static::allCached();
        return [
            'name'              => $s['app_name']      ?? 'AcharyaSetu',
            'url'               => $s['app_url']       ?? config('app.url'),
            'timezone'          => $s['timezone']      ?? 'Asia/Kolkata',
            'currency'          => $s['default_currency'] ?? 'INR',
            'currency_symbol'   => $s['currency_symbol']  ?? '₹',
            'date_format'       => $s['date_format']   ?? 'd/m/Y',
            'maintenance_mode'  => (bool)($s['maintenance_mode'] ?? false),
        ];
    }

    
}
