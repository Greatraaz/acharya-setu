@extends('admin.layouts.app')
@section('title', 'Career Services')
@section('heading', 'Career Services')

@section('content')
@php
    $statusStyles = [
        'completed'       => ['bg' => '#ecfdf5', 'text' => '#047857', 'dot' => '#10b981'],
        'submitted'       => ['bg' => '#fffbeb', 'text' => '#b45309', 'dot' => '#f59e0b'],
        'pending_payment' => ['bg' => '#fef2f2', 'text' => '#b91c1c', 'dot' => '#ef4444'],
        'cancelled'       => ['bg' => '#f8fafc', 'text' => '#64748b', 'dot' => '#94a3b8'],
    ];
@endphp
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
.admin-cs-list{ font-family:"DM Sans",system-ui,sans-serif; }
.admin-cs-list .admin-cs-stat{ font-family:"Sora",system-ui,sans-serif; }
</style>

<div class="admin-page max-w-6xl space-y-5 admin-cs-list">
    <div class="learn-intro">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="learn-eyebrow">Careers · Services</span>
                <h2>Career Services</h2>
                <p>Review resume &amp; LinkedIn requests, then send deliverables to mentees.</p>
            </div>
        </div>
    </div>

    @foreach(['success','error'] as $flash)
        @if(session($flash))
        <div class="flex items-center gap-3 text-sm px-4 py-3 rounded-xl border
            {{ $flash === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800' }}">
            {{ session($flash) }}
        </div>
        @endif
    @endforeach

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        @foreach([
            ['Under review', $stats['under_review'] ?? 0, 'bg-amber-50 text-amber-800 border-amber-100'],
            ['Completed', $stats['completed'] ?? 0, 'bg-emerald-50 text-emerald-800 border-emerald-100'],
            ['Awaiting pay', $stats['awaiting_pay'] ?? 0, 'bg-rose-50 text-rose-800 border-rose-100'],
            ['Resume', $stats['resume'] ?? 0, 'bg-indigo-50 text-indigo-800 border-indigo-100'],
            ['LinkedIn', $stats['linkedin'] ?? 0, 'bg-violet-50 text-violet-800 border-violet-100'],
        ] as [$label, $value, $tone])
        <div class="rounded-xl border px-4 py-3 {{ $tone }}">
            <div class="admin-cs-stat text-[11px] font-semibold uppercase tracking-wide opacity-70">{{ $label }}</div>
            <div class="admin-cs-stat text-2xl font-extrabold mt-0.5 tabular-nums tracking-tight">{{ $value }}</div>
        </div>
        @endforeach
    </div>

    <div class="learn-panel overflow-hidden">
        <div class="admin-table-toolbar px-5 py-4 border-b border-gray-100">
            <form method="GET" class="admin-table-filters w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/></svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search mentee, email, ID…"
                           class="learn-focus w-full border border-gray-200 rounded-lg pl-9 pr-3.5 py-2.5 text-sm outline-none">
                </div>
                <select name="type" class="learn-focus w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white outline-none">
                    <option value="">All types</option>
                    <option value="resume" @selected($type === 'resume')>Resume</option>
                    <option value="linkedin" @selected($type === 'linkedin')>LinkedIn</option>
                </select>
                <select name="status" class="learn-focus w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white outline-none">
                    <option value="">All status</option>
                    <option value="submitted" @selected($status === 'submitted')>Under review</option>
                    <option value="completed" @selected($status === 'completed')>Completed</option>
                    <option value="pending_payment" @selected($status === 'pending_payment')>Awaiting payment</option>
                    <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
                </select>
                <button type="submit" class="learn-btn-primary">
                    Search
                </button>
                @if($search !== '' || $type !== '' || $status !== '')
                <a href="{{ route('admin.career-services.index') }}" class="text-sm text-gray-500 hover:text-gray-800 px-2 py-2">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-gray-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold">Request</th>
                        <th class="text-left px-5 py-3 font-semibold">Mentee</th>
                        <th class="text-left px-5 py-3 font-semibold">Type</th>
                        <th class="text-left px-5 py-3 font-semibold">Payment</th>
                        <th class="text-left px-5 py-3 font-semibold">Status</th>
                        <th class="text-left px-5 py-3 font-semibold">Submitted</th>
                        <th class="text-right px-5 py-3 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $item)
                    @php
                        $sc = $statusStyles[$item->status] ?? $statusStyles['cancelled'];
                        $isResume = $item->type === 'resume';
                    @endphp
                    <tr class="hover:bg-amber-50/30 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="font-mono text-xs text-gray-400">#{{ $item->id }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-sm font-bold shrink-0">
                                    {{ strtoupper(substr($item->user->name ?? 'M', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-gray-900 truncate">{{ $item->user->name ?? '—' }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ $item->user->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full
                                {{ $isResume ? 'bg-amber-50 text-amber-700' : 'bg-violet-50 text-violet-700' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isResume ? 'bg-amber-500' : 'bg-violet-500' }}"></span>
                                {{ $item->typeLabel() }}
                            </span>
                            @if($item->mentee_rating)
                            <div class="text-xs text-amber-600 font-semibold mt-1">★ {{ $item->mentee_rating }}/5</div>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            @if($item->is_paid_addon)
                                <div class="text-sm font-semibold text-gray-800">₹{{ number_format((float) $item->amount, 0) }}</div>
                                <div class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $item->payment_status) }}</div>
                            @else
                                <div class="text-sm font-semibold text-indigo-700">Free (plan)</div>
                                @if($item->plan_slug)
                                <div class="text-xs text-gray-500">{{ ucfirst($item->plan_slug) }}</div>
                                @endif
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full"
                                  style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $sc['dot'] }}"></span>
                                {{ $item->statusLabel() }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-500 whitespace-nowrap">
                            <div>{{ $item->created_at?->format('d M Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $item->created_at?->format('h:i A') }}</div>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('admin.career-services.show', $item) }}"
                               class="inline-flex items-center gap-1.5 text-sm font-medium bg-white border border-gray-200 hover:border-amber-300 hover:bg-amber-50 text-amber-700 px-3 py-1.5 rounded-lg transition-colors">
                                View
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="text-sm font-medium text-gray-700">No career service requests yet</div>
                            <div class="text-xs text-gray-400 mt-1">New resume and LinkedIn requests will show up here.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $items->links() }}</div>
        @endif
    </div>
</div>
@endsection
