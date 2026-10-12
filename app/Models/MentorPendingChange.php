<?php

namespace App\Models;

use App\Support\ProfileUpdate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
 
class MentorPendingChange extends Model
{
    protected $fillable = [
        'mentor_id', 'changes', 'status',
        'rejection_reason', 'reviewed_by', 'reviewed_at',
    ];
 
    protected $casts = [
        'changes'     => 'array',
        'reviewed_at' => 'datetime',
    ];
 
    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
 
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }
 
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
 
    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_PENDING);
    }
 
    public function approve(int $adminId): void
    {
        // The column is named "changes", which collides with Eloquent's $changes property.
        $proposed = $this->getAttribute('changes');
        $allowed = array_intersect_key(is_array($proposed) ? $proposed : [], array_flip(ProfileUpdate::MODERATED));
        if ($allowed !== [] && $this->mentor) {
            $this->mentor->update($allowed);
        }
        $this->update([
            'status'      => self::STATUS_APPROVED,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);
        $this->mentor->update(['has_pending_changes' => false]);
    }
 
    public function reject(int $adminId, string $reason): void
    {
        $this->update([
            'status'           => self::STATUS_REJECTED,
            'reviewed_by'      => $adminId,
            'reviewed_at'      => now(),
            'rejection_reason' => $reason,
        ]);
        $this->mentor->update(['has_pending_changes' => false]);
    }
}