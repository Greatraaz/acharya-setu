<?php

namespace App\Models;

use App\Services\PublicFileStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DownloadCentre extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'title',
        'slug',
        'status',
        'image',
        'document',
        'description',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $item): void {
            if (empty($item->slug)) {
                $item->slug = static::uniqueSlug($item->title);
            }
        });

        static::updating(function (self $item): void {
            if ($item->isDirty('title') && ! $item->isDirty('slug')) {
                $item->slug = static::uniqueSlug($item->title, $item->id);
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

    public function documentUrl(): ?string
    {
        $path = PublicFileStorage::url($this->document);

        return $path ? url($path) : null;
    }

    public function documentExtension(): string
    {
        $path = PublicFileStorage::pathFromUrl($this->document) ?? ltrim((string) $this->document, '/');

        return strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'pdf');
    }

    public function downloadFilename(): string
    {
        $base = Str::slug($this->title) ?: 'download';
        $ext = $this->documentExtension();

        return $base.'.'.$ext;
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
        $base = Str::slug($title) ?: 'download';
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
