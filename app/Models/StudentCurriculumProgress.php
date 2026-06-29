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
        return static::updateOrCreate(
            ['user_id' => $userId, 'item_type' => $type, 'item_id' => $itemId],
            array_merge(['is_completed' => true, 'completed_at' => now()], $extra)
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
}