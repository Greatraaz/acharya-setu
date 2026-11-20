@extends('admin.layouts.app')
@section('title', 'Enrollment details')
@section('heading', 'Enrollment details')

@section('content')
@include('admin.partials.learning-ui')

<div class="learn-page space-y-5 max-w-4xl">
    <a href="{{ route('admin.curriculum.enrollments.index') }}" class="text-sm text-slate-400 hover:text-slate-700">← Back to enrollments</a>

    <div class="learn-intro">
        <span class="learn-eyebrow">{{ ucfirst($enrollment->status ?? 'enrollment') }}</span>
        <h2>{{ $enrollment->stream->name ?? 'Stream' }}</h2>
        <p>
            {{ $enrollment->mentee->name ?? 'Mentee' }}
            @if($enrollment->mentor)
                · Mentor {{ $enrollment->mentor->name }}
            @endif
        </p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([
            ['Overall', ($progress['percent'] ?? $progress['percentage'] ?? 0).'%'],
            ['Month', 'M'.($enrollment->current_month ?? 1)],
            ['Week', 'W'.($enrollment->current_week ?? 1)],
            ['Tasks done', ($progress['completed_tasks'] ?? $progress['tasks_completed'] ?? 0)],
        ] as [$label, $value])
        <div class="learn-stat">
            <div class="learn-stat__value">{{ $value }}</div>
            <div class="learn-stat__label">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    @if($enrollment->stream)
    <div class="learn-panel p-5">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="font-bold text-slate-900">Curriculum outline</h3>
            <a href="{{ route('admin.curriculum.months', $enrollment->stream) }}" class="learn-btn-ghost text-sm">Manage months</a>
        </div>
        <div class="space-y-3">
            @forelse($enrollment->stream->months ?? [] as $month)
            <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                <div class="font-semibold text-slate-900">Month {{ $month->month_number }} · {{ $month->title }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ $month->weeks->count() }} weeks · {{ $month->weeks->sum(fn ($w) => $w->tasks->count()) }} tasks</div>
            </div>
            @empty
            <p class="text-sm text-slate-400">No months on this stream yet.</p>
            @endforelse
        </div>
    </div>
    @endif
</div>
@endsection
