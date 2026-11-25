@extends('frontend.layouts.app')
@section('title', 'My Tasks — Vedrix')

@section('content')
@include('frontend.partials.tasks-premium-ui')

@php
    $summary = $summary ?? ['total' => 0, 'todo' => 0, 'completed' => 0, 'awaiting' => 0];
    $openCount = max(0, (int) $summary['todo']);
@endphp

<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-page tasks-premium" style="display:flex;flex-direction:column;gap:1.15rem;">

            <div class="learn-intro fu">
                <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-start;justify-content:space-between;">
                    <div>
                        <span class="learn-eyebrow">Learning · Assignments</span>
                        <h2>My Tasks</h2>
                        <p>Mentor and admin assignments outside your curriculum — submit work, track reviews, stay on top of deadlines.</p>
                    </div>
                </div>
            </div>

            <div class="tp-hero fu">
                <div class="tp-hero__kicker">Your assignment board</div>
                <h3 class="tp-hero__title">
                    @if($openCount > 0)
                        {{ $openCount }} open task{{ $openCount === 1 ? '' : 's' }} waiting
                    @elseif(($summary['awaiting'] ?? 0) > 0)
                        {{ $summary['awaiting'] }} under mentor review
                    @else
                        You’re all caught up
                    @endif
                </h3>
                <p class="tp-hero__sub">Filter by status, search by title or mentor, and open a task to submit your work.</p>
            </div>

            <div class="dash-grid dash-grid-4 fu">
                <div class="learn-stat">
                    <div style="display:flex;justify-content:space-between;gap:.75rem;">
                        <div>
                            <div class="learn-stat__value">{{ (int) $summary['total'] }}</div>
                            <div class="learn-stat__label">Total tasks</div>
                        </div>
                        <span class="learn-stat__icon">📋</span>
                    </div>
                </div>
                <div class="learn-stat">
                    <div style="display:flex;justify-content:space-between;gap:.75rem;">
                        <div>
                            <div class="learn-stat__value" style="color:var(--ln-dark);">{{ (int) $summary['todo'] }}</div>
                            <div class="learn-stat__label">To do</div>
                        </div>
                        <span class="learn-stat__icon">⏳</span>
                    </div>
                </div>
                <div class="learn-stat">
                    <div style="display:flex;justify-content:space-between;gap:.75rem;">
                        <div>
                            <div class="learn-stat__value">{{ (int) ($summary['awaiting'] ?? 0) }}</div>
                            <div class="learn-stat__label">Awaiting review</div>
                        </div>
                        <span class="learn-stat__icon">👀</span>
                    </div>
                </div>
                <div class="learn-stat">
                    <div style="display:flex;justify-content:space-between;gap:.75rem;">
                        <div>
                            <div class="learn-stat__value">{{ (int) $summary['completed'] }}</div>
                            <div class="learn-stat__label">Completed</div>
                        </div>
                        <span class="learn-stat__icon">✅</span>
                    </div>
                </div>
            </div>

            @if(session('success'))
            <div class="alert-premium fu" style="border-color:rgba(34,197,94,.3);background:rgba(34,197,94,.1);">
                <span>✓</span>
                <div><p>{{ session('success') }}</p></div>
            </div>
            @endif

            <form method="GET" action="{{ route('mentee.tasks.index') }}" class="learn-filter-bar fu">
                <div class="session-filter-tabs">
                    @foreach(['all' => 'All', 'todo' => 'To do', 'completed' => 'Completed'] as $key => $label)
                        @php
                            $tabParams = array_filter([
                                'status' => $key === 'all' ? null : $key,
                                'search' => $search ?: null,
                            ]);
                            $count = match ($key) {
                                'todo' => (int) $summary['todo'],
                                'completed' => (int) $summary['completed'],
                                default => (int) $summary['total'],
                            };
                        @endphp
                        <a href="{{ route('mentee.tasks.index', $tabParams) }}"
                           class="session-filter-tab {{ $status === $key ? 'active' : '' }}">
                            {{ $label }}
                            <span style="opacity:.8;margin-left:.15rem;">({{ $count }})</span>
                        </a>
                    @endforeach
                </div>
                <div class="learn-search">
                    @if($status !== 'all')
                        <input type="hidden" name="status" value="{{ $status }}">
                    @endif
                    <div class="session-search-field">
                        <span class="session-search-icon" aria-hidden="true">🔍</span>
                        <input type="search" name="search" class="form-input" value="{{ $search }}"
                               placeholder="Search task or mentor…" autocomplete="off" aria-label="Search tasks">
                    </div>
                    <button type="submit" class="learn-btn-ghost">Search</button>
                    @if($search !== '')
                        <a href="{{ route('mentee.tasks.index', array_filter(['status' => $status === 'all' ? null : $status])) }}" class="learn-btn-ghost">Clear</a>
                    @endif
                </div>
            </form>

            <div class="learn-list fu">
                @forelse($items as $task)
                @php
                    $uiStatus = $task->uiStatus($task->progress);
                    $statusLabel = match ($uiStatus) {
                        'awaiting_review' => 'Awaiting review',
                        'completed' => 'Completed',
                        'rejected' => 'Changes requested',
                        default => 'To do',
                    };
                    $chipClass = match ($uiStatus) {
                        'completed' => 'learn-chip--done',
                        'awaiting_review' => 'learn-chip--wait',
                        'rejected' => 'learn-chip--error',
                        default => 'learn-chip--todo',
                    };
                    $desc = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($task->description ?? ''))));
                    $isOverdue = $task->due_at && $task->due_at->isPast() && ! in_array($uiStatus, ['completed', 'awaiting_review'], true);
                @endphp
                <article class="tp-card">
                    <span class="tp-card__icon" aria-hidden="true">{{ \App\Models\MenteeTask::TYPE_ICONS[$task->type] ?? '✅' }}</span>
                    <div style="min-width:0;">
                        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;">
                            <h3 class="tp-card__title">{{ $task->title }}</h3>
                            <span class="learn-chip {{ $chipClass }}">{{ $statusLabel }}</span>
                        </div>
                        @if($desc !== '')
                            <p class="tp-card__desc">{{ \Illuminate\Support\Str::limit($desc, 150) }}</p>
                        @endif
                        <div class="tp-card__meta">
                            <span>👤 {{ $task->mentor->name ?? 'Mentor' }}</span>
                            <span>{{ \App\Models\MenteeTask::TYPES[$task->type] ?? ucfirst((string) $task->type) }}</span>
                            <span>{{ \App\Models\MenteeTask::SUBMISSION_TYPES[$task->submission_type] ?? $task->submission_type }}</span>
                            @if($task->due_at)
                                <span class="tp-due {{ $isOverdue ? 'is-overdue' : '' }}">
                                    {{ $isOverdue ? 'Overdue' : 'Due' }} {{ $task->due_at->format('d M Y') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="tp-card__cta">
                        <a href="{{ route('mentee.tasks.show', $task) }}"
                           class="{{ $uiStatus === 'completed' ? 'learn-btn-ghost' : 'learn-btn-primary' }}">
                            {{ $uiStatus === 'completed' ? 'View' : ($uiStatus === 'awaiting_review' ? 'Track' : 'Open') }}
                        </a>
                    </div>
                </article>
                @empty
                <div class="learn-panel empty-block">
                    <div class="icon">✅</div>
                    <div style="font-family:var(--font-head);font-weight:800;font-size:1.05rem;color:var(--text);margin-bottom:.3rem;">No tasks found</div>
                    <p>Try adjusting your filters, or check back when your mentor assigns a new task.</p>
                </div>
                @endforelse
            </div>

            @include('frontend.partials.pagination', ['paginator' => $items])
        </div>
    </div>
</div>
@endsection
