<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\{Request, JsonResponse};

class WalletController extends Controller
{
    
    public function balance(Request $request): JsonResponse
    {
        $u = $request->user();

        $hasTransactions = WalletTransaction::where('user_id', $u->id)->exists();

        if (!$hasTransactions) {
            return response()->json([
                'status'     => true,
                'statuscode' => 200,
                'message'    => 'No wallet transactions found for this user.',
            ], 200);
        }

        $credit = WalletTransaction::where('user_id', $u->id)
            ->where('type', 'credit')
            ->where('status', 'completed')
            ->sum('amount');
        $debit = WalletTransaction::where('user_id', $u->id)
            ->where('type', 'debit')
            ->where('status', 'completed')
            ->sum('amount');

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'balance'    => round($credit - $debit, 2),
            'credit'     => (float) $credit,
            'debit'      => (float) $debit,
        ], 200);
    }

    public function transactions(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $query = WalletTransaction::where('user_id', $userId)
            ->orderByDesc('created_at');

        if (!$query->exists()) {
            return response()->json([
                'status'     => false,
                'statuscode' => 200,
                'message'    => 'No wallet transactions found for this user.',
                'transactions' => [],
                'pagination'   => [],
            ], 200);
        }

        $transactions = $query->paginate(20);

        return response()->json([
            'status'        => true,
            'statuscode'    => 200,
            'transactions'  => $transactions->items(),
            'pagination'    => [
                'total'        => $transactions->total(),
                'per_page'     => $transactions->perPage(),
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'from'         => $transactions->firstItem(),
                'to'           => $transactions->lastItem(),
            ]
        ], 200);
    }

    public function topup(Request $request): JsonResponse
    {
        $d = $request->validate([
            'amount'       => 'required|numeric|min:1',
            'reference_id' => 'nullable|string',
            'description'  => 'nullable|string',
        ]);
        try {
            $t = WalletTransaction::create([
                'user_id'      => $request->user()->id,
                'amount'       => $d['amount'],
                'type'         => 'credit',
                'description'  => $d['description'] ?? 'Wallet top-up',
                'reference_id' => $d['reference_id'] ?? null,
                'status'       => 'completed',
            ]);
            return response()->json([
                'status'       => true,
                'statuscode'   => 201,
                'transaction'  => $t,
                'message'      => 'Wallet topped up'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'     => false,
                'statuscode' => 500,
                'message'    => 'Failed to top up wallet',
                'error'      => $e->getMessage(),
            ], 500);
        }
    }
}
