<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
 
class User extends Authenticatable
{
    use HasApiTokens,HasFactory, Notifiable, SoftDeletes;
 
    // ── Mentor status constants ───────────────────────────────
    const MENTOR_STATUS_PENDING  = 'pending';
    const MENTOR_STATUS_APPROVED = 'approved';
    const MENTOR_STATUS_REJECTED = 'rejected';
    const MENTOR_STATUS_SUSPENDED = 'suspended';
 
    // ── Onboarding steps ─────────────────────────────────────
    // MENTOR steps: 0=role, 1=personal, 2=professional, 3=expertise, 4=rates, 5=done
    // MENTEE steps: 0=role, 1=personal, 2=goals, 3=preferences, 4=done
    const MENTOR_STEPS = 5;
    const MENTEE_STEPS = 4;
 
    protected $fillable = [
        'name', 'email', 'password', 'role',
        'wallet_balance', 'bio', 'expertise', 'field', 'college', 'year',
        'gender', 'rating', 'total_sessions', 'avatar_url', 'phone',
        'linkedin', 'company', 'designation', 'experience_years',
        'is_active', 'rate_per_minute', 'assigned_mentor_id',
        'subscription_plan', 'mentor_status', 'education_stream',
        'career_goals', 'strengths', 'preferences',
        'onboarding_step', 'onboarding_completed', 'isVerifiedEmail',
        'approved_by', 'approved_at', 'rejection_reason', 'has_pending_changes',
    ];
 
    protected $hidden = ['password', 'remember_token'];
 
    protected $casts = [
        'expertise'            => 'array',
        'career_goals'         => 'array',
        'strengths'            => 'array',
        'preferences'          => 'array',
        'is_active'            => 'boolean',
        'onboarding_completed' => 'boolean',
        'has_pending_changes'  => 'boolean',
        'wallet_balance'       => 'decimal:2',
        'rating'               => 'decimal:2',
        'rate_per_minute'      => 'decimal:2',
        'approved_at'          => 'datetime',
        'email_verified_at'    => 'datetime',
    ];

    public function getAuthPassword(): string
    {
        return $this->password;
    }
 
    // ── Role helpers ──────────────────────────────────────────
    public function isMentor(): bool   { return $this->role === 'mentor'; }
    public function isMentee(): bool   { return $this->role === 'mentee'; }
    public function isAdmin(): bool    { return $this->role === 'admin'; }
 
    // ── Mentor approval helpers ───────────────────────────────
    public function isPendingApproval(): bool  { return $this->mentor_status === self::MENTOR_STATUS_PENDING; }
    public function isApproved(): bool         { return $this->mentor_status === self::MENTOR_STATUS_APPROVED; }
    public function isRejected(): bool         { return $this->mentor_status === self::MENTOR_STATUS_REJECTED; }
    public function isSuspended(): bool        { return $this->mentor_status === self::MENTOR_STATUS_SUSPENDED; }
 
    // ── Onboarding helpers ────────────────────────────────────
    public function getOnboardingTotalStepsAttribute(): int
    {
        return $this->isMentor() ? self::MENTOR_STEPS : self::MENTEE_STEPS;
    }
 
    public function getOnboardingProgressAttribute(): int
    {
        if ($this->onboarding_completed) return 100;
        return (int) round(($this->onboarding_step / $this->onboarding_total_steps) * 100);
    }
 
    public function needsOnboarding(): bool
    {
        return !$this->onboarding_completed;
    }
 
    // ── Relationships ─────────────────────────────────────────
    public function assignedMentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_mentor_id');
    }
 
    public function assignedMentees(): HasMany
    {
        return $this->hasMany(User::class, 'assigned_mentor_id');
    }
 
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
 
    public function pendingChanges(): HasMany
    {
        return $this->hasMany(MentorPendingChange::class, 'mentor_id');
    }
 
    public function latestPendingChange(): HasOne
    {
        return $this->hasOne(MentorPendingChange::class, 'mentor_id')
            ->where('status', 'pending')
            ->latest();
    }
 
    public function mentorSessions(): HasMany
    {
        return $this->hasMany(ConsultationSession::class, 'mentor_id');
    }
 
    public function menteeSessions(): HasMany
    {
        return $this->hasMany(ConsultationSession::class, 'mentee_id');
    }
 
    public function enrollments(): HasMany
    {
        return $this->hasMany(MenteeEnrollment::class, 'mentee_id');
    }
 
    // ── Scopes ────────────────────────────────────────────────
    public function scopeMentors(Builder $q): Builder  { return $q->where('role', 'mentor'); }
    public function scopeMentees(Builder $q): Builder  { return $q->where('role', 'mentee'); }
    public function scopeActive(Builder $q): Builder   { return $q->where('is_active', true); }
    public function scopeApproved(Builder $q): Builder { return $q->where('mentor_status', 'approved'); }
    public function scopePendingApproval(Builder $q): Builder { return $q->where('mentor_status', 'pending'); }
 
    // ── Accessors ─────────────────────────────────────────────
    public function getHourlyRateAttribute(): float
    {
        return round($this->rate_per_minute * 60, 2);
    }
 
    public function getAvgRatingAttribute(): float
    {
        return (float) $this->rating;
    }
 
    public function getIsVerifiedAttribute(): bool
    {
        return $this->mentor_status === self::MENTOR_STATUS_APPROVED;
    }
 
    // ── Mentor approval flow ──────────────────────────────────
    public function approve(int $adminId): void
    {
        $this->update([
            'mentor_status' => self::MENTOR_STATUS_APPROVED,
            'approved_by'   => $adminId,
            'approved_at'   => now(),
            'rejection_reason' => null,
            'is_active'     => true,
        ]);
    }
 
    public function reject(int $adminId, string $reason): void
    {
        $this->update([
            'mentor_status'    => self::MENTOR_STATUS_REJECTED,
            'approved_by'      => $adminId,
            'approved_at'      => now(),
            'rejection_reason' => $reason,
            'is_active'        => false,
        ]);
    }
 
    public function suspend(string $reason = ''): void
    {
        $this->update([
            'mentor_status' => self::MENTOR_STATUS_SUSPENDED,
            'is_active'     => false,
            'rejection_reason' => $reason,
        ]);
    }
 
    /**
     * Request a profile change — puts it in queue instead of saving directly.
     */
    public function requestProfileChange(array $changes): MentorPendingChange
    {
        // Cancel any previous pending request
        $this->pendingChanges()->pending()->delete();
 
        $pending = MentorPendingChange::create([
            'mentor_id' => $this->id,
            'changes'   => $changes,
            'status'    => MentorPendingChange::STATUS_PENDING,
        ]);
 
        $this->update(['has_pending_changes' => true]);
 
        return $pending;
    }
 
    /**
     * Recalculate rating from session reviews.
     */
    public function recalculateRating(): void
    {
        $avg = SessionReview::whereHas('session', fn($q) => $q->where('mentor_id', $this->id))
            ->where('reviewer_role', 'mentee')
            ->avg('overall_rating');
        $this->update(['rating' => round($avg ?? 0, 2)]);
    }

}