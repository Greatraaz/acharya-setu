@extends('admin.layouts.app')
@section('title', 'Mentor Earnings')
@section('heading', 'Mentor Earnings')
@section('content')

@php
    $inr = fn ($n) => '₹'.number_format((float) $n, 2);
@endphp

<div class="min-w-0 max-w-full space-y-6">

    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-sm text-gray-500">What each mentor earned from sessions, what you have already paid out, and what is still in their wallet.</p>
        </div>
        <a href="{{ route('admin.withdrawals.index') }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-orange-700 bg-orange-50 border border-orange-200 hover:bg-orange-100 px-3 py-2 rounded-xl">
            Withdrawal requests
            @if(($stats['pending_out'] ?? 0) > 0)
            <span class="bg-orange-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $inr($stats['pending_out']) }}</span>
            @endif
        </a>
    </div>

    <div class="grid grid-cols-2 xl:grid-cols-5 gap-3">
        @foreach([
            ['Mentors earned (net)', $stats['earned'], 'After 20% fee · '.$stats['session_count'].' sessions', 'text-slate-800', 'bg-slate-50', 'border-slate-200'],
            ['Platform fee kept', $stats['platform_fee'], '20% of session list price', 'text-indigo-700', 'bg-indigo-50', 'border-indigo-200'],
            ['Paid to mentors', $stats['paid_out'], 'Approved bank / UPI payouts', 'text-emerald-700', 'bg-emerald-50', 'border-emerald-200'],
            ['Still to pay', $stats['unpaid'], 'Sitting in mentor wallets', 'text-orange-700', 'bg-orange-50', 'border-orange-200'],
            ['Pending requests', $stats['pending_out'], 'Awaiting your approval', 'text-amber-700', 'bg-amber-50', 'border-amber-200'],
        ] as [$label, $value, $hint, $tc, $bg, $bc])
        <div class="{{ $bg }} border {{ $bc }} rounded-2xl p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $label }}</p>
            <p class="text-xl font-bold {{ $tc }} mt-1">{{ $inr($value) }}</p>
            <p class="text-[11px] text-gray-400 mt-1">{{ $hint }}</p>
        </div>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">
        <form method="GET" class="admin-filter-form flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search mentor</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Name or email"
                       class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-200">
            </div>
            <div class="w-44">
                <label class="block text-xs font-medium text-gray-500 mb-1">Show</label>
                <select name="filter" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
                    <option value="activity" @selected($filter === 'activity')>With activity</option>
                    <option value="unpaid" @selected($filter === 'unpaid')>Still to pay</option>
                    <option value="pending" @selected($filter === 'pending')>Pending requests</option>
                    <option value="paid" @selected($filter === 'paid')>Already paid</option>
                    <option value="all" @selected($filter === 'all')>All mentors</option>
                </select>
            </div>
            <div class="w-44">
                <label class="block text-xs font-medium text-gray-500 mb-1">Sort</label>
                <select name="sort" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
                    <option value="unpaid" @selected($sort === 'unpaid')>Unpaid first</option>
                    <option value="earned" @selected($sort === 'earned')>Highest earned</option>
                    <option value="paid" @selected($sort === 'paid')>Highest paid</option>
                    <option value="name" @selected($sort === 'name')>Name</option>
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2">Apply</button>
            <a href="{{ route('admin.earnings.index') }}" class="rounded-xl border border-gray-200 text-gray-600 text-sm font-medium px-4 py-2 hover:bg-gray-50">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Mentor</th>
                        <th class="px-4 py-3 font-semibold">Earned (net)</th>
                        <th class="px-4 py-3 font-semibold">Fee kept</th>
                        <th class="px-4 py-3 font-semibold">Paid out</th>
                        <th class="px-4 py-3 font-semibold">Pending</th>
                        <th class="px-4 py-3 font-semibold">Still to pay</th>
                        <th class="px-4 py-3 font-semibold"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($mentors as $row)
                    @php
                        $earned = (float) $row->earned;
                        $paid = (float) $row->paid_out;
                        $pct = $earned > 0 ? min(100, round(($paid / $earned) * 100)) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3 min-w-[180px]">
                                @if($row->avatar_url)
                                <img src="{{ $row->avatar_url }}" alt="" class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                                @else
                                <div class="w-9 h-9 rounded-full bg-orange-100 text-orange-700 text-xs font-bold flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($row->name, 0, 2)) }}
                                </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="font-semibold text-gray-800 truncate">{{ $row->name }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ $row->email }}</div>
                                    <div class="text-[11px] text-gray-400">{{ (int) $row->session_count }} paid sessions</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-bold text-gray-900">{{ $inr($earned) }}</div>
                            @if((float) $row->gross > 0)
                            <div class="text-[11px] text-gray-400">Gross {{ $inr($row->gross) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-indigo-700 font-medium">{{ $inr($row->platform_fee) }}</td>
                        <td class="px-4 py-4">
                            <div class="font-semibold text-emerald-700">{{ $inr($paid) }}</div>
                            <div class="mt-1 h-1.5 w-24 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="text-[11px] text-gray-400 mt-0.5">{{ $pct }}% paid</div>
                        </td>
                        <td class="px-4 py-4">
                            @if((float) $row->pending_out > 0)
                            <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 text-amber-700 px-2 py-0.5 text-xs font-semibold">{{ $inr($row->pending_out) }}</span>
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-bold {{ (float) $row->wallet_balance > 0 ? 'text-orange-600' : 'text-gray-400' }}">{{ $inr($row->wallet_balance) }}</div>
                            <div class="text-[11px] text-gray-400">Wallet balance</div>
                        </td>
                        <td class="px-4 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('admin.earnings.show', $row) }}"
                               class="inline-flex items-center text-sm font-semibold text-orange-600 hover:text-orange-700">
                                {{ (float) $row->wallet_balance > 0 ? 'Pay / details' : 'Details' }} →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-16 text-center text-gray-500">
                            <div class="font-semibold text-gray-700 mb-1">No mentor earnings yet</div>
                            <div class="text-sm">Totals appear here after completed paid sessions credit a mentor wallet.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($mentors->hasPages())
        <div class="px-4 py-4 border-t border-gray-100">{{ $mentors->links() }}</div>
        @endif
    </div>
</div>
@endsection
