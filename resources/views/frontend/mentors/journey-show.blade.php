@extends('frontend.layouts.app')
@section('title', 'Journey — '.$mentee->name)

@section('content')
@php
    $optionText = function ($option) {
        if (is_array($option)) {
            return (string) ($option['text'] ?? json_encode($option));
        }
        return (string) $option;
    };
@endphp
<style>
    .mentor-progress-page .dash-header__actions { flex-wrap: wrap; gap: 8px; }
    .mentor-progress-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }
    .mentor-progress-stat {
        padding: 14px 16px;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        background: var(--bg-2);
    }
    .mentor-progress-stat__label { font-size: 11px; color: var(--text-3); text-transform: uppercase; letter-spacing: .06em; font-weight: 700; }
    .mentor-progress-stat__value { font-size: 22px; font-weight: 800; margin-top: 6px; }
    .mentor-progress-layout {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .mentor-progress-stack { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
    .mentor-review-card { border: 1px solid rgba(245, 158, 11, .35); background: color-mix(in srgb, var(--brand) 6%, var(--bg-2)); margin-bottom: 12px; }
    .mentor-review-card__head { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 12px; }
    .mentor-review-card__eyebrow { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--text-3); margin-bottom: 4px; }
    .mentor-review-card__title { font-size: 14px; font-weight: 700; }
    .mentor-review-card__meta { font-size: 13px; color: var(--text-2); margin-top: 4px; }
    .mentor-review-card__body {
        padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm);
        margin-bottom: 12px; font-size: 13px; color: var(--text-2); white-space: pre-wrap; background: var(--bg);
    }
    .mentor-review-card__hint { font-size: 13px; color: var(--text-2); margin: 0 0 12px; }
    .mentor-review-card__form { display: grid; gap: 10px; }
    .mentor-review-card__actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .mentor-mcq-options { display: grid; gap: 8px; margin: 12px 0; }
    .mentor-mcq-option {
        padding: 10px 12px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
        font-size: 13px;
        color: var(--text-2);
        background: var(--bg);
        display: flex; flex-wrap: wrap; gap: 8px; align-items: center;
    }
    .mentor-mcq-option.is-selected { border-color: var(--brand); color: var(--text); background: var(--brand-muted); }
    .mentor-mcq-option.is-correct { border-color: var(--success); }
    .mentor-mcq-tag {
        font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
        padding: 2px 6px; border-radius: 999px; background: var(--bg-3); color: var(--text-2);
    }
    .mentor-mcq-tag--ok { background: color-mix(in srgb, var(--success) 18%, transparent); color: var(--success); }
    .mentor-week-block { border: 1px solid var(--border); border-radius: var(--radius); padding: 14px; margin-bottom: 12px; background: var(--bg); }
    .mentor-week-block:last-child { margin-bottom: 0; }
    .mentor-item-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
        font-size: 13px;
    }
    .mentor-item-row:last-child { border-bottom: none; padding-bottom: 0; }
    .mentor-item-row:first-child { padding-top: 0; }
    .mentor-track-card details > summary {
        list-style: none;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }
    .mentor-track-card details > summary::-webkit-details-marker { display: none; }
    @media (max-width: 1024px) {
        .mentor-progress-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
        .mentor-progress-stats { grid-template-columns: 1fr 1fr; }
        .mentor-item-row { flex-direction: column; }
    }
</style>

<div class="dash-layout mentor-progress-page">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="session-detail-breadcrumb" style="margin-bottom:12px;">
            <a href="{{ route('mentor.journey', ['tab' => 'progress']) }}" style="color:var(--brand);">← Progress</a>
            <span>/</span>
            <span>{{ $mentee->name }}</span>
        </div>

        <div class="dash-header dash-header--actions flex-between">
            <div class="dash-header__main">
                <div class="dash-title">{{ $mentee->name }}’s journey</div>
                <div class="dash-subtitle">Review pending work first, then check week-by-week progress.</div>
            </div>
            <div class="dash-header__actions">
                @if(($pendingSubmissions ?? collect())->isNotEmpty())
                <a href="#review-list" class="btn btn-primary btn-sm">
                    Review now ({{ $pendingSubmissions->count() }})
                </a>
                @endif
                <a href="{{ route('mentor.journey', ['tab' => 'reviews', 'mentee_id' => $mentee->id]) }}" class="btn btn-outline btn-sm">All reviews</a>
                <a href="{{ route('mentor.mentees.show', $mentee->id) }}" class="btn btn-outline btn-sm">View mentee</a>
                <a href="{{ route('mentor.curriculum.tracks', ['mentee_id' => $mentee->id]) }}" class="btn btn-ghost btn-sm">Edit curriculum</a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">
            <span class="alert-icon">✅</span>
            <div>{{ session('success') }}</div>
        </div>
        @endif

        <div class="mentor-progress-stats">
            <div class="mentor-progress-stat">
                <div class="mentor-progress-stat__label">Overall</div>
                <div class="mentor-progress-stat__value">{{ (int) ($summary['overall']['percent'] ?? 0) }}%</div>
            </div>
            <div class="mentor-progress-stat">
                <div class="mentor-progress-stat__label">Tasks</div>
                <div class="mentor-progress-stat__value">{{ (int) ($summary['tasks']['completed'] ?? 0) }}/{{ (int) ($summary['tasks']['total'] ?? 0) }}</div>
            </div>
            <div class="mentor-progress-stat">
                <div class="mentor-progress-stat__label">MCQs</div>
                <div class="mentor-progress-stat__value">{{ (int) ($summary['mcqs']['completed'] ?? 0) }}/{{ (int) ($summary['mcqs']['total'] ?? 0) }}</div>
            </div>
            <div class="mentor-progress-stat">
                <div class="mentor-progress-stat__label">Pending reviews</div>
                <div class="mentor-progress-stat__value" style="color:var(--brand);">{{ ($pendingSubmissions ?? collect())->count() }}</div>
            </div>
        </div>

        <div class="mentor-progress-layout">
            <div class="mentor-progress-stack">
                <div class="card" id="review-list">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:14px;">
                        <div>
                            <h3 style="font-size:15px;font-weight:700;margin:0;">Needs your review</h3>
                            <p style="font-size:12px;color:var(--text-2);margin:4px 0 0;">Approve to count toward progress, or request changes.</p>
                        </div>
                        <span class="session-status pending">{{ ($pendingSubmissions ?? collect())->count() }} pending</span>
                    </div>

                    @forelse($pendingSubmissions ?? [] as $row)
                        @include('frontend.mentors.partials.review-card', ['row' => $row, 'showMentee' => false])
                    @empty
                    <div class="empty-state" style="padding:28px 0;">
                        <div style="font-size:14px;color:var(--text-2);">No pending submissions from this mentee right now.</div>
                    </div>
                    @endforelse
                </div>

                @forelse($tracks ?? [] as $track)
                @php $trackProgress = $track->progress_data ?? []; @endphp
                <div class="card mentor-track-card">
                    <details open>
                        <summary>
                            <div>
                                <div style="font-size:15px;font-weight:700;">{{ $track->name }}</div>
                                <div style="font-size:12px;color:var(--text-2);margin-top:2px;">
                                    {{ $track->months->count() }} months · Overall {{ (int) ($trackProgress['percent'] ?? 0) }}%
                                </div>
                            </div>
                            <div style="min-width:140px;flex:1;max-width:220px;">
                                <div class="progress-bar"><div class="progress-fill" style="width:{{ (int) ($trackProgress['percent'] ?? 0) }}%"></div></div>
                            </div>
                        </summary>

                        <div style="margin-top:16px;">
                            @foreach($track->months as $month)
                            <div style="margin-bottom:18px;">
                                <div style="font-size:13px;font-weight:700;margin-bottom:10px;">Month {{ $month->month_number }}: {{ $month->title ?: 'Untitled' }}</div>
                                @forelse($month->weeks as $week)
                                @php $weekProgress = $week->progress_data ?? ['percent' => 0, 'completed' => 0, 'total' => 0]; @endphp
                                <div class="mentor-week-block">
                                    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
                                        <div>
                                            <div style="font-weight:700;font-size:14px;">Week {{ $week->week_number }}: {{ $week->title ?: 'Untitled' }}</div>
                                            <div style="font-size:12px;color:var(--text-3);">{{ $week->tasks->count() }} tasks · {{ $week->mcqs->count() }} MCQs</div>
                                        </div>
                                        <div style="font-size:12px;font-weight:700;">{{ (int) ($weekProgress['percent'] ?? 0) }}%</div>
                                    </div>

                                    @if($week->tasks->isNotEmpty())
                                    <div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;margin-bottom:4px;">Tasks</div>
                                    @foreach($week->tasks as $task)
                                    @php
                                        $tp = $task->progress_record ?? null;
                                        $status = 'Pending';
                                        $badge = 'pending';
                                        if ($tp?->is_completed || $tp?->submission_status === 'approved') { $status = 'Approved'; $badge = 'completed'; }
                                        elseif ($tp?->submission_status === 'submitted') { $status = 'Under review'; $badge = 'pending'; }
                                        elseif ($tp?->submission_status === 'rejected') { $status = 'Needs revision'; $badge = 'cancelled'; }
                                    @endphp
                                    <div class="mentor-item-row">
                                        <div style="min-width:0;flex:1;">
                                            <div style="font-weight:600;">{{ $task->title }}</div>
                                            @if($tp?->submission_text)
                                            <div style="font-size:12px;color:var(--text-2);margin-top:6px;white-space:pre-wrap;">{{ $tp->submission_text }}</div>
                                            @endif
                                            @if($tp?->submission_url)
                                            <div style="margin-top:8px;">
                                                <a href="{{ $tp->submissionLink() }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm" style="font-size:11px;">
                                                    📎 Open mentee submission
                                                </a>
                                            </div>
                                            @elseif($tp && in_array($tp->submission_status, ['submitted', 'approved', 'rejected'], true))
                                            <div style="font-size:12px;color:var(--text-3);margin-top:4px;">No file/link attached to this submission.</div>
                                            @endif
                                            @if($tp?->mentor_feedback)
                                            <div style="font-size:12px;color:var(--text-3);margin-top:6px;">
                                                Feedback: {{ $tp->mentor_feedback }}
                                            </div>
                                            @endif
                                        </div>
                                        <span class="session-status {{ $badge }}">{{ $status }}</span>
                                    </div>
                                    @endforeach
                                    @endif

                                    @if($week->mcqs->isNotEmpty())
                                    <div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;margin:12px 0 4px;">MCQs</div>
                                    @foreach($week->mcqs as $mcq)
                                    @php
                                        $mp = $mcq->progress_record ?? null;
                                        $attempt = $mcq->latest_attempt ?? null;
                                        $status = 'Pending';
                                        $badge = 'pending';
                                        if ($mp?->is_completed || $mp?->submission_status === 'approved') { $status = 'Approved'; $badge = 'completed'; }
                                        elseif ($mp?->submission_status === 'submitted') { $status = 'Under review'; $badge = 'pending'; }
                                        elseif ($mp?->submission_status === 'rejected') { $status = 'Needs revision'; $badge = 'cancelled'; }
                                        elseif ($attempt) { $status = $attempt->is_correct ? 'Answered' : 'Incorrect'; $badge = $attempt->is_correct ? 'confirmed' : 'cancelled'; }
                                        $options = is_array($mcq->options) ? $mcq->options : [];
                                        $selected = $attempt && isset($options[$attempt->selected_index]) ? $optionText($options[$attempt->selected_index]) : null;
                                    @endphp
                                    <div class="mentor-item-row">
                                        <div style="min-width:0;">
                                            <div style="font-weight:600;">{{ \Illuminate\Support\Str::limit($mcq->question, 100) }}</div>
                                            @if($selected)
                                            <div style="font-size:12px;color:var(--text-3);margin-top:2px;">
                                                Mentee chose: <strong style="color:var(--text);">{{ $selected }}</strong>
                                                @if($attempt) · {{ $attempt->is_correct ? 'Correct' : 'Wrong' }} @endif
                                            </div>
                                            @endif
                                        </div>
                                        <span class="session-status {{ $badge }}">{{ $status }}</span>
                                    </div>
                                    @endforeach
                                    @endif

                                    @if($week->tasks->isEmpty() && $week->mcqs->isEmpty())
                                    <div style="font-size:13px;color:var(--text-3);">No tasks or MCQs in this week.</div>
                                    @endif
                                </div>
                                @empty
                                <div style="font-size:13px;color:var(--text-3);">No weeks in this month yet.</div>
                                @endforelse
                            </div>
                            @endforeach
                        </div>
                    </details>
                </div>
                @empty
                <div class="empty-state card" style="padding:48px 20px;">
                    <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No curriculum tracks</div>
                    <p style="font-size:13px;color:var(--text-2);margin-bottom:16px;">Create a curriculum track for this mentee to start tracking progress.</p>
                    <a href="{{ route('mentor.curriculum.tracks', ['mentee_id' => $mentee->id]) }}" class="btn btn-primary">Create curriculum track</a>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
