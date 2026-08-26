<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ConsultationSession extends Model
{
    use SoftDeletes;

    public const SCHEDULE_TIMEZONE = 'Asia/Kolkata';

    protected $fillable = [
        'booking_ref', 'mentor_id', 'mentee_id', 'scheduled_at', 'duration_minutes', 'timezone',
        'title', 'agenda', 'mentor_notes', 'meeting_link', 'meeting_provider', 'meeting_channel',
        'status', 'cancellation_reason', 'cancelled_by', 'cancelled_at', 'started_at', 'ended_at',
        'actual_duration_seconds', 'amount', 'currency', 'payment_status', 'payment_method',
        'wallet_amount', 'razorpay_amount', 'payment_reference',
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
            if (empty($session->booking_ref)) {
                do {
                    $ref = 'AS-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
                } while (self::where('booking_ref', $ref)->exists());

                $session->booking_ref = $ref;
            }

            if (empty($session->meeting_channel)) {
                $session->meeting_channel = strtoupper(Str::random(10));
            }

            if (empty($session->meeting_link)) {
                $session->meeting_link = url('as/'.$session->meeting_channel);
            }

            if (empty($session->status)) {
                $session->status = self::STATUS_UPCOMING;
            }
        });
    }

    /** Soft hold for checkout drafts stored in cache (no DB row until paid). */
    public const PAYMENT_HOLD_MINUTES = 10;

    public const STATUS_UPCOMING  = 'upcoming';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /** @deprecated Kept for legacy call sites during cleanup */
    public const STATUS_PENDING   = 'upcoming';
    public const STATUS_CONFIRMED = 'upcoming';
    public const STATUS_ONGOING   = 'upcoming';
    public const STATUS_NO_SHOW   = 'completed';

    public const BOOKING_DURATIONS = [15, 30, 60, 90];

    public const STATUSES = [
        'upcoming'  => 'Upcoming',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

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
        return $q->where('status', self::STATUS_UPCOMING)
            ->where('scheduled_at', '>=', Carbon::now(self::SCHEDULE_TIMEZONE)->format('Y-m-d H:i:s'));
    }

    /**
     * Sessions that currently block a mentor time slot.
     */
    public function scopeOccupyingSlot(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_UPCOMING);
    }

    public static function bookingDraftCacheKey(string $orderId): string
    {
        return 'session_booking_order:'.$orderId;
    }

    public static function slotHoldCacheKey(int $mentorId, Carbon $scheduledAt): string
    {
        $stamp = $scheduledAt->copy()->timezone(self::SCHEDULE_TIMEZONE)->format('Y-m-d H:i:s');

        return 'session_slot_hold:'.$mentorId.':'.$stamp;
    }

    public static function hasActiveSlotHold(int $mentorId, Carbon $scheduledAt, ?int $exceptMenteeId = null): bool
    {
        $val = Cache::get(self::slotHoldCacheKey($mentorId, $scheduledAt));
        if (! $val) {
            return false;
        }
        if ($exceptMenteeId !== null && (int) ($val['mentee_id'] ?? 0) === $exceptMenteeId) {
            return true; // own hold still counts as occupied for others; caller decides
        }

        return true;
    }

    public static function slotHoldDayKey(int $mentorId, string $date): string
    {
        return 'session_slot_holds_day:'.$mentorId.':'.$date;
    }

    public static function putSlotHold(int $mentorId, int $menteeId, Carbon $scheduledAt, string $orderId): void
    {
        $scheduledAt = $scheduledAt->copy()->timezone(self::SCHEDULE_TIMEZONE);
        $payload = [
            'mentee_id' => $menteeId,
            'order_id'  => $orderId,
        ];
        Cache::put(self::slotHoldCacheKey($mentorId, $scheduledAt), $payload, now()->addMinutes(self::PAYMENT_HOLD_MINUTES));

        $dayKey = self::slotHoldDayKey($mentorId, $scheduledAt->toDateString());
        $day = Cache::get($dayKey, []);
        $day[$scheduledAt->format('H:i')] = $payload;
        Cache::put($dayKey, $day, now()->addMinutes(self::PAYMENT_HOLD_MINUTES));
    }

    public static function clearSlotHold(int $mentorId, Carbon $scheduledAt): void
    {
        $scheduledAt = $scheduledAt->copy()->timezone(self::SCHEDULE_TIMEZONE);
        Cache::forget(self::slotHoldCacheKey($mentorId, $scheduledAt));

        $dayKey = self::slotHoldDayKey($mentorId, $scheduledAt->toDateString());
        $day = Cache::get($dayKey, []);
        unset($day[$scheduledAt->format('H:i')]);
        if ($day === []) {
            Cache::forget($dayKey);
        } else {
            Cache::put($dayKey, $day, now()->addMinutes(self::PAYMENT_HOLD_MINUTES));
        }
    }

    /** @return list<string> HH:MM times held by unpaid checkouts */
    public static function heldTimesForDate(int $mentorId, string $date): array
    {
        $day = Cache::get(self::slotHoldDayKey($mentorId, $date), []);

        return array_keys(is_array($day) ? $day : []);
    }

    /** No-op compatibility: unpaid holds are cache-only now. */
    public static function expireAbandonedUnpaidPayments(): int
    {
        return 0;
    }

    /** No-op compatibility: unpaid holds are cache-only now. */
    public static function releaseOwnUnpaidHold(int $menteeId, int $mentorId, Carbon $scheduledAt): int
    {
        self::clearSlotHold($mentorId, $scheduledAt);

        return 0;
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
        return $q->where('status', self::STATUS_COMPLETED);
    }

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
            'upcoming'  => ['bg' => '#dbeafe', 'text' => '#1e40af', 'dot' => '#2563eb'],
            'completed' => ['bg' => '#f0fdf4', 'text' => '#14532d', 'dot' => '#22c55e'],
            'cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'dot' => '#dc2626'],
            default     => ['bg' => '#f3f4f6', 'text' => '#374151', 'dot' => '#9ca3af'],
        };
    }

    public function getScheduledEndAttribute(): Carbon
    {
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
    }

    public function getCanReviewAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->status === self::STATUS_UPCOMING && $this->isScheduledInFuture();
    }

    public function getActualDurationFormattedAttribute(): string
    {
        $s = $this->actual_duration_seconds ?? ($this->duration_minutes * 60);

        return sprintf('%dh %02dm', intdiv($s, 3600), intdiv($s % 3600, 60));
    }

    /**
     * After scheduled end time, mark remaining upcoming sessions as completed
     * (whether attended or not).
     */
    public static function expireMissedSessions(?int $mentorId = null, ?int $menteeId = null): int
    {
        $query = static::query()
            ->where('status', self::STATUS_UPCOMING)
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

        $count = 0;
        foreach ($query->get() as $session) {
            $session->complete();
            $count++;
        }

        return $count;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function canJoinCall(): bool
    {
        return $this->status === self::STATUS_UPCOMING;
    }

    /** @deprecated Confirm is removed; sessions are upcoming once paid. */
    public function confirm(): void
    {
        if ($this->status !== self::STATUS_CANCELLED) {
            $this->update(['status' => self::STATUS_UPCOMING]);
        }
    }

    /**
     * Record that a call started. Status stays upcoming until complete().
     */
    public function start(): void
    {
        if ($this->status !== self::STATUS_UPCOMING) {
            return;
        }

        $this->update([
            'started_at' => $this->started_at ?? now(),
        ]);
    }

    public function complete(): void
    {
        if ($this->status === self::STATUS_COMPLETED) {
            return;
        }

        if ($this->status === self::STATUS_CANCELLED) {
            return;
        }

        $duration = $this->started_at ? (int) $this->started_at->diffInSeconds(now()) : null;
        $this->update([
            'status'                  => self::STATUS_COMPLETED,
            'ended_at'                => $this->ended_at ?? now(),
            'actual_duration_seconds' => $this->actual_duration_seconds ?: $duration,
        ]);
        optional($this->mentor)->increment('total_sessions');

        $this->settleMentorPayout();
    }

    /**
     * Finalize past upcoming sessions (alias used by list pages).
     */
    public static function completeStaleOngoingSessions(
        ?int $mentorId = null,
        ?int $menteeId = null
    ): int {
        return self::expireMissedSessions($mentorId, $menteeId);
    }

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

        $reference = 'SES-EARN-'.$this->id;

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
            'Session earning: '.($this->title ?: ('Session #'.$this->id)),
            [
                'reference'            => $reference,
                'transactionable_type' => self::class,
                'transactionable_id'   => $this->id,
                'meta'                 => [
                    'source'           => 'session_mentor_payout',
                    'booking_ref'      => $this->booking_ref,
                    'session_id'       => $this->id,
                    'mentee_id'        => $this->mentee_id,
                    'mentee_name'      => $this->mentee?->name,
                    'duration_minutes' => $durationMinutes,
                    'gross_amount'     => $gross,
                    'platform_fee'     => $fee,
                    'platform_fee_rate'=> $feeRate,
                    'net_amount'       => $net,
                ],
            ]
        );
    }

    public function cancel(int $cancelledBy, string $reason = ''): void
    {
        $this->update([
            'status'              => self::STATUS_CANCELLED,
            'cancelled_by'        => $cancelledBy,
            'cancelled_at'        => now(),
            'cancellation_reason' => $reason,
        ]);
    }
}
