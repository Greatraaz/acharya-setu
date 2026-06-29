<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
 
class ActivityLog extends Model
{
    protected $fillable = [
        'causer_id', 'causer_type', 'causer_name',
        'subject_id', 'subject_type', 'subject_label',
        'event', 'description', 'module', 'level',
        'ip_address', 'user_agent', 'url', 'method',
        'properties', 'logged_at',
    ];
 
    protected $casts = [
        'properties' => 'array',
        'logged_at'  => 'datetime',
    ];
 
    // ── Level constants ───────────────────────────────────────
    const LEVEL_INFO    = 'info';
    const LEVEL_SUCCESS = 'success';
    const LEVEL_WARNING = 'warning';
    const LEVEL_DANGER  = 'danger';
 
    // ── Module constants ──────────────────────────────────────
    const MODULE_AUTH       = 'auth';
    const MODULE_USERS      = 'users';
    const MODULE_SESSIONS   = 'sessions';
    const MODULE_PAYMENTS   = 'payments';
    const MODULE_CURRICULUM = 'curriculum';
    const MODULE_JOBS       = 'jobs';
    const MODULE_PLANS      = 'plans';
    const MODULE_SETTINGS   = 'settings';
    const MODULE_SYSTEM     = 'system';
 
    // ── Relationships ─────────────────────────────────────────
    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }
 
    // ── Scopes ────────────────────────────────────────────────
    public function scopeForCauser(Builder $q, int $userId): Builder
    {
        return $q->where('causer_id', $userId);
    }
 
    public function scopeForSubject(Builder $q, string $type, int $id): Builder
    {
        return $q->where('subject_type', $type)->where('subject_id', $id);
    }
 
    public function scopeByModule(Builder $q, string $module): Builder
    {
        return $q->where('module', $module);
    }
 
    public function scopeByLevel(Builder $q, string $level): Builder
    {
        return $q->where('level', $level);
    }
 
    public function scopeByEvent(Builder $q, string $event): Builder
    {
        return $q->where('event', $event);
    }
 
    public function scopeSearch(Builder $q, string $term): Builder
    {
        return $q->where(function ($q) use ($term) {
            $q->where('description', 'like', "%{$term}%")
              ->orWhere('causer_name', 'like', "%{$term}%")
              ->orWhere('subject_label', 'like', "%{$term}%")
              ->orWhere('event', 'like', "%{$term}%")
              ->orWhere('ip_address', 'like', "%{$term}%");
        });
    }
 
    public function scopeDateRange(Builder $q, ?string $from, ?string $to): Builder
    {
        if ($from) $q->whereDate('logged_at', '>=', $from);
        if ($to)   $q->whereDate('logged_at', '<=', $to);
        return $q;
    }
 
    // ── Accessors ─────────────────────────────────────────────
    public function getLevelColorAttribute(): array
    {
        return match ($this->level) {
            'success' => ['bg' => 'bg-emerald-50',  'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
            'warning' => ['bg' => 'bg-amber-50',    'text' => 'text-amber-700',   'border' => 'border-amber-200',   'dot' => 'bg-amber-500'],
            'danger'  => ['bg' => 'bg-red-50',      'text' => 'text-red-700',     'border' => 'border-red-200',     'dot' => 'bg-red-500'],
            default   => ['bg' => 'bg-blue-50',     'text' => 'text-blue-700',    'border' => 'border-blue-200',    'dot' => 'bg-blue-500'],
        };
    }
 
    public function getModuleIconAttribute(): string
    {
        return match ($this->module) {
            'auth'       => '🔐',
            'users'      => '👤',
            'sessions'   => '🎥',
            'payments'   => '💳',
            'curriculum' => '📚',
            'jobs'       => '💼',
            'plans'      => '📋',
            'settings'   => '⚙️',
            'system'     => '🖥️',
            default      => '📝',
        };
    }
 
    public function getOldValuesAttribute(): array
    {
        return $this->properties['old'] ?? [];
    }
 
    public function getNewValuesAttribute(): array
    {
        return $this->properties['new'] ?? [];
    }
 
    public function getMetaAttribute(): array
    {
        return $this->properties['meta'] ?? [];
    }
 
    public function getChangedFieldsAttribute(): array
    {
        $old = $this->old_values;
        $new = $this->new_values;
        $diff = [];
        foreach ($new as $key => $value) {
            if (!isset($old[$key]) || $old[$key] !== $value) {
                $diff[$key] = ['old' => $old[$key] ?? null, 'new' => $value];
            }
        }
        return $diff;
    }
}
