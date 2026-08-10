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

    public const SCHEDULE_TIMEZONE = 'Asia/Kolkata';
 
    protected $fillable = [
        'booking_ref', 'mentor_id', 'mentee_id', 'scheduled_at', 'duration_minutes', 'timezone',
        'title', 'agenda', 'mentor_notes', 'meeting_link', 'meeting_provider', 'meeting_channel',
        'status', 'cancellation_reason', 'cancelled_by', 'cancelled_at', 'started_at', 'ended_at',
        'actual_duration_seconds',         'amount', 'currency', 'payment_status', 'payment_method', 'wallet_amount', 'razorpay_amount',
        'payment_reference',
        'razorpay_order_id', 'razorpay_payment_id',
    ];

    protected $casts = [
        'cancelled_at'    => 'datetime',
        'started_at'      => 'datetime',
        'ended_at'        => 'datetime',
        'amount'          => 'decimal:2',
        'wallet_amount'   => 'decimal:2',
        'razorpay_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $session): void {
            if (! empty($session->booking_ref)) {
                // continue
            } else {
                do {
                    $ref = 'AS-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
                } while (self::where('booking_ref', $ref)->exists());

                $session->booking_ref = $ref;
            }

            if (empty($session->meeting_channel)) {
                $session->meeting_channel = strtoupper(Str::random(10));
            }
        });
    }
 
    // Soft-hold for unpaid Razorpay checkouts. Abandoned payments auto-release
    // without needing a client cancel API.
    public const PAYMENT_HOLD_MINUTES = 10;

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

    public function sessionInvoice(): HasOne
    {
        return $this->hasOne(SessionInvoice::class, 'consultation_session_id');
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'plan' => 'Plan (included)',
            'free' => 'Free',
            'wallet' => 'Wallet',
            'razorpay' => 'Razorpay',
            'hybrid' => 'Wallet + Razorpay',
            default => $this->payment_status === 'waived' ? 'Waived' : (ucfirst((string) $this->payment_method) ?: '—'),
        };
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
                 ->where(function ($inner) {
                     // Do not treat abandoned unpaid checkouts as real upcoming bookings
                     $inner->where('payment_status', '!=', 'pending')
                           ->orWhere('status', '!=', self::STATUS_PENDING)
                           ->orWhere('created_at', '>=', now()->subMinutes(self::PAYMENT_HOLD_MINUTES));
                 })
                 ->where('scheduled_at', '>=', Carbon::now(self::SCHEDULE_TIMEZONE)->format('Y-m-d H:i:s'));
    }

    /**
     * Sessions that currently block a mentor time slot.
     * Expired unpaid pending checkouts do not occupy the slot.
     */
    public function scopeOccupyingSlot(Builder $q): Builder
    {
        return $q->whereNotIn('status', [
                self::STATUS_CANCELLED,
                self::STATUS_COMPLETED,
                self::STATUS_NO_SHOW,
            ])
            ->where(function ($inner) {
                $inner->where(function ($paidOrConfirmed) {
                    $paidOrConfirmed->where('status', '!=', self::STATUS_PENDING)
                        ->orWhere('payment_status', '!=', 'pending');
                })->orWhere('created_at', '>=', now()->subMinutes(self::PAYMENT_HOLD_MINUTES));
            });
    }

    /**
     * Cancel unpaid pending sessions whose payment hold window expired.
     */
    public static function expireAbandonedUnpaidPayments(): int
    {
        return static::query()
            ->where('status', self::STATUS_PENDING)
            ->where('payment_status', 'pending')
            ->where('created_at', '<', now()->subMinutes(self::PAYMENT_HOLD_MINUTES))
            ->update([
                'status'              => self::STATUS_CANCELLED,
                'cancellation_reason' => 'Payment not completed within ' . self::PAYMENT_HOLD_MINUTES . ' minutes',
                'cancelled_at'        => now(),
            ]);
    }

    /**
     * Immediately release this mentee's unpaid hold on a slot (payment cancelled / retry).
     */
    public static function releaseOwnUnpaidHold(int $menteeId, int $mentorId, Carbon $scheduledAt): int
    {
        return static::query()
            ->where('mentee_id', $menteeId)
            ->where('mentor_id', $mentorId)
            ->where('scheduled_at', $scheduledAt->copy()->timezone(self::SCHEDULE_TIMEZONE)->format('Y-m-d H:i:s'))
            ->where('status', self::STATUS_PENDING)
            ->where('payment_status', 'pending')
            ->update([
                'status'              => self::STATUS_CANCELLED,
                'cancellation_reason' => 'Replaced by a new booking attempt (previous payment not completed)',
                'cancelled_at'        => now(),
                'cancelled_by'        => $menteeId,
            ]);
    }

    public function sessionTimezone(): string
    {
        return self::SCHEDULE_TIMEZONE;
    }

    protected function parseScheduledAtRaw(?string $raw): ?Carbon
    {
        if (! $raw) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d H:i:s', substr(trim($raw), 0, 19), self::SCHEDULE_TIMEZONE);
    }

    public function isScheduledInFuture(): bool
    {
        $scheduled = $this->parseScheduledAtRaw($this->attributes['scheduled_at'] ?? null);

        if (! $scheduled) {
            return false;
        }

        return $scheduled->greaterThan(Carbon::now(self::SCHEDULE_TIMEZONE));
    }

    public function scheduledRelativeToNow(): ?string
    {
        $scheduled = $this->parseScheduledAtRaw($this->attributes['scheduled_at'] ?? null);

        if (! $scheduled) {
            return null;
        }

        $now = Carbon::now(self::SCHEDULE_TIMEZONE);

        if ($scheduled->lessThanOrEqualTo($now)) {
            return null;
        }

        $minutes = (int) ceil($now->diffInMinutes($scheduled));

        if ($minutes < 1) {
            return 'in 1 minute';
        }

        if ($minutes < 60) {
            return 'in '.$minutes.' minute'.($minutes === 1 ? '' : 's');
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return 'in '.$hours.' hour'.($hours === 1 ? '' : 's');
        }

        return 'in '.$hours.'h '.$remainingMinutes.'m';
    }
 
    public function scopeCompleted(Builder $q): Builder
    {
        return $q->where('status', 'completed');
    }
 
    // ── Accessors ─────────────────────────────────────────────
    public function getScheduledAtAttribute(?string $value): ?Carbon
    {
        return $this->parseScheduledAtRaw($value);
    }

    public function setScheduledAtAttribute(mixed $value): void
    {
        if ($value instanceof Carbon) {
            $this->attributes['scheduled_at'] = $value
                ->copy()
                ->timezone(self::SCHEDULE_TIMEZONE)
                ->format('Y-m-d H:i:s');

            return;
        }

        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
            $this->attributes['scheduled_at'] = $value;

            return;
        }

        $this->attributes['scheduled_at'] = $value
            ? Carbon::parse($value, self::SCHEDULE_TIMEZONE)->format('Y-m-d H:i:s')
            : null;
    }

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
            && $this->isScheduledInFuture();
    }
 
    public function getActualDurationFormattedAttribute(): string
    {
        $s = $this->actual_duration_seconds ?? ($this->duration_minutes * 60);
        return sprintf('%dh %02dm', intdiv($s, 3600), intdiv($s % 3600, 60));
    }
 
    // ── Business logic ────────────────────────────────────────

    /**
     * Mark only truly missed sessions as no_show.
     *
     * Never touches: completed, cancelled, ongoing, or any session that was
     * started/attended (started_at / ended_at / actual duration present).
     * Only pending / confirmed / upcoming whose scheduled end time has passed.
     */
    public static function expireMissedSessions(?int $mentorId = null, ?int $menteeId = null): int
    {
        $query = static::query()
            ->whereIn('status', [
                self::STATUS_PENDING,
                self::STATUS_CONFIRMED,
                self::STATUS_UPCOMING,
            ])
            ->whereNotIn('status', [
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
                self::STATUS_ONGOING,
                self::STATUS_NO_SHOW,
            ])
            // Attended / started sessions must never become no_show
            ->whereNull('started_at')
            ->whereNull('ended_at')
            ->where(function ($q) {
                $q->whereNull('actual_duration_seconds')
                    ->orWhere('actual_duration_seconds', 0);
            })
            ->whereRaw(
                'DATE_ADD(scheduled_at, INTERVAL COALESCE(duration_minutes, 0) MINUTE) < ?',
                [now()->timezone(self::SCHEDULE_TIMEZONE)->format('Y-m-d H:i:s')]
            );

        if ($mentorId) {
            $query->where('mentor_id', $mentorId);
        }

        if ($menteeId) {
            $query->where('mentee_id', $menteeId);
        }

        return $query->update(['status' => self::STATUS_NO_SHOW]);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

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
        optional($this->mentor)->increment('total_sessions');

        $this->settleMentorPayout();
    }

    /**
     * Credit mentor wallet with 80% of session amount when:
     * - session is completed
     * - mentee payment_status is paid
     * - amount > 0
     * Idempotent: skips if payout already recorded for this session.
     */
    public function settleMentorPayout(): ?WalletTransaction
    {
        $this->refresh();

        if ($this->status !== self::STATUS_COMPLETED) {
            return null;
        }

        if ($this->payment_status !== 'paid') {
            return null;
        }

        $gross = round((float) $this->amount, 2);
        if ($gross <= 0) {
            return null;
        }

        $reference = 'SES-EARN-' . $this->id;

        $existing = WalletTransaction::where('reference', $reference)->first()
            ?? WalletTransaction::where('user_id', $this->mentor_id)
                ->where('transactionable_type', self::class)
                ->where('transactionable_id', $this->id)
                ->whereIn('type', ['credit', 'transfer_in'])
                ->where('meta->source', 'session_mentor_payout')
                ->first();

        if ($existing) {
            return $existing;
        }

        $mentor = $this->mentor ?? User::find($this->mentor_id);
        if (! $mentor) {
            return null;
        }

        $feeRate = 0.20;
        $fee = round($gross * $feeRate, 2);
        $net = round($gross - $fee, 2);

        if ($net <= 0) {
            return null;
        }

        $durationMinutes = $this->duration_minutes;
        if ($this->actual_duration_seconds) {
            $durationMinutes = (int) max(1, ceil($this->actual_duration_seconds / 60));
        }

        $this->loadMissing('mentee:id,name');

        return $mentor->creditWallet(
            $net,
            'Session earning: ' . ($this->title ?: ('Session #' . $this->id)),
            [
                'reference'            => $reference,
                'transactionable_type' => self::class,
                'transactionable_id'   => $this->id,
                'meta'                 => [
                    'source'            => 'session_mentor_payout',
                    'booking_ref'       => $this->booking_ref,
                    'session_id'        => $this->id,
                    'mentee_id'         => $this->mentee_id,
                    'mentee_name'       => $this->mentee?->name,
                    'duration_minutes' => $durationMinutes,
                    'gross_amount'      => $gross,
                    'platform_fee'      => $fee,
                    'platform_fee_rate' => $feeRate,
                    'net_amount'        => $net,
                ],
            ]
        );
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
