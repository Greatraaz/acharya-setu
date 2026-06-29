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
    public function allTransactions(array $filters = [])
    {
        return WalletTransaction::with(['walletable', 'performedByAdmin'])
            ->when($filters['type'] ?? null, fn($q, $v) => $q->where('type', $v))
            ->when($filters['search'] ?? null, fn($q, $v) => $q->where('reference', 'like', "%{$v}%"))
            ->when($filters['from_date'] ?? null, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to_date'] ?? null, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(20);
    }
}