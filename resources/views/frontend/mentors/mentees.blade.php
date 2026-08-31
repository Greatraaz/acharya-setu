@extends('frontend.layouts.app')
@section('title', 'My Mentees — Vedrix Mentor')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">My Mentees</div>
            <div class="dash-subtitle">People assigned to you or who have booked sessions with you.</div>
        </div>

        <form method="GET" action="{{ route('mentor.mentees') }}" class="session-toolbar" style="margin-bottom:16px;">
            <div class="session-toolbar-controls" style="width:100%;">
                <div class="session-search-field" style="flex:1;">
                    <span class="session-search-icon" aria-hidden="true">🔍</span>
                    <input type="search" name="search" class="form-input" value="{{ $search ?? request('search') }}"
                           placeholder="Search by name, email, college, or field…" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-outline">Search</button>
                @if(request()->filled('search'))
                    <a href="{{ route('mentor.mentees') }}" class="btn btn-ghost">Clear</a>
                @endif
            </div>
        </form>

        @forelse($mentees as $mentee)
        <div class="card" style="margin-bottom:12px;padding:16px 18px;">
            <div style="display:flex;gap:14px;align-items:center;">
                <div class="mentor-avatar-lg" style="width:48px;height:48px;font-size:18px;">
                    @if($mentee->avatar_url)
                        <img src="{{ $mentee->avatar_url }}" alt="">
                    @else
                        {{ strtoupper(substr($mentee->name, 0, 1)) }}
                    @endif
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:15px;font-weight:700;">{{ $mentee->name }}</div>
                    <div style="font-size:12px;color:var(--text-2);">
                        {{ $mentee->email }}
                        @if($mentee->college) · {{ $mentee->college }} @endif
                        @if($mentee->field) · {{ $mentee->field }} @endif
                    </div>
                </div>
                <div style="display:flex;gap:8px;flex-shrink:0;">
                    <a href="{{ route('mentor.journey.show', $mentee->id) }}" class="btn btn-outline btn-sm">Journey</a>
                    <a href="{{ route('mentor.mentees.show', $mentee->id) }}" class="btn btn-primary btn-sm">View</a>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:60px 0;">
            <div style="font-size:48px;margin-bottom:12px;">🎓</div>
            <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No mentees yet</div>
            <p style="font-size:13px;color:var(--text-2);max-width:360px;margin:0 auto 20px;">When mentees are assigned to you or book a session, they’ll show up here.</p>
            <a href="{{ route('mentor.availability') }}" class="btn btn-primary">Set Availability</a>
        </div>
        @endforelse

        @include('frontend.partials.pagination', ['paginator' => $mentees])
    </div>
</div>
@endsection
