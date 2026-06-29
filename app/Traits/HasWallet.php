<?php

// app/Traits/HasWallet.php

namespace App\Traits;

use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait HasWallet
{
    // ─── Relationship ────────────────────────────────────────────────────
    public function walletTransactions()
    {
        return $this->morphMany(WalletTransaction::class, 'walletable')->latest();
    }

    // ─── Balance ─────────────────────────────────────────────────────────
    public function getWalletBalanceAttribute(): float
    {
        return (float) ($this->attributes['wallet_balance'] ?? 0);
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return $this->wallet_balance >= $amount;
    }

    // ─── Public API ───────────────────────────────────────────────────────
    public function creditWallet(float $amount, string $description = '', array $options = []): WalletTransaction
    {
        return $this->recordTransaction('credit', $amount, $description, $options);
    }

    public function debitWallet(float $amount, string $description = '', array $options = []): WalletTransaction
    {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \Exception("Insufficient wallet balance. Available: ₹{$this->wallet_balance}");
        }

        return $this->recordTransaction('debit', $amount, $description, $options);
    }

    public function refundWallet(float $amount, string $description = 'Refund', array $options = []): WalletTransaction
    {
        return $this->recordTransaction('refund', $amount, $description, $options);
    }

    // ─── Transfer (handled by WalletService) ─────────────────────────────
    // Internal use only — called by WalletService::transfer()
    public function _transferOut(float $amount, string $description, array $options = []): WalletTransaction
    {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \Exception("Insufficient wallet balance for transfer.");
        }

        return $this->recordTransaction('transfer_out', $amount, $description, $options);
    }

    public function _transferIn(float $amount, string $description, array $options = []): WalletTransaction
    {
        return $this->recordTransaction('transfer_in', $amount, $description, $options);
    }

    // ─── Core recorder ───────────────────────────────────────────────────
    private function recordTransaction(string $type, float $amount, string $description, array $options): WalletTransaction
    {
        return DB::transaction(function () use ($type, $amount, $description, $options) {
            // Lock row to prevent race conditions
            $this->newQuery()->lockForUpdate()->find($this->id);
            $this->refresh();

            $balanceBefore = $this->wallet_balance;

            if (in_array($type, ['credit', 'refund', 'transfer_in'])) {
                $this->increment('wallet_balance', $amount);
            } else {
                $this->decrement('wallet_balance', $amount);
            }

            $this->refresh();

            return $this->walletTransactions()->create([
                'type'                   => $type,
                'amount'                 => $amount,
                'balance_before'         => $balanceBefore,
                'balance_after'          => $this->wallet_balance,
                'description'            => $description,
                'reference'              => $options['reference'] ?? 'TXN-' . strtoupper(Str::random(10)),
                'status'                 => $options['status'] ?? 'completed',
                'transfer_pair_id'       => $options['transfer_pair_id'] ?? null,
                'transactionable_type'   => $options['transactionable_type'] ?? null,
                'transactionable_id'     => $options['transactionable_id'] ?? null,
                'performed_by'           => $options['performed_by'] ?? null,
                'meta'                   => $options['meta'] ?? null,
            ]);
        });
    }

    // ─── Summary ─────────────────────────────────────────────────────────
    public function walletSummary(): array
    {
        $base = $this->walletTransactions()->completed();

        return [
            'balance'        => $this->wallet_balance,
            'total_credited' => (clone $base)->whereIn('type', ['credit', 'refund', 'transfer_in'])->sum('amount'),
            'total_debited'  => (clone $base)->whereIn('type', ['debit', 'transfer_out'])->sum('amount'),
            'total_refunded' => (clone $base)->where('type', 'refund')->sum('amount'),
            'total_transfers'=> (clone $base)->whereIn('type', ['transfer_in', 'transfer_out'])->count(),
        ];
    }
}