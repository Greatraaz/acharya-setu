<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentProgress extends Model
{
    protected $table = 'assessment_progress';

    protected $fillable = [
        'user_id',
        'assessment_id',
        'answers',
        'score',
        'last_question',
        'completed_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'score' => 'decimal:2',
        'last_question' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class, 'assessment_id');
    }
}
