@extends('admin.layouts.app')
@section('title', 'Activity Logs')
@section('heading', 'Activity Logs')
@section('content')

<div class="space-y-5">

    {{-- Header row --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Real-time audit trail of all system and user activity.</p>
        <div class="flex items-center gap-2">
            {{-- Live indicator --}}
            <div class="flex items-center gap-1.5 text-xs text-gray-400 bg-white border border-gray-200 px-3 py-2 rounded-xl" id="live-indicator">
                <span class="w-2 h-2 bg-gray-300 rounded-full" id="live-dot"></span>
                <span id="live-text">Paused</span>
            </div>
            <button onclick="toggleLiveTail()"
                    id="live-btn"
                    class="text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 px-3 py-2 rounded-xl transition-colors">
                Enable Live Tail
            </button>
            <a href="{{ route('admin.logs.export', request()->all()) }}"
               class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 px-3 py-2 rounded-xl transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export CSV
            </a>
            <div class="relative" x-data="{ open: false }">
                <button onclick="document.getElementById('purge-modal').classList.remove('hidden')"
                        class="text-xs font-medium text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 px-3 py-2 rounded-xl transition-colors">
                    Purge Logs
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Stats row --}}
    <div class="grid grid-cols-6 gap-3">
        @foreach([
            ['Today',         $stats['total_today'],  'bg-slate-50',   'text-slate-700',   'border-slate-200'],
            ['Logins Today',  $stats['logins_today'], 'bg-blue-50',    'text-blue-700',    'border-blue-200'],
            ['Warnings',      $stats['warnings'],     'bg-amber-50',   'text-amber-700',   'border-amber-200'],
            ['Errors',        $stats['errors'],       'bg-red-50',     'text-red-700',     'border-red-200'],
            ['Total Logs',    number_format($stats['total_all']),   'bg-indigo-50',  'text-indigo-700',  'border-indigo-200'],
            ['Active Users',  $stats['unique_users'], 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200'],
        ] as [$label, $value, $bg, $tc, $bc])
        <div class="{{ $bg }} border {{ $bc }} rounded-xl p-3.5">
            <div class="text-xl font-bold {{ $tc }}">{{ $value }}</div>
            <div class="text-xs font-medium text-gray-500 mt-0.5">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    {{-- Filter bar --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Description, user, IP, event…"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all">
            </div>

            @foreach([
                ['module', 'Module', $modules->mapWithKeys(fn($m) => [$m => ucfirst($m)])->prepend('All Modules', '')->toArray()],
                ['level',  'Level',  ['' => 'All Levels', 'info' => 'Info', 'success' => 'Success', 'warning' => 'Warning', 'danger' => 'Danger']],
                ['event',  'Event',  $events->mapWithKeys(fn($e) => [$e => str_replace('_', ' ', ucfirst($e))])->prepend('All Events', '')->toArray()],
            ] as [$name, $label, $opts])
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ $label }}</label>
                <div class="relative">
                    <select name="{{ $name }}"
                            class="border border-gray-200 rounded-xl px-3.5 py-2 pr-8 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 appearance-none cursor-pointer transition-all">
                        @foreach($opts as $val => $text)
                        <option value="{{ $val }}" {{ request($name) === (string)$val ? 'selected' : '' }}>{{ $text }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </div>
            </div>
            @endforeach

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">User</label>
                <div class="relative">
                    <select name="user_id"
                            class="border border-gray-200 rounded-xl px-3.5 py-2 pr-8 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 appearance-none cursor-pointer transition-all">
                        <option value="">All Users</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all">
            </div>

            <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">Filter</button>
            <a href="{{ route('admin.logs.index') }}" class="text-sm text-gray-500 border border-gray-200 px-3 py-2 rounded-xl hover:bg-gray-50 transition-colors">Reset</a>
        </form>
    </div>

    {{-- Live tail new entries (injected by JS) --}}
    <div id="live-entries" class="space-y-1.5 hidden"></div>

    {{-- Log Table --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <h3 class="text-sm font-semibold text-gray-800">Log Entries</h3>
                <span class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full font-medium">{{ $logs->total() }} records</span>
            </div>
            <div class="text-xs text-gray-400">Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }}</div>
        </div>

        @if($logs->isEmpty())
        <div class="py-20 text-center">
            <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p class="text-gray-500 font-medium">No log entries found</p>
            <p class="text-gray-400 text-sm mt-1">Logs will appear here as users interact with the system.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-left">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-32">Time</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-20">Level</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">Module</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-32">User</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">IP</th>
                        <th class="px-4 py-3 w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50" id="log-table-body">
                    @foreach($logs as $log)
                    @php $lc = $log->level_color; @endphp
                    <tr class="hover:bg-gray-50 transition-colors group">

                        {{-- Time --}}
                        <td class="px-5 py-3.5">
                            <div class="text-xs font-mono text-gray-700 whitespace-nowrap">
                                {{ $log->logged_at->format('d M, H:i:s') }}
                            </div>
                            <div class="text-[10px] text-gray-400 mt-0.5">{{ $log->logged_at->diffForHumans() }}</div>
                        </td>

                        {{-- Level badge --}}
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $lc['bg'] }} {{ $lc['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $lc['dot'] }}"></span>
                                {{ ucfirst($log->level) }}
                            </span>
                        </td>

                        {{-- Module --}}
                        <td class="px-4 py-3.5">
                            @if($log->module)
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm leading-none">{{ $log->module_icon }}</span>
                                <span class="text-xs font-medium text-gray-600 capitalize">{{ $log->module }}</span>
                            </div>
                            @else
                            <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>

                        {{-- Description --}}
                        <td class="px-4 py-3.5">
                            <div class="text-sm text-gray-800 leading-snug">{{ $log->description }}</div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] font-mono text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">{{ $log->event }}</span>
                                @if($log->subject_label)
                                <span class="text-[10px] text-gray-400">→ {{ $log->subject_label }}</span>
                                @endif
                            </div>
                        </td>

                        {{-- User --}}
                        <td class="px-4 py-3.5">
                            @if($log->causer_name)
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0">
                                    {{ strtoupper(substr($log->causer_name, 0, 1)) }}
                                </div>
                                <span class="text-xs text-gray-700 font-medium truncate max-w-[80px]">{{ $log->causer_name }}</span>
                            </div>
                            @else
                            <span class="text-xs text-gray-400 italic">System</span>
                            @endif
                        </td>

                        {{-- IP --}}
                        <td class="px-4 py-3.5">
                            <span class="text-xs font-mono text-gray-500">{{ $log->ip_address ?? '—' }}</span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity justify-end">
                                <a href="{{ route('admin.logs.show', $log) }}"
                                   class="text-xs text-gray-400 hover:text-blue-600 px-2 py-1 rounded-lg hover:bg-blue-50 transition-colors">
                                    View
                                </a>
                                <form method="POST" action="{{ route('admin.logs.destroy', $log) }}"
                                      onsubmit="return confirm('Delete this log entry?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="text-xs text-gray-400 hover:text-red-500 px-2 py-1 rounded-lg hover:bg-red-50 transition-colors">
                                        Del
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex items-center justify-between px-5 py-3.5 border-t border-gray-100">
            <div class="text-xs text-gray-500">
                Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ number_format($logs->total()) }}
            </div>
            {{ $logs->links() }}
        </div>
        @endif
    </div>

    {{-- Module breakdown --}}
    @if($moduleBreakdown->isNotEmpty())
    <div class="bg-white border border-gray-200 rounded-2xl p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Activity by Module — last 7 days</h3>
        @php $maxCount = $moduleBreakdown->max('count'); @endphp
        <div class="space-y-3">
            @foreach($moduleBreakdown as $row)
            @php
            $pct = $maxCount > 0 ? round($row->count / $maxCount * 100) : 0;
            $mod = $row->module ?? 'unknown';
            $icon = \App\Models\ActivityLog::make(['module' => $mod])->module_icon;
            @endphp
            <div class="flex items-center gap-3">
                <div class="w-6 text-sm text-center flex-shrink-0">{{ $icon }}</div>
                <div class="text-xs font-medium text-gray-600 capitalize w-24 flex-shrink-0">{{ $mod }}</div>
                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-indigo-500 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                </div>
                <div class="text-xs font-semibold text-gray-700 w-10 text-right flex-shrink-0">{{ number_format($row->count) }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- Purge Modal --}}
<div id="purge-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-900">Purge Log Entries</h3>
                <p class="text-xs text-gray-500">This action is irreversible.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.logs.bulk-destroy') }}">
            @csrf
            <div class="space-y-2 mb-5">
                @foreach([
                    ['delete_older_30', 'Delete logs older than 30 days'],
                    ['delete_older_90', 'Delete logs older than 90 days'],
                    ['delete_all',      'Delete ALL logs (danger!)'],
                ] as [$val, $label])
                <label class="flex items-center gap-3 px-4 py-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="radio" name="action" value="{{ $val }}" class="text-red-600">
                    <span class="text-sm {{ $val === 'delete_all' ? 'text-red-600 font-semibold' : 'text-gray-700' }}">{{ $label }}</span>
                </label>
                @endforeach
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                    Purge
                </button>
                <button type="button" onclick="document.getElementById('purge-modal').classList.add('hidden')"
                        class="flex-1 border border-gray-200 text-gray-600 text-sm py-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let liveTailEnabled = false;
let lastLoggedAt    = '{{ now()->toISOString() }}';
let livePollInterval;

const levelColors = {
    info:    { bg: 'bg-blue-50',    text: 'text-blue-700',    dot: 'bg-blue-500'    },
    success: { bg: 'bg-emerald-50', text: 'text-emerald-700', dot: 'bg-emerald-500' },
    warning: { bg: 'bg-amber-50',   text: 'text-amber-700',   dot: 'bg-amber-500'   },
    danger:  { bg: 'bg-red-50',     text: 'text-red-700',     dot: 'bg-red-500'     },
};

const moduleIcons = {
    auth:'🔐', users:'👤', sessions:'🎥', payments:'💳',
    curriculum:'📚', jobs:'💼', plans:'📋', settings:'⚙️', system:'🖥️',
};

function toggleLiveTail() {
    liveTailEnabled = !liveTailEnabled;
    const btn  = document.getElementById('live-btn');
    const dot  = document.getElementById('live-dot');
    const text = document.getElementById('live-text');
    const wrap = document.getElementById('live-entries');

    if (liveTailEnabled) {
        btn.textContent   = 'Stop Live Tail';
        btn.className     = btn.className.replace('emerald', 'red');
        dot.className     = 'w-2 h-2 bg-emerald-500 rounded-full animate-pulse';
        text.textContent  = 'Live';
        wrap.classList.remove('hidden');
        livePollInterval = setInterval(pollLogs, 3000);
    } else {
        btn.textContent  = 'Enable Live Tail';
        btn.className    = btn.className.replace('red', 'emerald');
        dot.className    = 'w-2 h-2 bg-gray-300 rounded-full';
        text.textContent = 'Paused';
        wrap.classList.add('hidden');
        clearInterval(livePollInterval);
    }
}

async function pollLogs() {
    try {
        const res  = await fetch(`{{ route('admin.logs.latest') }}?since=${encodeURIComponent(lastLoggedAt)}`);
        const logs = await res.json();
        if (!logs.length) return;

        lastLoggedAt = logs[0].logged_at;
        const container = document.getElementById('live-entries');

        logs.reverse().forEach(log => {
            const lc   = levelColors[log.level] || levelColors.info;
            const icon = moduleIcons[log.module] || '📝';
            const row  = document.createElement('div');
            row.className = 'flex items-start gap-3 px-4 py-3 bg-white border border-gray-200 rounded-xl animate-pulse-once';
            row.style.animationDuration = '.4s';
            row.innerHTML = `
                <span class="text-sm mt-0.5 flex-shrink-0">${icon}</span>
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full flex-shrink-0 ${lc.bg} ${lc.text}">
                    <span class="w-1.5 h-1.5 rounded-full ${lc.dot}"></span>${ucfirst(log.level)}
                </span>
                <span class="text-sm text-gray-800 flex-1">${log.description}</span>
                <span class="text-xs font-mono text-gray-400 flex-shrink-0">${log.causer_name || 'System'}</span>
                <span class="text-xs text-gray-300 flex-shrink-0 font-mono">${log.logged_at.slice(11,19)}</span>
            `;
            container.prepend(row);
            // keep max 20 live entries
            while (container.children.length > 20) container.lastChild.remove();
        });
    } catch (e) { console.warn('Live tail error:', e); }
}

function ucfirst(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : s; }
</script>

@endsection