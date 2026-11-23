@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Admin Dashboard')

@section('content')
@php
    $growthBadge = function (?int $pct) {
        if ($pct === null) {
            return ['—', 'bg-slate-100 text-slate-500'];
        }
        if ($pct > 0) {
            return ['↑ '.$pct.'%', 'bg-emerald-50 text-emerald-700'];
        }
        if ($pct < 0) {
            return ['↓ '.abs($pct).'%', 'bg-rose-50 text-rose-700'];
        }

        return ['0%', 'bg-slate-100 text-slate-500'];
    };
@endphp

<style>
@keyframes dashFadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
.admin-dash .fu{animation:dashFadeUp .45s ease both}
.admin-dash .fu:nth-child(2){animation-delay:.05s}
.admin-dash .fu:nth-child(3){animation-delay:.1s}
.admin-dash .fu:nth-child(4){animation-delay:.15s}
.admin-dash .fu:nth-child(5){animation-delay:.2s}
.admin-dash .fu:nth-child(6){animation-delay:.25s}
.admin-dash .chart-wrap{position:relative;width:100%;min-height:240px}
.admin-dash .chart-wrap canvas{max-width:100%}
.admin-dash .qa-link{transition:border-color .15s ease,box-shadow .15s ease,transform .15s ease}
.admin-dash .qa-link:hover{border-color:rgba(245,158,11,.4);box-shadow:0 10px 22px rgba(245,158,11,.08);transform:translateY(-1px)}
</style>

<div class="admin-page admin-dash space-y-5">

    <div class="learn-intro">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="learn-eyebrow">Overview · Dashboard</span>
                <h2>Platform command centre</h2>
                <p>Live snapshot of people, sessions, revenue, and learning activity across Vedrix.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.sessions.index') }}" class="learn-btn-ghost">Sessions</a>
                <a href="{{ route('admin.wallet.index') }}" class="learn-btn-ghost">Wallet</a>
                <a href="{{ route('admin.mentor-approvals.index') }}" class="learn-btn-primary">
                    Approvals
                    @if(($stats['pendingMentors'] ?? 0) > 0)
                    <span class="ml-1 bg-white/25 text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $stats['pendingMentors'] }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    {{-- KPI row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
        @php
            [$gMentee, $gMenteeCls] = $growthBadge($stats['menteesGrowth'] ?? null);
            [$gMentor, $gMentorCls] = $growthBadge($stats['mentorsGrowth'] ?? null);
            [$gSess, $gSessCls] = $growthBadge($stats['sessionsGrowth'] ?? null);
            [$gRev, $gRevCls] = $growthBadge($stats['revenueGrowth'] ?? null);
        @endphp

        <div class="learn-stat fu">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="learn-stat__value">{{ number_format($stats['totalMentees']) }}</div>
                    <div class="learn-stat__label">Total mentees</div>
                    <div class="mt-2 text-[11px] text-slate-400">{{ $stats['activeMenteePct'] }}% active · {{ number_format($stats['activeMentees']) }} live</div>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <span class="learn-stat__icon">🎓</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $gMenteeCls }}">{{ $gMentee }}</span>
                </div>
            </div>
            <div class="mt-3 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-amber-600" style="width:{{ min(100, $stats['activeMenteePct']) }}%"></div>
            </div>
        </div>

        <div class="learn-stat fu">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="learn-stat__value">{{ number_format($stats['totalMentors']) }}</div>
                    <div class="learn-stat__label">Total mentors</div>
                    <div class="mt-2 text-[11px] text-slate-400">{{ $stats['approvedMentorPct'] }}% approved · {{ number_format($stats['totalExperts']) }} experts</div>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <span class="learn-stat__icon">👨‍💼</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $gMentorCls }}">{{ $gMentor }}</span>
                </div>
            </div>
            <div class="mt-3 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-amber-600" style="width:{{ min(100, $stats['approvedMentorPct']) }}%"></div>
            </div>
        </div>

        <div class="learn-stat fu">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="learn-stat__value">{{ number_format($stats['totalSessions']) }}</div>
                    <div class="learn-stat__label">Total sessions</div>
                    <div class="mt-2 text-[11px] text-slate-400">{{ $stats['completionRate'] }}% completed · {{ number_format($stats['sessionsToday']) }} today</div>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <span class="learn-stat__icon">📅</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $gSessCls }}">{{ $gSess }}</span>
                </div>
            </div>
            <div class="mt-3 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600" style="width:{{ min(100, $stats['completionRate']) }}%"></div>
            </div>
        </div>

        <div class="fu relative overflow-hidden rounded-2xl border border-amber-200/60 p-4 text-white"
             style="background:linear-gradient(135deg,#F59E0B,#D97706);box-shadow:0 14px 28px rgba(245,158,11,.22)">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between gap-3">
                <div>
                    <div class="text-2xl font-extrabold tracking-tight">₹{{ number_format($stats['monthRevenue']) }}</div>
                    <div class="text-xs font-semibold text-amber-50/90 mt-1">Revenue this month</div>
                    <div class="mt-2 text-[11px] text-amber-50/75">Lifetime ₹{{ number_format($stats['lifetimeRevenue']) }}</div>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <span class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center text-base">💰</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/20">{{ $gRev }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Secondary ops metrics --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
        <a href="{{ route('admin.mentor-approvals.index') }}" class="learn-stat block hover:border-amber-300">
            <div class="learn-stat__value text-amber-700">{{ number_format($stats['pendingMentors']) }}</div>
            <div class="learn-stat__label">Pending approvals</div>
        </a>
        <a href="{{ route('admin.mentors.pending-changes') }}" class="learn-stat block hover:border-amber-300">
            <div class="learn-stat__value">{{ number_format($stats['pendingChanges']) }}</div>
            <div class="learn-stat__label">Profile changes</div>
        </a>
        <a href="{{ route('admin.sessions.index') }}" class="learn-stat block hover:border-amber-300">
            <div class="learn-stat__value text-emerald-700">{{ number_format($stats['sessionsToday']) }}</div>
            <div class="learn-stat__label">Sessions today</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ $stats['ongoingSessions'] }} ongoing</div>
        </a>
        <div class="learn-stat">
            <div class="learn-stat__value">{{ number_format($stats['avgRating'], 1) }}<span class="text-sm font-semibold text-slate-400">/5</span></div>
            <div class="learn-stat__label">Avg mentor rating</div>
        </div>
        <a href="{{ route('admin.withdrawals.index') }}" class="learn-stat block hover:border-amber-300">
            <div class="learn-stat__value">{{ number_format($stats['pendingWithdrawals']) }}</div>
            <div class="learn-stat__label">Pending payouts</div>
            <div class="text-[11px] text-slate-400 mt-1">₹{{ number_format($stats['pendingWithdrawalAmount']) }}</div>
        </a>
        <a href="{{ route('admin.curriculum.streams') }}" class="learn-stat block hover:border-amber-300">
            <div class="learn-stat__value">{{ number_format($stats['activeJourneys']) }}</div>
            <div class="learn-stat__label">Active journeys</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ $stats['activeOffers'] }} live offers</div>
        </a>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 learn-panel p-5">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Session &amp; revenue trend</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Last 6 months — sessions booked vs completed revenue</p>
                </div>
                <div class="flex gap-3 text-[11px] font-semibold text-slate-500">
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>Sessions</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-800"></span>Revenue (₹)</span>
                </div>
            </div>
            <div class="chart-wrap"><canvas id="chartSessionsRevenue" height="110"></canvas></div>
        </div>

        <div class="learn-panel p-5">
            <h3 class="text-sm font-bold text-slate-900 mb-0.5">Session status mix</h3>
            <p class="text-xs text-slate-400 mb-4">Distribution across all bookings</p>
            <div class="chart-wrap" style="min-height:220px"><canvas id="chartStatus"></canvas></div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 learn-panel p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Daily session volume</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Last 14 days</p>
                </div>
                <a href="{{ route('admin.sessions.index') }}" class="text-xs font-bold text-amber-700 hover:underline">View sessions →</a>
            </div>
            <div class="chart-wrap" style="min-height:200px"><canvas id="chartDaily" height="90"></canvas></div>
        </div>

        <div class="learn-panel p-5">
            <h3 class="text-sm font-bold text-slate-900 mb-0.5">People split</h3>
            <p class="text-xs text-slate-400 mb-4">Mentees vs mentors on the platform</p>
            <div class="chart-wrap" style="min-height:200px"><canvas id="chartPeople"></canvas></div>
            <div class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Mentees</span><span class="font-bold text-slate-900">{{ number_format($stats['totalMentees']) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Mentors</span><span class="font-bold text-slate-900">{{ number_format($stats['totalMentors']) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Experts</span><span class="font-bold text-slate-900">{{ number_format($stats['totalExperts']) }}</span></div>
            </div>
        </div>
    </div>

    {{-- Tables + actions --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 learn-panel overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Recent sessions</h3>
                <a href="{{ route('admin.sessions.index') }}" class="text-xs font-bold text-amber-700 hover:underline">View all →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50/80 border-b border-slate-100">
                        <tr class="text-left">
                            <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wide text-slate-400">Mentee</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wide text-slate-400">Host</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wide text-slate-400">When</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wide text-slate-400">Amount</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wide text-slate-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @php
                            $sc = [
                                'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
                                'upcoming' => 'bg-sky-50 text-sky-700 border-sky-100',
                                'cancelled' => 'bg-rose-50 text-rose-700 border-rose-100',
                                'confirmed' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                            ];
                        @endphp
                        @forelse($recentSessions as $session)
                        <tr class="hover:bg-amber-50/30 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 text-amber-800 font-bold text-[11px] flex items-center justify-center">
                                        {{ strtoupper(substr($session->mentee->name ?? 'M', 0, 2)) }}
                                    </div>
                                    <span class="font-semibold text-slate-800">{{ $session->mentee->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-3.5">
                                <div class="font-medium text-slate-700">{{ $session->mentor->name ?? '—' }}</div>
                                <div class="text-[11px] {{ $session->isExpertHost() ? 'text-violet-600' : 'text-slate-400' }}">{{ $session->hostLabel() }}</div>
                            </td>
                            <td class="px-3 py-3.5 text-slate-500 whitespace-nowrap">
                                {{ optional($session->scheduled_at)->format('d M, h:i A') ?? '—' }}
                            </td>
                            <td class="px-3 py-3.5 font-semibold text-slate-800">
                                {{ $session->amount_paid ? '₹'.number_format($session->amount_paid) : ($session->amount ? '₹'.number_format($session->amount) : '—') }}
                            </td>
                            <td class="px-3 py-3.5">
                                <span class="inline-flex text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full border {{ $sc[$session->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                    {{ ucfirst($session->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-slate-400">No sessions yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            <div class="learn-panel p-4">
                <h3 class="text-sm font-bold text-slate-900 mb-3">Quick actions</h3>
                <div class="space-y-2">
                    @foreach([
                        [route('admin.mentor-approvals.index'), 'Approve mentors', '👤', $stats['pendingMentors']],
                        [route('admin.mentors.pending-changes'), 'Profile changes', '✏️', $stats['pendingChanges']],
                        [route('admin.withdrawals.index'), 'Review withdrawals', '🏦', $stats['pendingWithdrawals']],
                        [route('admin.wallet.add-fund'), 'Add mentee fund', '➕', null],
                        [route('admin.curriculum.templates'), 'Curriculum templates', '🧩', null],
                        [route('admin.offers.index'), 'Manage offers', '🎁', $stats['activeOffers']],
                        [route('admin.mentees.create'), 'Add mentee', '🎓', null],
                        [route('admin.mentors.create'), 'Add mentor', '👨‍💼', null],
                    ] as [$href, $label, $icon, $badge])
                    <a href="{{ $href }}" class="qa-link flex items-center gap-3 px-3 py-2.5 rounded-xl border border-slate-200 bg-white">
                        <span class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-sm">{{ $icon }}</span>
                        <span class="text-xs font-semibold text-slate-700 flex-1">{{ $label }}</span>
                        @if($badge)
                        <span class="text-[10px] font-bold bg-amber-500 text-white px-1.5 py-0.5 rounded-full">{{ $badge }}</span>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>

            <div class="learn-panel p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-900">Recent activity</h3>
                    <a href="{{ route('admin.logs.index') }}" class="text-xs font-bold text-amber-700 hover:underline">All logs →</a>
                </div>
                @php
                    $levelColors = ['info'=>'bg-sky-50','success'=>'bg-emerald-50','warning'=>'bg-amber-50','danger'=>'bg-rose-50'];
                    $levelIcons = ['info'=>'📋','success'=>'✅','warning'=>'⚠️','danger'=>'🚨'];
                @endphp
                <div class="space-y-3">
                    @forelse($recentLogs as $log)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $levelColors[$log->level] ?? 'bg-slate-50' }} flex items-center justify-center text-xs flex-shrink-0 border border-slate-100">
                            {{ $levelIcons[$log->level] ?? '📝' }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-slate-800 leading-snug line-clamp-2">{{ $log->description }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ optional($log->logged_at)->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-4">No recent activity</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Learning / funnel --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="learn-panel p-5">
            <h3 class="text-sm font-bold text-slate-900 mb-4">Mentor approval funnel</h3>
            @php
                $funnel = [
                    ['Registered', $stats['totalMentors'], '#F59E0B'],
                    ['Approved', $stats['approvedMentors'], '#10B981'],
                    ['Pending', $stats['pendingMentors'], '#FBBF24'],
                    ['Rejected', $stats['rejectedMentors'], '#F87171'],
                    ['Suspended', $stats['suspendedMentors'], '#94A3B8'],
                ];
                $maxFunnel = max(1, $stats['totalMentors']);
            @endphp
            <div class="space-y-3">
                @foreach($funnel as [$label, $count, $color])
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-medium text-slate-600">{{ $label }}</span>
                        <span class="font-bold text-slate-900">{{ number_format($count) }}</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full" style="width:{{ round($count / $maxFunnel * 100) }}%;background:{{ $color }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="learn-panel p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-900">Top curriculum enrollments</h3>
                <a href="{{ route('admin.curriculum.enrollments.index') }}" class="text-xs font-bold text-amber-700 hover:underline">View →</a>
            </div>
            <div class="space-y-3">
                @forelse($enrollmentStats as $row)
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-sm">🗺️</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-semibold text-slate-800 truncate">{{ $row->stream_name }}</div>
                        <div class="text-[10px] text-slate-400">Active enrollments</div>
                    </div>
                    <div class="text-sm font-extrabold text-amber-700">{{ $row->count }}</div>
                </div>
                @empty
                <p class="text-xs text-slate-400 text-center py-8">No active enrollments yet</p>
                @endforelse
            </div>
        </div>

        <div class="learn-panel p-5">
            <h3 class="text-sm font-bold text-slate-900 mb-4">Weekly check-ins</h3>
            @php
                $pctCI = $stats['totalCheckins'] > 0 ? (int) round($stats['answeredCheckins'] / $stats['totalCheckins'] * 100) : 0;
                $pendingCI = max(0, $stats['totalCheckins'] - $stats['answeredCheckins']);
            @endphp
            <div class="flex flex-col items-center">
                <div class="relative w-28 h-28 mb-4">
                    <svg class="w-28 h-28 -rotate-90" viewBox="0 0 112 112">
                        <circle cx="56" cy="56" r="44" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                        <circle cx="56" cy="56" r="44" fill="none" stroke="#F59E0B" stroke-width="10"
                                stroke-linecap="round"
                                stroke-dasharray="{{ 2 * pi() * 44 }}"
                                stroke-dashoffset="{{ 2 * pi() * 44 * (1 - $pctCI / 100) }}"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-xl font-extrabold text-slate-900">{{ $pctCI }}%</span>
                        <span class="text-[10px] text-slate-400">replied</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 w-full">
                    <div class="rounded-xl bg-slate-50 border border-slate-100 py-2.5 text-center">
                        <div class="text-lg font-extrabold text-slate-900">{{ number_format($stats['totalCheckins']) }}</div>
                        <div class="text-[10px] text-slate-400 font-semibold uppercase">Total</div>
                    </div>
                    <div class="rounded-xl bg-amber-50 border border-amber-100 py-2.5 text-center">
                        <div class="text-lg font-extrabold text-amber-700">{{ number_format($pendingCI) }}</div>
                        <div class="text-[10px] text-amber-700/70 font-semibold uppercase">Pending</div>
                    </div>
                </div>
                <div class="mt-3 w-full text-center text-[11px] text-slate-400">
                    Wallet credits this month: <span class="font-bold text-slate-700">₹{{ number_format($stats['walletCreditsMonth']) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const amber = '#F59E0B';
    const amberDark = '#D97706';
    const ink = '#0f172a';
    const muted = '#94a3b8';

    const sessionLabels = @json($sessionChart->pluck('month'));
    const sessionCounts = @json($sessionChart->pluck('sessions'));
    const sessionRevenue = @json($sessionChart->pluck('revenue'));
    const dailyLabels = @json($dailySessions->pluck('label'));
    const dailyCounts = @json($dailySessions->pluck('count'));
    const statusData = @json(array_values($statusBreakdown));
    const statusLabels = @json(array_map('ucfirst', array_keys($statusBreakdown)));

    Chart.defaults.font.family = 'ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, sans-serif';
    Chart.defaults.color = muted;

    new Chart(document.getElementById('chartSessionsRevenue'), {
        type: 'bar',
        data: {
            labels: sessionLabels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Sessions',
                    data: sessionCounts,
                    backgroundColor: 'rgba(245,158,11,.85)',
                    borderRadius: 8,
                    borderSkipped: false,
                    yAxisID: 'y',
                    order: 2,
                },
                {
                    type: 'line',
                    label: 'Revenue (₹)',
                    data: sessionRevenue,
                    borderColor: ink,
                    backgroundColor: 'rgba(15,23,42,.08)',
                    borderWidth: 2.5,
                    tension: .35,
                    pointRadius: 3,
                    pointBackgroundColor: ink,
                    fill: false,
                    yAxisID: 'y1',
                    order: 1,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 10,
                    cornerRadius: 8,
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' } } },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(148,163,184,.18)' },
                    ticks: { precision: 0, font: { size: 11 } },
                    title: { display: true, text: 'Sessions', color: muted, font: { size: 10 } }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: {
                        font: { size: 11 },
                        callback: (v) => '₹' + Number(v).toLocaleString('en-IN')
                    },
                    title: { display: true, text: 'Revenue', color: muted, font: { size: 10 } }
                }
            }
        }
    });

    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: ['#10B981', '#38BDF8', '#F87171', '#818CF8', '#FBBF24'],
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, pointStyle: 'circle', padding: 14, font: { size: 11, weight: '600' } }
                }
            }
        }
    });

    new Chart(document.getElementById('chartDaily'), {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Sessions',
                data: dailyCounts,
                borderColor: amber,
                backgroundColor: 'rgba(245,158,11,.15)',
                fill: true,
                tension: .4,
                borderWidth: 2.5,
                pointRadius: 2.5,
                pointBackgroundColor: amberDark,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 7, font: { size: 10 } } },
                y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,.15)' }, ticks: { precision: 0, font: { size: 11 } } }
            }
        }
    });

    new Chart(document.getElementById('chartPeople'), {
        type: 'doughnut',
        data: {
            labels: ['Mentees', 'Mentors', 'Experts'],
            datasets: [{
                data: [{{ (int) $stats['totalMentees'] }}, {{ (int) $stats['totalMentors'] }}, {{ (int) $stats['totalExperts'] }}],
                backgroundColor: [amber, '#0f172a', '#FDBA74'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, usePointStyle: true, pointStyle: 'circle', padding: 12, font: { size: 11, weight: '600' } }
                }
            }
        }
    });
})();
</script>
@endpush
@endsection
