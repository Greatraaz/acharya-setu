@extends('admin.layouts.app')
@section('title', 'Notifications')
@section('heading', 'Notifications')
@section('content')

<div class="max-w-3xl space-y-4">
    <p class="text-sm text-gray-500">Actionable alerts from mentor approvals, profile changes, and system logs.</p>

    @forelse($notifications as $item)
    <a href="{{ $item['url'] }}"
       class="block bg-white border border-gray-200 rounded-2xl p-4 hover:border-violet-200 hover:shadow-sm transition-all">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-lg flex-shrink-0">
                {{ $item['icon'] }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $item['title'] }}</h3>
                    <span class="text-xs text-gray-400 whitespace-nowrap">{{ $item['time'] }}</span>
                </div>
                <p class="text-sm text-gray-500 mt-0.5">{{ $item['body'] }}</p>
            </div>
        </div>
    </a>
    @empty
    <div class="bg-white border border-dashed border-gray-200 rounded-2xl p-12 text-center">
        <div class="text-4xl mb-3">🔔</div>
        <p class="text-gray-600 font-medium">You're all caught up</p>
        <p class="text-sm text-gray-400 mt-1">No pending notifications right now.</p>
    </div>
    @endforelse

    <div class="pt-2">
        <a href="{{ route('admin.logs.index') }}" class="text-sm font-medium text-violet-600 hover:text-violet-700">
            View full activity logs →
        </a>
    </div>
</div>

@endsection
