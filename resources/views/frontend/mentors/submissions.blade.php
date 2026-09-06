@extends('frontend.layouts.app')
@section('title', 'Curriculum Reviews — Vedrix Mentor')

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
    @media (max-width: 640px) {
        .dash-header.flex-between { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between" style="flex-wrap:wrap;gap:12px;">
            <div>
                <div class="dash-title">Curriculum Reviews</div>
                <div class="dash-subtitle">Approve or reject mentee task submissions and MCQ answers.</div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <span class="session-status pending">{{ (int) ($pendingCount ?? 0) }} pending</span>
                <a href="{{ route('mentor.journey') }}" class="btn btn-outline btn-sm">Progress Tracker</a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">
            <span class="alert-icon">✅</span>
            <div>{{ session('success') }}</div>
        </div>
        @endif

        @if(!empty($menteeId))
        <div class="alert alert-info" style="margin-bottom:16px;">
            <span class="alert-icon">ℹ️</span>
            <div style="font-size:13px;">Showing reviews for one mentee. <a href="{{ route('mentor.submissions') }}" style="color:var(--brand);">View all</a></div>
        </div>
        @endif

        @forelse($submissions as $row)
        @php
            $progress = $row['progress'];
            $context = $row['context'] ?? [];
            $mentee = $progress->user;
        @endphp
        <div class="card" style="margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
                <div>
                    <div style="font-size:12px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin-bottom:4px;">
                        {{ strtoupper($progress->item_type) }}
                        @if(!empty($context['month_number'])) · Month {{ $context['month_number'] }}@endif
                        @if(!empty($context['week_number'])) · Week {{ $context['week_number'] }}@endif
                    </div>
                    <div style="font-size:15px;font-weight:700;margin-bottom:4px;">{{ $context['title'] ?? ('Item #'.$progress->item_id) }}</div>
                    <div style="font-size:13px;color:var(--text-2);">
                        Mentee: <strong>{{ $mentee->name ?? '—' }}</strong>
                        @if(!empty($context['track_name'])) · {{ $context['track_name'] }}@endif
                    </div>
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
            @else
                @if($progress->submission_text)
                <div style="padding:12px;border:1px solid var(--border);border-radius:var(--radius);margin-bottom:12px;font-size:13px;color:var(--text-2);white-space:pre-wrap;">{{ $progress->submission_text }}</div>
                @endif
                @if($progress->submission_url)
                <div style="margin-bottom:12px;">
                    <a href="{{ $progress->submission_url }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">Open submission link / file</a>
                </div>
                @endif
            @endif

            <form method="POST" action="{{ route('mentor.submissions.review', $progress->id) }}" style="display:grid;gap:12px;">
                @csrf
                <div class="form-group" style="margin:0;">
                    <label class="form-label" for="feedback-{{ $progress->id }}">Feedback (optional)</label>
                    <textarea id="feedback-{{ $progress->id }}" name="mentor_feedback" class="form-input" rows="2" placeholder="Share guidance for the mentee…"></textarea>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" name="submission_status" value="approved" class="btn btn-primary btn-sm">Approve</button>
                    <button type="submit" name="submission_status" value="rejected" class="btn btn-outline btn-sm" style="color:var(--error);">Reject</button>
                    @if($mentee)
                    <a href="{{ route('mentor.journey.show', $mentee->id) }}" class="btn btn-ghost btn-sm">View progress</a>
                    @endif
                </div>
            </form>
        </div>
        @empty
        <div class="empty-state" style="padding:64px 0;">
            <div style="font-size:48px;margin-bottom:12px;">✅</div>
            <div style="font-size:18px;font-weight:700;margin-bottom:8px;">No pending reviews</div>
            <p style="font-size:14px;color:var(--text-2);max-width:360px;margin:0 auto;">When mentees submit tasks or answer MCQs correctly, they will appear here for your approval.</p>
        </div>
        @endforelse

        @include('frontend.partials.pagination', ['paginator' => $paginator])
    </div>
</div>
@endsection
