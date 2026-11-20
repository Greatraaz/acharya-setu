@extends('admin.layouts.app')
@section('title', 'Stream progress')
@section('heading', 'Stream progress')

@section('content')
@include('admin.partials.learning-ui')

<div class="learn-page space-y-5">
    <div class="learn-intro">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="learn-eyebrow">Learning · Progress</span>
                <h2>{{ $stream->name }}</h2>
                <p>Enrollment and month structure for this journey stream.</p>
            </div>
            <a href="{{ route('admin.curriculum.months', $stream) }}" class="learn-btn-primary self-start">Manage months</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="learn-panel p-5">
            <h3 class="font-bold text-slate-900 mb-3">Enrolled mentees ({{ $enrollments->count() }})</h3>
            <div class="space-y-2">
                @forelse($enrollments as $enrollment)
                <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 px-3 py-2.5">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">{{ $enrollment->mentee->name ?? '—' }}</div>
                        <div class="text-xs text-slate-400">{{ ucfirst($enrollment->status ?? '') }} · M{{ $enrollment->current_month ?? 1 }}/W{{ $enrollment->current_week ?? 1 }}</div>
                    </div>
                    <a href="{{ route('admin.curriculum.enrollments.show', $enrollment) }}" class="text-xs font-semibold text-amber-700">View</a>
                </div>
                @empty
                <p class="text-sm text-slate-400">No enrollments on this stream.</p>
                @endforelse
            </div>
        </div>

        <div class="learn-panel p-5">
            <h3 class="font-bold text-slate-900 mb-3">Months ({{ $months->count() }})</h3>
            <div class="space-y-2">
                @forelse($months as $month)
                <div class="rounded-xl border border-slate-100 px-3 py-2.5">
                    <div class="text-sm font-semibold text-slate-900">Month {{ $month->month_number }} · {{ $month->title }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">
                        {{ $month->weeks->count() }} weeks ·
                        {{ $month->weeks->sum(fn ($w) => $w->tasks->count()) }} tasks ·
                        {{ $month->weeks->sum(fn ($w) => $w->mcqs->count()) }} MCQs
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400">No months built yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
