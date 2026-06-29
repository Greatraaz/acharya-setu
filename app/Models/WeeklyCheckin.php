<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class WeeklyCheckin extends Model
{
    protected $fillable = [
        'mentee_id', 'week_id', 'mood_score',
        'wins', 'challenges', 'questions',
        'mentor_response', 'submitted_at', 'mentor_replied_at',
    ];
 
    protected $casts = [
        'submitted_at'      => 'datetime',
        'mentor_replied_at' => 'datetime',
    ];
 
    const MOOD_LABELS = [
        1 => '😔 Struggling',
        2 => '😕 Difficult',
        3 => '😐 Okay',
        4 => '😊 Good',
        5 => '🚀 Excellent',
    ];
 
    public function week(): BelongsTo
    {
        return $this->belongsTo(CurriculumWeek::class, 'week_id');
    }
 
    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }
}