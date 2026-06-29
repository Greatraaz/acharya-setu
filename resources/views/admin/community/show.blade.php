@extends('admin.layouts.app')
@section('title', '#' . $channel->name)
@section('content')

@php
$avatarColors = [
    'bg-blue-100 text-blue-700',
    'bg-emerald-100 text-emerald-700',
    'bg-pink-100 text-pink-700',
    'bg-amber-100 text-amber-700',
    'bg-violet-100 text-violet-700',
    'bg-orange-100 text-orange-700',
    'bg-teal-100 text-teal-700',
    'bg-red-100 text-red-700',
];
$colorMap   = [];
$colorIndex = 0;
$prevDate   = null;
@endphp

<div class="flex h-[calc(100vh-7rem)] bg-white border border-gray-200 rounded-2xl overflow-hidden">

    {{-- ══════════ SIDEBAR ══════════ --}}
    <aside class="w-56 flex-shrink-0 flex flex-col border-r border-gray-100 bg-gray-50/60">

        <div class="px-4 py-3.5 border-b border-gray-100">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Channels</p>
        </div>

        <nav class="flex-1 overflow-y-auto p-2 space-y-0.5">
            @foreach($channels as $ch)
            @php $isActive = $ch->id === $channel->id; @endphp
            <a href="{{ route('admin.community.show', $ch->slug) }}"
               class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm transition-all
                      {{ $isActive
                          ? 'bg-white text-gray-900 font-semibold shadow-sm border border-gray-200/80'
                          : 'text-gray-500 hover:bg-white hover:text-gray-700 hover:shadow-sm hover:border hover:border-gray-200/60 border border-transparent' }}">
                <span class="text-sm leading-none flex-shrink-0">{{ $ch->icon }}</span>
                <span class="text-gray-400 text-xs flex-shrink-0 font-medium">#</span>
                <span class="truncate text-[13px]">{{ $ch->name }}</span>
                @if(isset($ch->unread_count) && $ch->unread_count > 0)
                    <span class="ml-auto bg-blue-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none min-w-[18px] text-center">
                        {{ $ch->unread_count }}
                    </span>
                @endif
            </a>
            @endforeach
        </nav>

        <div class="p-2 border-t border-gray-100">
            <a href="{{ route('admin.community.create') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-xl text-[13px] text-gray-400
                      hover:bg-white hover:text-gray-600 hover:shadow-sm hover:border hover:border-gray-200
                      border border-transparent transition-all">
                <span class="w-5 h-5 rounded-md border border-dashed border-gray-300 flex items-center justify-center text-xs leading-none flex-shrink-0">+</span>
                New channel
            </a>
        </div>
    </aside>

    {{-- ══════════ MAIN ══════════ --}}
    <div class="flex-1 flex flex-col min-w-0 bg-white">

        {{-- Channel Header --}}
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center text-xl leading-none flex-shrink-0">
                    {{ $channel->icon }}
                </div>
                <div>
                    <div class="flex items-center gap-1">
                        <span class="text-sm font-bold text-gray-400">#</span>
                        <h2 class="text-sm font-semibold text-gray-900">{{ $channel->name }}</h2>
                    </div>
                    @if($channel->description)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $channel->description }}</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if(!$channel->isMember(Auth::user()))
                <form method="POST" action="{{ route('admin.community.join', $channel->slug) }}">
                    @csrf
                    <button type="submit"
                            class="text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white px-3.5 py-2 rounded-lg transition-colors">
                        Join channel
                    </button>
                </form>
                @elseif($channel->created_by !== Auth::id())
                <form method="POST" action="{{ route('admin.community.leave', $channel->slug) }}">
                    @csrf
                    <button type="submit"
                            class="text-xs font-medium text-gray-400 hover:text-red-500 hover:bg-red-50 px-3 py-1.5 rounded-lg border border-transparent hover:border-red-100 transition-all">
                        Leave
                    </button>
                </form>
                @else
                <form method="POST" action="{{ route('admin.community.destroy', $channel->slug) }}"
                      onsubmit="return confirm('Permanently delete #{{ $channel->name }} and all messages?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="text-xs font-medium text-gray-400 hover:text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-lg border border-transparent hover:border-red-100 transition-all">
                        Delete channel
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto py-4 px-5" id="messages-container">
            @forelse($messages as $message)
            @php
                $msgDate = $message->created_at->toDateString();

                if (!isset($colorMap[$message->user_id])) {
                    $colorMap[$message->user_id] = $avatarColors[$colorIndex % count($avatarColors)];
                    $colorIndex++;
                }
                $avClass = $colorMap[$message->user_id];
                $initial = strtoupper(substr($message->user->name, 0, 1));
            @endphp

            {{-- Date divider --}}
            @if($msgDate !== $prevDate)
            <div class="flex items-center gap-3 py-3 select-none">
                <div class="flex-1 h-px bg-gray-100"></div>
                <span class="text-xs text-gray-400 font-medium px-1 flex-shrink-0">
                    @if($message->created_at->isToday()) Today
                    @elseif($message->created_at->isYesterday()) Yesterday
                    @else {{ $message->created_at->format('d M Y') }}
                    @endif
                </span>
                <div class="flex-1 h-px bg-gray-100"></div>
            </div>
            @php $prevDate = $msgDate; @endphp
            @endif

            {{-- Message --}}
            <div class="group flex gap-3 px-2 py-1.5 -mx-2 rounded-xl hover:bg-gray-50/80 transition-colors duration-100"
                 id="msg-{{ $message->id }}">

                {{-- Avatar --}}
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5 {{ $avClass }}">
                    {{ $initial }}
                </div>

                <div class="flex-1 min-w-0">

                    {{-- Meta --}}
                    <div class="flex items-baseline gap-2 mb-0.5">
                        <span class="text-sm font-semibold text-gray-900 leading-snug">{{ $message->user->name }}</span>
                        <span class="text-[11px] text-gray-400">{{ $message->created_at->format('H:i') }}</span>
                        <span class="text-[11px] text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity">
                            · {{ $message->created_at->diffForHumans() }}
                        </span>
                        @if($message->user_id === Auth::id())
                        <form method="POST"
                              action="{{ route('admin.community.messages.destroy', $message) }}"
                              class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-1 text-[11px] text-gray-300 hover:text-red-400 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                </svg>
                                Delete
                            </button>
                        </form>
                        @endif
                    </div>

                    {{-- Body --}}
                    <p class="text-sm text-gray-700 leading-relaxed break-words">{{ $message->body }}</p>

                    {{-- Replies thread --}}
                    @if($message->replies->isNotEmpty())
                    <div class="mt-3 pl-3 border-l-2 border-gray-100 space-y-2">
                        @foreach($message->replies as $reply)
                        @php
                            if (!isset($colorMap[$reply->user_id])) {
                                $colorMap[$reply->user_id] = $avatarColors[$colorIndex % count($avatarColors)];
                                $colorIndex++;
                            }
                        @endphp
                        <div class="flex gap-2">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold flex-shrink-0 mt-0.5 {{ $colorMap[$reply->user_id] }}">
                                {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-baseline gap-1.5 mb-0.5">
                                    <span class="text-xs font-semibold text-gray-800">{{ $reply->user->name }}</span>
                                    <span class="text-[10px] text-gray-400">{{ $reply->created_at->format('H:i') }}</span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed">{{ $reply->body }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- Reply trigger --}}
                    @if($channel->isMember(Auth::user()))
                    <button type="button"
                            onclick="toggleReply({{ $message->id }})"
                            class="inline-flex items-center gap-1 mt-1.5 text-[11px] text-gray-400
                                   hover:text-blue-600 opacity-0 group-hover:opacity-100 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M6.598 5.013a.144.144 0 0 1 .202.134V6.3a.5.5 0 0 0 .5.5c.667 0 2.013.005 3.3.822.984.624 1.99 1.76 2.595 3.876-1.02-.983-2.185-1.516-3.205-1.799a8.7 8.7 0 0 0-1.921-.306 7 7 0 0 0-.798.008h-.013l-.005.001h-.001L7.3 9.9l-.05-.498a.5.5 0 0 0-.45.498v1.153c0 .108-.11.176-.202.134L2.614 8.254l-.042-.028a.147.147 0 0 1 0-.252l.042-.028z"/>
                        </svg>
                        Reply
                    </button>

                    <div id="reply-{{ $message->id }}" class="hidden mt-2">
                        <form method="POST" action="{{ route('admin.community.messages.store', $channel->slug) }}">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $message->id }}">
                            <div class="flex items-center gap-2 bg-gray-100 border border-gray-200 rounded-xl px-3.5 py-2
                                        focus-within:bg-white focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 transition-all">
                                <input type="text"
                                       name="body"
                                       placeholder="Reply to {{ $message->user->name }}…"
                                       class="flex-1 bg-transparent text-sm text-gray-800 placeholder-gray-400 outline-none"
                                       required>
                                <button type="submit"
                                        class="text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg flex-shrink-0 transition-colors">
                                    Send
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif

                </div>{{-- /msg-body --}}
            </div>{{-- /msg-group --}}

            @empty
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 border border-gray-200 flex items-center justify-center text-3xl mb-4 leading-none">
                    {{ $channel->icon }}
                </div>
                <p class="text-sm font-semibold text-gray-600 mb-1">No messages yet</p>
                <p class="text-xs text-gray-400">Be the first to say something in #{{ $channel->name }}</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($messages->hasPages())
        <div class="px-5 py-2 border-t border-gray-100 flex justify-center">
            {{ $messages->links() }}
        </div>
        @endif

        {{-- Input --}}
        @if($channel->isMember(Auth::user()) || ($channel->type ?? '') === 'public')
        <div class="px-4 py-3 border-t border-gray-100 flex-shrink-0">
            <form method="POST"
                  action="{{ route('admin.community.messages.store', $channel->slug) }}"
                  id="main-form"
                  class="flex items-center gap-3 bg-gray-100 border border-gray-200 rounded-2xl px-4 py-2.5
                         focus-within:bg-white focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 transition-all">
                @csrf
                <input type="text"
                       name="body"
                       id="main-input"
                       placeholder="Message #{{ $channel->name }}…"
                       autocomplete="off"
                       required
                       class="flex-1 bg-transparent text-sm text-gray-800 placeholder-gray-400 outline-none min-w-0">
                <button type="submit"
                        class="w-8 h-8 bg-blue-600 hover:bg-blue-700 active:scale-95 rounded-xl
                               flex items-center justify-center flex-shrink-0 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 fill-white" viewBox="0 0 16 16">
                        <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338L.684 6.266l13.447-3.69z"/>
                    </svg>
                </button>
            </form>
        </div>
        @else
        <div class="px-4 py-4 border-t border-gray-100 bg-gray-50 text-center flex-shrink-0">
            <p class="text-xs text-gray-400 mb-2">Join this channel to send messages</p>
            <form method="POST" action="{{ route('admin.community.join', $channel->slug) }}">
                @csrf
                <button type="submit"
                        class="text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl transition-colors">
                    Join #{{ $channel->name }}
                </button>
            </form>
        </div>
        @endif

    </div>{{-- /main --}}
</div>{{-- /wrap --}}

@push('scripts')
<script>
function toggleReply(id) {
    const allForms = document.querySelectorAll('[id^="reply-"]');
    const target   = document.getElementById('reply-' + id);
    const wasHidden = target.classList.contains('hidden');

    allForms.forEach(el => el.classList.add('hidden'));

    if (wasHidden) {
        target.classList.remove('hidden');
        const inp = target.querySelector('input[name="body"]');
        if (inp) inp.focus();
    }
}

(function () {
    const c = document.getElementById('messages-container');
    if (c) c.scrollTop = c.scrollHeight;
})();

document.getElementById('main-input')?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('main-form').submit();
    }
});
</script>
@endpush

@endsection