<?php

// app/Services/WalletService.php

namespace App\Services;

use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService
{
    /**
     * Credit any walletable (Customer or Admin)
     */
    public function credit($user, float $amount, string $description, array $options = []): WalletTransaction
    {
        return $user->creditWallet($amount, $description, $options);
    }

    /**
     * Debit any walletable
     */
    public function debit($user, float $amount, string $description, array $options = []): WalletTransaction
    {
        return $user->debitWallet($amount, $description, $options);
    }

    /**
     * Refund to wallet (e.g. order cancelled)
     */
    public function refund($user, float $amount, string $description, array $options = []): WalletTransaction
    {
        return $user->refundWallet($amount, $description, $options);
    }

    /**
     * Transfer between ANY two walletable users (Customer → Customer, Admin → Customer, etc.)
     */
    public function transfer($sender, $receiver, float $amount, string $note = '', array $options = []): array
    {
        if (!$sender->hasSufficientBalance($amount)) {
            throw new \Exception("Sender has insufficient balance.");
        }

        return DB::transaction(function () use ($sender, $receiver, $amount, $note, $options) {
            $reference = 'TRF-' . strtoupper(Str::random(10));

            // Sender: transfer_out
            $outTxn = $sender->_transferOut($amount, $note ?: "Transfer to {$receiver->name}", [
                'reference'    => $reference,
                'performed_by' => $options['performed_by'] ?? null,
                'meta'         => [
                    'to_type' => get_class($receiver),
                    'to_id'   => $receiver->id,
                    'to_name' => $receiver->name,
                ],
            ]);

            // Receiver: transfer_in
            $inTxn = $receiver->_transferIn($amount, $note ?: "Transfer from {$sender->name}", [
                'reference'      => $reference,
                'transfer_pair_id' => $outTxn->id,
                'performed_by'   => $options['performed_by'] ?? null,
                'meta'           => [
                    'from_type' => get_class($sender),
                    'from_id'   => $sender->id,
                    'from_name' => $sender->name,
                ],
            ]);

            // Link both sides
            $outTxn->update(['transfer_pair_id' => $inTxn->id]);

            return ['out' => $outTxn, 'in' => $inTxn];
        });
    }

    /**
     * Pay for an order using wallet balance
     */
    public function payForOrder($customer, $order): WalletTransaction
    {
        return $customer->debitWallet(
            $order->total_amount,
            "Payment for Order #{$order->order_number}",
            [
                'reference'            => 'ORD-' . $order->id,
                'transactionable_type' => get_class($order),
                'transactionable_id'   => $order->id,
            ]
        );
    }

    /**
     * Refund on order cancellation
     */
    public function refundOrder($customer, $order): WalletTransaction
    {
        return $customer->refundWallet(
            $order->total_amount,
            "Refund for cancelled Order #{$order->order_number}",
            [
                'reference'            => 'REF-' . $order->id,
                'transactionable_type' => get_class($order),
                'transactionable_id'   => $order->id,
            ]
        );
    }

    /**
     * All transactions across all walletable types (admin global view)
     */
    public function allTransactions(array $filters = [], int $perPage = 20)
    {
        return $this->filteredQuery($filters)
            ->with(['user', 'performedByAdmin', 'transactionable'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Summary totals for the current filter set (all matching rows, not just one page).
     */
    public function transactionSummary(array $filters = []): array
    {
        $base = $this->filteredQuery($filters);

        $totals = (clone $base)
            ->selectRaw("
                COUNT(*) as total_count,
                COALESCE(SUM(CASE WHEN type IN ('credit','refund','transfer_in') THEN amount ELSE 0 END), 0) as total_in,
                COALESCE(SUM(CASE WHEN type IN ('debit','transfer_out') THEN amount ELSE 0 END), 0) as total_out,
                COALESCE(SUM(CASE WHEN type = 'refund' THEN amount ELSE 0 END), 0) as total_refund,
                COALESCE(SUM(CASE WHEN type IN ('transfer_in','transfer_out') THEN 1 ELSE 0 END), 0) as transfer_count
            ")
            ->first();

        return [
            'total_count'    => (int) ($totals->total_count ?? 0),
            'total_in'       => (float) ($totals->total_in ?? 0),
            'total_out'      => (float) ($totals->total_out ?? 0),
            'total_refund'   => (float) ($totals->total_refund ?? 0),
            'transfer_count' => (int) ($totals->transfer_count ?? 0),
            'net'            => (float) ($totals->total_in ?? 0) - (float) ($totals->total_out ?? 0),
        ];
    }

    public function filteredQuery(array $filters = [])
    {
        $query = WalletTransaction::query();

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['role'])) {
            $query->whereHas('user', fn ($q) => $q->where('role', $filters['role']));
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (! empty($filters['min_amount'])) {
            $query->where('amount', '>=', (float) $filters['min_amount']);
        }

        if (! empty($filters['max_amount'])) {
            $query->where('amount', '<=', (float) $filters['max_amount']);
        }

        if (! empty($filters['search'])) {
            $term = trim((string) $filters['search']);
            $query->where(function ($q) use ($term) {
                $q->where('reference', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhereHas('user', function ($uq) use ($term) {
                        $uq->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                    });
            });
        }

        if (! empty($filters['category'])) {
            $this->applyCategoryFilter($query, (string) $filters['category']);
        }

        return $query;
    }

    private function applyCategoryFilter($query, string $category): void
    {
        $sessionClass = \App\Models\ConsultationSession::class;

        match ($category) {
            'session' => $query->where(function ($q) use ($sessionClass) {
                $q->where('transactionable_type', $sessionClass)
                    ->orWhere('description', 'like', '%session%')
                    ->orWhere('description', 'like', '%consultation%')
                    ->orWhere('description', 'like', '%mentor payout%')
                    ->orWhere('description', 'like', '%booking%');
            }),
            'topup' => $query->where(function ($q) {
                $q->where('description', 'like', '%top-up%')
                    ->orWhere('description', 'like', '%topup%')
                    ->orWhere('description', 'like', '%top up%')
                    ->orWhere(function ($qq) {
                        $qq->where('type', 'credit')
                            ->where('description', 'like', '%razorpay%');
                    });
            }),
            'withdrawal' => $query->where(function ($q) {
                $q->where('description', 'like', '%withdraw%')
                    ->orWhere('description', 'like', '%payout%')
                    ->orWhere('transactionable_type', 'like', '%Withdrawal%');
            }),
            'transfer' => $query->where(function ($q) {
                $q->whereIn('type', ['transfer_in', 'transfer_out'])
                    ->orWhere('reference', 'like', 'TRF-%');
            }),
            'plan' => $query->where(function ($q) {
                $q->where('description', 'like', '%plan%')
                    ->orWhere('description', 'like', '%subscription%')
                    ->orWhere('transactionable_type', 'like', '%Subscription%')
                    ->orWhere('transactionable_type', 'like', '%Plan%');
            }),
            'admin' => $query->where(function ($q) {
                $q->whereNotNull('performed_by')
                    ->whereNotIn('type', ['transfer_in', 'transfer_out'])
                    ->where(function ($qq) {
                        $qq->whereNull('reference')
                            ->orWhere('reference', 'not like', 'TRF-%');
                    });
            }),
            'bonus' => $query->where(function ($q) {
                $q->where('description', 'like', '%welcome%')
                    ->orWhere('description', 'like', '%bonus%');
            }),
            'other' => $query->where(function ($q) use ($sessionClass) {
                $q->whereNull('transactionable_type')
                    ->orWhere(function ($qq) use ($sessionClass) {
                        $qq->where('transactionable_type', '!=', $sessionClass)
                            ->where('transactionable_type', 'not like', '%Withdrawal%')
                            ->where('transactionable_type', 'not like', '%Subscription%')
                            ->where('transactionable_type', 'not like', '%Plan%');
                    });
            })->whereNotIn('type', ['transfer_in', 'transfer_out'])
                ->where(function ($q) {
                    $q->whereNull('description')
                        ->orWhere(function ($qq) {
                            $qq->where('description', 'not like', '%session%')
                                ->where('description', 'not like', '%consultation%')
                                ->where('description', 'not like', '%top-up%')
                                ->where('description', 'not like', '%topup%')
                                ->where('description', 'not like', '%withdraw%')
                                ->where('description', 'not like', '%plan%')
                                ->where('description', 'not like', '%subscription%')
                                ->where('description', 'not like', '%welcome%')
                                ->where('description', 'not like', '%bonus%');
                        });
                }),
            default => null,
        };
    }
}