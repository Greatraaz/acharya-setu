@extends('admin.layouts.app')
@section('title', $mentor->name.' — Earnings')
@section('heading', 'Mentor Earnings')
@section('content')

@php
    $inr = fn ($n) => '₹'.number_format((float) $n, 2);
    $paidPct = $summary['earned'] > 0 ? min(100, round(($summary['paid_out'] / $summary['earned']) * 100)) : 0;
@endphp

<div class="min-w-0 max-w-full space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.earnings.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700">
            ← All mentor earnings
        </a>
        <a href="{{ route('admin.withdrawals.index', ['search' => $mentor->email]) }}"
           class="text-sm font-medium text-orange-600 hover:text-orange-700">Withdrawal queue →</a>
    </div>

    @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif
    @if($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
        <ul class="list-disc pl-4 space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-wrap items-center gap-4">
        @if($mentor->avatar_url)
        <img src="{{ $mentor->avatar_url }}" alt="" class="w-14 h-14 rounded-2xl object-cover">
        @else
        <div class="w-14 h-14 rounded-2xl bg-orange-100 text-orange-700 font-bold text-lg flex items-center justify-center">{{ strtoupper(substr($mentor->name, 0, 2)) }}</div>
        @endif
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-lg font-semibold text-gray-800">{{ $mentor->name }}</h2>
                <span class="inline-flex items-center text-xs font-medium bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">{{ ucfirst($mentor->mentor_status ?? 'mentor') }}</span>
            </div>
            <p class="text-sm text-gray-500 truncate">{{ $mentor->email }}</p>
            @if($mentor->bank_details)
            <p class="text-xs text-gray-400 mt-1">Payout to: {{ $mentor->bank_details }}</p>
            @endif
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Wallet unpaid</p>
            <p class="text-2xl font-bold text-orange-600">{{ $inr($summary['balance']) }}</p>
            @if($summary['pending_out'] > 0)
            <p class="text-xs text-amber-600">{{ $inr($summary['pending_out']) }} held in pending requests</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach([
            ['Session earnings', $summary['earned'], $summary['session_count'].' sessions · net after fee', 'text-slate-800'],
            ['Gross billed', $summary['gross'], 'Mentees paid '.$inr($summary['mentee_paid']), 'text-gray-800'],
            ['Platform fee', $summary['platform_fee'], '20% kept by Vedrix', 'text-indigo-700'],
            ['Paid by admin', $summary['paid_out'], $paidPct.'% of earnings transferred', 'text-emerald-700'],
        ] as [$label, $value, $hint, $tc])
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $label }}</p>
            <p class="text-xl font-bold {{ $tc }} mt-1">{{ $inr($value) }}</p>
            <p class="text-[11px] text-gray-400 mt-1">{{ $hint }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-1 bg-white rounded-2xl border border-gray-100 shadow-sm p-5 h-fit">
            <h3 class="text-sm font-semibold text-gray-800 mb-1">Record a payout</h3>
            <p class="text-xs text-gray-500 mb-4">Debits the mentor wallet and marks this amount as paid by admin. Use this after you transfer money to their bank or UPI.</p>

            @if($summary['available'] <= 0)
            <div class="rounded-xl bg-gray-50 border border-gray-100 px-3 py-3 text-sm text-gray-500">
                @if($summary['pending_out'] > 0)
                    Approve the pending withdrawal request first, or wait until more session earnings credit this wallet.
                @else
                    Nothing left to pay. New earnings will show here after completed sessions.
                @endif
            </div>
            @else
            <form method="POST" action="{{ route('admin.earnings.pay', $mentor) }}" class="space-y-3"
                  onsubmit="return confirm('Confirm payout of ₹' + Number(this.amount.value).toFixed(2) + ' to {{ addslashes($mentor->name) }}?');">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Amount</label>
                    <input type="number" name="amount" min="1" step="0.01" max="{{ $summary['available'] }}"
                           value="{{ old('amount', number_format($summary['available'], 2, '.', '')) }}" required
                           class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
                    <p class="text-[11px] text-gray-400 mt-1">Available {{ $inr($summary['available']) }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Paid to (UPI / bank)</label>
                    <input type="text" name="bank_details" value="{{ old('bank_details', $mentor->bank_details) }}" required
                           placeholder="UPI ID or account details"
                           class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">UTR / payment reference</label>
                    <input type="text" name="payment_ref" value="{{ old('payment_ref') }}"
                           placeholder="Optional bank UTR"
                           class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Note</label>
                    <input type="text" name="admin_note" value="{{ old('admin_note') }}"
                           placeholder="Optional"
                           class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2.5">
                    Mark as paid
                </button>
            </form>
            @endif
        </div>

        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">Payouts you made</h3>
                    <span class="text-xs text-gray-400">{{ $payouts->total() }} records</span>
                </div>
                <div class="overflow-y-scroll overflow-x-auto max-h-64">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 font-semibold bg-gray-50">When</th>
                                <th class="px-4 py-3 font-semibold bg-gray-50">Amount</th>
                                <th class="px-4 py-3 font-semibold bg-gray-50">To</th>
                                <th class="px-4 py-3 font-semibold bg-gray-50">Status</th>
                                <th class="px-4 py-3 font-semibold bg-gray-50">Note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($payouts as $wd)
                            @php
                                $badge = match($wd->status) {
                                    'pending'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'paid'     => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'rejected' => 'bg-red-50 text-red-700 border-red-200',
                                    default    => 'bg-gray-50 text-gray-600 border-gray-200',
                                };
                            @endphp
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                    {{ $wd->created_at?->format('d M Y, h:i A') }}
                                    @if($wd->processed_at)
                                    <div class="text-[11px] text-gray-400">Processed {{ $wd->processed_at->format('d M Y') }} · {{ $wd->processedBy?->name ?? '—' }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-semibold">{{ $inr($wd->amount) }}</td>
                                <td class="px-4 py-3"><code class="text-xs bg-gray-50 border border-gray-100 rounded-lg px-2 py-1">{{ $wd->bank_details }}</code></td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">{{ $wd->status_label }}</span>
                                    @if($wd->isPending())
                                    <div class="mt-2 space-y-1">
                                        <form method="POST" action="{{ route('admin.withdrawals.approve', $wd) }}"
                                              onsubmit="return confirm('Approve and debit {{ $inr($wd->amount) }}?');">
                                            @csrf
                                            <button type="submit" class="text-xs font-semibold text-emerald-700 hover:underline">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.withdrawals.reject', $wd) }}">
                                            @csrf
                                            <input type="hidden" name="admin_note" value="Rejected from earnings page">
                                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Reject</button>
                                        </form>
                                    </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $wd->admin_note ?: '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No payouts recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($payouts->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">{{ $payouts->links() }}</div>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">Session earnings credited</h3>
                    <span class="text-xs text-gray-400">{{ $earnings->total() }} credits</span>
                </div>
                <div class="overflow-y-scroll overflow-x-auto max-h-80">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 font-semibold bg-gray-50">Date</th>
                                <th class="px-4 py-3 font-semibold bg-gray-50">Session</th>
                                <th class="px-4 py-3 font-semibold bg-gray-50">Gross</th>
                                <th class="px-4 py-3 font-semibold bg-gray-50">Fee</th>
                                <th class="px-4 py-3 font-semibold bg-gray-50">Mentor net</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($earnings as $txn)
                            @php
                                $meta = is_array($txn->meta) ? $txn->meta : [];
                                $session = $txn->transactionable;
                            @endphp
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $txn->created_at?->format('d M Y, h:i A') }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $meta['session_title'] ?? $txn->description }}</div>
                                    <div class="text-xs text-gray-400">
                                        {{ $meta['mentee_name'] ?? $session?->mentee?->name ?? '—' }}
                                        @if(!empty($meta['booking_ref'])) · {{ $meta['booking_ref'] }} @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ $inr($meta['gross_amount'] ?? $meta['list_amount'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-indigo-700">{{ $inr($meta['platform_fee'] ?? 0) }}</td>
                                <td class="px-4 py-3 font-semibold text-emerald-700">{{ $inr($txn->amount) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No session credits yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($earnings->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">{{ $earnings->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
