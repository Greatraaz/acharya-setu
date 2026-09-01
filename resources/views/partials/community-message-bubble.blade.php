@php
    $currentUserId = (int) auth()->id();
    $isMine = (int) $message->user_id === $currentUserId;
    $rowClass = $isMine ? 'community-chat-row--mine' : 'community-chat-row--other';
    $bubbleClass = $isMine ? 'community-bubble--mine' : 'community-bubble--other';
    $likeCount = (int) ($message->likes_count ?? count($message->liked_by ?? []));
    $canReply = $channel->isMember(Auth::user()) || ($channel->type ?? '') === 'public';

    $quoteText = null;
    if ($message->parent) {
        $quoteText = trim((string) $message->parent->body);
        if ($quoteText === '' && $message->parent->image_path) {
            $quoteText = 'Photo';
        } elseif ($quoteText === '' && $message->parent->video_path) {
            $quoteText = 'Video';
        } elseif ($quoteText === '') {
            $quoteText = 'Message';
        }
    }
@endphp

<div class="community-chat-row {{ $rowClass }}"
     id="msg-{{ $message->id }}"
     data-message-id="{{ $message->id }}"
     data-is-mine="{{ $isMine ? '1' : '0' }}"
     oncontextmenu="CommunityMsgMenu.openContextMenu(event, {{ $message->id }})">

    <div class="community-bubble {{ $bubbleClass }}">
        @if(! $isMine)
        <div class="community-bubble__author">{{ $message->user->name }}</div>
        @endif

        @if($message->parent)
        <button type="button"
                class="community-bubble__quote"
                onclick="event.stopPropagation(); CommunityMsgMenu.scrollToMessage({{ $message->parent->id }})"
                title="Go to original message">
            <span class="community-bubble__quote-author">{{ $message->parent->user->name ?? 'User' }}</span>
            <span class="community-bubble__quote-text">{{ Str::limit($quoteText, 140) }}</span>
        </button>
        @endif

        @if($message->body)
        <p class="community-bubble__text">{{ $message->body }}</p>
        @endif

        @if($message->image_url)
        <a href="{{ $message->image_url }}" target="_blank" rel="noopener" class="community-bubble__media-link">
            <img src="{{ $message->image_url }}" alt="Attachment" class="channel-msg-image community-bubble__image">
        </a>
        @endif

        @if($message->video_path)
        @include('partials.community-message-video', ['message' => $message])
        @endif

        <div class="community-bubble__footer">
            <span class="community-bubble__time">{{ $message->created_at->format('H:i') }}</span>
            @if($likeCount > 0)
            <span class="community-bubble__likes {{ $message->isLikedBy(Auth::id()) ? 'is-liked' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                {{ $likeCount }}
            </span>
            @endif
        </div>
    </div>

    @include('partials.community-message-menu', [
        'message' => $message,
        'channel' => $channel,
        'routePrefix' => $routePrefix,
        'canReply' => $canReply,
        'contextOnly' => true,
    ])
</div>

@if($canReply)
<div id="reply-{{ $message->id }}" class="hidden community-chat-reply-form {{ $rowClass }}">
    <form method="POST" action="{{ route($routePrefix.'.messages.store', $channel->slug) }}" enctype="multipart/form-data" class="channel-composer-form community-chat-reply-form__inner">
        @csrf
        <input type="hidden" name="parent_id" value="{{ $message->id }}">
        <div class="community-thread__composer">
            <input type="text" name="body" placeholder="Reply to {{ $message->user->name }}…" class="community-thread__composer-input">
            <label class="community-thread__composer-icon" title="Add image">
                <input type="file" name="image" accept="image/*" class="hidden" onchange="previewReplyImage(this, {{ $message->id }})">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </label>
            @include('partials.community-video-attach', [
                'chipId' => 'reply-video-chip-'.$message->id,
                'wrapperClass' => 'community-thread__composer-icon',
                'svgClass' => 'w-4 h-4',
                'inputClass' => 'hidden',
            ])
            <button type="submit" class="community-btn community-btn--primary community-btn--sm">Send</button>
        </div>
        <div style="margin-top:6px;">
            <span id="reply-video-chip-{{ $message->id }}" class="channel-composer__chip"></span>
        </div>
        <img id="reply-preview-{{ $message->id }}" class="hidden channel-msg-image channel-msg-image--reply" style="margin-top:8px;" alt="">
    </form>
</div>
@endif
