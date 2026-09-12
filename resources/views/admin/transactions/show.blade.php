@extends('admin.layouts.app')
@section('title', 'Transaction #'.$transaction->id)
@section('heading', 'Transaction Detail')
@section('content')

<div class="max-w-4xl space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('admin.transactions.index', ['tab' => 'wallet']) }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Back to transactions</a>
            <h1 class="text-xl font-semibold text-gray-900 mt-1">Transaction #{{ $transaction->id }}</h1>
        </div>
        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $transaction->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
            {{ ucfirst($transaction->status) }}
        </span>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6">
        <div class="flex flex-wrap items-end justify-between gap-4 pb-5 border-b border-gray-100">
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold">Amount</p>
                <p class="text-3xl font-bold {{ $transaction->is_debit ? 'text-red-500' : 'text-emerald-600' }}">
                    {{ $transaction->is_debit ? '−' : '+' }}₹{{ number_format($transaction->amount, 2) }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-400">{{ $transaction->created_at?->format('d M Y, h:i A') }}</p>
                <p class="text-sm font-medium text-gray-700 mt-1">{{ $transaction->type_label }} · {{ $transaction->category_label }}</p>
            </div>
        </div>

        <dl class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-xs text-gray-400 uppercase tracking-wider">User</dt>
                <dd class="mt-1 font-medium text-gray-900">
                    @if($transaction->user)
                        <a href="{{ route('admin.wallet.customer.show', $transaction->user->id) }}" class="hover:text-indigo-600">
                            {{ $transaction->user->name }}
                        </a>
                        <p class="text-xs text-gray-500 font-normal">{{ $transaction->user->email }} · {{ ucfirst($transaction->user->role) }}</p>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400 uppercase tracking-wider">Reference</dt>
                <dd class="mt-1 font-mono text-gray-800">{{ $transaction->reference ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400 uppercase tracking-wider">Balance before</dt>
                <dd class="mt-1 text-gray-800">₹{{ number_format($transaction->balance_before, 2) }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400 uppercase tracking-wider">Balance after</dt>
                <dd class="mt-1 text-gray-800">₹{{ number_format($transaction->balance_after, 2) }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs text-gray-400 uppercase tracking-wider">Description</dt>
                <dd class="mt-1 text-gray-800">{{ $transaction->description ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400 uppercase tracking-wider">Performed by</dt>
                <dd class="mt-1 text-gray-800">{{ $transaction->performedByAdmin?->name ?? 'System' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400 uppercase tracking-wider">Linked entity</dt>
                <dd class="mt-1 text-gray-800 text-xs">
                    @if($transaction->transactionable_type)
                        {{ class_basename($transaction->transactionable_type) }} #{{ $transaction->transactionable_id }}
                    @else
                        —
                    @endif
                </dd>
            </div>
            @if($transaction->transferPair)
            <div class="sm:col-span-2">
                <dt class="text-xs text-gray-400 uppercase tracking-wider">Transfer pair</dt>
                <dd class="mt-1">
                    <a href="{{ route('admin.transactions.show', $transaction->transferPair) }}" class="text-indigo-600 hover:underline">
                        #{{ $transaction->transferPair->id }} — {{ $transaction->transferPair->user?->name }} ({{ $transaction->transferPair->type_label }})
                    </a>
                </dd>
            </div>
            @endif
            @if(!empty($transaction->meta))
            <div class="sm:col-span-2">
                <dt class="text-xs text-gray-400 uppercase tracking-wider">Meta</dt>
                <dd class="mt-1">
                    <pre class="text-xs bg-gray-50 border border-gray-100 rounded-xl p-3 overflow-x-auto">{{ json_encode($transaction->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </dd>
            </div>
            @endif
        </dl>
    </div>
</div>

@endsection
