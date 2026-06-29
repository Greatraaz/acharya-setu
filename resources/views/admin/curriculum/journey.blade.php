@extends('admin.layouts.app')
@section('title', 'My Learning Journey')
@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --stream-color: {{ $stream->color ?: '#7c3aed' }};
        --stream-color-light: {{ $stream->color ?: '#7c3aed' }}18;
    }
    body { font-family: 'DM Sans', sans-serif; background: #f8f7ff; }
    .display-font { font-family: 'DM Serif Display', serif; }

    /* Progress ring */
    .ring-svg circle { transition: stroke-dashoffset 1s ease; }

    /* Month card */
    .month-card { transition: transform .2s ease, box-shadow .2s ease; }
    .month-card:hover { transform: translateY(-2px); box-shadow: 0 12px 40px rgba(0,0,0,.08); }

    /* Path connector */
    .path-line { position: absolute; left: 50%; top: 100%; width: 2px; height: 28px; background: linear-gradient(to bottom, var(--stream-color), transparent); transform: translateX(-50%); }

    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }
    .float-anim { animation: float 3s ease-in-out infinite; }
</style>

<div class="min-h-screen" style="background: #f8f7ff;">

    {{-- Hero Header --}}
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, {{ $stream->color ?: '#7c3aed' }}, {{ $stream->color ?: '#4c1d95' }});">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px), radial-gradient(circle at 80% 20%, white 1px, transparent 1px); background-size: 40px 40px;"></div>
        <div class="relative max-w-5xl mx-auto px-6 py-12">
            <div class="flex items-start justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2 text-white/70 text-sm mb-3">
                        <span class="text-2xl">{{ $stream->icon ?: '🎓' }}</span>
                        <span>{{ $stream->name }}</span>
                    </div>
                    <h1 class="display-font text-4xl text-white mb-2">Your 6-Month Journey</h1>
                    <p class="text-white/70 text-sm max-w-md">
                        Started {{ $enrollment->start_date->format('d M Y') }} ·
                        {{ $enrollment->days_remaining }} days remaining
                        @if($enrollment->mentor)
                        · Guided by <strong class="text-white">{{ $enrollment->mentor->name }}</strong>
                        @endif
                    </p>
                </div>

                {{-- Overall progress ring --}}
                <div class="flex-shrink-0 flex flex-col items-center">
                    <div class="relative w-24 h-24">
                        <svg class="ring-svg w-24 h-24 -rotate-90" viewBox="0 0 96 96">
                            <circle cx="48" cy="48" r="40" fill="none" stroke="rgba(255,255,255,.2)" stroke-width="8"/>
                            <circle cx="48" cy="48" r="40" fill="none" stroke="white" stroke-width="8"
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ 2 * pi() * 40 }}"
                                    stroke-dashoffset="{{ 2 * pi() * 40 * (1 - $progress['percent'] / 100) }}"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-xl font-bold text-white">{{ $progress['percent'] }}%</span>
                            <span class="text-xs text-white/70">done</span>
                        </div>
                    </div>
                    <div class="text-center mt-2">
                        <div class="text-white text-sm font-semibold">{{ $progress['completed'] }}/{{ $progress['total'] }}</div>
                        <div class="text-white/60 text-xs">items completed</div>
                    </div>
                </div>
            </div>

            {{-- Month progress strip --}}
            <div class="mt-8 grid grid-cols-6 gap-2">
                @foreach($monthProgress as $mp)
                @php $m = $mp['month']; $pct = $mp['percent']; @endphp
                <div class="text-center">
                    <div class="text-white/60 text-xs mb-1.5">M{{ $m->month_number }}</div>
                    <div class="h-1.5 bg-white/20 rounded-full overflow-hidden">
                        <div class="h-full bg-white rounded-full transition-all duration-1000" style="width:{{ $pct }}%"></div>
                    </div>
                    <div class="text-white/70 text-xs mt-1">{{ $pct }}%</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Journey Cards --}}
    <div class="max-w-5xl mx-auto px-6 py-10">

        @if(session('success'))
        <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl mb-6">✓ {{ session('success') }}</div>
        @endif

        <h2 class="display-font text-2xl text-gray-800 mb-6">The Journey</h2>

        <div class="space-y-4">
            @foreach($monthProgress as $idx => $mp)
            @php
            $m   = $mp['month'];
            $pct = $mp['percent'];
            $isLocked = $m->month_number > $enrollment->current_month + 1;
            $isCurrent = $m->month_number === (int) $enrollment->current_month;
            $isDone = $pct === 100;
            @endphp

            <div class="month-card relative bg-white border rounded-2xl overflow-hidden
                {{ $isLocked ? 'opacity-50' : '' }}
                {{ $isCurrent ? 'border-[var(--stream-color)] shadow-lg ring-1 ring-[var(--stream-color)]/20' : 'border-gray-200' }}">

                {{-- Top accent bar --}}
                @if(!$isLocked)
                <div class="h-1 w-full" style="background: {{ $isDone ? '#22c55e' : ($isCurrent ? ($stream->color ?: '#7c3aed') : '#e5e7eb') }};
                     background: {{ !$isDone && !$isCurrent ? 'linear-gradient(to right, '.($stream->color ?: '#7c3aed').' '.$pct.'%, #e5e7eb '.$pct.'%)' : '' }};"></div>
                @endif

                <div class="flex items-start gap-5 p-6">
                    {{-- Month number badge --}}
                    <div class="flex-shrink-0">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-xl font-bold shadow-sm relative
                            {{ $isLocked ? 'bg-gray-100 text-gray-400' : ($isDone ? 'text-white' : 'text-white') }}"
                             style="{{ !$isLocked ? 'background:'.($stream->color ?: '#7c3aed') : '' }};">
                            @if($isLocked)
                            🔒
                            @elseif($isDone)
                            ✅
                            @else
                            {{ $m->month_number }}
                            @endif
                        </div>
                        @if($isCurrent)
                        <div class="text-center mt-1.5">
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full text-white" style="background:var(--stream-color);">NOW</span>
                        </div>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <h3 class="font-bold text-gray-900">Month {{ $m->month_number }}: {{ $m->title }}</h3>
                                    @if($m->theme)
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:var(--stream-color-light);color:var(--stream-color);">{{ $m->theme }}</span>
                                    @endif
                                    @if($m->milestone_badge && $isDone)
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">🏅 {{ $m->milestone_badge }}</span>
                                    @endif
                                </div>
                                @if($m->description)
                                <p class="text-sm text-gray-500 leading-relaxed">{{ $m->description }}</p>
                                @endif
                            </div>
                            <div class="flex-shrink-0 text-right">
                                <div class="text-2xl font-bold text-gray-800">{{ $pct }}<span class="text-sm font-normal text-gray-400">%</span></div>
                                <div class="text-xs text-gray-400">{{ $mp['completed'] }}/{{ $mp['total'] }} items</div>
                            </div>
                        </div>

                        {{-- Progress bar --}}
                        @if(!$isLocked && $mp['total'] > 0)
                        <div class="mt-3 h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700"
                                 style="width:{{ $pct }}%;background:{{ $isDone ? '#22c55e' : ($stream->color ?: '#7c3aed') }};"></div>
                        </div>
                        @endif

                        {{-- Learning outcomes --}}
                        @if($m->learning_outcomes && count($m->learning_outcomes) && !$isLocked)
                        <div class="flex flex-wrap gap-1.5 mt-3">
                            @foreach(array_slice($m->learning_outcomes, 0, 4) as $outcome)
                            <span class="text-xs text-gray-600 bg-gray-50 border border-gray-200 px-2.5 py-0.5 rounded-full">{{ $outcome }}</span>
                            @endforeach
                        </div>
                        @endif

                        {{-- Week pills --}}
                        @if(!$isLocked && $m->weeks->isNotEmpty())
                        <div class="flex flex-wrap gap-2 mt-4">
                            @foreach($m->weeks as $week)
                            @php $wp = $week->getProgressForUser(auth()->id()); @endphp
                            <a href="{{ route('mentee.journey.week', $week) }}"
                               class="group inline-flex items-center gap-1.5 text-xs font-medium border px-3 py-1.5 rounded-xl transition-all
                               {{ $wp['percent'] === 100 ? 'bg-green-50 border-green-200 text-green-700' : 'bg-gray-50 border-gray-200 text-gray-600 hover:border-[var(--stream-color)] hover:bg-[var(--stream-color-light)] hover:text-gray-800' }}">
                                @if($wp['percent'] === 100)
                                <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                                @else
                                <span class="w-4 h-4 rounded-full text-center leading-4 text-white text-xs flex-shrink-0"
                                      style="background:var(--stream-color);font-size:9px;">{{ $week->week_number }}</span>
                                @endif
                                Week {{ $week->week_number }}: {{ Str::limit($week->title, 18) }}
                                <span class="text-gray-400">({{ $wp['completed'] }}/{{ $wp['total'] }})</span>
                            </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                {{-- CTA footer --}}
                @if(!$isLocked)
                <div class="flex items-center justify-between px-6 py-3 border-t border-gray-100 bg-gray-50">
                    <span class="text-xs text-gray-500">
                        {{ $m->weeks->count() }} weeks · {{ $mp['total'] }} items total
                    </span>
                    <a href="{{ route('mentee.journey.month', $m) }}"
                       class="text-xs font-semibold px-4 py-1.5 rounded-lg text-white transition-colors hover:opacity-90"
                       style="background:var(--stream-color);">
                        {{ $pct === 100 ? 'Review' : ($pct > 0 ? 'Continue →' : 'Start →') }}
                    </a>
                </div>
                @else
                <div class="px-6 py-3 border-t border-gray-100 bg-gray-50">
                    <span class="text-xs text-gray-400">🔒 Complete previous months to unlock</span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection