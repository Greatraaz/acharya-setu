@extends('frontend.layouts.app')
@section('title', $channel->name.' — Community')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between">
            <div>
                <div class="dash-title">{{ $channel->icon }} {{ $channel->name }}</div>
                <div class="dash-subtitle">{{ $channel->description ?: 'Channel discussion' }}</div>
            </div>
            <a href="{{ route('mentor.community') }}" class="btn btn-ghost">← All channels</a>
        </div>

        @unless($channel->isMember(auth()->user()))
        <div class="alert alert-info" style="margin-bottom:16px;">
            <span class="alert-icon">ℹ️</span>
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;width:100%;">
                <span style="font-size:13px;">Join this channel to post messages.</span>
                <form action="{{ route('mentor.community.join', $channel->slug) }}" method="POST">
                    @csrf
                    <button class="btn btn-primary btn-sm" type="submit">Join channel</button>
                </form>
            </div>
        </div>
        @endunless

        @if($channel->canPost(auth()->user()))
        <div class="card" style="margin-bottom:16px;">
            <form action="{{ route('mentor.community.messages.store', $channel->slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <textarea name="body" class="form-input" rows="3" placeholder="Share an update with the channel..." style="resize:vertical;"></textarea>
                <div style="display:flex;justify-content:space-between;gap:10px;margin-top:10px;align-items:center;">
                    <input type="file" name="image" accept="image/*" style="font-size:12px;">
                    <button type="submit" class="btn btn-primary btn-sm">Post</button>
                </div>
            </form>
        </div>
        @endif

        @forelse($messages as $message)
        <div class="card" style="margin-bottom:10px;padding:14px 16px;">
            <div style="display:flex;justify-content:space-between;gap:10px;margin-bottom:8px;">
                <div style="font-size:13px;font-weight:700;">{{ $message->user->name ?? 'User' }}
                    <span style="font-weight:500;color:var(--text-3);">· {{ $message->created_at?->diffForHumans() }}</span>
                </div>
                <form action="{{ route('mentor.community.messages.like', $message->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm">👍 {{ is_array($message->liked_by) ? count($message->liked_by) : 0 }}</button>
                </form>
            </div>
            @if($message->body)
            <div style="font-size:13px;color:var(--text-1);white-space:pre-wrap;">{{ $message->body }}</div>
            @endif
            @if($message->image_url ?? $message->image_path)
            <div style="margin-top:10px;">
                <img src="{{ $message->image_url ?? asset('storage/'.$message->image_path) }}" alt="" style="max-width:100%;border-radius:10px;">
            </div>
            @endif
        </div>
        @empty
        <div class="empty-state" style="padding:40px 0;">
            <div style="font-size:15px;font-weight:700;">No messages yet</div>
            <p style="font-size:13px;color:var(--text-2);">Be the first to start the conversation.</p>
        </div>
        @endforelse

        @if($messages->hasPages())
        <div style="margin-top:20px;display:flex;justify-content:center;">{{ $messages->links() }}</div>
        @endif
    </div>
</div>
@endsection
