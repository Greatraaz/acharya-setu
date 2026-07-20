@extends('admin.layouts.app')
@section('title','Consultation Sessions')
@section('heading','Consultation Sessions')
@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Manage mentor–mentee consultation sessions and track outcomes.</p>
        <div class="flex gap-2">
            <a href="{{ route('admin.sessions.export', request()->all()) }}"
               class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 border border-gray-200 px-4 py-2.5 rounded-xl hover:bg-gray-50 transition-colors bg-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
            <a href="{{ route('admin.sessions.create') }}"
               class="inline-flex items-center gap-2 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Book Session
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-6 gap-4">
        @foreach([
            ['Total',      $stats['total'],     '#e0e7ff', '#4338ca', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['Upcoming',   $stats['upcoming'],  '#dbeafe', '#1d4ed8', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Completed',  $stats['completed'], '#dcfce7', '#15803d', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Cancelled',  $stats['cancelled'], '#fee2e2', '#b91c1c', 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Revenue',    '₹'.number_format($stats['revenue'],0), '#fef9c3', '#92400e', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Avg Rating', $stats['avg_rating'] . ' ★', '#fce7f3', '#9d174d', 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
        ] as [$label, $value, $bg, $color, $icon])
        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:{{ $bg }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="{{ $color }}" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
            </div>
            <div>
                <div class="text-lg font-bold text-gray-900">{{ $value }}</div>
                <div class="text-xs text-gray-400 font-medium">{{ $label }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Booking ref, title, name…"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    <option value="">All Statuses</option>
                    @foreach(\App\Models\ConsultationSession::STATUSES as $val => $label)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">Filter</button>
            <a href="{{ route('admin.sessions.index') }}" class="text-sm text-gray-500 border border-gray-200 px-3 py-2 rounded-xl hover:bg-gray-50 transition-colors">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">All Sessions</h3>
            <span class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full">{{ $sessions->total() }} results</span>
        </div>

        @if($sessions->isEmpty())
        <div class="py-20 text-center">
            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-gray-600 font-medium mb-1">No sessions found</p>
            <p class="text-gray-400 text-sm mb-5">Book the first consultation session to get started.</p>
            <a href="{{ route('admin.sessions.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 text-white text-sm font-medium px-5 py-2.5 rounded-xl hover:bg-indigo-700 transition-colors">Book a Session</a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Session</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Mentor → Mentee</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Scheduled</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Duration</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Review</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($sessions as $session)
                    @php $sc = $session->status_color; @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.sessions.show', $session) }}" class="text-sm font-semibold text-gray-900 hover:text-indigo-600 transition-colors">{{ $session->title }}</a>
                            <div class="text-xs font-mono text-gray-400 mt-0.5">{{ $session->booking_ref }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-semibold text-indigo-700 flex-shrink-0">
                                    {{ strtoupper(substr($session->mentor->name ?? 'M', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-800">{{ $session->mentor->name ?? '—' }}</div>
                                    <div class="text-xs text-gray-400">Mentor</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center text-xs font-semibold text-emerald-700 flex-shrink-0">
                                    {{ strtoupper(substr($session->mentee->name ?? 'M', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-800">{{ $session->mentee->name ?? '—' }}</div>
                                    <div class="text-xs text-gray-400">Mentee</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            @php
                                $st = $session->scheduled_at;
                                $se = $session->scheduled_end;
                            @endphp
                            <div class="text-sm font-medium text-gray-800">{{ $st?->format('d M Y') ?? '—' }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $st?->format('H:i') ?? '—' }} – {{ $se?->format('H:i') ?? '—' }}</div>
                            @if($session->status !== 'cancelled' && ($relative = $session->scheduledRelativeToNow()))
                            <div class="text-xs text-indigo-500 font-medium mt-0.5">{{ $relative }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-sm font-semibold text-gray-700">{{ $session->duration_minutes }}m</span>
                        </td>
                        <td class="px-4 py-4">
                            @if($session->amount > 0)
                            <div class="text-sm font-semibold text-gray-800">₹{{ number_format($session->amount, 0) }}</div>
                            <div class="text-xs mt-0.5 {{ $session->payment_status === 'paid' ? 'text-green-600' : 'text-amber-500' }}">
                                {{ ucfirst($session->payment_status) }}
                            </div>
                            @else
                            <span class="text-xs text-gray-400">Free</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full"
                                  style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $sc['dot'] }}"></span>
                                {{ ucfirst(str_replace('_', ' ', $session->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            @if($session->menteeReview)
                            <div class="flex items-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="{{ $i <= $session->menteeReview->overall_rating ? '#f59e0b' : '#e5e7eb' }}" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
                                @endfor
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">by Mentee</div>
                            @elseif($session->status === 'completed')
                            <span class="text-xs text-amber-500 font-medium">Pending review</span>
                            @else
                            <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.sessions.show', $session) }}"
                                   class="text-xs font-medium px-2.5 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">View</a>
                                @if($session->status === 'pending')
                                <form method="POST" action="{{ route('admin.sessions.confirm', $session) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium px-2.5 py-1.5 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 transition-colors">Confirm</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100">
            <div class="text-xs text-gray-500">Showing {{ $sessions->firstItem() }}–{{ $sessions->lastItem() }} of {{ $sessions->total() }}</div>
            {{ $sessions->links() }}
        </div>
        @endif
    </div>
</div>

@endsection