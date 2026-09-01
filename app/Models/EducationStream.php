<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
 
class EducationStream extends Model
{
    protected $fillable = ['mentee_id', 'mentor_id', 'name', 'slug', 'icon', 'color', 'description', 'is_active', 'sort_order'];
 
    protected $casts = ['is_active' => 'boolean'];
 
    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->name);
            }
        });
    }
 
    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function months(): HasMany
    {
        return $this->hasMany(CurriculumMonth::class, 'stream_id')->orderBy('month_number');
    }
 
    public function enrollments(): HasMany
    {
        return $this->hasMany(MenteeEnrollment::class, 'stream_id');
    }
 
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /** Tracks a mentor may view/manage for their mentees (includes admin-created rows with missing mentor_id). */
    public function scopeForMentor(Builder $q, int $mentorId, iterable $menteeIds): Builder
    {
        $menteeIds = collect($menteeIds)->filter()->values();

        return $q->whereNotNull('mentee_id')
            ->where(function (Builder $inner) use ($mentorId, $menteeIds) {
                $inner->where('mentor_id', $mentorId);

                if ($menteeIds->isNotEmpty()) {
                    $inner->orWhereIn('mentee_id', $menteeIds);
                }
            });
    }

    /** Backfill mentor_id on mentee tracks when the mentee is assigned to this mentor. */
    public static function syncMentorForAssignedMentees(int $mentorId, iterable $menteeIds): void
    {
        $menteeIds = collect($menteeIds)->filter()->values();

        if ($menteeIds->isEmpty()) {
            return;
        }

        static::query()
            ->whereIn('mentee_id', $menteeIds)
            ->whereHas('mentee', fn (Builder $q) => $q->where('assigned_mentor_id', $mentorId))
            ->where(fn (Builder $q) => $q->whereNull('mentor_id')->orWhere('mentor_id', '!=', $mentorId))
            ->update(['mentor_id' => $mentorId]);
    }
 
    public function getTotalTasksAttribute(): int
    {
        return CurriculumTask::whereHas('week.month', fn($q) => $q->where('stream_id', $this->id))->count();
    }
 
    public function getTotalMcqsAttribute(): int
    {
        return CurriculumMcq::whereHas('week.month', fn($q) => $q->where('stream_id', $this->id))->count();
    }
}
 