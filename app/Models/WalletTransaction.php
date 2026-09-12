<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class WalletTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'type', 'amount', 'balance_before', 'balance_after',
        'description', 'reference', 'status',
        'transfer_pair_id',
        'transactionable_type', 'transactionable_id',
        'performed_by', 'meta',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after'  => 'decimal:2',
        'meta'           => 'array',
    ];

    /**
     * Keep API timestamps in app timezone (IST), not UTC.
     * Default Laravel JSON serialization converts Carbon to UTC (...Z).
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return Carbon::instance($date)
            ->timezone(config('app.timezone', 'Asia/Kolkata'))
            ->format('Y-m-d\TH:i:sP');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function transferPair(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'transfer_pair_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /** Alias kept for existing admin views / eager loads. */
    public function performedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForDateRange($query, $from, $to)
    {
        return $query
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));
    }

    public function getIsDebitAttribute(): bool
    {
        return in_array($this->type, ['debit', 'transfer_out']);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'credit'       => 'Credit',
            'debit'        => 'Debit',
            'refund'       => 'Refund',
            'transfer_in'  => 'Transfer In',
            'transfer_out' => 'Transfer Out',
            default        => ucfirst($this->type),
        };
    }

    public function getTypeBadgeColorAttribute(): string
    {
        return match ($this->type) {
            'credit'       => 'success',
            'debit'        => 'danger',
            'refund'       => 'info',
            'transfer_in'  => 'primary',
            'transfer_out' => 'warning',
            default        => 'secondary',
        };
    }

    /**
     * Business category inferred from linked entity / description / reference.
     */
    public function getCategoryAttribute(): string
    {
        $type = (string) ($this->transactionable_type ?? '');
        $desc = strtolower((string) ($this->description ?? ''));
        $ref  = strtoupper((string) ($this->reference ?? ''));
        $meta = is_array($this->meta) ? $this->meta : [];

        if ($type !== '' && str_contains($type, 'ConsultationSession')) {
            return 'session';
        }
        if ($type !== '' && (str_contains($type, 'UserSubscription') || str_contains($type, 'Plan'))) {
            return 'plan';
        }
        if ($type !== '' && str_contains($type, 'WithdrawalRequest')) {
            return 'withdrawal';
        }

        if (str_starts_with($ref, 'TRF-') || in_array($this->type, ['transfer_in', 'transfer_out'], true)) {
            return 'transfer';
        }
        if (str_contains($desc, 'withdraw') || str_contains($desc, 'payout')) {
            return 'withdrawal';
        }
        if (str_contains($desc, 'top-up') || str_contains($desc, 'topup') || str_contains($desc, 'top up')) {
            return 'topup';
        }
        if (str_contains($desc, 'session') || str_contains($desc, 'consultation') || str_contains($desc, 'mentor payout') || str_contains($desc, 'booking')) {
            return 'session';
        }
        if (str_contains($desc, 'plan') || str_contains($desc, 'subscription')) {
            return 'plan';
        }
        if (str_contains($desc, 'welcome') || str_contains($desc, 'bonus')) {
            return 'bonus';
        }
        if (($meta['source'] ?? null) === 'admin' || $this->performed_by) {
            if (! str_starts_with($ref, 'TRF-')) {
                return 'admin';
            }
        }

        return 'other';
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'session'    => 'Session',
            'topup'      => 'Wallet Top-up',
            'withdrawal' => 'Withdrawal',
            'transfer'   => 'Transfer',
            'plan'       => 'Plan / Subscription',
            'admin'      => 'Admin Adjust',
            'bonus'      => 'Bonus',
            default      => 'Other',
        };
    }

    public static function categoryOptions(): array
    {
        return [
            'session'    => 'Session',
            'topup'      => 'Wallet Top-up',
            'withdrawal' => 'Withdrawal',
            'transfer'   => 'Transfer',
            'plan'       => 'Plan / Subscription',
            'admin'      => 'Admin Adjust',
            'bonus'      => 'Bonus',
            'other'      => 'Other',
        ];
    }
}
