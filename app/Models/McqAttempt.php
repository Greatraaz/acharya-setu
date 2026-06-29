<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class McqAttempt extends Model
{
    protected $fillable = [
        'user_id', 'mcq_id', 'selected_index',
        'is_correct', 'points_earned', 'attempted_at',
    ];
 
    protected $casts = [
        'is_correct'   => 'boolean',
        'attempted_at' => 'datetime',
    ];
 
    public function mcq(): BelongsTo
    {
        return $this->belongsTo(CurriculumMcq::class, 'mcq_id');
    }
 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}