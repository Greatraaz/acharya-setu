{{-- ============================================================
FILE: resources/views/admin/mentors/review.blade.php
Full mentor profile review + quick approve/reject
============================================================ --}}
@extends('admin.layouts.app')
@section('title', 'Review Mentor — ' . $mentor->name)
@section('heading', 'Mentor Review')
@section('content')

<div class="max-w-5xl space-y-5">

    <a href="{{ route('admin.mentor-approvals.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Approvals
    </a>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">✓ {{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-3 gap-5">

        {{-- Left: Profile --}}
        <div class="col-span-2 space-y-5">

            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                {{-- Header --}}
                <div class="px-6 pt-6 pb-5 flex items-start gap-4 border-b border-gray-100">
                    @if($mentor->avatar_url)
                    <img src="{{ $mentor->avatar_url }}" class="w-16 h-16 rounded-2xl object-cover flex-shrink-0">
                    @else
                    <div class="w-16 h-16 rounded-2xl bg-indigo-100 text-indigo-700 font-bold text-xl flex items-center justify-center flex-shrink-0">
                        {{ strtoupper(substr($mentor->name, 0, 2)) }}
                    </div>
                    @endif
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="text-lg font-bold text-gray-900">{{ $mentor->name }}</h2>
                            @php
                            $sc = ['pending'=>'bg-amber-50 text-amber-700','approved'=>'bg-green-50 text-green-700','rejected'=>'bg-red-50 text-red-700','suspended'=>'bg-gray-100 text-gray-600'];
                            @endphp
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $sc[$mentor->mentor_status] ?? 'bg-gray-100 text-gray-500' }}">{{ ucfirst($mentor->mentor_status) }}</span>
                            @if($mentor->has_pending_changes)
                            <span class="text-xs font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Profile changes pending</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600">{{ $mentor->designation }}{{ $mentor->company ? ' at ' . $mentor->company : '' }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $mentor->email }} · Joined {{ $mentor->created_at->format('d M Y') }}</p>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        @foreach([
                            ['Field',       $mentor->field ?? '—'],
                            ['Experience',  ($mentor->experience_years ?? 0) . ' years'],
                            ['Rate',        $mentor->rate_per_minute > 0 ? '₹' . $mentor->rate_per_minute . '/min' : 'Free'],
                            ['Phone',       $mentor->phone ?? '—'],
                            ['Mentees',     $mentor->assignedMentees->count()],
                            ['Sessions',    $mentor->total_sessions],
                            ['Rating',      $mentor->rating > 0 ? '★ ' . number_format($mentor->rating, 1) : 'No ratings yet'],
                            ['Subscription',$mentor->subscription_plan ?? 'free'],
                        ] as [$label, $value])
                        <div class="bg-gray-50 rounded-xl px-4 py-3">
                            <div class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-0.5">{{ $label }}</div>
                            <div class="text-sm font-semibold text-gray-800">{{ $value }}</div>
                        </div>
                        @endforeach
                    </div>

                    @if($mentor->linkedin)
                    <div class="flex items-center gap-2 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 16 16"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/></svg>
                        <a href="{{ $mentor->linkedin }}" target="_blank" class="text-blue-600 hover:underline">LinkedIn Profile</a>
                    </div>
                    @endif

                    @if($mentor->expertise)
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-2">Expertise</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach((array)$mentor->expertise as $skill)
                            <span class="text-xs bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-full font-medium">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($mentor->bio)
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-2">Bio</p>
                        <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 rounded-xl p-4">{{ $mentor->bio }}</p>
                    </div>
                    @endif

                    @if($mentor->rejection_reason)
                    <div class="p-4 bg-red-50 border border-red-100 rounded-xl">
                        <p class="text-xs font-bold text-red-700 mb-1">Previous Rejection Reason</p>
                        <p class="text-sm text-red-600">{{ $mentor->rejection_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Pending profile change --}}
            @if($pendingChange)
            <div class="bg-white border border-amber-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 bg-amber-50 border-b border-amber-100 flex items-center gap-2">
                    <div class="text-lg">✏️</div>
                    <h3 class="text-sm font-bold text-amber-800">Pending Profile Change Request</h3>
                    <span class="text-[10px] bg-amber-200 text-amber-800 px-2 py-0.5 rounded-full font-bold ml-auto">{{ $pendingChange->created_at->diffForHumans() }}</span>
                </div>
                <div class="p-5 space-y-3">
                    @foreach($pendingChange->changes as $field => $value)
                    <div class="flex items-start gap-4">
                        <div class="text-xs font-mono font-semibold text-gray-500 w-28 flex-shrink-0 pt-0.5">{{ $field }}</div>
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <div class="flex-1 min-w-0">
                                <div class="text-[10px] text-gray-400 mb-0.5">Current</div>
                                <div class="text-xs font-mono text-gray-600 bg-red-50 border border-red-100 px-2.5 py-1.5 rounded-lg break-all">
                                    {{ is_array($mentor->$field) ? json_encode($mentor->$field) : ($mentor->$field ?? 'null') }}
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-300 flex-shrink-0 mt-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            <div class="flex-1 min-w-0">
                                <div class="text-[10px] text-gray-400 mb-0.5">Proposed</div>
                                <div class="text-xs font-mono text-gray-800 bg-green-50 border border-green-100 px-2.5 py-1.5 rounded-lg break-all">
                                    {{ is_array($value) ? json_encode($value) : $value }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <div class="flex gap-2 pt-2 border-t border-gray-100">
                        <form method="POST" action="{{ route('admin.mentors.approve-change', $pendingChange) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                                ✓ Approve Changes
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.mentors.reject-change', $pendingChange) }}" class="flex-1">
                            @csrf
                            <textarea name="reason" required minlength="10" placeholder="Reason for rejecting these changes…" rows="1"
                                      class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs mb-2 outline-none focus:border-red-400 resize-none"></textarea>
                            <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-sm font-semibold py-2 rounded-xl transition-colors">
                                ✗ Reject Changes
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Right sidebar: Actions --}}
        <div class="space-y-4">

            {{-- Quick actions --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Quick Actions</h3>
                </div>
                <div class="p-4 space-y-2">
                    @if($mentor->mentor_status === 'pending')
                    <form method="POST" action="{{ route('admin.mentors.approve', $mentor) }}">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                            ✓ Approve Mentor
                        </button>
                    </form>
                    <button onclick="document.getElementById('action-modal').classList.remove('hidden');document.getElementById('action-type').value='reject'"
                            class="w-full bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        ✗ Reject Application
                    </button>
                    @elseif($mentor->mentor_status === 'approved')
                    <button onclick="document.getElementById('action-modal').classList.remove('hidden');document.getElementById('action-type').value='suspend'"
                            class="w-full bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        ⏸ Suspend Mentor
                    </button>
                    @elseif($mentor->mentor_status === 'suspended')
                    <form method="POST" action="{{ route('admin.mentors.reinstate', $mentor) }}">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                            ↩ Reinstate Mentor
                        </button>
                    </form>
                    @endif

                    <form method="POST" action="{{ route('admin.mentors.destroy', $mentor) }}"
                          onsubmit="return confirm('Soft-delete this user? They can be restored later.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full text-sm font-medium text-gray-400 hover:text-red-500 hover:bg-red-50 border border-gray-200 hover:border-red-100 py-2.5 rounded-xl transition-all">
                            🗑 Deactivate Account
                        </button>
                    </form>
                </div>
            </div>

            {{-- Approval history --}}
            @if($mentor->approved_by)
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Approval History</h3>
                <div class="text-xs text-gray-600 space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Reviewed by</span>
                        <span class="font-medium">{{ $mentor->approvedBy?->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Date</span>
                        <span class="font-medium">{{ $mentor->approved_at?->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Decision</span>
                        <span class="font-medium capitalize">{{ $mentor->mentor_status }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Action modal (reject / suspend) --}}
<div id="action-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <h3 class="text-sm font-bold text-gray-900 mb-4" id="action-modal-title">Provide Reason</h3>
        <form id="action-form" method="POST">
            @csrf
            <input type="hidden" name="action_type" id="action-type">
            <textarea name="reason" rows="4" required minlength="10"
                      placeholder="Explain the reason…"
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-gray-400 resize-none mb-4"></textarea>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">Confirm</button>
                <button type="button" onclick="document.getElementById('action-modal').classList.add('hidden')"
                        class="flex-1 border border-gray-200 text-gray-600 text-sm py-2.5 rounded-xl hover:bg-gray-50 transition-colors">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('action-type')?.addEventListener?.('change', function() {});
document.getElementById('action-modal')?.addEventListener('click', function(e) {
    const type = document.getElementById('action-type').value;
    const form = document.getElementById('action-form');
    if (type === 'reject') {
        form.action = '{{ route('admin.mentors.reject', $mentor) }}';
        document.getElementById('action-modal-title').textContent = 'Reject Application';
    } else if (type === 'suspend') {
        form.action = '{{ route('admin.mentors.suspend', $mentor) }}';
        document.getElementById('action-modal-title').textContent = 'Suspend Mentor';
    }
});

// Update action form when type changes via button clicks
function openActionModal(type) {
    document.getElementById('action-type').value = type;
    const form = document.getElementById('action-form');
    if (type === 'reject') {
        form.action = '{{ route('admin.mentors.reject', $mentor) }}';
        document.getElementById('action-modal-title').textContent = 'Reject Application';
    } else if (type === 'suspend') {
        form.action = '{{ route('admin.mentors.suspend', $mentor) }}';
        document.getElementById('action-modal-title').textContent = 'Suspend Mentor';
    }
    document.getElementById('action-modal').classList.remove('hidden');
}

// Fix buttons to use openActionModal
document.querySelectorAll('[onclick*="action-type"]').forEach(btn => {
    const typeMatch = btn.getAttribute('onclick').match(/value='(\w+)'/);
    if (typeMatch) {
        btn.setAttribute('onclick', `openActionModal('${typeMatch[1]}')`);
    }
});
</script>

@endsection