@php
    $routePrefix = $routePrefix ?? match (true) {
        request()->routeIs('admin.*') => 'admin.community',
        request()->routeIs('mentor.*') => 'mentor.community',
        default => 'mentee.community',
    };

    $user = auth()->user();
    $isOwner = (int) $message->user_id === (int) $user->id;
    $isLiked = $message->isLikedBy($user->id);
    $canReply = $canReply ?? ($channel->isMember($user) || ($channel->type ?? '') === 'public');
    $canDelete = $isOwner
        || $user->isAdmin()
        || ($channel->isAdmin($user) ?? false);
    $canReport = ! $isOwner;
    $likeCount = (int) ($message->likes_count ?? count($message->liked_by ?? []));
@endphp

<div class="community-msg-menu-wrap {{ !empty($contextOnly) ? 'community-msg-menu-wrap--context-only' : '' }}">
    @empty($contextOnly)
    <button type="button"
            class="community-msg-menu-btn"
            aria-label="Message options"
            aria-haspopup="true"
            aria-expanded="false"
            onclick="CommunityMsgMenu.toggleButton(event, {{ $message->id }})">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/>
        </svg>
    </button>
    @endempty

    <div id="msg-menu-{{ $message->id }}"
         class="community-msg-menu hidden"
         role="menu"
         aria-label="Message actions">
        <form method="POST" action="{{ route($routePrefix.'.messages.like', $message) }}" role="none">
            @csrf
            <button type="submit" class="community-msg-menu__item" role="menuitem">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="{{ $isLiked ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                {{ $isLiked ? 'Unlike' : 'Like' }}{{ $likeCount > 0 ? ' ('.$likeCount.')' : '' }}
            </button>
        </form>

        @if($canReply)
        <button type="button"
                class="community-msg-menu__item"
                role="menuitem"
                onclick="CommunityMsgMenu.reply({{ $message->id }})">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                <path d="M6.598 5.013a.144.144 0 0 1 .202.134V6.3a.5.5 0 0 0 .5.5c.667 0 2.013.005 3.3.822.984.624 1.99 1.76 2.595 3.876-1.02-.983-2.185-1.516-3.205-1.799a8.7 8.7 0 0 0-1.921-.306 7 7 0 0 0-.798.008h-.013l-.005.001h-.001L7.3 9.9l-.05-.498a.5.5 0 0 0-.45.498v1.153c0 .108-.11.176-.202.134L2.614 8.254l-.042-.028a.147.147 0 0 1 0-.252l.042-.028z"/>
            </svg>
            Reply
        </button>
        @endif

        @if($canReport)
        <form method="POST"
              action="{{ route($routePrefix.'.messages.report', $message) }}"
              role="none"
              onsubmit="return confirm('Report this post? It will be hidden from your view.')">
            @csrf
            <button type="submit" class="community-msg-menu__item community-msg-menu__item--warn" role="menuitem">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M7.002 1.598a1 1 0 0 1 .996.006l6.002 3.499a1 1 0 0 1 .496.864v6.066a1 1 0 0 1-.496.864l-6.002 3.499a1 1 0 0 1-.996-.006l-6.002-3.499A1 1 0 0 1 1 12.027V5.967a1 1 0 0 1 .496-.864zM8 4.754a.75.75 0 0 0-.75.75v4.5a.75.75 0 0 0 1.5 0v-4.5A.75.75 0 0 0 8 4.754m0 8.002a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                </svg>
                Report
            </button>
        </form>
        @endif

        @if($canDelete)
        <form method="POST"
              action="{{ route($routePrefix.'.messages.destroy', $message) }}"
              role="none"
              onsubmit="return confirm('Delete this message?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="community-msg-menu__item community-msg-menu__item--danger" role="menuitem">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                </svg>
                Delete
            </button>
        </form>
        @endif
    </div>
</div>
