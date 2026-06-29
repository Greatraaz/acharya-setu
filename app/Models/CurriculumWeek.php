<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
 
class CurriculumWeek extends Model
{
    protected $fillable = [
        'month_id', 'week_number', 'title', 'focus',
        'mentor_guide', 'resources', 'video_url', 'is_active', 'sort_order',
    ];
 
    protected $casts = [
        'resources' => 'array',
        'is_active' => 'boolean',
    ];
 
    public function month(): BelongsTo
    {
        return $this->belongsTo(CurriculumMonth::class, 'month_id');
    }
 
    public function tasks(): HasMany
    {
        return $this->hasMany(CurriculumTask::class, 'week_id')->orderBy('order_index');
    }
 
    public function mcqs(): HasMany
    {
        return $this->hasMany(CurriculumMcq::class, 'week_id')->orderBy('order_index');
    }
 
    public function checkins(): HasMany
    {
        return $this->hasMany(WeeklyCheckin::class, 'week_id');
    }
 
    public function getProgressForUser(int $userId): array
    {
        $taskIds = $this->tasks->pluck('id');
        $mcqIds  = $this->mcqs->pluck('id');
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
