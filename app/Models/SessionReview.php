<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class SessionReview extends Model
{
    protected $fillable = [
        'session_id', 'reviewer_id', 'reviewee_id', 'reviewer_role',
        'overall_rating', 'communication_rating', 'knowledge_rating',
        'punctuality_rating', 'helpfulness_rating',
        'review_text', 'would_recommend', 'is_public', 'submitted_at',
    ];
 
    protected $casts = [
        'would_recommend' => 'boolean',
        'is_public'       => 'boolean',
        'submitted_at'    => 'datetime',
    ];
 
    public function session(): BelongsTo
    {
        return $this->belongsTo(ConsultationSession::class, 'session_id');
    }
 
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
 
    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }
 
    public function getAverageDetailedRatingAttribute(): float
    {
        $ratings = array_filter([
            $this->communication_rating,
            $this->knowledge_rating,
            $this->punctuality_rating,
            $this->helpfulness_rating,
        ]);
        return count($ratings) ? array_sum($ratings) / count($ratings) : $this->overall_rating;
    }
}
