@extends('admin.layouts.app')
@section('title', 'Transactions')
@section('heading', 'Transactions')
@section('content')

@php
    $tab = $tab ?? 'wallet';
    $qs = request()->except('page');
@endphp

<div class="min-w-0 max-w-full space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">All Transactions</h1>
            <p class="text-sm text-gray-500 mt-0.5">Wallet ledger, session payments, and plan invoices across the platform.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.transactions.export', array_merge($qs, ['tab' => $tab])) }}"
               class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 border border-gray-200 bg-white px-3.5 py-2 rounded-xl hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
            <a href="{{ route('admin.wallet.index') }}"
               class="inline-flex items-center gap-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-xl">
                Transfer / Adjust
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-1.5 flex flex-wrap gap-1">
        @foreach([
            'wallet'   => 'Wallet Ledger',
            'sessions' => 'Session Payments',
            'plans'    => 'Plan Invoices',
        ] as $key => $label)
            <a href="{{ route('admin.transactions.index', ['tab' => $key]) }}"
               class="flex-1 min-w-[140px] text-center text-sm font-medium px-3 py-2.5 rounded-xl transition-colors
               {{ $tab === $key ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if($tab === 'wallet')
        @include('admin.transactions.partials.wallet-tab')
    @elseif($tab === 'sessions')
        @include('admin.transactions.partials.sessions-tab')
    @else
        @include('admin.transactions.partials.plans-tab')
    @endif

</div>

@endsection
