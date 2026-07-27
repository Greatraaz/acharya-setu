@extends('admin.layouts.app')
@section('title', 'Community Channels')

@section('content')
@php $r = request()->routeIs('admin.*') ? 'admin.community' : 'mentee.community'; @endphp
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="font-display text-2xl font-bold text-gray-900">Community Channels</h1>
        <p class="text-sm text-gray-500 mt-1">Public &amp; private channels for mentors and mentees - threads, likes, invites</p>
    </div>
    @if(request()->routeIs('admin.*'))
    <a href="{{ route('admin.community.create') }}"
       class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors">
        + New Channel
    </a>
    @endif
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($channels as $channel)
    <a href="{{ route($r.'.show', $channel->slug) }}"
       class="group bg-white border border-gray-100 rounded-2xl p-5 hover:border-blue-200 hover:shadow-sm transition-all duration-200">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-xl">
                {{ $channel->icon }}
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                {{ $channel->type === 'public' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $channel->type }}
            </span>
        </div>
        <h3 class="font-semibold text-gray-900 mb-1 group-hover:text-blue-700 transition-colors">
            # {{ $channel->name }}
        </h3>
        <p class="text-sm text-gray-500 line-clamp-2 mb-3">{{ $channel->description ?? 'No description.' }}</p>
        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-400">
            <span>{{ $channel->all_messages_count }} messages</span>
            <span class="text-gray-300">|</span>
            <span>{{ $channel->members_count ?? 0 }} members</span>
            @if($channel->category)
            <span class="text-gray-300">|</span>
            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium">
                {{ \App\Models\Channel::CATEGORIES[$channel->category] ?? $channel->category }}
            </span>
            @endif
        </div>
        @if(($channel->unread_count ?? 0) > 0)
        <div class="mt-2 text-xs font-semibold text-blue-600">{{ $channel->unread_count }} unread</div>
        @endif
    </a>
    @empty
    <div class="col-span-3 text-center py-20 text-gray-400">
        <div class="text-4xl mb-3">💬</div>
        <p class="font-medium">No channels yet</p>
        <p class="text-sm mt-1">Create the first channel to start the conversation</p>
    </div>
    @endforelse
</div>
@endsection
