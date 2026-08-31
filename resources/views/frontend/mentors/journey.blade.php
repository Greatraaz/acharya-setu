@extends('frontend.layouts.app')
@section('title', 'Journey Tracker — Vedrix Mentor')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between" style="flex-wrap:wrap;gap:12px;">
            <div>
                <div class="dash-title">Progress Tracker</div>
                <div class="dash-subtitle">Track curriculum progress for your mentees.</div>
            </div>
            <a href="{{ route('mentor.curriculum.tracks') }}" class="btn btn-primary btn-sm">Build curriculum</a>
        </div>

        <form method="GET" action="{{ route('mentor.journey') }}" class="session-toolbar" style="margin-bottom:16px;">
            <div class="session-filter-tabs">
                @foreach(['' => 'All', 'active' => 'Active', 'completed' => 'Completed', 'paused' => 'Paused'] as $key => $label)
                    @php $tabParams = array_filter(['status' => $key ?: null, 'search' => ($search ?? request('search')) ?: null]); @endphp
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

        @forelse($enrollments as $enrollment)
        @php $progress = $enrollment->progress_data ?? []; @endphp
        <div class="card" style="margin-bottom:12px;padding:16px 18px;">
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                <div>
                    <div style="font-size:15px;font-weight:700;">{{ $enrollment->mentee->name ?? 'Mentee' }}</div>
                    <div style="font-size:12px;color:var(--text-2);margin-top:2px;">
                        {{ $enrollment->stream->name ?? 'Stream' }}
                        · {{ ucfirst($enrollment->status) }}
                        · Month {{ $enrollment->current_month }} · Week {{ $enrollment->current_week }}
                    </div>
                </div>
                <a href="{{ route('mentor.journey.show', $enrollment->mentee_id) }}" class="btn btn-outline btn-sm">View progress</a>
            </div>
            <div style="margin-top:12px;">
                <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-2);margin-bottom:6px;">
                    <span>Overall progress</span>
                    <span>{{ (int) ($progress['percent'] ?? $progress['percentage'] ?? 0) }}%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width:{{ (int) ($progress['percent'] ?? $progress['percentage'] ?? 0) }}%"></div>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:48px 0;">
            <div style="font-size:48px;margin-bottom:12px;">🗺️</div>
            <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No active journeys</div>
            <p style="font-size:13px;color:var(--text-2);max-width:380px;margin:0 auto 16px;">Create a mentee curriculum track, then progress will appear here as they complete work.</p>
            <a href="{{ route('mentor.curriculum.tracks') }}" class="btn btn-primary">Create curriculum</a>
        </div>
        @endforelse

        @include('frontend.partials.pagination', ['paginator' => $enrollments])

        @if($menteesWithoutEnrollment->isNotEmpty())
        <div class="card" style="margin-top:20px;">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;">Mentees without a curriculum</h3>
            @foreach($menteesWithoutEnrollment as $mentee)
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px;align-items:center;gap:8px;">
                <span>{{ $mentee->name }}</span>
                <div style="display:flex;gap:6px;">
                    <a href="{{ route('mentor.curriculum.tracks', ['mentee_id' => $mentee->id]) }}" class="btn btn-outline btn-sm">Create track</a>
                    <a href="{{ route('mentor.mentees.show', $mentee->id) }}" class="btn btn-ghost btn-sm">Profile</a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
