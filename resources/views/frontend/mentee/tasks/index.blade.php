@extends('frontend.layouts.app')
@section('title', 'My Tasks — Vedrix')

@section('content')
<style>
    .mentee-task-toolbar {
        flex-wrap: nowrap;
        align-items: center;
        gap: 12px;
    }
    .mentee-task-toolbar .session-filter-tabs {
        flex-wrap: nowrap;
        flex: 0 0 auto;
    }
    .mentee-task-toolbar .mentee-assess-toolbar__search {
        flex-wrap: nowrap;
        margin-left: auto;
    }
    .mentee-task-card__title-row { display: flex; align-items: flex-start; gap: 10px; }
    .mentee-task-card__icon { font-size: 18px; line-height: 1.3; flex-shrink: 0; }
    .mentee-task-card__status.is-wait { color: var(--warning, #f59e0b); }
    .mentee-task-card__status.is-error { color: var(--error); }
    @media (max-width: 768px) {
        .mentee-task-toolbar { flex-wrap: wrap; }
        .mentee-task-toolbar .mentee-assess-toolbar__search { margin-left: 0; width: 100%; }
    }
</style>

<div class="dash-layout mentee-assess-page">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">My Tasks</div>
            <div class="dash-subtitle">Tasks assigned by your mentor or Vedrix admin (outside curriculum).</div>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">
            <span class="alert-icon">✓</span>
            <div style="font-size:13px;">{{ session('success') }}</div>
        </div>
        @endif

        <form method="GET" action="{{ route('mentee.tasks.index') }}" class="mentee-assess-toolbar mentee-task-toolbar">
            <div class="session-filter-tabs mentee-assess-toolbar__tabs">
                @foreach(['all' => 'All', 'todo' => 'To do', 'completed' => 'Completed'] as $key => $label)
                    @php
                        $tabParams = array_filter([
                            'status' => $key === 'all' ? null : $key,
                            'search' => $search ?: null,
                        ]);
                    @endphp
                    <a href="{{ route('mentee.tasks.index', $tabParams) }}"
                       class="session-filter-tab {{ $status === $key ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <div class="mentee-assess-toolbar__search">
                @if($status !== 'all')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <div class="session-search-field mentee-assess-toolbar__field">
                    <span class="session-search-icon" aria-hidden="true">🔍</span>
                    <input type="search" name="search" class="form-input" value="{{ $search }}"
                           placeholder="Search task or mentor…" autocomplete="off" aria-label="Search tasks">
                </div>
                <button type="submit" class="btn btn-outline mentee-assess-toolbar__btn">Search</button>
                @if($search !== '')
                    <a href="{{ route('mentee.tasks.index', array_filter(['status' => $status === 'all' ? null : $status])) }}" class="btn btn-ghost mentee-assess-toolbar__btn">Clear</a>
                @endif
            </div>
        </form>

        <div class="mentee-assess-list">
            @forelse($items as $task)
            @php
                $uiStatus = $task->uiStatus($task->progress);
                $statusLabel = match ($uiStatus) {
                    'awaiting_review' => 'Awaiting review',
                    'completed' => 'Completed',
                    'rejected' => 'Changes requested',
                    default => 'To do',
                };
                $statusClass = match ($uiStatus) {
                    'completed' => 'is-done',
                    'awaiting_review' => 'is-wait',
                    'rejected' => 'is-error',
                    default => 'is-todo',
                };
                $desc = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($task->description ?? ''))));
            @endphp
            <article class="mentee-assess-card">
                <div class="mentee-assess-card__body">
                    <div class="mentee-task-card__title-row">
                        <span class="mentee-task-card__icon" aria-hidden="true">{{ \App\Models\MenteeTask::TYPE_ICONS[$task->type] ?? '✅' }}</span>
                        <h3 class="mentee-assess-card__title">{{ $task->title }}</h3>
                    </div>
                    @if($desc !== '')
                    <p class="mentee-assess-card__desc">{{ $desc }}</p>
                    @endif
                    <div class="mentee-assess-card__meta">
                        <span>{{ $task->mentor->name ?? 'Mentor' }}</span>
                        <span>{{ \App\Models\MenteeTask::TYPES[$task->type] ?? ucfirst((string) $task->type) }}</span>
                        <span>{{ \App\Models\MenteeTask::SUBMISSION_TYPES[$task->submission_type] ?? $task->submission_type }}</span>
                        @if($task->due_at)
                            <span>Due {{ $task->due_at->format('d M Y') }}</span>
                        @endif
                        <span class="mentee-assess-card__status mentee-task-card__status {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                </div>
                <a href="{{ route('mentee.tasks.show', $task) }}"
                   class="btn {{ $uiStatus === 'completed' ? 'btn-outline' : 'btn-primary' }} btn-sm mentee-assess-card__cta">
                    {{ $uiStatus === 'completed' ? 'View' : 'Open' }}
                </a>
            </article>
            @empty
            <div class="empty-state" style="padding:60px 0;">
                <div style="font-size:48px;margin-bottom:12px;">✅</div>
                <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:8px;">No tasks found</div>
                <p style="font-size:13px;color:var(--text-2);max-width:380px;margin:0 auto;">
                    Try adjusting your filters, or check back when your mentor assigns a new task.
                </p>
            </div>
            @endforelse
        </div>

        @include('frontend.partials.pagination', ['paginator' => $items])
    </div>
</div>
@endsection
