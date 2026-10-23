<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceToken extends Model
{
    public const PLATFORM_WEB     = 'web';
    public const PLATFORM_ANDROID = 'android';
    public const PLATFORM_IOS     = 'ios';

    protected $fillable = [
        'user_id',
        'token',
        'platform',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function register(User $user, string $token, string $platform = self::PLATFORM_WEB): self
    {
        $platform = in_array($platform, [self::PLATFORM_WEB, self::PLATFORM_ANDROID, self::PLATFORM_IOS], true)
            ? $platform
            : self::PLATFORM_WEB;

        return static::updateOrCreate(
            ['token' => $token],
            [
                'user_id'      => $user->id,
                'platform'     => $platform,
                'last_used_at' => now(),
            ]
        );
    }
}
