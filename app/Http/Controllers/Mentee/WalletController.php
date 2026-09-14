<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use App\Models\SessionInvoice;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WalletController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $transactions = WalletTransaction::where('user_id', $user->id)
            ->with(['transactionable' => function ($morphTo) {
                $morphTo->morphWith([
                    ConsultationSession::class => [
                        'mentor:id,name',
                        'sessionInvoice:id,consultation_session_id,invoice_number,user_id,total_amount,duration_minutes,session_at,meta',
                    ],
                ]);
            }])
            ->latest()
            ->paginate(20);

        // Attach invoices for session rows that only have booking_ref / WAL- reference.
        $this->attachFallbackInvoices($transactions, (int) $user->id);

        $stats = [
            'balance'  => $user->wallet_balance,
            'spent'    => WalletTransaction::where('user_id', $user->id)->where('type', 'debit')->sum('amount'),
            'refunded' => WalletTransaction::where('user_id', $user->id)->where('type', 'refund')->sum('amount'),
        ];

        return view('frontend.mentee.wallet', compact('transactions', 'stats'));
    }

    /**
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection  $transactions
     */
    private function attachFallbackInvoices($transactions, int $userId): void
    {
        if (! Schema::hasTable('session_invoices')) {
            return;
        }

        $needsLookup = [];
        foreach ($transactions as $txn) {
            $session = $txn->transactionable instanceof ConsultationSession
                ? $txn->transactionable
                : null;

            if ($session?->sessionInvoice) {
                $txn->setAttribute('resolved_invoice', $session->sessionInvoice);
                continue;
            }

            $meta = is_array($txn->meta) ? $txn->meta : [];
            $bookingRef = $meta['booking_ref']
                ?? (str_starts_with((string) $txn->reference, 'WAL-')
                    ? substr((string) $txn->reference, 4)
                    : null);

            if ($bookingRef) {
                $needsLookup[$txn->id] = $bookingRef;
            }
        }

        if ($needsLookup === []) {
            return;
        }

        $invoices = SessionInvoice::query()
            ->where('user_id', $userId)
            ->whereIn('booking_ref', array_values($needsLookup))
            ->get()
            ->keyBy('booking_ref');

        foreach ($transactions as $txn) {
            if ($txn->getAttribute('resolved_invoice')) {
                continue;
            }
            $ref = $needsLookup[$txn->id] ?? null;
            if ($ref && isset($invoices[$ref])) {
                $txn->setAttribute('resolved_invoice', $invoices[$ref]);
            }
        }
    }

    /**
     * Create a Razorpay order for wallet top-up.
     * POST /mentee/wallet/topup/initiate
     */
    public function initiateTopup(Request $request)
    {
        $request->validate(['amount' => 'required|integer|min:100|max:100000']);

        $amountPaise = $request->amount * 100;   // Razorpay uses paise

        try {
            $api = new \Razorpay\Api\Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $order = $api->order->create([
                'amount'   => $amountPaise,
                'currency' => 'INR',
                'receipt'  => 'wallet_' . auth()->id() . '_' . time(),
                'notes'    => ['user_id' => auth()->id(), 'purpose' => 'wallet_topup'],
            ]);

            return response()->json([
                'order_id' => $order->id,
                'amount'   => $amountPaise,
                'currency' => 'INR',
                'key'      => config('services.razorpay.key'),
                'name'     => 'Vedrix',
                'description' => 'Wallet Top-up',
                'prefill'  => [
                    'name'  => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'contact' => auth()->user()->phone ?? '',
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Razorpay order create failed: ' . $e->getMessage());
            return response()->json(['message' => 'Payment gateway error. Please try again.'], 500);
        }
    }

    /**
     * Verify Razorpay payment and credit wallet.
     * POST /mentee/wallet/topup/verify
     */
    public function verifyTopup(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id'   => 'required|string',
            'razorpay_signature'  => 'required|string',
            'amount'              => 'required|numeric',
        ]);

        // Verify signature
        $expectedSig = hash_hmac('sha256',
            $request->razorpay_order_id . '|' . $request->razorpay_payment_id,
            config('services.razorpay.secret')
        );

        if ($expectedSig !== $request->razorpay_signature) {
            return response()->json(['message' => 'Payment verification failed.'], 422);
        }

        $user          = auth()->user();
        $amount        = $request->amount;   // in rupees
        $balanceBefore = $user->wallet_balance;

        $user->increment('wallet_balance', $amount);

        WalletTransaction::create([
            'user_id'        => $user->id,
            'type'           => 'credit',
            'amount'         => $amount,
            'balance_before' => $balanceBefore,
            'balance_after'  => $user->fresh()->wallet_balance,
            'description'    => 'Wallet top-up via Razorpay',
            'reference'      => $request->razorpay_payment_id,
            'status'         => 'completed',
        ]);

        return response()->json([
            'message' => "₹" . number_format($amount, 0) . " added to your wallet!",
            'balance' => $user->fresh()->wallet_balance,
        ]);
    }
}