@extends('admin.layouts.app')
@section('title', $user->name . ' — Wallet')
@section('heading', 'Wallet')
@section('content')

<div class="min-w-0 max-w-full space-y-6">

    {{-- Back + breadcrumb --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.wallet.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Wallet Transactions
        </a>
        <nav class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-blue-600 transition-colors">Dashboard</a>
            <span class="text-gray-300">/</span>
            <a href="{{ route('admin.wallet.index') }}" class="text-gray-500 hover:text-blue-600 transition-colors">Wallet</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-700 font-medium">{{ $user->name }}</span>
        </nav>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16">
            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- User profile strip --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-wrap items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-700 font-bold text-lg flex items-center justify-center flex-shrink-0">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-lg font-semibold text-gray-800">{{ $user->name }}</h2>
                @if($user->role === 'admin')
                    <span class="inline-flex items-center text-xs font-medium bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full">Admin</span>
                @elseif($user->role === 'mentor')
                    <span class="inline-flex items-center text-xs font-medium bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Mentor</span>
                @elseif($user->role === 'mentee')
                    <span class="inline-flex items-center text-xs font-medium bg-cyan-100 text-cyan-700 px-2 py-0.5 rounded-full">Mentee</span>
                @endif
            </div>
            <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Current Balance</p>
                <p class="text-2xl font-bold text-blue-600">₹{{ number_format($summary['balance'], 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Credited</p>
                <p class="text-2xl font-bold text-emerald-600">₹{{ number_format($summary['total_credited'], 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Debited</p>
                <p class="text-2xl font-bold text-red-500">₹{{ number_format($summary['total_debited'], 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Refunded</p>
                <p class="text-2xl font-bold text-sky-500">₹{{ number_format($summary['total_refunded'], 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-sky-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Adjust Wallet --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden min-w-0">
        <div class="px-5 py-4 border-b border-gray-100">
            <h6 class="font-semibold text-gray-800">Adjust Wallet</h6>
            <p class="text-xs text-gray-400 mt-0.5">Manually credit or debit this user's wallet balance</p>
        </div>
        <div class="p-5">
            <form method="POST"
                  action="{{ route('admin.wallet.adjust', [$user->role === 'admin' ? 'admin' : 'customer', $user->id]) }}"
                  class="flex flex-wrap items-end gap-3 min-w-0">
                @csrf

                <div class="flex flex-col gap-1 min-w-[120px]">
                    <label class="text-xs font-medium text-gray-600">Action</label>
                    <select name="action"
                        class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="credit">Credit</option>
                        <option value="debit">Debit</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1 min-w-[140px]">
                    <label class="text-xs font-medium text-gray-600">Amount (₹)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">₹</span>
                        <input type="number" name="amount" step="0.01" min="0.01" required
                            class="w-full text-sm border border-gray-200 rounded-lg pl-7 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex flex-col gap-1 flex-1 min-w-[200px]">
                    <label class="text-xs font-medium text-gray-600">Reason</label>
                    <input type="text" name="description" required placeholder="Reason for adjustment"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <button type="submit"
                    class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Apply Adjustment
                </button>
            </form>
        </div>
    </div>

    {{-- Transaction History --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden min-w-0">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h6 class="font-semibold text-gray-800">Transaction History</h6>
            <span class="text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full">
                {{ $transactions->total() }} records
            </span>
        </div>

        <div class="overflow-x-auto max-w-full">
            <table class="w-full min-w-[860px] text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3 text-left">Date & Time</th>
                        <th class="px-4 py-3 text-left">Reference</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Description</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-right">Bal. Before</th>
                        <th class="px-4 py-3 text-right">Bal. After</th>
                        <th class="px-4 py-3 text-left">Performed By</th>
                        <th class="px-4 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($transactions as $txn)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3 text-nowrap">
                            <p class="text-gray-700 font-medium">{{ $txn->created_at->format('d M Y') }}</p>
                            <p class="text-gray-400 text-xs">{{ $txn->created_at->format('h:i A') }}</p>
                        </td>
                        <td class="px-4 py-3 max-w-[140px]">
                            <span class="font-mono text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded inline-block max-w-full truncate"
                                title="{{ $txn->reference ?? '—' }}">
                                {{ $txn->reference ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $typeStyles = [
                                    'credit'       => 'bg-emerald-100 text-emerald-700',
                                    'debit'        => 'bg-red-100 text-red-600',
                                    'refund'       => 'bg-sky-100 text-sky-600',
                                    'transfer_in'  => 'bg-blue-100 text-blue-700',
                                    'transfer_out' => 'bg-amber-100 text-amber-700',
                                ];
                            @endphp
                            <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full {{ $typeStyles[$txn->type] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $txn->type_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 max-w-[160px]">
                            <p class="truncate text-xs" title="{{ $txn->description }}">
                                {{ $txn->description ?? '—' }}
                            </p>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold {{ $txn->is_debit ? 'text-red-500' : 'text-emerald-600' }}">
                            {{ $txn->is_debit ? '−' : '+' }}₹{{ number_format($txn->amount, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-400 text-xs">
                            ₹{{ number_format($txn->balance_before, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-700">
                            ₹{{ number_format($txn->balance_after, 2) }}
                        </td>
                        <td class="px-4 py-3">
                            @if($txn->performedByAdmin)
                                <span class="text-xs text-gray-700 font-medium">{{ $txn->performedByAdmin->name }}</span>
                            @else
                                <span class="text-xs text-gray-400 italic">System</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $statusStyles = [
                                    'completed' => 'bg-emerald-100 text-emerald-700',
                                    'pending'   => 'bg-amber-100 text-amber-700',
                                    'failed'    => 'bg-red-100 text-red-600',
                                ];
                            @endphp
                            <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full {{ $statusStyles[$txn->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($txn->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-16 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="font-medium text-gray-500">No transactions yet</p>
                            <p class="text-xs mt-1">Wallet activity will appear here</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <p class="text-xs text-gray-400">
                Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of {{ $transactions->total() }} transactions
            </p>
            <div class="text-sm">
                {{ $transactions->links() }}
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
