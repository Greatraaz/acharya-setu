@php
    $canPost = $channel->canPost(auth()->user());
@endphp

<div class="card" style="padding:0;overflow:hidden;min-height:calc(100vh - 220px);">
    <div style="display:grid;grid-template-columns:260px 1fr 220px;min-height:calc(100vh - 220px);">
        <aside style="border-right:1px solid var(--border);background:#f8fafc;overflow:auto;">
            <div style="padding:12px 14px;border-bottom:1px solid var(--border);font-size:12px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.04em;">
                Channels
            </div>
            <div style="padding:8px;display:grid;gap:4px;">
                @foreach($channels as $ch)
                    @php $active = (int) $ch->id === (int) $channel->id; @endphp
                    <a href="{{ route($showRouteName, $ch->slug) }}"
                       style="display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:10px;text-decoration:none;color:inherit;{{ $active ? 'background:#e8f1ff;border:1px solid #c7ddff;' : 'border:1px solid transparent;' }}">
                        @if($ch->image_url)
                            <img src="{{ $ch->image_url }}" alt="{{ $ch->name }}" style="width:30px;height:30px;border-radius:50%;object-fit:cover;">
                        @else
                            <span style="font-size:17px;line-height:1;">{{ $ch->icon }}</span>
                        @endif
                        <div style="min-width:0;flex:1;">
                            <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#000;">{{ $ch->name }}</div>
                            <div style="font-size:11px;color:var(--text-3);">{{ $ch->members_count }} members</div>
                        </div>
                        @if(($ch->unread_count ?? 0) > 0)
                            <span style="font-size:10px;font-weight:700;background:#2563eb;color:#fff;border-radius:999px;padding:2px 6px;">{{ $ch->unread_count }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </aside>

        <main style="display:flex;flex-direction:column;min-width:0;background:#efeae2;">
            <div style="background:#f0f2f5;border-bottom:1px solid var(--border);padding:10px 14px;display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                <div style="display:flex;gap:10px;align-items:flex-start;">
                    @if($channel->image_url)
                        <img src="{{ $channel->image_url }}" alt="{{ $channel->name }}" style="width:38px;height:38px;border-radius:50%;object-fit:cover;">
                    @else
                        <div style="width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#dbeafe;">{{ $channel->icon }}</div>
                    @endif
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#111827;">{{ $channel->name }}</div>
                        <div style="font-size:12px;color:#6b7280;">{{ $channel->description ?: 'Channel discussion' }}</div>
                        @if($channel->video_url)
                            <div style="margin-top:6px;max-width:260px;">
                                <video src="{{ $channel->video_url }}" controls playsinline preload="metadata" style="width:100%;max-height:90px;background:#000;border-radius:8px;"></video>
                            </div>
                        @endif
                    </div>
                </div>
                <a href="{{ $allChannelsRoute }}" class="btn btn-ghost btn-sm">All channels</a>
            </div>

            <div style="flex:1;overflow:auto;padding:14px 12px;">
                @forelse($messages as $message)
                    <div style="display:flex;justify-content:{{ (int)$message->user_id === (int)auth()->id() ? 'flex-end' : 'flex-start' }};margin-bottom:12px;">
                        <div style="max-width:min(78%,640px);background:{{ (int)$message->user_id === (int)auth()->id() ? '#d9fdd3' : '#fff' }};border-radius:10px;padding:10px 12px;border:1px solid rgba(0,0,0,.06);">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:4px;">
                                <strong style="font-size:12px;color:#0f172a;">{{ $message->user->name ?? 'User' }}</strong>
                                <span style="font-size:11px;color:#64748b;">{{ $message->created_at?->diffForHumans() }}</span>
                            </div>
                            @if($message->body)
                                <div style="font-size:13px;color:#0f172a;line-height:1.55;white-space:pre-wrap;">{{ $message->body }}</div>
                            @endif
                            @if($message->image_url)
                                <div style="margin-top:8px;">
                                    <a href="{{ $message->image_url }}" target="_blank" rel="noopener">
                                        <img src="{{ $message->image_url }}" alt="" class="channel-msg-image">
                                    </a>
                                </div>
                            @endif
                            @if($message->video_path)
                                @include('partials.community-message-video', ['message' => $message])
                            @endif

                            <div style="display:flex;align-items:center;gap:6px;margin-top:8px;flex-wrap:wrap;">
                                <form action="{{ route($likeRouteName, $message->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost btn-sm">👍 {{ is_array($message->liked_by) ? count($message->liked_by) : 0 }}</button>
                                </form>
                                @if($canPost)
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="toggleReply({{ $message->id }})">↩ Reply</button>
                                @endif
                                @include('partials.community-message-report', ['message' => $message])
                            </div>

                            @if($message->replies->isNotEmpty())
                                <div style="margin-top:8px;padding-left:10px;border-left:2px solid #cbd5e1;display:grid;gap:8px;">
                                    @foreach($message->replies as $reply)
                                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px;">
                                            <div style="font-size:11px;font-weight:700;color:#0f172a;">
                                                {{ $reply->user->name ?? 'User' }}
                                                <span style="font-weight:500;color:#64748b;"> · {{ $reply->created_at?->diffForHumans() }}</span>
                                            </div>
                                            @if($reply->body)
                                                <div style="font-size:12px;color:#334155;line-height:1.5;white-space:pre-wrap;">{{ $reply->body }}</div>
                                            @endif
                                            @if($reply->image_url)
                                                <div style="margin-top:6px;">
                                                    <a href="{{ $reply->image_url }}" target="_blank" rel="noopener">
                                                        <img src="{{ $reply->image_url }}" alt="" class="channel-msg-image channel-msg-image--reply">
                                                    </a>
                                                </div>
                                            @endif
                                            @if($reply->video_path)
                                                @include('partials.community-message-video', ['message' => $reply, 'reply' => true])
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if($canPost)
                                <div id="reply-{{ $message->id }}" style="display:none;margin-top:8px;">
                                    <form action="{{ route($storeRouteName, $channel->slug) }}" method="POST" enctype="multipart/form-data" class="channel-composer-form">
                                        @csrf
                                        <input type="hidden" name="parent_id" value="{{ $message->id }}">
                                        <div class="channel-composer channel-composer--reply">
                                            <input type="text" name="body" class="channel-composer__input" placeholder="Reply…" autocomplete="off">
                                            <div class="channel-composer__actions">
                                                <label class="channel-composer__attach" title="Add image">
                                                    <input type="file" name="image" accept="image/*" data-chip="reply-image-chip-{{ $message->id }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </label>
                                                @include('partials.community-video-attach', ['chipId' => 'reply-video-chip-'.$message->id])
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
                    </div>
                @empty
                    <div class="empty-state" style="padding:40px 0;">
                        <div style="font-size:15px;font-weight:700;color:#000;">No messages yet</div>
                        <p style="font-size:13px;color:var(--text-2);">Be the first to start the conversation.</p>
                    </div>
                @endforelse
            </div>

            @if($canPost)
                <div style="background:#f0f2f5;border-top:1px solid var(--border);padding:10px 12px;">
                    <form action="{{ route($storeRouteName, $channel->slug) }}" method="POST" enctype="multipart/form-data" class="channel-composer-form">
                        @csrf
                        <div class="channel-composer">
                            <input type="text" name="body" class="channel-composer__input" placeholder="Type a message" autocomplete="off">
                            <div class="channel-composer__actions">
                                <label class="channel-composer__attach" title="Add image">
                                    <input type="file" name="image" accept="image/*" data-chip="main-image-chip">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </label>
                                @include('partials.community-video-attach', ['chipId' => 'main-video-chip'])
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
        </main>

        <aside style="border-left:1px solid var(--border);background:#f8fafc;overflow:auto;">
            <div style="padding:12px 14px;border-bottom:1px solid var(--border);font-size:12px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.04em;">
                Members
            </div>
            <div style="padding:8px;display:grid;gap:6px;">
                @foreach(($members ?? collect()) as $member)
                    <div style="display:flex;align-items:center;gap:8px;padding:7px 8px;border-radius:8px;background:#fff;border:1px solid #e5e7eb;">
                        <div style="width:24px;height:24px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">
                            {{ strtoupper(substr($member->name, 0, 1)) }}   
                        </div>
                        <div style="min-width:0;">
                            <div style="font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#000;">{{ $member->name }}</div>
                            <div style="font-size:10px;color:#64748b;">{{ $member->role }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </aside>
    </div>
</div>
