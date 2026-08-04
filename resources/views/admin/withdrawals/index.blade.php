@extends('admin.layouts.app')
@section('content')

<div class="min-w-0 max-w-full space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h6 class="text-lg font-semibold text-gray-800">Withdrawal Requests</h6>
            <p class="text-sm text-gray-500 mt-0.5">Review mentor payout requests and mark them paid or rejected</p>
        </div>
        <nav class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-1 text-gray-500 hover:text-blue-600 transition-colors">
                <iconify-icon icon="solar:home-smile-angle-outline" class="text-base"></iconify-icon>
                Dashboard
            </a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-700 font-medium">Withdrawals</span>
        </nav>
    </div>

    @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif
    @if($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
        <ul class="list-disc pl-4 space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Pending</p>
            <p class="text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Pending Amount</p>
            <p class="text-2xl font-bold text-orange-600">₹{{ number_format($stats['pending_amount'], 0) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Paid</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $stats['paid'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Rejected</p>
            <p class="text-2xl font-bold text-red-500">{{ $stats['rejected'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Mentor name, email, UPI..."
                       class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-200">
            </div>
            <div class="w-44">
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-200">
                    <option value="">All</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2">Filter</button>
            <a href="{{ route('admin.withdrawals.index') }}" class="rounded-xl border border-gray-200 text-gray-600 text-sm font-medium px-4 py-2 hover:bg-gray-50">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Mentor</th>
                        <th class="px-4 py-3 font-semibold">Amount</th>
                        <th class="px-4 py-3 font-semibold">Payout Details</th>
                        <th class="px-4 py-3 font-semibold">Requested</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold">Note / Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($withdrawals as $wd)
                    <tr class="align-top">
                        <td class="px-4 py-4">
                            <div class="font-semibold text-gray-800">{{ $wd->user?->name ?? '—' }}</div>
                            <div class="text-xs text-gray-500">{{ $wd->user?->email }}</div>
                            <div class="text-xs text-gray-400 mt-1">Balance: ₹{{ number_format((float) ($wd->user?->wallet_balance ?? 0), 0) }}</div>
                        </td>
                        <td class="px-4 py-4 font-bold text-gray-900">₹{{ number_format((float) $wd->amount, 0) }}</td>
                        <td class="px-4 py-4">
                            <code class="text-xs bg-gray-50 border border-gray-100 rounded-lg px-2 py-1">{{ $wd->bank_details }}</code>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-gray-600">
                            {{ $wd->created_at?->format('d M Y, h:i A') }}
                            @if($wd->processed_at)
                            <div class="text-xs text-gray-400 mt-1">Processed: {{ $wd->processed_at->format('d M Y') }}</div>
                            <div class="text-xs text-gray-400">by {{ $wd->processedBy?->name ?? '—' }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @php
                                $badge = match($wd->status) {
                                    'pending'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'paid'     => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'rejected' => 'bg-red-50 text-red-700 border-red-200',
                                    default    => 'bg-gray-50 text-gray-600 border-gray-200',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">
                                {{ $wd->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-4 min-w-[260px]">
                            @if($wd->admin_note)
                            <p class="text-xs text-gray-500 mb-2">{{ $wd->admin_note }}</p>
                            @endif

                            @if($wd->isPending())
                            <div class="space-y-2">
                                <form method="POST" action="{{ route('admin.withdrawals.approve', $wd) }}" class="space-y-2"
                                      onsubmit="return confirm('Approve and debit ₹{{ number_format((float) $wd->amount, 0) }} from mentor wallet?');">
                                    @csrf
                                    <input type="text" name="admin_note" placeholder="Optional note"
                                           class="w-full rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs">
                                    <button type="submit" class="w-full rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold py-2">
                                        Approve &amp; Debit
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.withdrawals.reject', $wd) }}" class="space-y-2">
                                    @csrf
                                    <input type="text" name="admin_note" placeholder="Rejection reason (required)" required
                                           class="w-full rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs">
                                    <button type="submit" class="w-full rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-xs font-semibold py-2">
                                        Reject
                                    </button>
                                </form>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-16 text-center text-gray-500">
                            <div class="font-semibold text-gray-700 mb-1">No withdrawal requests</div>
                            <div class="text-sm">Mentor payout requests will appear here.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($withdrawals->hasPages())
        <div class="px-4 py-4 border-t border-gray-100">{{ $withdrawals->links() }}</div>
        @endif
    </div>
</div>
@endsection
