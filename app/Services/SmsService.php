<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SmsService
 *
 * Reads provider & credentials from app_settings table.
 * Supports MSG91 and Fast2SMS.
 *
 * Usage:
 *   SmsService::send('+919876543210', 'Your OTP is 123456');
 *   SmsService::sendOtp('+919876543210', '123456');
 */
class SmsService
{
    /**
     * Send any SMS message.
     *
     * @param  string $phone   Phone with country code, e.g. +919876543210
     * @param  string $message The message text
     */
    public static function send(string $phone, string $message): bool
    {
        $provider = AppSetting::smsProvider(); // msg91 | fast2sms

        try {
            return match ($provider) {
                'msg91'    => static::sendViaMSG91($phone, $message),
                'fast2sms' => static::sendViaFast2SMS($phone, $message),
                default    => static::logOnly($phone, $message),
            };
        } catch (\Throwable $e) {
            Log::error("[SmsService] Failed ({$provider}): " . $e->getMessage(), [
                'phone'   => $phone,
                'message' => $message,
            ]);
            return false;
        }
    }

    /**
     * Send an OTP SMS.
     * MSG91 uses template_id; Fast2SMS sends a plain message.
     */
    public static function sendOtp(string $phone, string $otp): bool
    {
        $provider = AppSetting::smsProvider();
        $appName  = AppSetting::get('app_name', 'AcharyaSetu');

        try {
            if ($provider === 'msg91') {
                return static::sendOtpViaMSG91($phone, $otp);
            }

            // Fallback: plain text OTP message
            $message = "{$otp} is your {$appName} verification OTP. Valid for 10 minutes. Do not share with anyone.";
            return static::send($phone, $message);

        } catch (\Throwable $e) {
            Log::error("[SmsService] OTP send failed: " . $e->getMessage());
            return false;
        }
    }

    // ── MSG91 ─────────────────────────────────────────────────

    private static function sendViaMSG91(string $phone, string $message): bool
    {
        $cfg     = AppSetting::msg91();
        $authKey = $cfg['auth_key'];

        if (empty($authKey)) {
            Log::warning('[SmsService] MSG91 auth_key not configured.');
            return false;
        }

        $phone = static::normalizePhone($phone);

        $response = Http::withHeaders([
            'authkey'      => $authKey,
            'content-type' => 'application/json',
        ])->post('https://api.msg91.com/api/v5/flow/', [
            'template_id' => $cfg['template_id'] ?: null,
            'short_url'   => '0',
            'realTimeResponse' => '1',
            'recipients'  => [
                ['mobiles' => $phone, 'OTP' => $message],
            ],
        ]);

        $success = $response->successful() &&
                   ($response->json('type') === 'success' || $response->status() === 200);

        Log::info("[SmsService] MSG91 to {$phone}", ['status' => $response->status(), 'body' => $response->body()]);
        return $success;
    }

    private static function sendOtpViaMSG91(string $phone, string $otp): bool
    {
        $cfg     = AppSetting::msg91();
        $authKey = $cfg['auth_key'];

        if (empty($authKey)) {
            Log::warning('[SmsService] MSG91 auth_key not configured.');
            return false;
        }

        $phone = static::normalizePhone($phone);

        // If a specific OTP template_id is set, use the OTP endpoint
        if (!empty($cfg['template_id'])) {
            $response = Http::withHeaders([
                'authkey'      => $authKey,
                'content-type' => 'application/json',
            ])->post('https://api.msg91.com/api/v5/flow/', [
                'template_id' => $cfg['template_id'],
                'recipients'  => [
                    ['mobiles' => $phone, 'OTP' => $otp],
                ],
            ]);

            Log::info("[SmsService] MSG91 OTP to {$phone}", ['status' => $response->status()]);
            return $response->successful();
        }

        // Fallback: plain SMS via MSG91 send endpoint
        $appName = AppSetting::get('app_name', 'AcharyaSetu');
        $message = "{$otp} is your {$appName} OTP. Valid 10 minutes. Do not share.";

        $response = Http::withHeaders(['authkey' => $authKey])
            ->post("https://api.msg91.com/api/sendhttp.php", [
                'authkey'  => $authKey,
                'mobiles'  => $phone,
                'message'  => $message,
                'sender'   => $cfg['sender_id'],
                'route'    => $cfg['route'],
                'country'  => '91',
            ]);

        Log::info("[SmsService] MSG91 plain to {$phone}", ['status' => $response->status()]);
        return $response->successful();
    }

    // ── Fast2SMS ──────────────────────────────────────────────

    private static function sendViaFast2SMS(string $phone, string $message): bool
    {
        $cfg    = AppSetting::fast2sms();
        $apiKey = $cfg['api_key'];

        if (empty($apiKey)) {
            Log::warning('[SmsService] Fast2SMS api_key not configured.');
            return false;
        }

        // Strip country code for Fast2SMS
        $phone = preg_replace('/^\+91/', '', $phone);
        $phone = preg_replace('/^91/', '', $phone);

        $response = Http::withHeaders([
            'authorization' => $apiKey,
            'Content-Type'  => 'application/json',
        ])->post('https://www.fast2sms.com/dev/bulkV2', [
            'route'    => $cfg['route'] ?? 'q',
            'message'  => $message,
            'language' => 'english',
            'flash'    => '0',
            'numbers'  => $phone,
        ]);

        Log::info("[SmsService] Fast2SMS to {$phone}", ['status' => $response->status(), 'body' => $response->body()]);
        return $response->successful();
    }

    // ── Log-only (no provider configured) ────────────────────

    private static function logOnly(string $phone, string $message): bool
    {
        Log::info("[SmsService] No provider — SMS not sent", [
            'phone'   => $phone,
            'message' => $message,
        ]);
        return true; // Return true so dev flow isn't broken
    }

    // ── Normalize phone to +91XXXXXXXXXX format ───────────────

    private static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 10) {
            return '91' . $digits;
        }
        if (str_starts_with($digits, '91') && strlen($digits) === 12) {
            return $digits;
        }
        return $digits;
    }
}