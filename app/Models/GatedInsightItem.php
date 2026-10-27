<?php

namespace App\Models;

use App\Services\PublicFileStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class GatedInsightItem extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'type',
        'title',
        'slug',
        'status',
        'image',
        'description',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $item): void {
            if (empty($item->slug)) {
                $item->slug = static::uniqueSlug((string) $item->type, $item->title);
            }
        });

        static::updating(function (self $item): void {
            if ($item->isDirty('title') && ! $item->isDirty('slug')) {
                $item->slug = static::uniqueSlug((string) $item->type, $item->title, $item->id);
            }
        });
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(GatedInsightDownload::class);
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
        $base = Str::slug($this->title) ?: 'vedrix-insight';

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

    public static function uniqueSlug(string $type, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'insight';
        $slug = $base;
        $i = 2;

        while (
            static::withTrashed()
                ->where('type', $type)
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
