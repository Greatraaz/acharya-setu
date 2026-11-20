@extends('admin.layouts.app')
@section('title', 'Community Channels')

@section('heading', 'Community Channels')
@section('content')
<link rel="stylesheet" href="{{ asset('css/community-thread.css') }}?v={{ filemtime(public_path('css/community-thread.css')) }}">

<div class="admin-page space-y-5">
    <div class="learn-intro">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="learn-eyebrow">Community · Channels</span>
                <h2>Community Channels</h2>
                <p>Public &amp; private channels for mentors and mentees — threads, likes, invites.</p>
            </div>
            <a href="{{ route('admin.community.create') }}" class="learn-btn-primary self-start">
                + New Channel
            </a>
        </div>
    </div>

<div class="community-channel-grid">
    @forelse($channels as $channel)
        @include('partials.community-channel-card', [
            'channel' => $channel,
            'showRoute' => 'admin.community.show',
            'canDelete' => (int) $channel->created_by === (int) Auth::id() || Auth::user()->isAdmin(),
            'deleteRoute' => 'admin.community.destroy',
        ])
    @empty
    <div class="col-span-3 text-center py-20 text-gray-400" style="grid-column:1/-1;">
        <div class="text-4xl mb-3">💬</div>
        <p class="font-medium">No channels yet</p>
        <p class="text-sm mt-1">Create the first channel to start the conversation</p>
    </div>
    @endforelse
</div>
@include('admin.partials.pagination', ['paginator' => $channels])
</div>
@endsection
