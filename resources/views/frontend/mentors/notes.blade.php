@extends('frontend.layouts.app')
@section('title', 'Session Notes — Vedrix Mentor')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Session Notes</div>
            <div class="dash-subtitle">Notes and resources you’ve added across mentoring sessions.</div>
        </div>

        <form method="GET" action="{{ route('mentor.notes') }}" class="session-toolbar" style="margin-bottom:16px;">
            <div class="session-filter-tabs">
                @foreach(['' => 'All', 'shared' => 'Shared', 'private' => 'Private'] as $key => $label)
                    @php $tabParams = array_filter(['visibility' => $key ?: null, 'search' => ($search ?? request('search')) ?: null]); @endphp
                    <a href="{{ route('mentor.notes', $tabParams) }}"
                       class="session-filter-tab {{ ($visibility ?? request('visibility', '')) === $key ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <div class="session-toolbar-controls">
                @if(($visibility ?? request('visibility')))
                    <input type="hidden" name="visibility" value="{{ $visibility ?? request('visibility') }}">
                @endif
                <div class="session-search-field">
                    <span class="session-search-icon" aria-hidden="true">🔍</span>
                    <input type="search" name="search" class="form-input" value="{{ $search ?? request('search') }}"
                           placeholder="Search notes, session, or mentee…" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-outline">Search</button>
            </div>
        </form>

        @if($sessionsWithoutNotes->isNotEmpty())
        <div class="card" style="margin-bottom:20px;">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;">Sessions needing notes</h3>
            @foreach($sessionsWithoutNotes as $session)
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;padding:10px 0;border-bottom:1px solid var(--border);">
                <div>
                    <div style="font-size:13px;font-weight:600;">{{ $session->title ?? 'Session' }} · {{ $session->mentee->name ?? 'Mentee' }}</div>
                    <div style="font-size:12px;color:var(--text-2);">{{ $session->scheduled_at?->format('D, d M Y · g:i A') }}</div>
                </div>
                <a href="{{ route('mentor.sessions.show', $session->id) }}" class="btn btn-outline btn-sm">Add notes</a>
            </div>
            @endforeach
        </div>
        @endif

        @forelse($notes as $note)
        <div class="card" style="margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:8px;">
                <div>
                    <div style="font-size:14px;font-weight:700;">{{ $note->session->title ?? 'Session' }}</div>
                    <div style="font-size:12px;color:var(--text-2);">
                        Mentee: {{ $note->session->mentee->name ?? '—' }}
                        · {{ $note->created_at?->format('d M Y, g:i A') }}
                    </div>
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <span class="session-status {{ $note->is_shared ? 'confirmed' : 'pending' }}">{{ $note->is_shared ? 'Shared' : 'Private' }}</span>
                    <a href="{{ route('mentor.sessions.show', $note->session_id) }}" class="btn btn-ghost btn-sm">Open</a>
                </div>
            </div>
            <div style="font-size:13px;color:var(--text-2);white-space:pre-wrap;">{{ $note->content }}</div>
        </div>
        @empty
        <div class="empty-state" style="padding:60px 0;">
            <div style="font-size:48px;margin-bottom:12px;">📝</div>
            <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No session notes yet</div>
            <p style="font-size:13px;color:var(--text-2);max-width:360px;margin:0 auto 20px;">After completing a session, open it and add notes for yourself or to share with the mentee.</p>
            <a href="{{ route('mentor.sessions', ['filter' => 'completed']) }}" class="btn btn-primary">View completed sessions</a>
        </div>
        @endforelse

        @include('frontend.partials.pagination', ['paginator' => $notes])
    </div>
</div>
@endsection
