<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class MenteeEnrollment extends Model
{
    protected $fillable = [
        'mentee_id', 'mentor_id', 'stream_id',
        'start_date', 'expected_end_date', 'actual_end_date',
        'status', 'current_month', 'current_week', 'mentor_notes',
    ];
 
    protected $casts = [
        'start_date'        => 'date',
        'expected_end_date' => 'date',
        'actual_end_date'   => 'date',
    ];
 
    const STATUSES = [
        'active'    => 'Active',
        'paused'    => 'Paused',
        'completed' => 'Completed',
        'dropped'   => 'Dropped',
    ];
 
    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }
 
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }
 
    public function stream(): BelongsTo
    {
        return $this->belongsTo(EducationStream::class, 'stream_id');
    }
 
    public function getProgressAttribute(): array
    {
        return StudentCurriculumProgress::getOverallProgress($this->mentee_id, $this->stream_id);
    }
 
    public function getDaysElapsedAttribute(): int
    {
        return (int) $this->start_date->diffInDays(now());
    }
 
    public function getDaysRemainingAttribute(): int
    {
        return max(0, (int) now()->diffInDays($this->expected_end_date, false));
    }
}