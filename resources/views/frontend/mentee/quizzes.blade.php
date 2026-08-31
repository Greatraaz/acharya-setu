@extends('frontend.layouts.app')
@section('title', 'Quizzes — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Quizzes</div>
            <div class="dash-subtitle">Practice quizzes to test and strengthen your knowledge.</div>
        </div>

        <form method="GET" action="{{ route('mentee.quizzes.index') }}" class="session-toolbar" style="margin-bottom:16px;">
            <div class="session-filter-tabs">
                @foreach([
                    'all' => 'All',
                    'not_attempted' => 'Not attempted',
                    'completed' => 'Completed',
                    'passed' => 'Passed',
                    'failed' => 'Failed',
                ] as $key => $label)
                    @php $tabParams = array_filter(['status' => $key === 'all' ? null : $key, 'search' => ($search ?? request('search')) ?: null]); @endphp
                    <a href="{{ route('mentee.quizzes.index', $tabParams) }}"
                       class="session-filter-tab {{ ($status ?? request('status', 'all')) === $key ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <div class="session-toolbar-controls">
                @if(($status ?? request('status')) && ($status ?? request('status')) !== 'all')
                    <input type="hidden" name="status" value="{{ $status ?? request('status') }}">
                @endif
                <div class="session-search-field">
                    <span class="session-search-icon" aria-hidden="true">🔍</span>
                    <input type="search" name="search" class="form-input" value="{{ $search ?? request('search') }}"
                           placeholder="Search quizzes…" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-outline">Search</button>
            </div>
        </form>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;">
            @forelse($quizzes as $quiz)
            @php $attempt = $myAttempts[$quiz->id] ?? null; @endphp
            <div class="card" style="padding:18px;display:flex;flex-direction:column;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                    <div style="font-size:28px;">🧠</div>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;">
                        @if($attempt)
                        <span class="session-status completed">{{ $attempt->passed ? 'Passed' : 'Completed' }}</span>
                        @endif
                        @if($quiz->time_limit)
                        <span class="tag">⏱ {{ $quiz->time_limit }}m</span>
                        @endif
                    </div>
                </div>
                <div style="font-size:15px;font-weight:700;margin-bottom:6px;">{{ $quiz->title }}</div>
                <div style="font-size:13px;color:var(--text-2);flex:1;margin-bottom:12px;">{{ Str::limit($quiz->description ?: 'No description.', 110) }}</div>
                <div style="font-size:12px;color:var(--text-3);margin-bottom:14px;">
                    {{ $quiz->questions_count }} questions · Pass {{ $quiz->pass_score }}%
                    @if($attempt) · Score {{ (int) $attempt->percentage }}% @endif
                </div>
                <a href="{{ route('mentee.quizzes.show', $quiz) }}" class="btn {{ $attempt ? 'btn-outline' : 'btn-primary' }} btn-sm" style="width:100%;">
                    {{ $attempt ? 'Review / Retake' : 'Start Quiz' }}
                </a>
            </div>
            @empty
            <div class="empty-state" style="grid-column:1/-1;padding:60px 0;">
                <div style="font-size:48px;margin-bottom:12px;">🧠</div>
                <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No quizzes found</div>
                <p style="font-size:13px;color:var(--text-2);">Try adjusting your filters or check back later.</p>
            </div>
            @endforelse
        </div>

        @include('frontend.partials.pagination', ['paginator' => $quizzes])
    </div>
</div>
@endsection
