<?php

namespace App\Models;

use App\Services\PublicFileStorage;
use App\Support\YoutubeUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Podcast extends Model
{
    use SoftDeletes;

    public const TYPE_AUDIO = 'audio';
    public const TYPE_YOUTUBE = 'youtube_url';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const TYPES = [
        self::TYPE_AUDIO => 'Audio',
        self::TYPE_YOUTUBE => 'YouTube URL',
    ];

    protected $fillable = [
        'title',
        'slug',
        'image',
        'description',
        'podcast_type',
        'audio',
        'youtube_url',
        'status',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $podcast): void {
            if (empty($podcast->slug)) {
                $podcast->slug = static::uniqueSlug($podcast->title);
            }
        });

        static::updating(function (self $podcast): void {
            if ($podcast->isDirty('title') && ! $podcast->isDirty('slug')) {
                $podcast->slug = static::uniqueSlug($podcast->title, $podcast->id);
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

    public function isAudio(): bool
    {
        return $this->podcast_type === self::TYPE_AUDIO;
    }

    public function isYoutube(): bool
    {
        return $this->podcast_type === self::TYPE_YOUTUBE;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->podcast_type] ?? ucfirst((string) $this->podcast_type);
    }

    public function imageUrl(): ?string
    {
        $path = PublicFileStorage::url($this->image);

        return $path ? url($path) : null;
    }

    public function audioUrl(): ?string
    {
        $path = PublicFileStorage::url($this->audio);

        return $path ? url($path) : null;
    }

    public function thumbnailUrl(): ?string
    {
        if ($this->imageUrl()) {
            return $this->imageUrl();
        }

        if ($this->isYoutube()) {
            return YoutubeUrl::thumbnailUrl($this->youtube_url);
        }

        return null;
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
        $base = Str::slug($title) ?: 'podcast';
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

    public static function extractYoutubeId(?string $url): ?string
    {
        return YoutubeUrl::extractId($url);
    }
}
