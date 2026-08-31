<?php

namespace App\Models;

use App\Services\PublicFileStorage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InsightEvent extends Model
{
    use SoftDeletes;

    public const TYPE_WEBINAR = 'webinar';
    public const TYPE_EVENT = 'event';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const TYPES = [
        self::TYPE_WEBINAR => 'Webinar',
        self::TYPE_EVENT => 'Event',
    ];

    protected $fillable = [
        'type',
        'title',
        'slug',
        'speaker',
        'location',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'image',
        'description',
        'event_agenda',
        'who_should_attend',
        'what_you_will_learn',
        'faq',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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

    public function registrations(): HasMany
    {
        return $this->hasMany(InsightEventRegistration::class);
    }

    public function isActive(): bool
    {
        return ($this->status ?? self::STATUS_ACTIVE) === self::STATUS_ACTIVE;
    }

    public function isWebinar(): bool
    {
        return $this->type === self::TYPE_WEBINAR;
    }

    public function isEvent(): bool
    {
        return $this->type === self::TYPE_EVENT;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }

    public function imageUrl(): ?string
    {
        $path = PublicFileStorage::url($this->image);

        return $path ? url($path) : null;
    }

    public function startsAt(): Carbon
    {
        $date = $this->start_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $time = $this->start_time ? substr((string) $this->start_time, 0, 8) : '00:00:00';

        return Carbon::parse($date.' '.$time, config('app.timezone', 'Asia/Kolkata'));
    }

    public function endsAt(): Carbon
    {
        $date = $this->end_date?->format('Y-m-d') ?? $this->start_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $time = $this->end_time ? substr((string) $this->end_time, 0, 8) : '23:59:59';

        return Carbon::parse($date.' '.$time, config('app.timezone', 'Asia/Kolkata'));
    }

    public function isUpcoming(): bool
    {
        return $this->endsAt()->isFuture();
    }

    public function isPast(): bool
    {
        return ! $this->isUpcoming();
    }

    public function scheduleBadge(): string
    {
        return $this->isUpcoming() ? 'UPCOMING' : 'PAST';
    }

    public function dateLabel(): string
    {
        return optional($this->start_date)->format('M d, Y') ?: '—';
    }

    public function timeRangeLabel(): string
    {
        if (! $this->start_time && ! $this->end_time) {
            return '—';
        }

        $start = $this->start_time ? Carbon::parse($this->start_time)->format('h:i A') : '';
        $end = $this->end_time ? Carbon::parse($this->end_time)->format('h:i A') : '';

        if ($start && $end) {
            return $start.' - '.$end;
        }

        return $start ?: $end;
    }

    public function excerpt(int $words = 28): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->description)) ?? '');

        if ($text === '') {
            return '';
        }

        return Str::words($text, $words, '…');
    }

    public function faqLines(): array
    {
        if (! $this->faq) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', (string) $this->faq))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeUpcoming($query)
    {
        return $query->where(function ($q) {
            $q->whereDate('end_date', '>', now()->toDateString())
                ->orWhere(function ($inner) {
                    $inner->whereDate('end_date', now()->toDateString())
                        ->where(function ($timeQ) {
                            $timeQ->whereNull('end_time')
                                ->orWhereTime('end_time', '>=', now()->format('H:i:s'));
                        });
                });
        });
    }

    public function scopePast($query)
    {
        return $query->where(function ($q) {
            $q->whereDate('end_date', '<', now()->toDateString())
                ->orWhere(function ($inner) {
                    $inner->whereDate('end_date', now()->toDateString())
                        ->whereNotNull('end_time')
                        ->whereTime('end_time', '<', now()->format('H:i:s'));
                });
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'session';
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
