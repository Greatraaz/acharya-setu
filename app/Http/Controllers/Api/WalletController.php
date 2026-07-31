<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function balance(Request $request): JsonResponse
    {
        $u = $request->user();

        $credit = (float) WalletTransaction::where('user_id', $u->id)
            ->whereIn('type', ['credit', 'refund', 'transfer_in'])
            ->where('status', 'completed')
            ->sum('amount');

        $debit = (float) WalletTransaction::where('user_id', $u->id)
            ->whereIn('type', ['debit', 'transfer_out'])
            ->where('status', 'completed')
            ->sum('amount');

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'balance'    => round((float) $u->wallet_balance, 2),
            'credit'     => round($credit, 2),
            'debit'      => round($debit, 2),
        ], 200);
    }

    public function transactions(Request $request): JsonResponse
    {
        $transactions = WalletTransaction::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'status'       => true,
            'statuscode'   => 200,
            'transactions' => collect($transactions->items())
                ->map(fn (WalletTransaction $txn) => $this->formatTransaction($txn))
                ->values(),
            'pagination'   => [
                'total'        => $transactions->total(),
                'per_page'     => $transactions->perPage(),
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'from'         => $transactions->firstItem(),
                'to'           => $transactions->lastItem(),
            ],
        ], 200);
    }

    /**
     * Create Razorpay order for wallet top-up.
     * POST /api/v1/{role}/wallet/topup/initiate
     */
    public function initiateTopup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:100000',
        ]);

        $user = $request->user();
        $amountRupees = round((float) $data['amount'], 2);
        $amountPaise = (int) round($amountRupees * 100);

        if ($amountPaise < 100) {
            return response()->json([
                'status'  => false,
                'message' => 'Minimum top-up amount is ₹1.',
            ], 422);
        }

        $creds = $this->razorpayCredentials();
        if (empty($creds['key']) || empty($creds['secret'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment gateway is not configured.',
            ], 503);
        }

        if (! ($creds['enabled'] ?? true)) {
            return response()->json([
                'status'  => false,
                'message' => 'Razorpay is disabled in admin settings.',
            ], 503);
        }

        $receipt = Str::limit('wal_' . $user->id . '_' . time(), 40, '');

        try {
            $response = Http::withBasicAuth($creds['key'], $creds['secret'])
                ->acceptJson()
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount'   => $amountPaise,
                    'currency' => 'INR',
                    'receipt'  => $receipt,
                    'notes'    => [
                        'user_id' => (string) $user->id,
                        'role'    => (string) $user->role,
                        'purpose' => 'wallet_topup',
                        'amount'  => (string) $amountRupees,
                    ],
                ]);

            if (! $response->successful()) {
                $razorpayError = $response->json('error.description')
                    ?? $response->json('error.reason')
                    ?? $response->body();

                Log::error('Razorpay wallet top-up order failed.', [
                    'user_id' => $user->id,
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'Unable to initiate payment right now.',
                    'error'   => config('app.debug') ? $razorpayError : null,
                ], 502);
            }

            $order = $response->json();
        } catch (\Throwable $e) {
            Log::error('Razorpay wallet top-up exception.', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Unable to initiate payment right now.',
            ], 502);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Payment order created.',
            'data'    => [
                'razorpay_key'  => $creds['key'],
                'order_id'      => $order['id'] ?? null,
                'amount'        => $amountRupees,
                'amount_paise'  => $amountPaise,
                'currency'      => 'INR',
                'name'          => 'AcharyaSetu',
                'description'   => 'Wallet Top-up',
                'prefill'       => [
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'contact' => $user->phone ?? '',
                ],
            ],
        ], 201);
    }

    /**
     * Verify Razorpay payment and credit wallet.
     * POST /api/v1/{role}/wallet/topup/verify
     */
    public function verifyTopup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
            'amount'              => 'required|numeric|min:1',
        ]);

        $creds = $this->razorpayCredentials();
        if (empty($creds['secret'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment gateway is not configured.',
            ], 503);
        }

        $expectedSig = hash_hmac(
            'sha256',
            $data['razorpay_order_id'] . '|' . $data['razorpay_payment_id'],
            $creds['secret']
        );

        if (! hash_equals($expectedSig, $data['razorpay_signature'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment signature verification failed.',
            ], 422);
        }

        $user = $request->user();
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
                'status'  => true,
                'message' => 'Wallet already credited for this payment.',
                'balance' => round((float) $user->fresh()->wallet_balance, 2),
                'transaction' => $this->formatTransaction($existing),
            ]);
        }

        try {
            $txn = $user->creditWallet(
                $amount,
                'Wallet top-up via Razorpay',
                [
                    'reference' => $paymentId,
                    'meta'      => [
                        'razorpay_order_id'   => $data['razorpay_order_id'],
                        'razorpay_payment_id' => $paymentId,
                        'source'              => 'api_wallet_topup',
                    ],
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Wallet credit after Razorpay verify failed.', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Payment verified but wallet credit failed. Contact support with payment id: ' . $paymentId,
            ], 500);
        }

        return response()->json([
            'status'      => true,
            'message'     => '₹' . number_format($amount, 0) . ' added to your wallet.',
            'balance'     => round((float) $user->fresh()->wallet_balance, 2),
            'transaction' => $this->formatTransaction($txn),
        ], 200);
    }

    /**
     * Normalize wallet txn timestamps to IST for API clients.
     */
    private function formatTransaction(WalletTransaction $txn): array
    {
        $data = $txn->toArray();

        // Read naive DB clock as IST (how we store it), never shift as if it were UTC
        $created = $txn->created_at
            ? \Illuminate\Support\Carbon::parse($txn->getRawOriginal('created_at'), 'Asia/Kolkata')
            : null;
        $updated = $txn->updated_at
            ? \Illuminate\Support\Carbon::parse($txn->getRawOriginal('updated_at'), 'Asia/Kolkata')
            : null;

        $data['created_at']     = $created?->format('Y-m-d\TH:i:sP');
        $data['updated_at']     = $updated?->format('Y-m-d\TH:i:sP');
        $data['created_at_ist'] = $created?->format('d M Y, h:i A');
        $data['updated_at_ist'] = $updated?->format('d M Y, h:i A');
        $data['timezone']       = 'Asia/Kolkata';

        return $data;
    }

    private function razorpayCredentials(): array
    {
        $settings = AppSetting::razorpay();

        return [
            'enabled' => $settings['enabled'] ?? true,
            'key'     => $settings['key'] ?: env('RAZORPAY_KEY_ID', ''),
            'secret'  => $settings['secret'] ?: env('RAZORPAY_KEY_SECRET', ''),
        ];
    }
}
