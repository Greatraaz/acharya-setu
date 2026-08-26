<?php

namespace App\Models;

use App\Services\PublicFileStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class WhitePaper extends Model
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
        static::creating(function (self $paper): void {
            if (empty($paper->slug)) {
                $paper->slug = static::uniqueSlug($paper->title);
            }
        });

        static::updating(function (self $paper): void {
            if ($paper->isDirty('title') && ! $paper->isDirty('slug')) {
                $paper->slug = static::uniqueSlug($paper->title, $paper->id);
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

    public function imageUrl(): ?string
    {
        $path = PublicFileStorage::url($this->image);

        return $path ? url($path) : null;
    }

    public function downloadFilename(): string
    {
        $base = Str::slug($this->title) ?: 'white-paper';

        return $base.'.pdf';
    }

    public function excerpt(int $words = 28): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->description)) ?? '');

        if ($text === '') {
            return '';
        }

        return Str::words($text, $words, '…');
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'white-paper';
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
