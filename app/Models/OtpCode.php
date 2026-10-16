<?php

namespace App\Models;

use App\Support\IndianPhone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Table: otp_codes
 * One active code per destination (identifier + channel).
 * purpose is registration, login, or reset.
 */
class OtpCode extends Model
{
    public const PURPOSES = ['registration', 'login', 'reset'];

    public const TTL_MINUTES = 10;

    public const COOLDOWN_SECONDS = 30;

    public const MAX_SENDS = 5;

    public const SEND_WINDOW_MINUTES = 15;

    public const MAX_ATTEMPTS = 5;

    protected $fillable = ['identifier', 'channel', 'purpose', 'code', 'attempts', 'expires_at', 'verified_at'];

    protected $hidden = ['code'];

    protected $casts = [
        'expires_at'  => 'datetime',
        'verified_at' => 'datetime',
        'attempts'    => 'integer',
    ];

    /**
     * Issue a new OTP, replacing any previous code for this destination.
     *
     * @return array{ok:bool,status:int,message:string,otp?:string,identifier?:string,expires_in?:int,retry_after?:int}
     */
    public static function issue(string $identifier, string $channel, string $purpose): array
    {
        $identifier = static::normalizeIdentifier($identifier, $channel);
        $blocked = static::sendBlocked($identifier, $channel);
        if ($blocked) {
            return $blocked;
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        static::updateOrCreate(
            ['identifier' => $identifier, 'channel' => $channel],
            [
                'purpose'     => $purpose,
                'code'        => bcrypt($otp),
                'attempts'    => 0,
                'expires_at'  => now()->addMinutes(self::TTL_MINUTES),
                'verified_at' => null,
            ]
        );

        static::rememberSend($identifier, $channel);

        return [
            'ok'         => true,
            'status'     => 200,
            'message'    => 'OTP sent successfully.',
            'otp'        => $otp,
            'identifier' => $identifier,
            'expires_in' => self::TTL_MINUTES * 60,
        ];
    }

    /**
     * Check a code and consume it on success.
     * Returns ok, invalid, expired, used, missing, mismatch, or locked.
     */
    public static function verify(string $identifier, string $channel, string $otp, ?string $purpose = null): string
    {
        $identifier = static::normalizeIdentifier($identifier, $channel);
        $otp = trim($otp);

        if (! preg_match('/^\d{6}$/', $otp)) {
            return 'invalid';
        }

        return DB::transaction(function () use ($identifier, $channel, $otp, $purpose) {
            $record = static::query()
                ->where('identifier', $identifier)
                ->where('channel', $channel)
                ->lockForUpdate()
                ->first();

            if (! $record) {
                return 'missing';
            }

            if ($purpose !== null && $record->purpose !== $purpose) {
                return 'mismatch';
            }

            if ($record->verified_at) {
                return 'used';
            }

            if ($record->expires_at->lte(now())) {
                return 'expired';
            }

            if ($record->attempts >= self::MAX_ATTEMPTS) {
                return 'locked';
            }

            if (! password_verify($otp, $record->code)) {
                $record->increment('attempts');
                if ($record->fresh()->attempts >= self::MAX_ATTEMPTS) {
                    $record->update(['expires_at' => now()]);
                }

                return 'invalid';
            }

            $consumed = static::query()
                ->whereKey($record->id)
                ->whereNull('verified_at')
                ->update(['verified_at' => now()]);

            return $consumed === 1 ? 'ok' : 'used';
        });
    }

    public static function message(string $result): string
    {
        return match ($result) {
            'ok'       => 'OTP verified.',
            'expired'  => 'This OTP has expired. Request a new one.',
            'used'     => 'This OTP has already been used.',
            'mismatch' => 'This OTP was sent for a different request.',
            'locked'   => 'Too many incorrect attempts. Request a new OTP.',
            'invalid', 'missing' => 'Incorrect OTP. Please try again.',
            default    => 'Invalid or expired OTP.',
        };
    }

    public static function normalizeIdentifier(string $identifier, string $channel): string
    {
        $identifier = trim($identifier);

        if ($channel === 'email') {
            return strtolower($identifier);
        }

        return IndianPhone::normalize($identifier) ?? $identifier;
    }

    /**
     * @return array{ok:bool,status:int,message:string,retry_after:int}|null
     */
    private static function sendBlocked(string $identifier, string $channel): ?array
    {
        $until = Cache::get(static::cooldownKey($identifier, $channel));
        if (is_int($until) && $until > time()) {
            $wait = $until - time();

            return [
                'ok'          => false,
                'status'      => 429,
                'message'     => "Please wait {$wait} seconds before requesting another OTP.",
                'retry_after' => $wait,
            ];
        }

        $count = (int) Cache::get(static::rateKey($identifier, $channel), 0);
        if ($count >= self::MAX_SENDS) {
            return [
                'ok'          => false,
                'status'      => 429,
                'message'     => 'Too many OTP requests. Please try again in a few minutes.',
                'retry_after' => 60,
            ];
        }

        return null;
    }

    private static function rememberSend(string $identifier, string $channel): void
    {
        Cache::put(static::cooldownKey($identifier, $channel), time() + self::COOLDOWN_SECONDS, self::COOLDOWN_SECONDS);

        $rateKey = static::rateKey($identifier, $channel);
        if (! Cache::has($rateKey)) {
            Cache::put($rateKey, 1, now()->addMinutes(self::SEND_WINDOW_MINUTES));
        } else {
            Cache::increment($rateKey);
        }
    }

    public static function cooldownKey(string $identifier, string $channel): string
    {
        return 'otp-cooldown:'.$channel.':'.sha1(static::normalizeIdentifier($identifier, $channel));
    }

    public static function rateKey(string $identifier, string $channel): string
    {
        return 'otp-rate:'.$channel.':'.sha1(static::normalizeIdentifier($identifier, $channel));
    }

    public static function cleanup(): void
    {
        static::where('expires_at', '<', now()->subHours(1))->delete();
    }
}
