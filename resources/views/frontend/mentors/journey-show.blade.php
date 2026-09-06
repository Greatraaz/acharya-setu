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
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(280px, .9fr);
        gap: 20px;
        align-items: start;
    }
    .mentor-progress-stack { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
    .mentor-progress-side { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
    .mentor-progress-side-sticky { position: sticky; top: calc(var(--nav-h, 64px) + 16px); display: flex; flex-direction: column; gap: 16px; }
    .mentor-review-card { border: 1px solid rgba(245, 158, 11, .35); background: color-mix(in srgb, var(--brand) 6%, var(--bg-2)); }
    .mentor-mcq-options { display: grid; gap: 8px; margin: 12px 0; }
    .mentor-mcq-option {
        padding: 10px 12px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
        font-size: 13px;
        color: var(--text-2);
        background: var(--bg);
    }
    .mentor-mcq-option.is-selected { border-color: var(--brand); color: var(--text); background: var(--brand-muted); }
    .mentor-mcq-option.is-correct { border-color: var(--success); }
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
        .mentor-progress-layout { grid-template-columns: 1fr; }
        .mentor-progress-side-sticky { position: static; }
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
            <a href="{{ route('mentor.journey') }}" style="color:var(--brand);">← Progress Tracker</a>
            <span>/</span>
            <span>{{ $mentee->name }}</span>
        </div>

        <div class="dash-header dash-header--actions flex-between">
            <div class="dash-header__main">
                <div class="dash-title">{{ $mentee->name }}’s Progress</div>
                <div class="dash-subtitle">Review submitted tasks & MCQs, then track week-by-week completion.</div>
            </div>
            <div class="dash-header__actions">
                <a href="{{ route('mentor.submissions', ['mentee_id' => $mentee->id]) }}" class="btn btn-primary btn-sm">
                    Reviews @if(($pendingSubmissions ?? collect())->isNotEmpty()) ({{ $pendingSubmissions->count() }}) @endif
                </a>
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
                        <h3 style="font-size:15px;font-weight:700;margin:0;">Awaiting your review</h3>
                        <span class="session-status pending">{{ ($pendingSubmissions ?? collect())->count() }} pending</span>
                    </div>

                    @forelse($pendingSubmissions ?? [] as $row)
                    @php
                        $progress = $row['progress'];
                        $context = $row['context'] ?? [];
                    @endphp
                    <div class="card mentor-review-card" style="margin-bottom:12px;" id="review-{{ $progress->id }}">
                        <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:8px;">
                            <div>
                                <div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-3);">
                                    {{ strtoupper($progress->item_type) }}
                                    @if(!empty($context['track_name'])) · {{ $context['track_name'] }}@endif
                                    @if(!empty($context['month_number'])) · M{{ $context['month_number'] }}@endif
                                    @if(!empty($context['week_number'])) · W{{ $context['week_number'] }}@endif
                                </div>
                                <div style="font-size:14px;font-weight:700;margin-top:4px;">{{ $context['title'] ?? ('Item #'.$progress->item_id) }}</div>
                            </div>
                            <span class="session-status pending">Under review</span>
                        </div>

                        @if($progress->item_type === 'mcq')
                            <div class="mentor-mcq-options">
                                @foreach(($context['options'] ?? []) as $idx => $option)
                                @php
                                    $isSelected = isset($context['selected_index']) && (int) $context['selected_index'] === (int) $idx;
                                    $isCorrect = isset($context['correct_index']) && (int) $context['correct_index'] === (int) $idx;
                                @endphp
                                <div class="mentor-mcq-option {{ $isSelected ? 'is-selected' : '' }} {{ $isCorrect ? 'is-correct' : '' }}">
                                    <strong>{{ chr(65 + (int) $idx) }}.</strong> {{ $optionText($option) }}
                                    @if($isSelected) · mentee answer @endif
                                    @if($isCorrect) · correct @endif
                                </div>
                                @endforeach
                            </div>
                            @if(!empty($context['is_correct']))
                            <div style="font-size:13px;color:var(--success);margin-bottom:10px;">Correct answer submitted — confirm to count toward progress.</div>
                            @endif
                        @else
                            @if($progress->submission_text)
                            <div style="padding:12px;border:1px solid var(--border);border-radius:var(--radius-sm);margin-bottom:10px;font-size:13px;white-space:pre-wrap;color:var(--text-2);">{{ $progress->submission_text }}</div>
                            @endif
                            @if($progress->submission_url)
                            <div style="margin-bottom:10px;">
                                <a href="{{ $progress->submission_url }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">Open submission</a>
                            </div>
                            @endif
                            @if(! $progress->submission_text && ! $progress->submission_url)
                            <div style="font-size:13px;color:var(--text-3);margin-bottom:10px;">No written submission attached.</div>
                            @endif
                        @endif

                        <form method="POST" action="{{ route('mentor.submissions.review', $progress->id) }}" style="display:grid;gap:10px;">
                            @csrf
                            <textarea name="mentor_feedback" class="form-input" rows="2" placeholder="Optional feedback for mentee…"></textarea>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                <button type="submit" name="submission_status" value="approved" class="btn btn-primary btn-sm">Approve</button>
                                <button type="submit" name="submission_status" value="rejected" class="btn btn-outline btn-sm" style="color:var(--error);">Reject</button>
                            </div>
                        </form>
                    </div>
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
                                        <div>
                                            <div style="font-weight:600;">{{ $task->title }}</div>
                                            @if($tp?->submission_text)
                                            <div style="font-size:12px;color:var(--text-3);margin-top:2px;">{{ \Illuminate\Support\Str::limit($tp->submission_text, 90) }}</div>
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

            <div class="mentor-progress-side">
                <div class="mentor-progress-side-sticky">
                    <div class="card">
                        <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;">Mentee</h3>
                        <div class="session-detail-person">
                            <div class="session-detail-person__avatar mentor-avatar-lg" style="width:48px;height:48px;border-radius:50%;overflow:hidden;display:flex;align-items:center;justify-content:center;background:var(--brand-muted);font-weight:700;">
                                @if($mentee->avatar_url)
                                    <img src="{{ $mentee->avatar_url }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    {{ strtoupper(substr($mentee->name ?? '?', 0, 1)) }}
                                @endif
                            </div>
                            <div class="session-detail-person__info">
                                <div class="session-detail-person__name">{{ $mentee->name }}</div>
                                <div class="session-detail-person__email">{{ $mentee->email }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;">Enrollment</h3>
                        @forelse($enrollments as $enrollment)
                        @php $ep = $enrollment->progress_data ?? []; @endphp
                        <div style="padding:10px 0;border-bottom:1px solid var(--border);">
                            <div style="font-weight:700;font-size:13px;">{{ $enrollment->stream->name ?? 'Track' }}</div>
                            <div style="font-size:12px;color:var(--text-3);margin-top:2px;">
                                {{ ucfirst($enrollment->status) }} · Month {{ $enrollment->current_month }} · Week {{ $enrollment->current_week }}
                            </div>
                            <div style="font-size:12px;margin-top:6px;">{{ (int) ($ep['percent'] ?? $ep['percentage'] ?? 0) }}% complete</div>
                        </div>
                        @empty
                        <div style="font-size:13px;color:var(--text-3);">No enrollment rows yet — tracks below still sync progress.</div>
                        @endforelse
                    </div>

                    <div class="card">
                        <h3 style="font-size:14px;font-weight:700;margin-bottom:10px;">Quick actions</h3>
                        <div style="display:grid;gap:8px;">
                            <a href="{{ route('mentor.submissions', ['mentee_id' => $mentee->id]) }}" class="btn btn-primary btn-sm" style="justify-content:center;">Open all reviews</a>
                            <a href="{{ route('mentor.curriculum.tracks', ['mentee_id' => $mentee->id]) }}" class="btn btn-outline btn-sm" style="justify-content:center;">Edit curriculum content</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
