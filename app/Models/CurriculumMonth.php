<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
 
class CurriculumMonth extends Model
{
    protected $fillable = [
        'stream_id', 'month_number', 'title', 'description', 'theme',
        'cover_image', 'learning_outcomes', 'milestone_badge', 'is_active', 'sort_order',
    ];
 
    protected $casts = [
        'learning_outcomes' => 'array',
        'is_active'         => 'boolean',
    ];
 
    public function stream(): BelongsTo
    {
        return $this->belongsTo(EducationStream::class, 'stream_id');
    }
 
    public function weeks(): HasMany
    {
        return $this->hasMany(CurriculumWeek::class, 'month_id')->orderBy('week_number');
    }
 
    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(CurriculumTask::class, CurriculumWeek::class, 'month_id', 'week_id');
    }
 
    public function mcqs(): HasManyThrough
    {
        return $this->hasManyThrough(CurriculumMcq::class, CurriculumWeek::class, 'month_id', 'week_id');
    }
 
    public function getProgressForUser(int $userId): array
    {
        $weekIds = $this->weeks->pluck('id');
        $taskIds = CurriculumTask::whereIn('week_id', $weekIds)->pluck('id');
        $mcqIds  = CurriculumMcq::whereIn('week_id', $weekIds)->pluck('id');
        $total   = $taskIds->count() + $mcqIds->count();
 
        if ($total === 0) {
            return ['percent' => 0, 'completed' => 0, 'total' => 0];
        }
 
        $done = StudentCurriculumProgress::where('user_id', $userId)
            ->where('is_completed', true)
            ->where(function ($q) use ($taskIds, $mcqIds) {
                $q->where(fn($q) => $q->where('item_type', 'task')->whereIn('item_id', $taskIds))
                  ->orWhere(fn($q) => $q->where('item_type', 'mcq')->whereIn('item_id', $mcqIds));
            })->count();
 
        return ['percent' => (int) round($done / $total * 100), 'completed' => $done, 'total' => $total];
    }
}