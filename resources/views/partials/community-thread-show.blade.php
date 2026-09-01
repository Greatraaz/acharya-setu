@php
    $r = match (true) {
        request()->routeIs('admin.*') => 'admin.community',
        request()->routeIs('mentor.*') => 'mentor.community',
        default => 'mentee.community',
    };

    $indexRoute = match (true) {
        request()->routeIs('admin.*') => 'admin.community.index',
        request()->routeIs('mentor.*') => 'mentor.community',
        default => 'mentee.community.index',
    };

    $canCreateChannel = request()->routeIs('admin.*') || request()->routeIs('mentor.*');

    $prevDate = null;
@endphp

<link rel="stylesheet" href="{{ asset('css/community-thread.css') }}?v={{ filemtime(public_path('css/community-thread.css')) }}">

<div class="community-thread">

    {{-- Mobile channel picker --}}
    <div class="community-thread__mobile-picker">
        <label style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--ct-text-faint);display:block;margin-bottom:6px;">Channel</label>
        <select class="form-input form-select w-full"
                onchange="if (this.value) window.location.href = this.value">
            @foreach($channels as $ch)
            <option value="{{ route($r.'.show', $ch->slug) }}" @selected($ch->id === $channel->id)>
                #{{ $ch->name }}
            </option>
            @endforeach
        </select>
    </div>

    {{-- Sidebar --}}
    <aside class="community-thread__sidebar">
        <div class="community-thread__sidebar-head">Channels</div>
        <nav class="community-thread__nav">
            @foreach($channels as $ch)
            <a href="{{ route($r.'.show', $ch->slug) }}"
               class="community-thread__nav-link {{ $ch->id === $channel->id ? 'is-active' : '' }}">
                @include('partials.community-channel-thumb', ['channel' => $ch, 'size' => 'sidebar'])
                <span class="community-thread__nav-name">{{ $ch->name }}</span>
                @if(($ch->unread_count ?? 0) > 0)
                    <span class="community-thread__nav-unread">{{ $ch->unread_count }}</span>
                @endif
            </a>
            @endforeach
        </nav>
        @if($canCreateChannel)
        <div style="padding:8px;border-top:1px solid var(--ct-border);">
            <a href="{{ route($r.'.create') }}" class="community-thread__nav-link">
                <span style="width:20px;height:20px;border-radius:6px;border:1px dashed var(--ct-border-strong);display:flex;align-items:center;justify-content:center;font-size:12px;">+</span>
                New channel
            </a>
        </div>
        @endif
    </aside>

    {{-- Main --}}
    <div class="community-thread__main">

        {{-- Header --}}
        <div class="community-thread__header">
            <div class="community-thread__header-info">
                @include('partials.community-channel-thumb', ['channel' => $channel, 'size' => 'md'])
                <div>
                    <div class="community-thread__header-title"># {{ $channel->name }}</div>
                    @if($channel->description)
                    <div class="community-thread__header-desc">{{ $channel->description }}</div>
                    @endif
                </div>
            </div>
            <div class="community-thread__header-actions">
                @if(!$channel->isMember(Auth::user()))
                    @if($channel->isRemoved(Auth::user()))
                    <span class="text-xs" style="color:var(--warning,#d97706);background:var(--warning-muted,rgba(245,158,11,.12));padding:8px 12px;border-radius:8px;">
                        Removed — wait for a mentor invite to rejoin
                    </span>
                    @elseif($channel->type === 'public')
                    <form method="POST" action="{{ route($r.'.join', $channel->slug) }}">
                        @csrf
                        <button type="submit" class="community-btn community-btn--primary community-btn--sm">Join channel</button>
                    </form>
                    @else
                    <span class="text-xs" style="color:var(--ct-text-faint);">Private — invite only</span>
                    @endif
                @elseif((int) $channel->created_by !== (int) Auth::id())
                <form method="POST" action="{{ route($r.'.leave', $channel->slug) }}">
                    @csrf
                    <button type="submit" class="community-btn community-btn--ghost community-btn--sm">Leave</button>
                </form>
                @endif

                @if((int) $channel->created_by === (int) Auth::id() || Auth::user()->isAdmin())
                <form method="POST" action="{{ route($r.'.destroy', $channel->slug) }}"
                      onsubmit="return confirm('Permanently delete #{{ $channel->name }} and all messages? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="community-btn community-btn--danger community-btn--sm">Delete channel</button>
                </form>
                @endif
            </div>
        </div>

        {{-- Messages --}}
        <div class="community-thread__messages" id="messages-container">
            @if($messages->hasPages() && $messages->currentPage() > 1)
            <div class="community-thread__load-older">
                @if(request()->routeIs('admin.*'))
                    <a href="{{ $messages->previousPageUrl() }}" class="community-btn community-btn--ghost community-btn--sm">↑ Load older messages</a>
                @else
                    <a href="{{ $messages->previousPageUrl() }}" class="btn btn-ghost btn-sm">↑ Load older messages</a>
                @endif
                <span class="community-thread__load-older-meta">Page {{ $messages->currentPage() }} of {{ $messages->lastPage() }}</span>
            </div>
            @endif

            @forelse($messages as $message)
            @php
                $msgDate = $message->created_at->toDateString();
            @endphp

            @if($msgDate !== $prevDate)
            <div class="community-thread__date-divider">
                <span>
                    @if($message->created_at->isToday()) Today
                    @elseif($message->created_at->isYesterday()) Yesterday
                    @else {{ $message->created_at->format('d M Y') }}
                    @endif
                </span>
            </div>
            @php $prevDate = $msgDate; @endphp
            @endif

            @include('partials.community-message-bubble', [
                'message' => $message,
                'channel' => $channel,
                'routePrefix' => $r,
            ])
            @empty
            <div class="community-thread__empty">
                @include('partials.community-channel-thumb', ['channel' => $channel, 'size' => 'lg'])
                <p class="community-thread__empty-title" style="margin-top:16px;">No messages yet</p>
                <p class="community-thread__empty-sub">Be the first to say something in #{{ $channel->name }}</p>
            </div>
            @endforelse
        </div>

        @if($channel->canPost(Auth::user()))
        <div class="community-thread__composer-wrap">
            <form method="POST" action="{{ route($r.'.messages.store', $channel->slug) }}" id="main-form" enctype="multipart/form-data" class="channel-composer-form">
                @csrf
                <div class="community-thread__composer">
                    <input type="text" name="body" id="main-input" placeholder="Message #{{ $channel->name }}…" autocomplete="off" class="community-thread__composer-input">
                    <label class="community-thread__composer-icon" title="Add image">
                        <input type="file" name="image" id="main-image" accept="image/*" class="hidden" onchange="previewMainImage(this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </label>
                    @include('partials.community-video-attach', [
                        'chipId' => 'main-video-chip',
                        'wrapperClass' => 'community-thread__composer-icon',
                        'svgClass' => 'w-5 h-5',
                        'inputClass' => 'hidden',
                        'inputId' => 'main-video',
                    ])
                    <button type="submit" class="community-thread__composer-send" aria-label="Send message">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="white" viewBox="0 0 16 16">
                            <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338L.684 6.266l13.447-3.69z"/>
                        </svg>
                    </button>
                </div>
                <div style="margin-top:6px;">
                    <span id="main-video-chip" class="channel-composer__chip"></span>
                </div>
                <img id="main-image-preview" class="hidden channel-msg-image" style="margin-top:8px;max-height:112px;" alt="">
            </form>
        </div>
        @elseif($channel->isRemoved(Auth::user()))
        <div class="community-thread__composer-wrap" style="background:var(--warning-muted,rgba(245,158,11,.08));text-align:center;">
            <p class="text-xs" style="color:var(--warning,#d97706);">You were removed from this channel. Ask a mentor to invite you again.</p>
        </div>
        @else
        <div class="community-thread__composer-wrap" style="background:var(--ct-bg-muted);text-align:center;">
            <p class="text-xs" style="color:var(--ct-text-faint);margin-bottom:8px;">Join this channel to send messages</p>
            @if($channel->type === 'public')
            <form method="POST" action="{{ route($r.'.join', $channel->slug) }}">
                @csrf
                <button type="submit" class="community-btn community-btn--primary">Join #{{ $channel->name }}</button>
            </form>
            @endif
        </div>
        @endif
    </div>

    {{-- Members --}}
    <aside class="community-thread__members">
        <div class="community-thread__sidebar-head">
            Members
            <div style="font-size:12px;font-weight:400;text-transform:none;letter-spacing:0;color:var(--ct-text-muted);margin-top:2px;">
                {{ ($members ?? collect())->count() }} people
            </div>
        </div>
        <div class="community-thread__nav" style="padding-top:4px;">
            @foreach(($members ?? collect())->groupBy(fn($m) => $m->pivot->role) as $role => $group)
                <p style="padding:8px 12px 4px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--ct-text-faint);">
                    {{ $role }} — {{ $group->count() }}
                </p>
                @foreach($group as $member)
                <div style="display:flex;align-items:center;gap:8px;padding:6px 12px;border-radius:8px;" onmouseenter="this.style.background='var(--ct-hover)'" onmouseleave="this.style.background='transparent'">
                    <div class="community-thread__reply-avatar" style="width:24px;height:24px;font-size:10px;background:var(--ct-composer-bg);color:var(--ct-text-muted);">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                    <div style="min-width:0;flex:1;">
                        <p style="font-size:12px;font-weight:600;color:var(--ct-text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $member->name }}</p>
                        <p style="font-size:10px;color:var(--ct-text-faint);">{{ $member->role }}</p>
                    </div>
                    @if($channel->created_by !== $member->id && ($channel->isAdmin(Auth::user()) || Auth::user()->isAdmin() || $channel->created_by === Auth::id()))
                    <form method="POST" action="{{ route($r.'.members.remove', [$channel->slug, $member->id]) }}"
                          onsubmit="return confirm('Remove {{ $member->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="font-size:10px;color:var(--ct-text-faint);background:none;border:none;cursor:pointer;">×</button>
                    </form>
                    @endif
                </div>
                @endforeach
            @endforeach
        </div>

        @if($channel->isAdmin(Auth::user()) || Auth::user()->isAdmin() || $channel->created_by === Auth::id())
        <div style="padding:12px;border-top:1px solid var(--ct-border);">
            <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--ct-text-faint);margin-bottom:8px;">Invite</p>
            <form method="POST" action="{{ route($r.'.invite', $channel->slug) }}">
                @csrf
                <select name="user_id" required class="form-input form-select" style="width:100%;font-size:12px;margin-bottom:8px;">
                    <option value="">Select mentor/mentee…</option>
                    @foreach(($inviteCandidates ?? []) as $candidate)
                        <option value="{{ $candidate->id }}">{{ $candidate->name }} ({{ $candidate->role }})</option>
                    @endforeach
                </select>
                <button type="submit" class="community-btn community-btn--primary community-btn--sm" style="width:100%;">Invite</button>
            </form>
        </div>
        @endif
    </aside>
</div>

@once
@push('scripts')
<script>
window.CommunityMsgMenu = {
    closeAll() {
        document.querySelectorAll('.community-msg-menu').forEach(menu => {
            menu.classList.add('hidden');
            menu.style.position = '';
            menu.style.left = '';
            menu.style.top = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
        document.querySelectorAll('.community-msg-menu-btn[aria-expanded="true"]').forEach(btn => {
            btn.setAttribute('aria-expanded', 'false');
        });
    },

    positionMenu(menu, x, y) {
        menu.classList.remove('hidden');
        menu.style.position = 'fixed';
        menu.style.zIndex = '9999';

        const rect = menu.getBoundingClientRect();
        const pad = 8;
        let left = x;
        let top = y;

        if (left + rect.width > window.innerWidth - pad) {
            left = window.innerWidth - rect.width - pad;
        }
        if (top + rect.height > window.innerHeight - pad) {
            top = window.innerHeight - rect.height - pad;
        }

        menu.style.left = Math.max(pad, left) + 'px';
        menu.style.top = Math.max(pad, top) + 'px';
    },

    toggleButton(event, id) {
        event.preventDefault();
        event.stopPropagation();

        const menu = document.getElementById('msg-menu-' + id);
        const btn = event.currentTarget;
        if (!menu) return;

        const willOpen = menu.classList.contains('hidden');
        this.closeAll();

        if (willOpen) {
            menu.classList.remove('hidden');
            const rect = btn.getBoundingClientRect();
            const menuWidth = menu.offsetWidth || 168;
            this.positionMenu(menu, rect.right - menuWidth, rect.bottom + 6);
            btn.setAttribute('aria-expanded', 'true');
        }
    },

    openContextMenu(event, id) {
        event.preventDefault();
        event.stopPropagation();
        this.closeAll();

        const menu = document.getElementById('msg-menu-' + id);
        if (!menu) return;

        this.positionMenu(menu, event.clientX, event.clientY);
    },

    reply(id) {
        this.closeAll();
        toggleReply(id);
    },

    scrollToMessage(id) {
        this.closeAll();
        const el = document.getElementById('msg-' + id);
        if (!el) {
            window.alert('Original message is not in the current view. Try loading older messages.');
            return;
        }
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.classList.add('community-chat-row--highlight');
        window.setTimeout(() => el.classList.remove('community-chat-row--highlight'), 1800);
    }
};

function toggleReply(id) {
    const target = document.getElementById('reply-' + id);
    if (!target) return;
    const wasHidden = target.classList.contains('hidden');
    document.querySelectorAll('[id^="reply-"]').forEach(el => {
        if (/^reply-\d+$/.test(el.id)) el.classList.add('hidden');
    });
    if (wasHidden) {
        target.classList.remove('hidden');
        target.querySelector('input[name="body"]')?.focus();
    }
}

function previewMainImage(input) {
    const img = document.getElementById('main-image-preview');
    if (!img) return;
    if (input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
        img.classList.remove('hidden');
    } else {
        img.classList.add('hidden');
    }
}

function previewReplyImage(input, id) {
    const img = document.getElementById('reply-preview-' + id);
    if (!img) return;
    if (input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
        img.classList.remove('hidden');
    } else {
        img.classList.add('hidden');
    }
}

function scrollMessagesToBottom() {
    const c = document.getElementById('messages-container');
    if (!c) return;
    c.scrollTop = c.scrollHeight;
}

document.addEventListener('click', () => CommunityMsgMenu.closeAll());
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') CommunityMsgMenu.closeAll();
});
window.addEventListener('resize', () => CommunityMsgMenu.closeAll());

document.addEventListener('DOMContentLoaded', () => {
    scrollMessagesToBottom();
    requestAnimationFrame(scrollMessagesToBottom);
    setTimeout(scrollMessagesToBottom, 100);
});

document.getElementById('main-input')?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('main-form')?.submit();
    }
});
</script>
@include('partials.community-composer-scripts')
@endpush
@endonce
