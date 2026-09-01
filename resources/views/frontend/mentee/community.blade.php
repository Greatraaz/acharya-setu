@extends('frontend.layouts.app')
@section('title', 'Community — Vedrix')

@section('content')
<link rel="stylesheet" href="{{ asset('css/community-thread.css') }}?v={{ filemtime(public_path('css/community-thread.css')) }}">

<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Channels</div>
            <div class="dash-subtitle">Join discussions with mentors and other mentees.</div>
        </div>

        @include('frontend.partials.community-filters', [
            'routeName' => 'mentee.community.index',
            'channels' => $channels,
        ])

        <div class="community-channel-grid">
            @forelse($channels as $channel)
                @include('partials.community-channel-card', [
                    'channel' => $channel,
                    'showRoute' => 'mentee.community.show',
                    'canDelete' => false,
                ])
            @empty
            <div class="empty-state" style="grid-column:1/-1;padding:60px 0;">
                <div style="font-size:48px;margin-bottom:12px;">💬</div>
                <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No channels found</div>
                <p style="font-size:13px;color:var(--text-2);">Try adjusting your filters to find more channels.</p>
            </div>
            @endforelse
        </div>

        @include('frontend.partials.pagination', ['paginator' => $channels])
    </div>
</div>
@endsection
