@extends('admin.layouts.app')
@section('title', 'Mock Interviews')
@section('heading', 'Mock Interviews')

@section('content')
@php
    $statusStyles = [
        'completed' => ['bg' => '#ecfdf5', 'text' => '#047857', 'dot' => '#10b981'],
        'confirmed' => ['bg' => '#fffbeb', 'text' => '#b45309', 'dot' => '#f59e0b'],
        'submitted' => ['bg' => '#fff7ed', 'text' => '#c2410c', 'dot' => '#fb923c'],
        'cancelled' => ['bg' => '#f8fafc', 'text' => '#64748b', 'dot' => '#94a3b8'],
    ];
@endphp
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
.admin-mi-list{ font-family:"DM Sans",system-ui,sans-serif; }
.admin-mi-list .admin-mi-stat-label{ font-family:"Sora",system-ui,sans-serif; }
</style>

<div class="max-w-6xl space-y-5 admin-mi-list">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm text-gray-500">Confirm slots, host interviews, and send feedback to mentees.</p>
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

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        @foreach([
            ['Awaiting', $stats['awaiting'] ?? 0, 'bg-amber-50 text-amber-800 border-amber-100'],
            ['Confirmed', $stats['confirmed'] ?? 0, 'bg-orange-50 text-orange-800 border-orange-100'],
            ['Completed', $stats['completed'] ?? 0, 'bg-emerald-50 text-emerald-800 border-emerald-100'],
            ['Cancelled', $stats['cancelled'] ?? 0, 'bg-slate-50 text-slate-700 border-slate-100'],
            ['Paid add-ons', $stats['paid_addon'] ?? 0, 'bg-violet-50 text-violet-800 border-violet-100'],
        ] as [$label, $value, $tone])
        <div class="rounded-xl border px-4 py-3 {{ $tone }}">
            <div class="admin-mi-stat-label text-[11px] font-semibold uppercase tracking-wide opacity-70">{{ $label }}</div>
            <div class="admin-mi-stat-label text-2xl font-extrabold mt-0.5 tabular-nums tracking-tight">{{ $value }}</div>
        </div>
        @endforeach
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="admin-table-toolbar px-5 py-4 border-b border-gray-100">
            <form method="GET" class="admin-table-filters w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/></svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search mentee, email, ID, role…"
                           class="w-full border border-gray-200 rounded-lg pl-9 pr-3.5 py-2.5 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
                </div>
                <select name="status" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none">
                    <option value="">All status</option>
                    <option value="submitted" @selected($status === 'submitted')>Awaiting confirmation</option>
                    <option value="confirmed" @selected($status === 'confirmed')>Confirmed</option>
                    <option value="completed" @selected($status === 'completed')>Completed</option>
                    <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
                </select>
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                    Search
                </button>
                @if($search !== '' || $status !== '')
                <a href="{{ route('admin.mock-interviews.index') }}" class="text-sm text-gray-500 hover:text-gray-800 px-2 py-2">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-gray-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold">Request</th>
                        <th class="text-left px-5 py-3 font-semibold">Mentee</th>
                        <th class="text-left px-5 py-3 font-semibold">Preferred time</th>
                        <th class="text-left px-5 py-3 font-semibold">Duration</th>
                        <th class="text-left px-5 py-3 font-semibold">Payment</th>
                        <th class="text-left px-5 py-3 font-semibold">Status</th>
                        <th class="text-right px-5 py-3 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $item)
                    @php $sc = $statusStyles[$item->status] ?? $statusStyles['cancelled']; @endphp
                    <tr class="hover:bg-amber-50/30 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="font-mono text-xs text-gray-400">#{{ $item->id }}</div>
                            @if($item->target_role)
                            <div class="text-xs text-gray-500 mt-0.5 truncate max-w-[140px]">{{ $item->target_role }}</div>
                            @endif
                            @if($item->mentee_rating)
                            <div class="text-xs text-amber-600 font-semibold mt-0.5">★ {{ $item->mentee_rating }}/5</div>
                            @endif
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
                        <td class="px-5 py-3.5 text-gray-700 whitespace-nowrap">
                            <div>{{ $item->preferred_at?->timezone($item->timezone ?: 'Asia/Kolkata')->format('d M Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $item->preferred_at?->timezone($item->timezone ?: 'Asia/Kolkata')->format('h:i A') }}</div>
                        </td>
                        <td class="px-5 py-3.5">{{ $item->duration_minutes }} min</td>
                        <td class="px-5 py-3.5">
                            @if($item->is_paid_addon)
                                <div class="text-sm font-semibold text-gray-800">₹{{ number_format((float) $item->amount, 0) }}</div>
                                <div class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $item->payment_status) }}</div>
                            @else
                                <div class="text-sm font-semibold text-amber-700">Free (plan)</div>
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
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('admin.mock-interviews.show', $item) }}"
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                            </div>
                            <div class="text-sm font-medium text-gray-700">No mock interview requests yet</div>
                            <div class="text-xs text-gray-400 mt-1">New bookings will show up here for confirmation.</div>
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
