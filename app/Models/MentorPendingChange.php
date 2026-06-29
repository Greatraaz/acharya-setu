<?php

namespace App\Models;

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
        // Apply changes to the mentor's profile
        $this->mentor->update($this->changes);
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