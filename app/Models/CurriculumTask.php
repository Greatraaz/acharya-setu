<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class CurriculumTask extends Model
{
    protected $fillable = [
        'week_id', 'plan_id', 'title', 'description', 'type', 'order_index',
        'estimated_minutes', 'is_required', 'is_active', 'attachments', 'submission_type',
    ];
 
    protected $casts = [
        'is_required'  => 'boolean',
        'is_active'    => 'boolean',
        'attachments'  => 'array',
    ];
 
    const TYPES = [
        'task'       => 'Task',
        'reading'    => 'Reading',
        'video'      => 'Video',
        'project'    => 'Project',
        'quiz'       => 'Quiz',
        'reflection' => 'Reflection',
    ];
 
    const SUBMISSION_TYPES = [
        'none'  => 'No Submission',
        'text'  => 'Text',
        'file'  => 'File Upload',
        'link'  => 'URL Link',
        'pdf'   => 'PDF',
        'video' => 'Video',
    ];
 
    const TYPE_ICONS = [
        'task'       => '✅',
        'reading'    => '📖',
        'video'      => '🎬',
        'project'    => '🚀',
        'quiz'       => '❓',
        'reflection' => '💭',
    ];
 
    public function week(): BelongsTo
    {
        return $this->belongsTo(CurriculumWeek::class, 'week_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
 
    public function getProgressForUser(int $userId): ?StudentCurriculumProgress
    {
        return StudentCurriculumProgress::where('user_id', $userId)
            ->where('item_type', 'task')
            ->where('item_id', $this->id)
            ->first();
    }
 
    public function isCompletedByUser(int $userId): bool
    {
        return StudentCurriculumProgress::where('user_id', $userId)
            ->where('item_type', 'task')
            ->where('item_id', $this->id)
            ->where('is_completed', true)
            ->exists();
    }
}