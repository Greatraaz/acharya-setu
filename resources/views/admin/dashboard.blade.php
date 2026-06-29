@extends('admin.layouts.app')
@section('title','Dashboard')
@section('heading','Admin Dashboard')
@section('content')

<style>
    @keyframes fadeUp{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:none;}}
    .fu{animation:fadeUp .4s ease both;}
    .fu:nth-child(2){animation-delay:.07s;}.fu:nth-child(3){animation-delay:.14s;}.fu:nth-child(4){animation-delay:.21s;}
    .bar{transition:height 1s cubic-bezier(.4,0,.2,1);}
    .ring-anim{transition:stroke-dashoffset 1.2s cubic-bezier(.4,0,.2,1);}
    .sparkline path{stroke-dasharray:1000;stroke-dashoffset:1000;animation:draw 1.5s ease forwards .4s;}
    @keyframes draw{to{stroke-dashoffset:0;}}
</style>

<div class="space-y-6">

    {{-- ═══ KPI STAT CARDS ═══ --}}
    <div class="grid grid-cols-4 gap-4">

        {{-- Mentees --}}
        <div class="fu bg-white border border-gray-200 rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-full -translate-y-8 translate-x-8"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/><path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z"/><path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">↑ 18%</span>
                </div>
                <div class="text-3xl font-bold text-gray-900">{{ number_format($stats['totalMentees']) }}</div>
                <div class="text-sm text-gray-500 mt-1">Total Mentees</div>
                <div class="mt-3 h-1 bg-gray-100 rounded-full overflow-hidden">
                    @php $activePct = $stats['totalMentees'] > 0 ? round($stats['activeMentees']/$stats['totalMentees']*100) : 0; @endphp
                    <div class="h-full bg-blue-500 rounded-full" style="width:{{ $activePct }}%"></div>
                </div>
                <div class="text-xs text-gray-400 mt-1">{{ $activePct }}% active this month</div>
            </div>
        </div>

        {{-- Mentors --}}
        <div class="fu bg-white border border-gray-200 rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-violet-50 rounded-full -translate-y-8 translate-x-8"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-violet-600" fill="currentColor" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">↑ 9%</span>
                </div>
                <div class="text-3xl font-bold text-gray-900">{{ number_format($stats['totalMentors']) }}</div>
                <div class="text-sm text-gray-500 mt-1">Total Mentors</div>
                <div class="mt-3 h-1 bg-gray-100 rounded-full overflow-hidden">
                    @php $approvedPct = $stats['totalMentors'] > 0 ? round($stats['approvedMentors']/$stats['totalMentors']*100) : 0; @endphp
                    <div class="h-full bg-violet-500 rounded-full" style="width:{{ $approvedPct }}%"></div>
                </div>
                <div class="text-xs text-gray-400 mt-1">{{ $approvedPct }}% approved &amp; active</div>
            </div>
        </div>

        {{-- Sessions --}}
        <div class="fu bg-white border border-gray-200 rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-full -translate-y-8 translate-x-8"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 16 16"><path d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2V5z"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">↑ 14%</span>
                </div>
                <div class="text-3xl font-bold text-gray-900">{{ number_format($stats['totalSessions']) }}</div>
                <div class="text-sm text-gray-500 mt-1">Total Sessions</div>
                <div class="mt-3 h-1 bg-gray-100 rounded-full overflow-hidden">
                    @php $compPct = $stats['totalSessions'] > 0 ? round($stats['completedSessions']/$stats['totalSessions']*100) : 0; @endphp
                    <div class="h-full bg-emerald-500 rounded-full" style="width:{{ $compPct }}%"></div>
                </div>
                <div class="text-xs text-gray-400 mt-1">{{ $compPct }}% completion rate</div>
            </div>
        </div>

        {{-- Revenue --}}
        <div class="fu bg-gradient-to-br from-orange-500 to-amber-500 rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-28 h-28 bg-white/10 rounded-full -translate-y-10 translate-x-10"></div>
            <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-6 -translate-x-6"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 16 16"><path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/><path d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V4zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2H3z"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-orange-100 bg-white/20 px-2 py-0.5 rounded-full">↑ 22%</span>
                </div>
                <div class="text-3xl font-bold text-white">₹{{ number_format($stats['monthRevenue']) }}</div>
                <div class="text-sm text-orange-100 mt-1">Monthly Revenue</div>
                <svg class="sparkline mt-3 w-full" height="32" viewBox="0 0 120 32" preserveAspectRatio="none">
                    <path d="M0,28 C10,24 20,20 30,22 C40,24 50,12 60,10 C70,8 80,14 90,8 C100,2 110,6 120,4"
                          fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ═══ SECONDARY METRICS ═══ --}}
    <div class="grid grid-cols-4 gap-4">

        {{-- Pending Approvals --}}
        <a href="{{ route('admin.mentor-approvals.index') }}"
           class="bg-white border border-gray-200 rounded-2xl p-5 hover:shadow-md transition-shadow block">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zm4.5 8a3.5 3.5 0 0 1 3.5 3.5V14a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1v-1.5A3.5 3.5 0 0 1 3.5 9h9z"/></svg>
                </div>
                <span class="text-xs font-semibold text-gray-600">Pending Approvals</span>
            </div>
            <div class="text-2xl font-bold text-amber-600">{{ $stats['pendingMentors'] }}</div>
            <div class="text-xs text-amber-600 mt-1 font-medium">Review now →</div>
        </a>

        {{-- Profile Changes --}}
        <a href="{{ route('admin.mentors.pending-changes') }}"
           class="bg-white border border-gray-200 rounded-2xl p-5 hover:shadow-md transition-shadow block">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-violet-600" fill="currentColor" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10z"/></svg>
                </div>
                <span class="text-xs font-semibold text-gray-600">Profile Changes</span>
            </div>
            <div class="text-2xl font-bold text-violet-600">{{ $stats['pendingChanges'] }}</div>
            <div class="text-xs text-violet-600 mt-1 font-medium">Review →</div>
        </a>

        {{-- Sessions Today --}}
        <a href="{{ route('admin.sessions.index') }}"
           class="bg-white border border-gray-200 rounded-2xl p-5 hover:shadow-md transition-shadow block">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 16 16"><path d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2V5z"/></svg>
                </div>
                <span class="text-xs font-semibold text-gray-600">Sessions Today</span>
            </div>
            <div class="text-2xl font-bold text-emerald-600">{{ $stats['sessionsToday'] }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $stats['ongoingSessions'] }} ongoing right now</div>
        </a>

        {{-- Avg Rating --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-lg bg-yellow-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
                </div>
                <span class="text-xs font-semibold text-gray-600">Avg. Rating</span>
            </div>
            <div class="text-2xl font-bold text-yellow-600">{{ number_format($stats['avgRating'],1) }}<span class="text-sm font-normal text-gray-400">/5</span></div>
            <div class="flex mt-1.5">
                @for($i=1;$i<=5;$i++)
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 {{ $i<=round($stats['avgRating']) ? 'text-yellow-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
                @endfor
            </div>
        </div>
    </div>

    {{-- ═══ CHARTS ROW ═══ --}}
    <div class="grid grid-cols-3 gap-5">

        {{-- Monthly Sessions Bar Chart --}}
        <div class="col-span-2 bg-white border border-gray-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Session Volume</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Monthly sessions — last 6 months</p>
                </div>
                <div class="flex gap-3 text-xs">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>Sessions</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange-400 inline-block"></span>Revenue (×100)</span>
                </div>
            </div>
            @php
            $maxS = $sessionChart->max('sessions') ?: 1;
            $maxR = $sessionChart->max('revenue') ?: 1;
            @endphp
            <div class="flex items-end justify-between gap-2 h-44">
                @foreach($sessionChart as $row)
                <div class="flex-1 flex flex-col items-center gap-1 group">
                    <div class="text-[10px] text-gray-400 font-semibold opacity-0 group-hover:opacity-100 transition-opacity">
                        {{ $row['sessions'] }}s · ₹{{ number_format($row['revenue']) }}
                    </div>
                    <div class="w-full flex gap-1 items-end" style="height:136px;">
                        <div class="flex-1 rounded-t-lg bg-blue-500 bar"
                             style="height:{{ round($row['sessions']/$maxS*100) }}%; min-height:4px;"></div>
                        <div class="flex-1 rounded-t-lg bg-orange-400 bar"
                             style="height:{{ round($row['revenue']/$maxR*100) }}%; min-height:4px; animation-delay:.1s;"></div>
                    </div>
                    <div class="text-[11px] text-gray-400">{{ $row['month'] }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Donut Ring — User Split --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-6 flex flex-col">
            <h3 class="text-base font-bold text-gray-900 mb-1">User Split</h3>
            <p class="text-xs text-gray-400 mb-5">Mentees vs Mentors</p>
            @php
            $total   = $stats['totalMentees'] + $stats['totalMentors'];
            $pct     = $total > 0 ? round($stats['totalMentees']/$total*100) : 79;
            $circ    = 2 * pi() * 52;
            $offset  = $circ * (1 - $pct/100);
            @endphp
            <div class="flex-1 flex items-center justify-center">
                <div class="relative w-36 h-36">
                    <svg class="w-36 h-36 -rotate-90" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="52" fill="none" stroke="#f1f5f9" stroke-width="14"/>
                        <circle cx="60" cy="60" r="52" fill="none" stroke="#3b82f6" stroke-width="14"
                                stroke-linecap="round" class="ring-anim"
                                stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $offset }}"/>
                        <circle cx="60" cy="60" r="52" fill="none" stroke="#f97316" stroke-width="14"
                                stroke-linecap="round"
                                stroke-dasharray="{{ $circ*(1-$pct/100) }} {{ $circ }}"
                                stroke-dashoffset="{{ -$circ*$pct/100 }}"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <div class="text-xl font-bold text-gray-900">{{ number_format($total) }}</div>
                        <div class="text-xs text-gray-400">Total</div>
                    </div>
                </div>
            </div>
            <div class="space-y-2 mt-4">
                @foreach([
                    ['bg-blue-500',   'Mentees', $stats['totalMentees']],
                    ['bg-orange-400', 'Mentors',  $stats['totalMentors']],
                ] as [$dot,$label,$count])
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full {{ $dot }}"></span>
                        <span class="text-gray-600">{{ $label }}</span>
                    </span>
                    <span class="font-bold text-gray-900">{{ number_format($count) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══ TABLE + QUICK ACTIONS ═══ --}}
    <div class="grid grid-cols-3 gap-5">

        {{-- Recent Sessions from DB --}}
        <div class="col-span-2 bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800">Recent Sessions</h3>
                <a href="{{ route('admin.sessions.index') }}" class="text-xs font-semibold text-orange-500 hover:text-orange-700 transition-colors">View All →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-left">
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Mentee</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Mentor</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @php
                        $sc = ['completed'=>'bg-green-50 text-green-700','pending'=>'bg-amber-50 text-amber-700','ongoing'=>'bg-blue-50 text-blue-700','cancelled'=>'bg-red-50 text-red-600','confirmed'=>'bg-indigo-50 text-indigo-700'];
                        $avatarColors = ['bg-blue-100 text-blue-700','bg-pink-100 text-pink-700','bg-emerald-100 text-emerald-700','bg-violet-100 text-violet-700','bg-amber-100 text-amber-700'];
                        @endphp
                        @forelse($recentSessions as $i => $session)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full {{ $avatarColors[$i%5] }} font-bold text-xs flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($session->mentee->name ?? 'M',0,2)) }}
                                    </div>
                                    <span class="text-sm font-medium text-gray-800">{{ $session->mentee->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($session->mentor->name ?? 'M',0,2)) }}
                                    </div>
                                    <span class="text-sm text-gray-600">{{ $session->mentor->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-sm text-gray-500">{{ $session->scheduled_at->format('d M') }}</td>
                            <td class="px-4 py-3.5 text-sm font-semibold text-gray-700">
                                {{ $session->amount_paid ? '₹'.number_format($session->amount_paid) : '—' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full {{ $sc[$session->status] ?? 'bg-gray-100 text-gray-500' }}">
                                    {{ ucfirst($session->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">No sessions yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Quick Actions + Activity --}}
        <div class="space-y-5">

            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <h3 class="text-sm font-bold text-gray-800 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    @foreach([
                        [route('admin.mentor-approvals.index'),        'Approve Mentors',    'bg-orange-50 border-orange-100 text-orange-700', 'bg-orange-100',  '👤', $stats['pendingMentors']],
                        [route('admin.mentors.pending-changes'),   'Profile Changes',    'bg-violet-50 border-violet-100 text-violet-700', 'bg-violet-100',  '✏️', $stats['pendingChanges']],
                        [route('admin.curriculum.streams'),        'Manage Curriculum',  'bg-blue-50 border-blue-100 text-blue-700',       'bg-blue-100',    '📚', null],
                        [route('admin.logs.index'),                'Activity Logs',      'bg-slate-50 border-slate-100 text-slate-700',    'bg-slate-100',   '📋', null],
                        [route('admin.mentees.index'),             'Manage Mentees',     'bg-emerald-50 border-emerald-100 text-emerald-700','bg-emerald-100','👥', null],
                        [route('admin.mentors.index'),             'Manage Mentors',     'bg-indigo-50 border-indigo-100 text-indigo-700', 'bg-indigo-100',  '🎓', null],
                        [route('admin.mentors.create'),  'Add Mentor',         'bg-teal-50 border-teal-100 text-teal-700',       'bg-teal-100',    '➕', null],
                        [route('admin.mentees.create'),  'Add Mentee',         'bg-pink-50 border-pink-100 text-pink-700',       'bg-pink-100',    '➕', null],
                    ] as [$href,$label,$cls,$iconBg,$icon,$badge])
                    <a href="{{ $href }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl border {{ $cls }} hover:opacity-90 transition-opacity">
                        <span class="{{ $iconBg }} w-7 h-7 rounded-lg flex items-center justify-center text-sm flex-shrink-0">{{ $icon }}</span>
                        <span class="text-xs font-semibold flex-1">{{ $label }}</span>
                        @if($badge)
                        <span class="text-[10px] font-bold bg-red-500 text-white px-1.5 py-0.5 rounded-full">{{ $badge }}</span>
                        @endif
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 opacity-40 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Recent Activity Logs --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-800">Recent Activity</h3>
                    <a href="{{ route('admin.logs.index') }}" class="text-xs text-orange-500 font-medium hover:underline">View All →</a>
                </div>
                @php
                $levelColors = ['info'=>'bg-blue-100','success'=>'bg-green-100','warning'=>'bg-amber-100','danger'=>'bg-red-100'];
                $levelIcons  = ['info'=>'📋','success'=>'✅','warning'=>'⚠️','danger'=>'🚨'];
                @endphp
                <div class="space-y-3">
                    @forelse($recentLogs as $log)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl {{ $levelColors[$log->level] ?? 'bg-gray-100' }} flex items-center justify-center text-xs flex-shrink-0">
                            {{ $levelIcons[$log->level] ?? '📝' }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-gray-800 leading-tight truncate">{{ $log->description }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $log->logged_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400 text-center py-4">No recent activity</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ ONBOARDING + ENROLLMENT OVERVIEW ═══ --}}
    <div class="grid grid-cols-3 gap-5">

        {{-- Mentor approval funnel --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-6">
            <h3 class="text-sm font-bold text-gray-800 mb-5">Mentor Approval Funnel</h3>
            @php
            $funnelData = [
                ['Registered',  $stats['totalMentors'],    'bg-blue-500'],
                ['Approved',    $stats['approvedMentors'], 'bg-emerald-500'],
                ['Pending',     $stats['pendingMentors'],  'bg-amber-500'],
                ['Rejected',    $stats['rejectedMentors'], 'bg-red-400'],
                ['Suspended',   $stats['suspendedMentors'],'bg-gray-400'],
            ];
            $maxFunnel = $stats['totalMentors'] ?: 1;
            @endphp
            <div class="space-y-3">
                @foreach($funnelData as [$label,$count,$color])
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-600 font-medium">{{ $label }}</span>
                        <span class="font-bold text-gray-900">{{ number_format($count) }}</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="{{ $color }} h-full rounded-full bar" style="width:{{ $maxFunnel > 0 ? round($count/$maxFunnel*100) : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Curriculum enrollment overview --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-bold text-gray-800">Curriculum Enrollments</h3>
                <a href="{{ route('admin.curriculum.enrollments.index') }}" class="text-xs text-orange-500 hover:underline font-medium">View all →</a>
            </div>
            <div class="space-y-3">
                @forelse($enrollmentStats as $row)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center text-sm flex-shrink-0">📚</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-semibold text-gray-800 truncate">{{ $row->stream_name ?? 'Stream' }}</div>
                        <div class="text-[10px] text-gray-400">{{ $row->count }} enrolled</div>
                    </div>
                    <div class="text-xs font-bold text-orange-600">{{ $row->count }}</div>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-6">No enrollments yet</p>
                @endforelse
            </div>
        </div>

        {{-- Pending check-ins for mentors --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-bold text-gray-800">Unanswered Check-ins</h3>
            </div>
            <div class="flex flex-col items-center justify-center py-4">
                <div class="relative w-24 h-24 mb-4">
                    <svg class="w-24 h-24 -rotate-90" viewBox="0 0 96 96">
                        <circle cx="48" cy="48" r="38" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                        @php
                        $circ2  = 2 * pi() * 38;
                        $pctCI  = $stats['totalCheckins'] > 0 ? round($stats['answeredCheckins']/$stats['totalCheckins']*100) : 0;
                        @endphp
                        <circle cx="48" cy="48" r="38" fill="none" stroke="#10b981" stroke-width="10"
                                stroke-linecap="round" class="ring-anim"
                                stroke-dasharray="{{ $circ2 }}"
                                stroke-dashoffset="{{ $circ2*(1-$pctCI/100) }}"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-lg font-bold text-gray-900">{{ $pctCI }}%</span>
                        <span class="text-[10px] text-gray-400">replied</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 w-full text-center">
                    <div class="bg-gray-50 rounded-xl py-2">
                        <div class="text-lg font-bold text-gray-900">{{ $stats['totalCheckins'] }}</div>
                        <div class="text-[10px] text-gray-400">Total</div>
                    </div>
                    <div class="bg-amber-50 rounded-xl py-2">
                        <div class="text-lg font-bold text-amber-600">{{ $stats['totalCheckins'] - $stats['answeredCheckins'] }}</div>
                        <div class="text-[10px] text-gray-400">Pending</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection