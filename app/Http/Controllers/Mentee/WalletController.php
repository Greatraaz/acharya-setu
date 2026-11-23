<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\ConsultationSession;
use App\Models\SessionInvoice;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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

        $creds = $this->razorpayCredentials();
        if (! ($creds['enabled'] ?? false)) {
            return response()->json(['message' => 'Online payment is disabled. Ask an admin to enable Razorpay.'], 422);
        }
        if ($creds['key'] === '' || $creds['secret'] === '') {
            return response()->json([
                'message' => $creds['message'] ?: 'Payment gateway is not configured.',
            ], 503);
        }

        $user = auth()->user();
        $amountPaise = (int) $request->amount * 100;

        try {
            $response = Http::withBasicAuth($creds['key'], $creds['secret'])
                ->acceptJson()
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount'   => $amountPaise,
                    'currency' => 'INR',
                    'receipt'  => Str::limit('wal_'.$user->id.'_'.time(), 40, ''),
                    'notes'    => [
                        'user_id' => (string) $user->id,
                        'purpose' => 'wallet_topup',
                        'amount'  => (string) $request->amount,
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('Razorpay wallet top-up order failed.', [
                    'user_id' => $user->id,
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);

                return response()->json(['message' => 'Payment gateway error. Please try again.'], 502);
            }

            $order = $response->json();
        } catch (\Throwable $e) {
            Log::error('Razorpay order create failed: '.$e->getMessage());

            return response()->json(['message' => 'Payment gateway error. Please try again.'], 500);
        }

        return response()->json([
            'order_id' => $order['id'] ?? null,
            'amount'   => $amountPaise,
            'currency' => 'INR',
            'key'      => $creds['key'],
            'name'     => 'Vedrix',
            'description' => 'Wallet Top-up',
            'prefill'  => [
                'name'    => $user->name,
                'email'   => $user->email,
                'contact' => $user->phone ?? '',
            ],
        ]);
    }

    /**
     * Verify Razorpay payment and credit wallet.
     * POST /mentee/wallet/topup/verify
     */
    public function verifyTopup(Request $request)
    {
        $data = $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id'   => 'required|string',
            'razorpay_signature'  => 'required|string',
            'amount'              => 'required|numeric|min:1',
        ]);

        $creds = $this->razorpayCredentials();
        if (($creds['secret'] ?? '') === '') {
            return response()->json(['message' => 'Payment gateway is not configured.'], 503);
        }

        $expectedSig = hash_hmac(
            'sha256',
            $data['razorpay_order_id'].'|'.$data['razorpay_payment_id'],
            $creds['secret']
        );

        if (! hash_equals($expectedSig, $data['razorpay_signature'])) {
            return response()->json(['message' => 'Payment verification failed.'], 422);
        }

        $user = auth()->user();
        $amount = round((float) $data['amount'], 2);
        $paymentId = $data['razorpay_payment_id'];

        // Idempotent: same Razorpay payment already credited
        $existing = WalletTransaction::where('user_id', $user->id)
            ->where('reference', $paymentId)
            ->where('type', 'credit')
            ->where('status', 'completed')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Wallet already credited for this payment.',
                'balance' => round((float) $user->fresh()->wallet_balance, 2),
            ]);
        }

        try {
            $user->creditWallet(
                $amount,
                'Wallet top-up via Razorpay',
                [
                    'reference' => $paymentId,
                    'meta' => [
                        'razorpay_order_id' => $data['razorpay_order_id'],
                        'razorpay_payment_id' => $paymentId,
                        'source' => 'web_wallet_topup',
                    ],
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Wallet credit after Razorpay verify failed.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Payment verified but wallet credit failed. Contact support with payment id: '.$paymentId,
            ], 500);
        }

        $notifications = app(\App\Services\NotificationService::class);
        $notifications->send(
            $user,
            'wallet_topup',
            'Wallet credited',
            '₹'.number_format($amount, 0).' was added to your wallet.',
            ['amount' => $amount, 'reference' => $paymentId],
            $notifications->roleUrl($user, 'wallet')
        );

        return response()->json([
            'message' => '₹'.number_format($amount, 0).' added to your wallet!',
            'balance' => round((float) $user->fresh()->wallet_balance, 2),
        ]);
    }

    /**
     * Same keys session booking uses (admin settings, then .env).
     *
     * @return array{enabled: bool, key: string, secret: string, message: string|null, mode: string}
     */
    private function razorpayCredentials(): array
    {
        $settings = AppSetting::razorpay();

        return [
            'enabled' => (bool) ($settings['enabled'] ?? false),
            'key'     => (string) ($settings['key'] ?? ''),
            'secret'  => (string) ($settings['secret'] ?? ''),
            'message' => $settings['message'] ?? null,
            'mode'    => $settings['mode'] ?? 'test',
        ];
    }
}