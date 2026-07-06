<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class StudentCurriculumProgress extends Model
{
    protected $fillable = [
        'user_id', 'item_type', 'item_id', 'is_completed', 'completed_at',
        'submission_url', 'submission_text', 'submission_status',
        'mentor_feedback', 'reviewed_at',
    ];
 
    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];
 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
 
    /**
     * Upsert a progress record for a user.
     */
    public static function markComplete(int $userId, string $type, int $itemId, array $extra = []): static
    {
        $isCompleted = $extra['is_completed'] ?? true;

        $payload = array_merge(
            ['is_completed' => $isCompleted],
            $isCompleted ? ['completed_at' => now()] : [],
            $extra
        );

        return static::updateOrCreate(
            ['user_id' => $userId, 'item_type' => $type, 'item_id' => $itemId],
            $payload
        );
    }
 
    /**
     * Calculate overall progress for a user across a full stream.
     */
    public static function getOverallProgress(int $userId, int $streamId): array
    {
        $months = CurriculumMonth::where('stream_id', $streamId)->with('weeks')->get();
        $total  = 0;
        $done   = 0;
 
        foreach ($months as $month) {
            foreach ($month->weeks as $week) {
                $p      = $week->getProgressForUser($userId);
                $total += $p['total'];
                $done  += $p['completed'];
            }
        }
 
        return [
            'percent'   => $total ? (int) round($done / $total * 100) : 0,
            'completed' => $done,
            'total'     => $total,
        ];
    }

    /**
     * Full mentee progress summary: tasks, MCQs, videos (percent calculated dynamically).
     */
    public static function getMenteeProgressSummary(int $menteeId): array
    {
        $taskIds = CurriculumTask::where('mentee_id', $menteeId)->where('is_active', true)->pluck('id');
        $mcqIds  = CurriculumMcq::where('mentee_id', $menteeId)->where('is_active', true)->pluck('id');

        $tasksCompleted = static::where('user_id', $menteeId)
            ->where('item_type', 'task')
            ->where('is_completed', true)
            ->whereIn('item_id', $taskIds)
            ->count();

        $mcqsCompleted = static::where('user_id', $menteeId)
            ->where('item_type', 'mcq')
            ->where('is_completed', true)
            ->whereIn('item_id', $mcqIds)
            ->count();

        $tasksTotal = $taskIds->count();
        $mcqsTotal  = $mcqIds->count();

        $videoFileIds = MentorVideoFile::whereHas('mentorVideo', fn ($q) => $q->where('is_active', true))->pluck('id');
        $videosTotal  = $videoFileIds->count();
        $videosWatched = MentorVideoWatch::where('mentee_id', $menteeId)
            ->whereIn('mentor_video_file_id', $videoFileIds)
            ->count();

        $tasksBreakdown = [
            'total'     => $tasksTotal,
            'completed' => $tasksCompleted,
            'percent'   => $tasksTotal ? (int) round($tasksCompleted / $tasksTotal * 100) : 0,
        ];

        $mcqsBreakdown = [
            'total'     => $mcqsTotal,
            'completed' => $mcqsCompleted,
            'percent'   => $mcqsTotal ? (int) round($mcqsCompleted / $mcqsTotal * 100) : 0,
        ];

        $videosBreakdown = [
            'total'   => $videosTotal,
            'watched' => $videosWatched,
            'percent' => $videosTotal ? (int) round($videosWatched / $videosTotal * 100) : 0,
        ];

        $overallTotal     = $tasksTotal + $mcqsTotal + $videosTotal;
        $overallCompleted = $tasksCompleted + $mcqsCompleted + $videosWatched;

        return [
            'overall' => [
                'total'     => $overallTotal,
                'completed' => $overallCompleted,
                'percent'   => $overallTotal ? (int) round($overallCompleted / $overallTotal * 100) : 0,
            ],
            'tasks'  => $tasksBreakdown,
            'mcqs'   => $mcqsBreakdown,
            'videos' => $videosBreakdown,
        ];
    }
}