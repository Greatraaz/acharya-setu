@extends('frontend.layouts.app')
@section('title', 'Community — Vedrix Mentor')

@section('content')
<link rel="stylesheet" href="{{ asset('css/community-thread.css') }}?v={{ filemtime(public_path('css/community-thread.css')) }}">

<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between" style="flex-wrap:wrap;gap:12px;">
            <div>
                <div class="dash-title">Community</div>
                <div class="dash-subtitle">Channels you can join and discuss with mentees and other mentors.</div>
            </div>
            <a href="{{ route('mentor.community.create') }}" class="btn btn-primary btn-sm">+ Create Channel</a>
        </div>

        @include('frontend.partials.community-filters', [
            'routeName' => 'mentor.community',
            'channels' => $channels,
        ])

        <div class="community-channel-grid">
            @forelse($channels as $channel)
                @include('partials.community-channel-card', [
                    'channel' => $channel,
                    'showRoute' => 'mentor.community.show',
                    'canDelete' => (int) $channel->created_by === (int) Auth::id(),
                    'deleteRoute' => 'mentor.community.destroy',
                ])
            @empty
            <div class="empty-state" style="grid-column:1/-1;padding:60px 0;">
                <div style="font-size:48px;margin-bottom:12px;">💬</div>
                <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No channels found</div>
                <p style="font-size:13px;color:var(--text-2);">Try adjusting your filters or create a new channel.</p>
            </div>
            @endforelse
        </div>

        @include('frontend.partials.pagination', ['paginator' => $channels])
    </div>
</div>
@endsection
