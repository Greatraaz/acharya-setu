@extends('admin.layouts.app')
@section('title', 'Plan cancellations')
@section('heading', 'Yearly plan cancellations')
@section('content')

<div class="space-y-6">
    <p class="text-sm text-gray-500">Approve a yearly cancellation to refund unused months (current month is kept) and stop the plan. Pending requests auto-approve after 72 hours.</p>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Pending</p>
            <p class="text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Approved</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Rejected</p>
            <p class="text-2xl font-bold text-red-500">{{ $stats['rejected'] }}</p>
        </div>
    </div>

    <form method="GET" class="admin-filter-form bg-white border border-gray-200 rounded-2xl p-4">
        <div class="admin-filter-search">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search mentee / subscription"
                   class="w-full border border-gray-200 rounded-xl px-3 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
        </div>
        <div>
            <select name="status" class="w-full border border-gray-200 rounded-xl px-3 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                <option value="pending" @selected(request('status', 'pending') === 'pending')>Pending</option>
                <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                <option value="all" @selected(request('status') === 'all')>All</option>
            </select>
        </div>
        <div class="admin-filter-actions">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl px-4">Filter</button>
        </div>
    </form>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-left text-xs uppercase tracking-wider text-gray-500">
                    <th class="px-5 py-3">Mentee</th>
                    <th class="px-4 py-3">Plan</th>
                    <th class="px-4 py-3">Refund</th>
                    <th class="px-4 py-3">Months</th>
                    <th class="px-4 py-3">Auto-approve</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($requests as $item)
                    <tr>
                        <td class="px-5 py-3">
                            <div class="font-semibold text-gray-800">{{ $item->user->name ?? '—' }}</div>
                            <div class="text-xs text-gray-500">{{ $item->user->email ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div>{{ $item->subscription->plan->name ?? 'Yearly plan' }}</div>
                            <div class="text-xs text-gray-400">{{ $item->subscription->subscription_id ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-semibold">₹{{ number_format((float) $item->refund_amount, 2) }}</div>
                            <div class="text-xs text-gray-400">via {{ $item->refund_destination === 'razorpay' ? 'original payment' : 'wallet' }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            Used {{ $item->consumed_months }} · Refund {{ $item->refund_months }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                            {{ $item->auto_approve_at?->format('d M Y, g:i A') ?? '—' }}
                            @if($item->isPending())
                                <div class="text-amber-600">{{ $item->hoursUntilAutoApprove() }}h left</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $item->status === 'pending' ? 'bg-amber-50 text-amber-700' : ($item->status === 'approved' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($item->isPending())
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('admin.subscription-cancels.approve', $item) }}">
                                    @csrf
                                    <button class="bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.subscription-cancels.reject', $item) }}" onsubmit="return confirm('Decline this cancellation? The plan will stay active.');">
                                    @csrf
                                    <button class="bg-white border border-gray-200 text-gray-700 text-xs font-medium px-3 py-1.5 rounded-lg">Decline</button>
                                </form>
                            </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-gray-400">No cancellation requests.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $requests->links() }}</div>
        @endif
    </div>
</div>
@endsection
