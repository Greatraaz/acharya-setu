@extends('admin.layouts.app')
@section('title', 'Session: ' . $session->booking_ref)
@section('heading', 'Session Detail')
@section('content')

@php $sc = $session->status_color; @endphp

<div class="max-w-6xl space-y-5">

    {{-- Top bar --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.sessions.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            All Sessions
        </a>
        <div class="flex gap-2">
            @if($session->status === 'pending')
            <form method="POST" action="{{ route('admin.sessions.confirm', $session) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-medium bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl transition-colors">
                    ✓ Confirm Session
                </button>
            </form>
            @endif
            @if(in_array($session->status, ['pending','confirmed']))
            <button onclick="document.getElementById('cancel-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 text-sm font-medium bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-xl transition-colors border border-red-200">
                Cancel Session
            </button>
            @endif
            @if($session->status === 'confirmed')
            <form method="POST" action="{{ route('admin.sessions.complete', $session) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl transition-colors">
                    Mark Completed
                </button>
            </form>
            @endif
            <a href="{{ route('admin.sessions.edit', $session) }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-xl transition-colors bg-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
        </div>
    </div>

    @foreach(['success','error','info'] as $type)
    @if(session($type))
    <div class="flex items-center gap-3 text-sm px-4 py-3 rounded-xl border
        {{ $type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : ($type === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-blue-50 border-blue-200 text-blue-800') }}">
        {{ session($type) }}
    </div>
    @endif
    @endforeach

    <div class="grid grid-cols-3 gap-5">

        {{-- LEFT: Main details --}}
        <div class="col-span-2 space-y-5">

            {{-- Session overview card --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <div class="flex items-start justify-between mb-5">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="text-base font-bold text-gray-900">{{ $session->title }}</h2>
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full"
                                  style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $sc['dot'] }}"></span>
                                {{ ucfirst(str_replace('_', ' ', $session->status)) }}
                            </span>
                        </div>
                        <div class="font-mono text-xs text-gray-400">{{ $session->booking_ref }}</div>
                    </div>
                    @if($session->meeting_link)
                    <a href="{{ $session->meeting_link }}" target="_blank"
                       class="inline-flex items-center gap-2 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Join {{ ucfirst($session->meeting_provider ?? 'Meeting') }}
                    </a>
                    @endif
                </div>

                {{-- Key details grid --}}
                <div class="grid grid-cols-2 gap-4">
                    @foreach([
                        ['Scheduled', $session->scheduled_at->format('D, d M Y · H:i').' – '.$session->scheduled_end->format('H:i').' ('.$session->timezone.')'],
                        ['Duration', $session->duration_minutes.' minutes'],
                        ['Amount', $session->amount > 0 ? '₹'.number_format($session->amount,0).' ('.$session->payment_status.')' : 'Free'],
                        ['Provider', ucfirst($session->meeting_provider ?? 'Not set')],
                    ] as [$label, $value])
                    <div class="bg-gray-50 rounded-xl px-4 py-3">
                        <div class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">{{ $label }}</div>
                        <div class="text-sm font-semibold text-gray-800">{{ $value }}</div>
                    </div>
                    @endforeach
                </div>

                @if($session->agenda)
                <div class="mt-4 p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                    <div class="text-xs text-indigo-600 font-semibold uppercase tracking-wide mb-2">Session Agenda</div>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $session->agenda }}</p>
                </div>
                @endif

                @if($session->actual_duration_seconds)
                <div class="mt-4 p-3 bg-green-50 rounded-xl border border-green-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm text-green-800">Actual duration: <strong>{{ $session->actual_duration_formatted }}</strong></span>
                </div>
                @endif

                @if($session->status === 'cancelled')
                <div class="mt-4 p-4 bg-red-50 rounded-xl border border-red-100">
                    <div class="text-xs text-red-600 font-semibold uppercase tracking-wide mb-1">Cancellation Details</div>
                    <p class="text-sm text-gray-700">Cancelled by <strong>{{ $session->cancelledBy?->name ?? 'Admin' }}</strong> on {{ $session->cancelled_at?->format('d M Y H:i') }}</p>
                    @if($session->cancellation_reason)
                    <p class="text-sm text-gray-600 mt-1">Reason: {{ $session->cancellation_reason }}</p>
                    @endif
                </div>
                @endif
            </div>

            {{-- Reviews --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-sm font-bold text-gray-800">Session Reviews</h3>
                    @if($session->status === 'completed' && $session->reviews->count() < 2)
                    <span class="text-xs text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full font-medium border border-amber-200">
                        {{ 2 - $session->reviews->count() }} review(s) pending
                    </span>
                    @endif
                </div>

                @if($session->reviews->isEmpty())
                <div class="text-center py-10 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    <p class="text-sm font-medium text-gray-500">No reviews yet</p>
                    <p class="text-xs text-gray-400 mt-1">Reviews are submitted after the session is completed.</p>
                </div>
                @else
                <div class="space-y-4">
                    @foreach($session->reviews as $review)
                    @php $isMentee = $review->reviewer_role === 'mentee'; @endphp
                    <div class="border border-gray-100 rounded-xl p-5 {{ $isMentee ? 'bg-indigo-50/30' : 'bg-emerald-50/30' }}">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold {{ $isMentee ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ strtoupper(substr($review->reviewer->name ?? 'U', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $review->reviewer->name }}</div>
                                    <div class="text-xs font-medium {{ $isMentee ? 'text-indigo-600' : 'text-emerald-600' }}">
                                        {{ ucfirst($review->reviewer_role) }}'s Review
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center gap-0.5 justify-end mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="{{ $i <= $review->overall_rating ? '#f59e0b' : '#e5e7eb' }}" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
                                    @endfor
                                </div>
                                <div class="text-xs text-gray-400">{{ $review->submitted_at?->format('d M Y') }}</div>
                            </div>
                        </div>

                        {{-- Detailed ratings --}}
                        @if($review->communication_rating || $review->knowledge_rating)
                        <div class="grid grid-cols-4 gap-2 mb-3">
                            @foreach([
                                ['Communication', $review->communication_rating],
                                ['Knowledge',     $review->knowledge_rating],
                                ['Punctuality',   $review->punctuality_rating],
                                ['Helpfulness',   $review->helpfulness_rating],
                            ] as [$label, $rating])
                            @if($rating)
                            <div class="text-center">
                                <div class="text-lg font-bold text-gray-800">{{ $rating }}<span class="text-xs text-gray-400">/5</span></div>
                                <div class="text-xs text-gray-500">{{ $label }}</div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        @endif

                        @if($review->review_text)
                        <p class="text-sm text-gray-700 leading-relaxed bg-white rounded-lg px-3 py-2 border border-gray-100">
                            "{{ $review->review_text }}"
                        </p>
                        @endif

                        <div class="flex items-center gap-4 mt-3">
                            @if($review->would_recommend)
                            <span class="text-xs text-green-600 font-medium flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Would recommend
                            </span>
                            @endif
                            @if(!$review->is_public)
                            <span class="text-xs text-gray-400 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                Private
                            </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Notes --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-sm font-bold text-gray-800">Session Notes & Resources</h3>
                    <span class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full">{{ $session->notes->count() }} items</span>
                </div>

                @if($session->notes->isNotEmpty())
                <div class="space-y-3 mb-5">
                    @foreach($session->notes->sortByDesc('created_at') as $note)
                    @php
                    $typeStyles = [
                        'note'        => ['bg-blue-50 text-blue-700',    '📝'],
                        'resource'    => ['bg-purple-50 text-purple-700', '🔗'],
                        'action_item' => ['bg-orange-50 text-orange-700', '✅'],
                    ];
                    [$noteStyle, $emoji] = $typeStyles[$note->type] ?? ['bg-gray-50 text-gray-600', '•'];
                    @endphp
                    <div class="flex gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <span class="text-base leading-none mt-0.5 flex-shrink-0">{{ $emoji }}</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $noteStyle }}">{{ ucfirst(str_replace('_',' ',$note->type)) }}</span>
                                <span class="text-xs text-gray-400">by {{ $note->author->name }}</span>
                                @if($note->is_shared)
                                <span class="text-xs text-indigo-500 font-medium">Shared</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-700">{{ $note->content }}</p>
                            @if($note->resource_url)
                            <a href="{{ $note->resource_url }}" target="_blank" class="text-xs text-indigo-600 hover:underline mt-1 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                {{ $note->resource_url }}
                            </a>
                            @endif
                        </div>
                        <div class="text-xs text-gray-400 flex-shrink-0">{{ $note->created_at->format('d M, H:i') }}</div>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Add note form --}}
                <form method="POST" action="{{ route('admin.sessions.add-note', $session) }}" class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                    @csrf
                    <div class="grid grid-cols-3 gap-3 mb-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Note / Resource</label>
                            <textarea name="content" rows="2" required placeholder="Add a note, action item, or resource link…"
                                      class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 resize-none bg-white"></textarea>
                        </div>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                                <select name="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white outline-none focus:border-indigo-400">
                                    <option value="note">📝 Note</option>
                                    <option value="resource">🔗 Resource</option>
                                    <option value="action_item">✅ Action Item</option>
                                </select>
                            </div>
                            <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                                <input type="checkbox" name="is_shared" value="1" class="rounded">
                                Share with participant
                            </label>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <input type="url" name="resource_url" placeholder="Resource URL (optional)" class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-indigo-400 bg-white">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">Add</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- RIGHT: Participants + Sidebar --}}
        <div class="space-y-5">

            {{-- Mentor card --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <div class="text-xs font-semibold text-indigo-600 uppercase tracking-widest mb-3">Mentor</div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-base flex-shrink-0">
                        {{ strtoupper(substr($session->mentor->name ?? 'M', 0, 2)) }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">{{ $session->mentor->name ?? '—' }}</div>
                        <div class="text-xs text-gray-500">{{ $session->mentor->email ?? '' }}</div>
                    </div>
                </div>
                @php $mp = $session->mentor->mentorProfile ?? null; @endphp
                @if($mp)
                <div class="space-y-1.5 text-xs">
                    @if($mp->title)<div class="text-gray-600 font-medium">{{ $mp->title }}</div>@endif
                    @if($mp->expertise_area)<div class="text-gray-500">{{ $mp->expertise_area }}</div>@endif
                    <div class="flex items-center gap-1.5 pt-1">
                        <span class="text-amber-500 font-semibold">★ {{ number_format($mp->avg_rating, 1) }}</span>
                        <span class="text-gray-400">· {{ $mp->total_sessions }} sessions</span>
                    </div>
                </div>
                @endif
            </div>

            {{-- Mentee card --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <div class="text-xs font-semibold text-emerald-600 uppercase tracking-widest mb-3">Mentee</div>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-base flex-shrink-0">
                        {{ strtoupper(substr($session->mentee->name ?? 'M', 0, 2)) }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">{{ $session->mentee->name ?? '—' }}</div>
                        <div class="text-xs text-gray-500">{{ $session->mentee->email ?? '' }}</div>
                    </div>
                </div>
            </div>

            {{-- Quick info --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">Session Info</div>
                <div class="space-y-2">
                    @foreach([
                        ['Booking Ref', $session->booking_ref, 'font-mono'],
                        ['Created',     $session->created_at->format('d M Y, H:i'), ''],
                        ['Last Updated',$session->updated_at->format('d M Y, H:i'), ''],
                    ] as [$l, $v, $extra])
                    <div class="flex justify-between items-start text-xs">
                        <span class="text-gray-400 font-medium">{{ $l }}</span>
                        <span class="text-gray-700 font-semibold {{ $extra }} text-right">{{ $v }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Mark no-show --}}
            @if(in_array($session->status, ['confirmed','pending']))
            <form method="POST" action="{{ route('admin.sessions.no-show', $session) }}"
                  onsubmit="return confirm('Mark this session as No Show?')">
                @csrf
                <button type="submit"
                        class="w-full text-sm font-medium text-gray-500 border border-gray-200 py-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                    Mark as No Show
                </button>
            </form>
            @endif

            {{-- Delete --}}
            <form method="POST" action="{{ route('admin.sessions.destroy', $session) }}"
                  onsubmit="return confirm('Permanently delete this session?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-full text-sm font-medium text-red-500 border border-red-100 py-2.5 rounded-xl hover:bg-red-50 transition-colors">
                    Delete Session
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Cancel modal --}}
<div id="cancel-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
        <h3 class="text-base font-bold text-gray-900 mb-1">Cancel Session</h3>
        <p class="text-sm text-gray-500 mb-4">This action cannot be undone. Please provide a reason.</p>
        <form method="POST" action="{{ route('admin.sessions.cancel', $session) }}">
            @csrf
            <textarea name="reason" rows="3" placeholder="Reason for cancellation (optional)…"
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100 resize-none mb-4"></textarea>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                    Cancel Session
                </button>
                <button type="button" onclick="document.getElementById('cancel-modal').classList.add('hidden')"
                        class="flex-1 border border-gray-200 text-gray-600 text-sm font-medium py-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                    Keep Session
                </button>
            </div>
        </form>
    </div>
</div>

@endsection