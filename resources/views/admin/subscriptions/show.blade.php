@extends('admin.layouts.app')
@section('title', 'Subscription #'.$subscription->id)
@section('heading', 'Subscription detail')
@section('content')

<div class="space-y-6 max-w-4xl">
    <a href="{{ route('admin.subscriptions.index') }}" class="text-sm text-blue-600 hover:underline">← Back to subscriptions</a>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">{{ session('error') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl p-6 grid md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Mentee</h3>
            <div class="text-base font-semibold">{{ $subscription->user->name ?? '—' }}</div>
            <div class="text-sm text-gray-500">{{ $subscription->user->email ?? '' }}</div>
            <div class="text-sm text-gray-500">{{ $subscription->user->phone ?? '' }}</div>
        </div>
        <div>
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Plan</h3>
            <div class="text-base font-semibold">{{ $subscription->plan->name ?? $subscription->plan->plan_name ?? '—' }}</div>
            <div class="text-sm text-gray-500">Code: {{ $subscription->subscription_id }}</div>
            <div class="text-sm text-gray-500">Amount: ₹{{ number_format((float) $subscription->amount_paid, 2) }}</div>
        </div>
        <div>
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Status</h3>
            <div class="text-sm">{{ ucfirst($subscription->status) }} / {{ ucfirst($subscription->payment_status) }}</div>
            <div class="text-sm text-gray-500 mt-1">
                @if($subscription->starts_at && $subscription->expires_at)
                    {{ $subscription->starts_at->format('d M Y H:i') }} → {{ $subscription->expires_at->format('d M Y H:i') }}
                @else —
                @endif
            </div>
        </div>
        <div>
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Payment</h3>
            <div class="text-sm text-gray-600 break-all">Ref: {{ $subscription->payment_reference ?: '—' }}</div>
            <div class="text-sm text-gray-600 break-all">Razorpay: {{ $subscription->razorpay_payment_id ?: '—' }}</div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">Invoice</h3>
                @if($subscription->invoice)
                <p class="text-xs text-gray-500 mt-1">{{ $subscription->invoice->invoice_number }} · ₹{{ number_format((float) $subscription->invoice->total_amount, 2) }}</p>
                @endif
            </div>
            @if($subscription->invoice)
                <div class="flex gap-3 items-center">
                    <a href="{{ route('admin.invoices.download', $subscription->invoice) }}"
                       class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-xl">
                        Download PDF
                    </a>
                    <a href="{{ route('admin.invoices.print', $subscription->invoice) }}" class="text-sm text-amber-700 hover:underline">Print</a>
                </div>
            @elseif($subscription->payment_status === 'paid')
                <form method="POST" action="{{ route('admin.subscriptions.invoice', $subscription) }}">
                    @csrf
                    <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl">Generate &amp; download ready</button>
                </form>
            @else
                <span class="text-sm text-gray-400">Available after payment</span>
            @endif
        </div>
    </div>
</div>
@endsection
