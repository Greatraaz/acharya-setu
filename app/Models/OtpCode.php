<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Table: otp_codes
 * Columns: id, identifier (email/phone), channel (email|phone),
 *          code (hashed), expires_at, verified_at, created_at, updated_at
 */
class OtpCode extends Model
{
    protected $fillable = ['identifier','channel','code','expires_at','verified_at'];

    protected $casts = [
        'expires_at'   => 'datetime',
        'verified_at'  => 'datetime',
    ];

    /**
     * Store (or replace) an OTP for the given identifier + channel.
     * The OTP is hashed before saving.
     */
    public static function storeOtp(string $identifier, string $channel, string $otp): void
    {
        static::updateOrCreate(
            ['identifier' => $identifier, 'channel' => $channel],
            [
                'code'         => bcrypt($otp),          // hash it
                'expires_at'   => now()->addMinutes(10),
                'verified_at'  => null,
            ]
        );
    }

    /**
     * Verify the OTP.  Returns true on success and marks the record verified.
     */
    public static function verify(string $identifier, string $channel, string $otp): bool
    {
        $record = static::where('identifier', $identifier)
            ->where('channel', $channel)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $record) return false;
        if (! password_verify($otp, $record->code)) return false;

        $record->update(['verified_at' => now()]);
        return true;
    }

    /**
     * Clean up expired/verified OTPs (call from a scheduled command).
     */
    public static function cleanup(): void
    {
        static::where('expires_at', '<', now()->subHours(1))->delete();
    }
}