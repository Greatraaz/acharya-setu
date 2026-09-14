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

        <form method="GET" action="{{ route('mentor.mentees') }}" class="mentor-mentees-toolbar">
            <div class="mentor-mentees-toolbar__row">
                <div class="session-search-field mentor-mentees-toolbar__search">
                    <span class="session-search-icon" aria-hidden="true">🔍</span>
                    <input type="search" name="search" class="form-input" value="{{ $search ?? request('search') }}"
                           placeholder="Search by name, email, college, or field…" autocomplete="off" aria-label="Search mentees">
                </div>
                <button type="submit" class="btn btn-outline mentor-mentees-toolbar__submit">Search</button>
                @if(request()->filled('search'))
                    <a href="{{ route('mentor.mentees') }}" class="btn btn-ghost mentor-mentees-toolbar__clear">Clear</a>
                @endif
            </div>
        </form>

        @forelse($mentees as $mentee)
        <div class="card mentor-mentee-card">
            <div class="mentor-mentee-card__row">
                <div class="mentor-avatar-lg mentor-mentee-card__avatar">
                    @if($mentee->avatar_url)
                        <img src="{{ $mentee->avatar_url }}" alt="">
                    @else
                        {{ strtoupper(substr($mentee->name, 0, 1)) }}
                    @endif
                </div>
                <div class="mentor-mentee-card__body">
                    <div class="mentor-mentee-card__name">{{ $mentee->name }}</div>
                    <div class="mentor-mentee-card__email" title="{{ $mentee->email }}">{{ $mentee->email }}</div>
                    @if($mentee->college || $mentee->field)
                    <div class="mentor-mentee-card__meta">
                        @if($mentee->college)<span>{{ $mentee->college }}</span>@endif
                        @if($mentee->college && $mentee->field)<span aria-hidden="true"> · </span>@endif
                        @if($mentee->field)<span>{{ $mentee->field }}</span>@endif
                    </div>
                    @endif
                </div>
                <div class="mentor-mentee-card__actions">
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
