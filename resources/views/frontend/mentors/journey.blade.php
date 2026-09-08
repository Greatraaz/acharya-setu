@extends('frontend.layouts.app')
@section('title', 'Progress — Vedrix Mentor')

@section('content')
<style>
    .mentor-hub-tabs { margin-bottom: 18px; }
    .mentor-journey-list { display: grid; gap: 12px; }
    .mentor-journey-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 16px;
        align-items: center;
        padding: 16px 18px;
    }
    .mentor-journey-card__meta { font-size: 12px; color: var(--text-2); margin-top: 4px; }
    .mentor-journey-card__progress { margin-top: 12px; max-width: 420px; }
    .mentor-journey-card__actions { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
    .mentor-review-card { margin-bottom: 14px; border: 1px solid rgba(245, 158, 11, .28); }
    .mentor-review-card__head { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 12px; }
    .mentor-review-card__eyebrow { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--text-3); margin-bottom: 4px; }
    .mentor-review-card__title { font-size: 15px; font-weight: 700; }
    .mentor-review-card__meta { font-size: 13px; color: var(--text-2); margin-top: 4px; }
    .mentor-review-card__body {
        padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm);
        margin-bottom: 12px; font-size: 13px; color: var(--text-2); white-space: pre-wrap;
        background: var(--bg);
    }
    .mentor-review-card__hint { font-size: 13px; color: var(--text-2); margin: 0 0 12px; }
    .mentor-review-card__form { display: grid; gap: 10px; }
    .mentor-review-card__actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .mentor-mcq-options { display: grid; gap: 8px; margin: 0 0 12px; }
    .mentor-mcq-option {
        padding: 10px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border);
        font-size: 13px; color: var(--text-2); background: var(--bg);
        display: flex; flex-wrap: wrap; gap: 8px; align-items: center;
    }
    .mentor-mcq-option.is-selected { border-color: var(--brand); color: var(--text); background: var(--brand-muted); }
    .mentor-mcq-option.is-correct { border-color: var(--success); }
    .mentor-mcq-tag {
        font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
        padding: 2px 6px; border-radius: 999px; background: var(--bg-3); color: var(--text-2);
    }
    .mentor-mcq-tag--ok { background: color-mix(in srgb, var(--success) 18%, transparent); color: var(--success); }
    @media (max-width: 768px) {
        .mentor-journey-card { grid-template-columns: 1fr; }
        .mentor-journey-card__actions { justify-content: stretch; }
        .mentor-journey-card__actions .btn { flex: 1; justify-content: center; }
        .mentor-journey-card__progress { max-width: none; }
    }
</style>

@php
    $tab = $tab ?? 'progress';
    $pendingCount = (int) ($pendingCount ?? 0);
@endphp

<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between" style="flex-wrap:wrap;gap:12px;">
            <div>
                <div class="dash-title">Progress</div>
                <div class="dash-subtitle">Review mentee work, then track how their 6‑month journey is moving.</div>
            </div>
            <a href="{{ route('mentor.curriculum.tracks') }}" class="btn btn-outline btn-sm">Edit curriculum</a>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">
            <span class="alert-icon">✅</span>
            <div>{{ session('success') }}</div>
        </div>
        @endif

        <div class="session-filter-tabs mentor-hub-tabs">
            <a href="{{ route('mentor.journey', ['tab' => 'reviews']) }}"
               class="session-filter-tab {{ $tab === 'reviews' ? 'active' : '' }}">
                Needs review
                @if($pendingCount > 0)
                    <span class="si-badge" style="margin-left:6px;">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('mentor.journey', array_filter(['tab' => 'progress', 'status' => ($status ?? null) ?: null, 'search' => ($search ?? null) ?: null])) }}"
               class="session-filter-tab {{ $tab === 'progress' ? 'active' : '' }}">
                Mentee progress
            </a>
        </div>

        @if($tab === 'reviews')
            @if(!empty($menteeFilter))
            <div class="alert alert-info" style="margin-bottom:16px;">
                <span class="alert-icon">ℹ️</span>
                <div style="font-size:13px;">
                    Showing reviews for one mentee.
                    <a href="{{ route('mentor.journey', ['tab' => 'reviews']) }}" style="color:var(--brand);">Clear filter</a>
                </div>
            </div>
            @endif

            @forelse($pendingSubmissions ?? [] as $row)
                @include('frontend.mentors.partials.review-card', ['row' => $row, 'showMentee' => true])
            @empty
            <div class="empty-state card" style="padding:56px 20px;">
                <div style="font-size:40px;margin-bottom:12px;">✅</div>
                <div style="font-size:17px;font-weight:700;margin-bottom:8px;">You’re all caught up</div>
                <p style="font-size:14px;color:var(--text-2);max-width:380px;margin:0 auto 18px;">
                    No tasks or MCQs waiting. When mentees submit work, it appears in this tab first.
                </p>
                <a href="{{ route('mentor.journey', ['tab' => 'progress']) }}" class="btn btn-primary">View mentee progress</a>
            </div>
            @endforelse

            @if($reviewPaginator)
                @include('frontend.partials.pagination', ['paginator' => $reviewPaginator])
            @endif
        @else
            <form method="GET" action="{{ route('mentor.journey') }}" class="session-toolbar" style="margin-bottom:16px;">
                <input type="hidden" name="tab" value="progress">
                <div class="session-filter-tabs">
                    @foreach(['' => 'All', 'active' => 'Active', 'completed' => 'Completed', 'paused' => 'Paused'] as $key => $label)
                        @php
                            $tabParams = array_filter([
                                'tab' => 'progress',
                                'status' => $key ?: null,
                                'search' => ($search ?? request('search')) ?: null,
                            ]);
                        @endphp
                        <a href="{{ route('mentor.journey', $tabParams) }}"
                           class="session-filter-tab {{ ($status ?? request('status', '')) === $key ? 'active' : '' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
                <div class="session-toolbar-controls">
                    @if(($status ?? request('status')))
                        <input type="hidden" name="status" value="{{ $status ?? request('status') }}">
                    @endif
                    <div class="session-search-field">
                        <span class="session-search-icon" aria-hidden="true">🔍</span>
                        <input type="search" name="search" class="form-input" value="{{ $search ?? request('search') }}"
                               placeholder="Search mentee name…" autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-outline">Search</button>
                </div>
            </form>

            <div class="mentor-journey-list">
            @forelse($enrollments ?? [] as $enrollment)
            @php
                $progress = $enrollment->progress_data ?? [];
                $pending = (int) ($enrollment->pending_reviews ?? 0);
            @endphp
            <div class="card mentor-journey-card">
                <div>
                    <div style="font-size:15px;font-weight:700;">{{ $enrollment->mentee->name ?? 'Mentee' }}</div>
                    <div class="mentor-journey-card__meta">
                        {{ $enrollment->stream->name ?? 'Stream' }}
                        · {{ ucfirst($enrollment->status) }}
                        · Month {{ $enrollment->current_month }} · Week {{ $enrollment->current_week }}
                        @if($pending > 0)
                            · <span style="color:var(--brand);font-weight:700;">{{ $pending }} needs review</span>
                        @endif
                    </div>
                    <div class="mentor-journey-card__progress">
                        <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-2);margin-bottom:6px;">
                            <span>Overall progress</span>
                            <span>{{ (int) ($progress['percent'] ?? $progress['percentage'] ?? 0) }}%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:{{ (int) ($progress['percent'] ?? $progress['percentage'] ?? 0) }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="mentor-journey-card__actions">
                    @if($pending > 0)
                    <a href="{{ route('mentor.journey.show', $enrollment->mentee_id) }}#review-list" class="btn btn-primary btn-sm">Review ({{ $pending }})</a>
                    @endif
                    <a href="{{ route('mentor.journey.show', $enrollment->mentee_id) }}" class="btn btn-outline btn-sm">Open journey</a>
                </div>
            </div>
            @empty
            <div class="empty-state" style="padding:48px 0;">
                <div style="font-size:48px;margin-bottom:12px;">🗺️</div>
                <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No mentee journeys yet</div>
                <p style="font-size:13px;color:var(--text-2);max-width:380px;margin:0 auto 16px;">
                    Assign a curriculum track to a mentee, then track and review their work here.
                </p>
                <a href="{{ route('mentor.curriculum.tracks') }}" class="btn btn-primary">Go to curriculum</a>
            </div>
            @endforelse
            </div>

            @if($enrollments)
                @include('frontend.partials.pagination', ['paginator' => $enrollments])
            @endif

            @if(($menteesWithoutEnrollment ?? collect())->isNotEmpty())
            <div class="card" style="margin-top:20px;">
                <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;">Mentees without a curriculum</h3>
                @foreach($menteesWithoutEnrollment as $mentee)
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span>{{ $mentee->name }}</span>
                    <div style="display:flex;gap:6px;">
                        <a href="{{ route('mentor.curriculum.tracks', ['mentee_id' => $mentee->id]) }}" class="btn btn-outline btn-sm">Create track</a>
                        <a href="{{ route('mentor.mentees.show', $mentee->id) }}" class="btn btn-ghost btn-sm">Profile</a>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        @endif
    </div>
</div>
@endsection
