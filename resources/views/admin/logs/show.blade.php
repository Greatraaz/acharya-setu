@extends('admin.layouts.app')
@section('title', 'Log #' . $log->id)
@section('heading', 'Log Entry Detail')
@section('content')

@php $lc = $log->level_color; @endphp

<div class="max-w-4xl space-y-5">

    {{-- Back --}}
    <a href="{{ route('admin.logs.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Logs
    </a>

    {{-- Main card --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        {{-- Level strip --}}
        <div class="h-1 w-full {{ $lc['dot'] }}"></div>

        <div class="p-6">
            <div class="flex items-start justify-between gap-4 mb-5">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center text-lg flex-shrink-0">
                        {{ $log->module_icon }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="text-base font-bold text-gray-900">{{ $log->description }}</h2>
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $lc['bg'] }} {{ $lc['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $lc['dot'] }}"></span>
                                {{ ucfirst($log->level) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-400">
                            <span class="font-mono bg-gray-100 px-2 py-0.5 rounded">{{ $log->event }}</span>
                            @if($log->module)
                            <span class="capitalize bg-gray-100 px-2 py-0.5 rounded">{{ $log->module }}</span>
                            @endif
                            <span>#{{ $log->id }}</span>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logs.destroy', $log) }}"
                      onsubmit="return confirm('Delete this log entry?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="text-xs font-medium text-red-500 border border-red-200 bg-red-50 hover:bg-red-100 px-3 py-2 rounded-lg transition-colors">
                        Delete Entry
                    </button>
                </form>
            </div>

            {{-- Detail grid --}}
            <div class="grid grid-cols-2 gap-4">
                @foreach([
                    ['Logged At',    $log->logged_at->format('d M Y, H:i:s') . ' (' . $log->logged_at->diffForHumans() . ')'],
                    ['Event',        $log->event],
                    ['Module',       $log->module ? ucfirst($log->module) : '—'],
                    ['Level',        ucfirst($log->level)],
                    ['IP Address',   $log->ip_address ?? '—'],
                    ['HTTP Method',  $log->method ?? '—'],
                ] as [$label, $value])
                <div class="bg-gray-50 rounded-xl px-4 py-3">
                    <div class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">{{ $label }}</div>
                    <div class="text-sm font-semibold text-gray-800 font-mono">{{ $value }}</div>
                </div>
                @endforeach
            </div>

            @if($log->url)
            <div class="mt-4 bg-gray-50 rounded-xl px-4 py-3">
                <div class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">URL</div>
                <div class="text-sm text-gray-700 font-mono break-all">{{ $log->url }}</div>
            </div>
            @endif

            @if($log->user_agent)
            <div class="mt-3 bg-gray-50 rounded-xl px-4 py-3">
                <div class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">User Agent</div>
                <div class="text-xs text-gray-600 font-mono break-all">{{ $log->user_agent }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Actors --}}
    <div class="grid grid-cols-2 gap-5">
        {{-- Causer --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Performed By</div>
            @if($log->causer)
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 font-bold text-sm flex items-center justify-center flex-shrink-0">
                    {{ strtoupper(substr($log->causer->name, 0, 2)) }}
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-900">{{ $log->causer->name }}</div>
                    <div class="text-xs text-gray-500">{{ $log->causer->email }}</div>
                    <div class="text-xs text-gray-400 mt-0.5 capitalize">{{ $log->causer->role ?? 'user' }}</div>
                </div>
            </div>
            <a href="{{ route('admin.logs.index', ['user_id' => $log->causer_id]) }}"
               class="block mt-3 text-xs text-blue-600 hover:underline">
                View all logs for this user →
            </a>
            @elseif($log->causer_name)
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-500 font-bold text-sm flex items-center justify-center flex-shrink-0">
                    {{ strtoupper(substr($log->causer_name, 0, 1)) }}
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-700">{{ $log->causer_name }}</div>
                    <div class="text-xs text-gray-400 italic">(User no longer exists)</div>
                </div>
            </div>
            @else
            <div class="flex items-center gap-2 text-sm text-gray-400 italic">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                System action
            </div>
            @endif
        </div>

        {{-- Subject --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Acted Upon</div>
            @if($log->subject_type)
            <div>
                <div class="text-sm font-semibold text-gray-800">{{ $log->subject_label ?? '—' }}</div>
                <div class="text-xs text-gray-400 font-mono mt-1">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</div>
                <a href="{{ route('admin.logs.index', ['subject_type' => $log->subject_type, 'subject_id' => $log->subject_id]) }}"
                   class="block mt-3 text-xs text-blue-600 hover:underline">
                    View all logs for this record →
                </a>
            </div>
            @else
            <p class="text-sm text-gray-400 italic">No subject recorded</p>
            @endif
        </div>
    </div>

    {{-- Changed fields --}}
    @if(count($log->changed_fields))
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
            <h3 class="text-sm font-semibold text-gray-800">Changed Fields</h3>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                @foreach($log->changed_fields as $field => $change)
                <div class="flex items-start gap-4 py-2.5 border-b border-gray-50 last:border-0">
                    <div class="text-xs font-semibold text-gray-500 font-mono w-32 flex-shrink-0 pt-0.5">{{ $field }}</div>
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="flex-1 min-w-0">
                            <div class="text-xs text-gray-400 mb-1">Before</div>
                            <div class="text-xs font-mono text-gray-600 bg-red-50 border border-red-100 px-2.5 py-1.5 rounded-lg break-all">
                                {{ is_array($change['old']) ? json_encode($change['old']) : ($change['old'] ?? 'null') }}
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs text-gray-400 mb-1">After</div>
                            <div class="text-xs font-mono text-gray-800 bg-emerald-50 border border-emerald-100 px-2.5 py-1.5 rounded-lg break-all">
                                {{ is_array($change['new']) ? json_encode($change['new']) : ($change['new'] ?? 'null') }}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Raw properties --}}
    @if($log->properties)
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-800">Raw Properties</h3>
            <button onclick="document.getElementById('raw-json').classList.toggle('hidden')"
                    class="text-xs text-gray-400 hover:text-gray-600 transition-colors">Toggle</button>
        </div>
        <div id="raw-json" class="p-6">
            <pre class="text-xs font-mono text-gray-700 bg-gray-50 border border-gray-100 rounded-xl p-4 overflow-x-auto leading-relaxed">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    </div>
    @endif
</div>

@endsection