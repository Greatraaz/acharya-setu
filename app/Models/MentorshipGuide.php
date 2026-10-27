<?php

namespace App\Models;

use App\Services\PublicFileStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MentorshipGuide extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'title',
        'slug',
        'status',
        'image',
        'description',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $guide): void {
            if (empty($guide->slug)) {
                $guide->slug = static::uniqueSlug($guide->title);
            }
        });

        static::updating(function (self $guide): void {
            if ($guide->isDirty('title') && ! $guide->isDirty('slug')) {
                $guide->slug = static::uniqueSlug($guide->title, $guide->id);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(MentorshipGuideDownload::class);
    }

    public function isActive(): bool
    {
        return ($this->status ?? self::STATUS_ACTIVE) === self::STATUS_ACTIVE;
    }

    public function imageUrl(): ?string
    {
        $path = PublicFileStorage::url($this->image);

        return $path ? url($path) : null;
    }

    public function downloadFilename(): string
    {
        $base = Str::slug($this->title) ?: 'mentorship-guide';

        return $base.'.pdf';
    }

    public function excerpt(int $words = 28): string
    {
        return html_excerpt($this->description, $words);
    }

    public function readTimeMinutes(): int
    {
        $words = str_word_count(strip_tags((string) $this->description));

        return max(1, (int) ceil($words / 200));
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'mentorship-guide';
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
