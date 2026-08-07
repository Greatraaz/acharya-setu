@extends('frontend.layouts.app')
@section('title', $channel->name.' — Community')

@section('content')
@php $canPost = $channel->canPost(auth()->user()); @endphp
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between">
            <div>
                <div class="dash-title">{{ $channel->icon }} {{ $channel->name }}</div>
                <div class="dash-subtitle">{{ $channel->description ?: 'Channel discussion' }}</div>
            </div>
            <a href="{{ route('mentee.community.index') }}" class="btn btn-ghost">← All channels</a>
        </div>

        @unless($channel->isMember(auth()->user()))
        <div class="alert alert-info" style="margin-bottom:16px;">
            <span class="alert-icon">ℹ️</span>
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;width:100%;">
                <span style="font-size:13px;">Join this channel to post and reply.</span>
                <form action="{{ route('mentee.community.join', $channel->slug) }}" method="POST">
                    @csrf
                    <button class="btn btn-primary btn-sm" type="submit">Join channel</button>
                </form>
            </div>
        </div>
        @endunless

        @if($canPost)
        <div class="card" style="margin-bottom:16px;">
            <form action="{{ route('mentee.community.messages.store', $channel->slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <textarea name="body" class="form-input" rows="3" placeholder="Share an update with the channel..." style="resize:vertical;"></textarea>
                <div style="display:flex;justify-content:space-between;gap:10px;margin-top:10px;align-items:center;">
                    <input type="file" name="image" accept="image/*" style="font-size:12px;color:var(--text-2);">
                    <button type="submit" class="btn btn-primary btn-sm">Post</button>
                </div>
            </form>
        </div>
        @endif

        @forelse($messages as $message)
        <div class="card" style="margin-bottom:10px;padding:14px 16px;">
            <div style="display:flex;justify-content:space-between;gap:10px;margin-bottom:8px;align-items:flex-start;">
                <div style="font-size:13px;font-weight:700;color:var(--text);">
                    {{ $message->user->name ?? 'User' }}
                    <span style="font-weight:500;color:var(--text-3);">· {{ $message->created_at?->diffForHumans() }}</span>
                </div>
                <div style="display:flex;gap:6px;align-items:center;">
                    <form action="{{ route('mentee.community.messages.like', $message->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm">👍 {{ is_array($message->liked_by) ? count($message->liked_by) : 0 }}</button>
                    </form>
                    @if($canPost)
                    <button type="button" class="btn btn-ghost btn-sm" onclick="toggleReply({{ $message->id }})">↩ Reply</button>
                    @endif
                </div>
            </div>

            @if($message->body)
            <div style="font-size:13px;color:var(--text);white-space:pre-wrap;line-height:1.6;">{{ $message->body }}</div>
            @endif
            @if($message->image_url ?? $message->image_path)
            <div style="margin-top:10px;">
                <a href="{{ $message->image_url ?? asset('storage/'.$message->image_path) }}" target="_blank" rel="noopener">
                    <img src="{{ $message->image_url ?? asset('storage/'.$message->image_path) }}" alt="" class="channel-msg-image">
                </a>
            </div>
            @endif

            @if($message->replies->isNotEmpty())
            <div style="margin-top:14px;padding-left:12px;border-left:2px solid var(--border);display:grid;gap:10px;">
                @foreach($message->replies as $reply)
                <div>
                    <div style="font-size:12px;font-weight:700;color:var(--text);">
                        {{ $reply->user->name ?? 'User' }}
                        <span style="font-weight:500;color:var(--text-3);">· {{ $reply->created_at?->diffForHumans() }}</span>
                    </div>
                    @if($reply->body)
                    <div style="font-size:12px;color:var(--text-2);margin-top:2px;white-space:pre-wrap;line-height:1.55;">{{ $reply->body }}</div>
                    @endif
                    @if($reply->image_url ?? $reply->image_path)
                    <div style="margin-top:6px;">
                        <a href="{{ $reply->image_url ?? asset('storage/'.$reply->image_path) }}" target="_blank" rel="noopener">
                            <img src="{{ $reply->image_url ?? asset('storage/'.$reply->image_path) }}" alt="" class="channel-msg-image channel-msg-image--reply">
                        </a>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            @if($canPost)
            <div id="reply-{{ $message->id }}" style="display:none;margin-top:12px;">
                <form action="{{ route('mentee.community.messages.store', $channel->slug) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $message->id }}">
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <input type="text" name="body" class="form-input" style="flex:1;min-width:180px;" placeholder="Write a reply or attach an image…">
                        <label class="btn btn-ghost btn-sm" style="cursor:pointer;" title="Attach image">
                            🖼
                            <input type="file" name="image" accept="image/*" style="display:none;">
                        </label>
                        <button type="submit" class="btn btn-primary btn-sm">Send</button>
                        <button type="button" class="btn btn-ghost btn-sm" onclick="toggleReply({{ $message->id }})">Cancel</button>
                    </div>
                </form>
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

@push('scripts')
<script>
function toggleReply(id) {
    const el = document.getElementById('reply-' + id);
    if (!el) return;
    const open = el.style.display === 'block';
    document.querySelectorAll('[id^="reply-"]').forEach(node => {
        if (/^reply-\d+$/.test(node.id)) node.style.display = 'none';
    });
    if (!open) {
        el.style.display = 'block';
        el.querySelector('input[name="body"]')?.focus();
    }
}
</script>
@endpush
