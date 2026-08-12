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
            <form action="{{ route('mentee.community.messages.store', $channel->slug) }}" method="POST" enctype="multipart/form-data" class="channel-composer-form">
                @csrf
                <div class="channel-composer">
                    <input type="text" name="body" class="channel-composer__input" placeholder="Message #{{ $channel->name }}…" autocomplete="off">
                    <div class="channel-composer__actions">
                        <label class="channel-composer__attach" title="Add image">
                            <input type="file" name="image" accept="image/*" data-chip="main-image-chip">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </label>
                        <label class="channel-composer__attach" title="Add video">
                            <input type="file" name="video" accept="video/mp4,video/webm,video/quicktime,video/x-msvideo,video/mpeg,.mp4,.mov,.avi,.webm,.mpeg,.mpg" data-chip="main-video-chip">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </label>
                        <button type="submit" class="channel-composer__send" title="Send" aria-label="Send message">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" aria-hidden="true">
                                <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338L.684 6.266l13.447-3.69z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="channel-composer__meta">
                    <span id="main-image-chip" class="channel-composer__chip"></span>
                    <span id="main-video-chip" class="channel-composer__chip"></span>
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
            @if($message->video_url ?? $message->video_path)
            <div style="margin-top:10px;">
                <video class="channel-msg-video" controls playsinline preload="metadata" src="{{ $message->video_url ?? asset('storage/'.$message->video_path) }}"></video>
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
                    @if($reply->video_url ?? $reply->video_path)
                    <div style="margin-top:6px;">
                        <video class="channel-msg-video channel-msg-video--reply" controls playsinline preload="metadata" src="{{ $reply->video_url ?? asset('storage/'.$reply->video_path) }}"></video>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            @if($canPost)
            <div id="reply-{{ $message->id }}" style="display:none;margin-top:12px;">
                <form action="{{ route('mentee.community.messages.store', $channel->slug) }}" method="POST" enctype="multipart/form-data" class="channel-composer-form">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $message->id }}">
                    <div class="channel-composer channel-composer--reply">
                        <input type="text" name="body" class="channel-composer__input" placeholder="Reply to {{ $message->user->name ?? 'message' }}…" autocomplete="off">
                        <div class="channel-composer__actions">
                            <label class="channel-composer__attach" title="Add image">
                                <input type="file" name="image" accept="image/*" data-chip="reply-image-chip-{{ $message->id }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </label>
                            <label class="channel-composer__attach" title="Add video">
                                <input type="file" name="video" accept="video/mp4,video/webm,video/quicktime,video/x-msvideo,video/mpeg,.mp4,.mov,.avi,.webm,.mpeg,.mpg" data-chip="reply-video-chip-{{ $message->id }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </label>
                            <button type="submit" class="channel-composer__send" title="Send" aria-label="Send reply">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" aria-hidden="true">
                                    <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338L.684 6.266l13.447-3.69z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="channel-composer__meta">
                        <span id="reply-image-chip-{{ $message->id }}" class="channel-composer__chip"></span>
                        <span id="reply-video-chip-{{ $message->id }}" class="channel-composer__chip"></span>
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

document.addEventListener('change', function (e) {
    const input = e.target;
    if (!(input instanceof HTMLInputElement) || input.type !== 'file') return;
    const chipId = input.getAttribute('data-chip');
    if (!chipId) return;
    const chip = document.getElementById(chipId);
    const label = input.closest('.channel-composer__attach');
    if (!chip) return;
    if (input.files && input.files[0]) {
        chip.textContent = input.files[0].name;
        chip.classList.add('is-visible');
        label?.classList.add('is-active');
    } else {
        chip.textContent = '';
        chip.classList.remove('is-visible');
        label?.classList.remove('is-active');
    }
});
</script>
@endpush
