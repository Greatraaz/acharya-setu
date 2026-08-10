@extends('admin.layouts.app')
@section('title', 'Subscriptions')
@section('heading', 'Plan Subscriptions')
@section('content')

<div class="space-y-6">
    <div>
        <p class="text-sm text-gray-500">See which mentee subscribed to which plan and download invoices from here.</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">{{ session('error') }}</div>
    @endif

    <form method="GET" class="bg-white border border-gray-200 rounded-2xl p-4 grid grid-cols-1 md:grid-cols-5 gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search mentee / email / payment"
               class="md:col-span-2 border border-gray-200 rounded-xl px-3 py-2 text-sm">
        <select name="plan_id" class="border border-gray-200 rounded-xl px-3 py-2 text-sm">
            <option value="">All plans</option>
            @foreach($plans as $plan)
                <option value="{{ $plan->id }}" @selected((string) request('plan_id') === (string) $plan->id)>
                    {{ $plan->name ?? $plan->plan_name }}
                </option>
            @endforeach
        </select>
        <select name="status" class="border border-gray-200 rounded-xl px-3 py-2 text-sm">
            <option value="">All statuses</option>
            @foreach(['active','pending','expired','cancelled'] as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl px-4 py-2">Filter</button>
    </form>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Mentee</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Plan</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Amount</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Period</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Invoice</th>
                    <th class="px-4 py-3"></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($subscriptions as $sub)
                    <tr>
                        <td class="px-5 py-3">
                            <div class="text-sm font-semibold text-gray-800">{{ $sub->user->name ?? '—' }}</div>
                            <div class="text-xs text-gray-500">{{ $sub->user->email ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $sub->plan->name ?? $sub->plan->plan_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm">₹{{ number_format((float) $sub->amount_paid, 2) }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $sub->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($sub->status) }}
                            </span>
                            <div class="text-xs text-gray-400 mt-1">{{ ucfirst($sub->payment_status) }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                            @if($sub->starts_at && $sub->expires_at)
                                {{ $sub->starts_at->format('d M Y') }} → {{ $sub->expires_at->format('d M Y') }}
                            @else —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($sub->invoice)
                                <div class="text-xs text-gray-500 mb-1">{{ $sub->invoice->invoice_number }}</div>
                                <a href="{{ route('admin.invoices.download', $sub->invoice) }}"
                                   class="inline-flex items-center text-xs font-semibold text-white bg-green-600 hover:bg-green-700 px-2.5 py-1 rounded-lg">
                                    Download PDF
                                </a>
                            @elseif($sub->payment_status === 'paid')
                                <form method="POST" action="{{ route('admin.subscriptions.invoice', $sub) }}">
                                    @csrf
                                    <button class="text-amber-700 text-xs font-semibold hover:underline">Generate</button>
                                </form>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.subscriptions.show', $sub) }}" class="text-sm text-blue-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500">No subscriptions found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $subscriptions->links() }}</div>
        @endif
    </div>
</div>
@endsection
