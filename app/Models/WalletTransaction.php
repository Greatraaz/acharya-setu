<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\HasWallet;

class WalletTransaction extends Model
{
    //
    use HasWallet;
    
    protected $fillable = [
        'user_id',
        'walletable_type', 'walletable_id',
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

    // Who owns this transaction record
    public function walletable(): MorphTo
    {
        return $this->morphTo();
    }

    // Linked source entity (Order, Invoice, etc.)
    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    // The paired transaction (for transfers)
    public function transferPair(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'transfer_pair_id');
    }

    public function user()        
    { 
        return $this->belongsTo(User::class); 
    }

    public function performedBy() 
    {
        return $this->belongsTo(User::class, 'performed_by'); 
    }

    // Which admin performed it
    public function performedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'performed_by');
    }

    // Scopes
    public function scopeOfType($query, string $type)     { return $query->where('type', $type); }
    public function scopeCompleted($query)                 { return $query->where('status', 'completed'); }
    public function scopeForDateRange($query, $from, $to)
    {
        return $query
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('created_at', '<=', $to));
    }

    // Helpers
    public function getIsDebitAttribute(): bool
    {
        return in_array($this->type, ['debit', 'transfer_out']);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
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
        return match($this->type) {
            'credit'       => 'success',
            'debit'        => 'danger',
            'refund'       => 'info',
            'transfer_in'  => 'primary',
            'transfer_out' => 'warning',
            default        => 'secondary',
        };
    }
}
