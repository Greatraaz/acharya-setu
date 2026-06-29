@extends('admin.layouts.app')
@section('title', 'Pending Profile Changes')
@section('heading', 'Pending Profile Changes')
@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.mentors.index') }}"
               class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Mentors
            </a>
            <span class="text-gray-300">/</span>
            <span class="text-sm font-medium text-gray-700">Profile Change Requests</span>
        </div>
        <span class="text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200 px-3 py-1.5 rounded-full">
            {{ $changes->total() }} pending
        </span>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if($changes->isEmpty())
    <div class="bg-white border border-gray-200 rounded-2xl py-20 text-center">
        <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-gray-600 font-semibold text-sm">All clear!</p>
        <p class="text-gray-400 text-xs mt-1">No pending profile change requests from mentors.</p>
    </div>
    @else

    <div class="space-y-4">
        @foreach($changes as $change)
        @php
        $mentor = $change->mentor;
        $fields = $change->changes ?? [];
        @endphp

        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden hover:shadow-sm transition-shadow">

            {{-- Card header --}}
            <div class="flex items-center gap-4 px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                @if($mentor->avatar_url)
                    <img src="{{ $mentor->avatar_url }}" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                @else
                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 font-bold text-sm flex items-center justify-center flex-shrink-0">
                        {{ strtoupper(substr($mentor->name ?? 'M', 0, 2)) }}
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-bold text-gray-900">{{ $mentor->name }}</span>
                        <span class="text-xs text-gray-400">·</span>
                        <span class="text-xs text-gray-500">{{ $mentor->email }}</span>
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5">
                        {{ $mentor->designation ?? '' }}{{ $mentor->company ? ' at ' . $mentor->company : '' }}
                        · Submitted {{ $change->created_at->diffForHumans() }}
                    </div>
                </div>

                <span class="flex-shrink-0 text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-full">
                    {{ count($fields) }} field{{ count($fields) !== 1 ? 's' : '' }} changed
                </span>

                <a href="{{ route('admin.mentors.review', $mentor) }}"
                   class="flex-shrink-0 text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                    Full Profile →
                </a>
            </div>

            {{-- Diff rows --}}
            <div class="px-6 py-5 space-y-3">
                @foreach($fields as $field => $newValue)
                @php
                $currentValue = $mentor->$field ?? null;
                $fieldLabel   = str_replace('_', ' ', ucfirst($field));
                $isArray      = is_array($newValue) || is_array($currentValue);
                $displayOld   = $isArray ? implode(', ', (array) $currentValue) : ($currentValue ?? '—');
                $displayNew   = $isArray ? implode(', ', (array) $newValue)     : ($newValue ?? '—');
                if ($displayOld === $displayNew) continue;
                @endphp
                <div class="grid grid-cols-[130px_1fr_20px_1fr] items-start gap-3">
                    <div class="text-xs font-semibold text-gray-500 font-mono pt-2 capitalize">{{ $fieldLabel }}</div>
                    <div class="bg-red-50 border border-red-100 rounded-xl px-3 py-2.5">
                        <div class="text-[10px] font-bold text-red-400 uppercase tracking-wide mb-1">Current</div>
                        <div class="text-xs text-gray-700 break-all leading-relaxed">{{ $displayOld }}</div>
                    </div>
                    <div class="flex items-center justify-center pt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                    <div class="bg-green-50 border border-green-100 rounded-xl px-3 py-2.5">
                        <div class="text-[10px] font-bold text-green-500 uppercase tracking-wide mb-1">Proposed</div>
                        <div class="text-xs text-gray-800 font-semibold break-all leading-relaxed">{{ $displayNew }}</div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/40 flex-wrap">

                {{-- Approve --}}
                <form method="POST" action="{{ route('admin.mentors.approve-change', $change) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold bg-green-600 hover:bg-green-700 active:bg-green-800 text-white px-5 py-2 rounded-xl transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Approve Changes
                    </button>
                </form>

                {{-- Reject toggle --}}
                <button type="button"
                        onclick="toggleRejectForm({{ $change->id }})"
                        id="reject-btn-{{ $change->id }}"
                        class="inline-flex items-center gap-1.5 text-sm font-medium bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 px-4 py-2 rounded-xl transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reject
                </button>

                {{-- Reject inline form (hidden by default) --}}
                <div id="reject-form-{{ $change->id }}" class="hidden flex-1 min-w-64">
                    <form method="POST" action="{{ route('admin.mentors.reject-change', $change) }}"
                          class="flex items-center gap-2">
                        @csrf
                        <input type="text" name="reason" required minlength="10"
                               placeholder="Reason for rejection (min 10 chars)…"
                               class="flex-1 border border-gray-200 rounded-xl px-3.5 py-2 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100 transition-all bg-white">
                        <button type="submit"
                                class="flex-shrink-0 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded-xl transition-colors">
                            Confirm
                        </button>
                        <button type="button"
                                onclick="toggleRejectForm({{ $change->id }})"
                                class="flex-shrink-0 text-sm text-gray-400 hover:text-gray-600 transition-colors px-2">
                            Cancel
                        </button>
                    </form>
                </div>

                <div class="ml-auto text-xs text-gray-400 flex-shrink-0">
                    Request #{{ $change->id }} · {{ $change->created_at->format('d M Y, H:i') }}
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($changes->hasPages())
    <div class="flex items-center justify-between">
        <div class="text-xs text-gray-500">
            Showing {{ $changes->firstItem() }}–{{ $changes->lastItem() }} of {{ $changes->total() }}
        </div>
        {{ $changes->links() }}
    </div>
    @endif

    @endif
</div>

<script>
function toggleRejectForm(id) {
    const form = document.getElementById('reject-form-' + id);
    const btn  = document.getElementById('reject-btn-' + id);
    const isHidden = form.classList.contains('hidden');

    if (isHidden) {
        form.classList.remove('hidden');
        form.classList.add('flex');
        btn.classList.add('bg-red-100');
        form.querySelector('input[name="reason"]').focus();
    } else {
        form.classList.add('hidden');
        form.classList.remove('flex');
        btn.classList.remove('bg-red-100');
        form.querySelector('input[name="reason"]').value = '';
    }
}
</script>

@endsection