@extends('frontend.layouts.app')
@section('title', 'Community — Vedrix Mentor')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Community</div>
            <div class="dash-subtitle">Channels you can join and discuss with mentees and other mentors.</div>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-bottom:14px;">
            <a href="{{ route('mentor.community.create') }}" class="btn btn-primary btn-sm">+ Create Channel</a>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;">
            @forelse($channels as $channel)
            <a href="{{ route('mentor.community.show', $channel->slug) }}" class="card" style="display:block;text-decoration:none;color:inherit;padding:18px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                    <div style="font-size:28px;">{{ $channel->icon ?: '💬' }}</div>
                    <span class="session-status {{ $channel->type === 'public' ? 'confirmed' : 'pending' }}">{{ ucfirst($channel->type) }}</span>
                </div>
                <div style="font-size:15px;font-weight:700;margin-bottom:4px;">{{ $channel->name }}</div>
                <div style="font-size:12px;color:var(--text-2);margin-bottom:12px;min-height:36px;">{{ Str::limit($channel->description ?: 'No description', 90) }}</div>
                <div style="font-size:11px;color:var(--text-3);display:flex;gap:12px;">
                    <span>{{ $channel->members_count }} members</span>
                    <span>{{ $channel->all_messages_count }} messages</span>
                    @if(($channel->unread_count ?? 0) > 0)
                        <span style="color:var(--brand);font-weight:700;">{{ $channel->unread_count }} unread</span>
                    @endif
                </div>
            </a>
            @empty
            <div class="empty-state" style="grid-column:1/-1;padding:60px 0;">
                <div style="font-size:48px;margin-bottom:12px;">💬</div>
                <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No channels yet</div>
                <p style="font-size:13px;color:var(--text-2);">Community channels will appear here once they’re created.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
