<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
 
class CurriculumMcq extends Model
{
    protected $fillable = [
        'week_id', 'question', 'options', 'correct_index',
        'explanation', 'difficulty', 'points', 'order_index', 'is_active',
    ];
 
    protected $casts = [
        'options'   => 'array',
        'is_active' => 'boolean',
    ];
 
    const DIFFICULTY_COLORS = [
        'easy'   => '#16a34a',
        'medium' => '#d97706',
        'hard'   => '#dc2626',
    ];
 
    public function week(): BelongsTo
    {
        return $this->belongsTo(CurriculumWeek::class, 'week_id');
    }
 
    public function attempts(): HasMany
    {
        return $this->hasMany(McqAttempt::class, 'mcq_id');
    }
 
    public function getAttemptForUser(int $userId): ?McqAttempt
    {
        return $this->attempts()->where('user_id', $userId)->latest()->first();
    }
 
    public function isAnsweredCorrectlyByUser(int $userId): bool
    {
        return $this->attempts()
            ->where('user_id', $userId)
            ->where('is_correct', true)
            ->exists();
    }
}
 