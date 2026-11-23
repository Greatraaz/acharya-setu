@extends('admin.layouts.app')
@section('title', $mentor->name.' — Earnings')
@section('heading', ($mentor->isExpert() ? 'Expert' : 'Mentor').' Earnings')
@section('content')

@php
    $inr = fn ($n) => '₹'.number_format((float) $n, 2);
    $paidPct = $summary['earned'] > 0 ? min(100, round(($summary['paid_out'] / $summary['earned']) * 100)) : 0;
@endphp

<div class="admin-page min-w-0 max-w-full space-y-6">

    <div class="learn-intro">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="learn-eyebrow">Finance · Mentor Earnings</span>
                <h2>{{ $mentor->name }}</h2>
                <p>{{ $mentor->email }} · Session earnings and payout history.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.earnings.index') }}" class="learn-btn-ghost">← All earnings</a>
                <a href="{{ route('admin.withdrawals.index', ['search' => $mentor->email]) }}" class="learn-btn-ghost">Withdrawal queue →</a>
            </div>
        </div>
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

    @if($mentor->bank_details)
    <p class="text-sm text-gray-500">Payout to: <span class="font-medium text-gray-700">{{ $mentor->bank_details }}</span></p>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3">
        @foreach([
            ['Session earnings', $inr($summary['earned']), $summary['session_count'].' sessions · net after fee', 'text-slate-800'],
            ['Gross billed', $inr($summary['gross']), 'Mentees paid '.$inr($summary['mentee_paid']), 'text-gray-800'],
            ['Platform fee', $inr($summary['platform_fee']), '20% kept by Vedrix', 'text-amber-700'],
            ['Paid by admin', $inr($summary['paid_out']), $paidPct.'% of earnings transferred', 'text-emerald-700'],
            ['Wallet unpaid', $inr($summary['balance']), $summary['pending_out'] > 0 ? $inr($summary['pending_out']).' held in pending requests' : 'Available '.$inr($summary['available']), 'text-orange-600'],
        ] as [$label, $value, $hint, $tc])
        <div class="learn-stat">
            <div class="learn-stat__value {{ $tc }}">{{ $value }}</div>
            <div class="learn-stat__label">{{ $label }}</div>
            <p class="text-[11px] text-gray-400 mt-1">{{ $hint }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-1 learn-panel p-5 h-fit">
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
                           class="learn-focus w-full rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none">
                    <p class="text-[11px] text-gray-400 mt-1">Available {{ $inr($summary['available']) }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Paid to (UPI / bank)</label>
                    <input type="text" name="bank_details" value="{{ old('bank_details', $mentor->bank_details) }}" required
                           placeholder="UPI ID or account details"
                           class="learn-focus w-full rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">UTR / payment reference</label>
                    <input type="text" name="payment_ref" value="{{ old('payment_ref') }}"
                           placeholder="Optional bank UTR"
                           class="learn-focus w-full rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Note</label>
                    <input type="text" name="admin_note" value="{{ old('admin_note') }}"
                           placeholder="Optional"
                           class="learn-focus w-full rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none">
                </div>
                <button type="submit" class="learn-btn-primary w-full justify-center !py-3">
                    Mark as paid
                </button>
            </form>
            @endif
        </div>

        <div class="xl:col-span-2 space-y-6">
            <div class="learn-panel overflow-hidden">
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

            <div class="learn-panel overflow-hidden">
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
