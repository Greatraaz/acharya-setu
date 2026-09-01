@php
    $showRoute = $showRoute ?? 'admin.community.show';
    $canDelete = $canDelete ?? false;
    $deleteRoute = $deleteRoute ?? null;
@endphp
<div class="community-channel-card group">
    <a href="{{ route($showRoute, $channel->slug) }}" style="flex:1;display:flex;flex-direction:column;text-decoration:none;color:inherit;min-width:0;">
        <div class="community-channel-card__head">
            @include('partials.community-channel-thumb', ['channel' => $channel, 'size' => 'card'])
            <span class="community-badge community-badge--{{ $channel->type === 'public' ? 'public' : 'private' }}">
                {{ $channel->type }}
            </span>
        </div>
        <h3 class="community-channel-card__title"># {{ $channel->name }}</h3>
        <p class="community-channel-card__desc">{{ $channel->description ?: 'No description.' }}</p>
        <div class="community-channel-card__meta">
            <span>{{ $channel->all_messages_count ?? $channel->messages_count ?? 0 }} messages</span>
            <span class="community-channel-card__meta-sep">|</span>
            <span>{{ $channel->members_count ?? 0 }} members</span>
            @if($channel->category)
                <span class="community-channel-card__meta-sep">|</span>
                <span class="community-channel-card__category">
                    {{ \App\Models\Channel::CATEGORIES[$channel->category] ?? $channel->category }}
                </span>
            @endif
            @if(($channel->unread_count ?? 0) > 0)
                <span class="community-channel-card__unread">{{ $channel->unread_count }} unread</span>
            @endif
        </div>
    </a>

    @if($canDelete && $deleteRoute)
    <div class="community-channel-card__footer">
        <form method="POST" action="{{ route($deleteRoute, $channel->slug) }}"
              onsubmit="return confirm('Permanently delete #{{ $channel->name }} and all messages? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="community-btn community-btn--danger community-btn--sm">Delete</button>
        </form>
    </div>
    @endif
</div>
