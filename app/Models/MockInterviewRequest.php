<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MockInterviewRequest extends Model
{
    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    /** Allowed booking lengths (minutes). */
    public const DURATIONS = [30, 45, 60, 90];

    protected $fillable = [
        'user_id',
        'status',
        'duration_minutes',
        'preferred_at',
        'confirmed_at',
        'timezone',
        'target_role',
        'mentee_notes',
        'mentor_id',
        'assigned_by',
        'assigned_at',
        'is_paid_addon',
        'amount',
        'rate_per_minute',
        'currency',
        'payment_status',
        'payment_method',
        'wallet_amount',
        'razorpay_amount',
        'razorpay_order_id',
        'razorpay_payment_id',
        'payment_reference',
        'plan_slug',
        'admin_notes',
        'feedback',
        'reviewed_by',
        'reviewed_at',
        'completed_at',
        'meeting_channel',
        'meeting_link',
        'meeting_provider',
    ];

    protected $casts = [
        'preferred_at'     => 'datetime',
        'confirmed_at'     => 'datetime',
        'assigned_at'      => 'datetime',
        'reviewed_at'      => 'datetime',
        'completed_at'     => 'datetime',
        'is_paid_addon'    => 'boolean',
        'amount'           => 'decimal:2',
        'rate_per_minute'  => 'decimal:2',
        'wallet_amount'    => 'decimal:2',
        'razorpay_amount'  => 'decimal:2',
        'duration_minutes' => 'integer',
    ];

    protected static function booted(): void
    {
        // Unpaid bookings must never appear in mentee/admin lists.
        static::addGlobalScope('paid_or_free', function (Builder $builder) {
            $builder->where('status', '!=', self::STATUS_PENDING_PAYMENT);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    /** Admin who confirmed / hosts the interview. */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function interviewer(): BelongsTo
    {
        return $this->assigner();
    }

    public function isMentee(User $user): bool
    {
        return (int) $this->user_id === (int) $user->id;
    }

    /** Any admin may host; preferred host is the confirming admin. */
    public function isHost(User $user): bool
    {
        if (($user->role ?? null) === 'admin') {
            return true;
        }

        return (int) ($this->assigned_by ?? 0) === (int) $user->id;
    }

    public function isParticipant(User $user): bool
    {
        return $this->isMentee($user) || $this->isHost($user);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_PAYMENT => 'Awaiting payment',
            self::STATUS_SUBMITTED       => 'Awaiting confirmation',
            self::STATUS_CONFIRMED       => 'Confirmed',
            self::STATUS_COMPLETED       => 'Completed',
            self::STATUS_CANCELLED       => 'Cancelled',
            default                      => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    /** Prefer `confirmed_at` slot start, else mentee's preferred time. */
    public function scheduledStart(): ?\Carbon\Carbon
    {
        return $this->preferred_at?->copy();
    }

    public function scheduledEnd(): ?\Carbon\Carbon
    {
        $start = $this->scheduledStart();
        if (! $start) {
            return null;
        }

        return $start->copy()->addMinutes(max(1, (int) $this->duration_minutes));
    }

    public function isWithinCallWindow(): bool
    {
        if ($this->status !== self::STATUS_CONFIRMED) {
            return false;
        }

        // Join anytime after confirmation until the scheduled window ends.
        return ! $this->callWindowEnded();
    }

    public function callWindowEnded(): bool
    {
        $end = $this->scheduledEnd();
        if (! $end) {
            return false;
        }

        return now('Asia/Kolkata')->gte($end->copy()->timezone('Asia/Kolkata'));
    }

    public function canJoinCall(): bool
    {
        return $this->status === self::STATUS_CONFIRMED
            && filled($this->meeting_channel)
            && $this->isWithinCallWindow();
    }

    public function toPublicArray(): array
    {
        $interviewer = $this->assigner;

        return [
            'id'                => $this->id,
            'status'            => $this->status,
            'status_label'      => $this->statusLabel(),
            'duration_minutes'  => (int) $this->duration_minutes,
            'preferred_at'      => $this->preferred_at?->toDateTimeString(),
            'confirmed_at'      => $this->confirmed_at?->toDateTimeString(),
            'timezone'          => $this->timezone ?: 'Asia/Kolkata',
            'target_role'       => $this->target_role,
            'mentee_notes'      => $this->mentee_notes,
            'interviewer'       => $interviewer ? [
                'id'         => $interviewer->id,
                'name'       => $interviewer->name,
                'avatar_url' => $interviewer->avatar_url,
                'role'       => 'admin',
            ] : null,
            'is_paid_addon'     => (bool) $this->is_paid_addon,
            'amount'            => (float) $this->amount,
            'rate_per_minute'   => $this->rate_per_minute !== null ? (float) $this->rate_per_minute : null,
            'currency'          => $this->currency ?: 'INR',
            'payment_status'    => $this->payment_status,
            'payment_method'    => $this->payment_method,
            'wallet_amount'     => (float) ($this->wallet_amount ?? 0),
            'razorpay_amount'   => (float) ($this->razorpay_amount ?? 0),
            'payment_reference' => $this->payment_reference,
            'plan_slug'         => $this->plan_slug,
            'admin_notes'       => $this->admin_notes,
            'feedback'          => $this->feedback,
            'completed_at'      => $this->completed_at?->toDateTimeString(),
            'created_at'        => $this->created_at?->toDateTimeString(),
            'meeting'           => [
                'provider'   => $this->meeting_provider ?: 'agora',
                'channel'    => $this->meeting_channel,
                'link'       => $this->meeting_link,
                'can_join'   => $this->canJoinCall(),
                'window_ends_at' => $this->scheduledEnd()?->toDateTimeString(),
            ],
        ];
    }
}
