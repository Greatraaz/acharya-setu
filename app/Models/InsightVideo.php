<?php

namespace App\Models;

use App\Support\YoutubeUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InsightVideo extends Model
{
    use SoftDeletes;

    protected $table = 'insight_videos';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'youtube_url',
        'status',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $video): void {
            if (empty($video->slug)) {
                $video->slug = static::uniqueSlug($video->title);
            }
        });

        static::updating(function (self $video): void {
            if ($video->isDirty('title') && ! $video->isDirty('slug')) {
                $video->slug = static::uniqueSlug($video->title, $video->id);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return ($this->status ?? self::STATUS_ACTIVE) === self::STATUS_ACTIVE;
    }

    public function thumbnailUrl(): ?string
    {
        return YoutubeUrl::thumbnailUrl($this->youtube_url);
    }

    public function youtubeEmbedUrl(bool $autoplay = false): ?string
    {
        return YoutubeUrl::embedUrl($this->youtube_url, $autoplay);
    }

    public function youtubeWatchUrl(): ?string
    {
        return YoutubeUrl::watchUrl($this->youtube_url);
    }

    public function excerpt(int $words = 28): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->description)) ?? '');

        if ($text === '') {
            return '';
        }

        return Str::words($text, $words, '…');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'video';
        $slug = $base;
        $i = 2;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
