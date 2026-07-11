<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Str;
 
class ConsultationSession extends Model
{
    use SoftDeletes;
 
    protected $fillable = [
        'booking_ref', 'mentor_id', 'mentee_id', 'scheduled_at', 'duration_minutes', 'timezone',
        'title', 'agenda', 'mentor_notes', 'meeting_link', 'meeting_provider', 'meeting_channel',
        'status', 'cancellation_reason', 'cancelled_by', 'cancelled_at', 'started_at', 'ended_at',
        'actual_duration_seconds', 'amount', 'currency', 'payment_status', 'payment_reference',
        'razorpay_order_id', 'razorpay_payment_id',
    ];

    protected $casts = [
        'scheduled_at'  => 'datetime',
        'cancelled_at'  => 'datetime',
        'started_at'    => 'datetime',
        'ended_at'      => 'datetime',
        'amount'        => 'decimal:2',
    ];
 
    // ── Status constants ──────────────────────────────────────
    const STATUS_PENDING   = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_ONGOING   = 'ongoing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_UPCOMING  = 'upcoming';
    const STATUS_NO_SHOW   = 'no_show';
 
    const STATUSES = [
        'pending'   => 'Pending',
        'confirmed' => 'Confirmed',
        'ongoing'   => 'Ongoing',
        'completed' => 'Completed',
        'upcoming'  => 'Upcoming',
        'cancelled' => 'Cancelled',
        'no_show'   => 'No Show',
    ];
 
    // ── Relationships ─────────────────────────────────────────
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }
 
    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }
 
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
 
    public function reviews(): HasMany
    {
        return $this->hasMany(SessionReview::class, 'session_id');
    }
 
    public function menteeReview(): HasOne
    {
        return $this->hasOne(SessionReview::class, 'session_id')
                    ->where('reviewer_role', 'mentee');
    }
 
    public function mentorReview(): HasOne
    {
        return $this->hasOne(SessionReview::class, 'session_id')
                    ->where('reviewer_role', 'mentor');
    }
 
    public function notes(): HasMany
    {
        return $this->hasMany(SessionNote::class, 'session_id');
    }
 
    // ── Scopes ────────────────────────────────────────────────
    public function scopeForMentor(Builder $q, int $id): Builder
    {
        return $q->where('mentor_id', $id);
    }
 
    public function scopeForMentee(Builder $q, int $id): Builder
    {
        return $q->where('mentee_id', $id);
    }
 
    public function scopeUpcoming(Builder $q): Builder
    {
        return $q->whereIn('status', ['pending', 'confirmed'])
                 ->where('scheduled_at', '>=', now());
    }
 
    public function scopeCompleted(Builder $q): Builder
    {
        return $q->where('status', 'completed');
    }
 
    // ── Accessors ─────────────────────────────────────────────
    public function getStatusColorAttribute(): array
    {
        return match ($this->status) {
            'pending'   => ['bg' => '#fef9c3', 'text' => '#854d0e', 'dot' => '#ca8a04'],
            'confirmed' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'dot' => '#2563eb'],
            'ongoing'   => ['bg' => '#dcfce7', 'text' => '#166534', 'dot' => '#16a34a'],
            'completed' => ['bg' => '#f0fdf4', 'text' => '#14532d', 'dot' => '#22c55e'],
            'cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'dot' => '#dc2626'],
            'no_show'   => ['bg' => '#f3f4f6', 'text' => '#374151', 'dot' => '#9ca3af'],
            default     => ['bg' => '#f3f4f6', 'text' => '#374151', 'dot' => '#9ca3af'],
        };
    }
 
    public function getScheduledEndAttribute(): Carbon
    {
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
    }
 
    public function getCanReviewAttribute(): bool
    {
        return $this->status === 'completed';
    }
 
    public function getIsUpcomingAttribute(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'])
            && $this->scheduled_at->isFuture();
    }
 
    public function getActualDurationFormattedAttribute(): string
    {
        $s = $this->actual_duration_seconds ?? ($this->duration_minutes * 60);
        return sprintf('%dh %02dm', intdiv($s, 3600), intdiv($s % 3600, 60));
    }
 
    // ── Business logic ────────────────────────────────────────
    public function confirm(): void
    {
        $this->update(['status' => self::STATUS_CONFIRMED]);
    }
 
    public function start(): void
    {
        $this->update(['status' => self::STATUS_ONGOING, 'started_at' => now()]);
    }
 
    public function complete(): void
    {
        $duration = $this->started_at ? (int) $this->started_at->diffInSeconds(now()) : null;
        $this->update([
            'status'                   => self::STATUS_COMPLETED,
            'ended_at'                 => now(),
            'actual_duration_seconds'  => $duration,
        ]);
        // Increment mentor stats
        optional($this->mentor->mentorProfile)->increment('total_sessions');
    }
 
    public function cancel(int $cancelledBy, string $reason = ''): void
    {
        $this->update([
            'status'              => self::STATUS_CANCELLED,
            'cancelled_by'        => $cancelledBy,
            'cancellation_reason' => $reason,
        ]);
    }
}
