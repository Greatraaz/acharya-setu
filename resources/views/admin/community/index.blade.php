@extends('admin.layouts.app')
@section('title', 'Community Channels')

@section('content')
<link rel="stylesheet" href="{{ asset('css/community-thread.css') }}?v={{ filemtime(public_path('css/community-thread.css')) }}">

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="font-display text-2xl font-bold text-gray-900">Community Channels</h1>
        <p class="text-sm text-gray-500 mt-1">Public &amp; private channels for mentors and mentees — threads, likes, invites</p>
    </div>
    <a href="{{ route('admin.community.create') }}"
       class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors">
        + New Channel
    </a>
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
@endsection
