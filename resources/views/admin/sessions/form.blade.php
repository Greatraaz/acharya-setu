@extends('admin.layouts.app')
@section('title', $session->exists ? 'Edit Session' : 'Book Session')
@section('heading', $session->exists ? 'Edit Session' : 'Book Consultation Session')
@section('content')

<form method="POST"
      action="{{ $session->exists ? route('admin.sessions.update', $session) : route('admin.sessions.store') }}"
      class="max-w-4xl">
    @csrf
    @if($session->exists) @method('PUT') @endif

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.sessions.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-5">
        <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-3 gap-5">
        <div class="col-span-2 space-y-5">

            {{-- Session Details --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Session Details</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Session Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $session->title) }}"
                               placeholder="e.g. Career Guidance — Product Management"
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                        @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Mentor <span class="text-red-500">*</span></label>
                            <select name="mentor_id"
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                                <option value="">Select Mentor…</option>
                                @foreach($mentors as $mentor)
                                <option value="{{ $mentor->id }}" {{ old('mentor_id', $session->mentor_id) == $mentor->id ? 'selected' : '' }}>
                                    {{ $mentor->name }} @if($mentor->mentorProfile?->expertise_area)— {{ $mentor->mentorProfile->expertise_area }}@endif
                                </option>
                                @endforeach
                            </select>
                            @error('mentor_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Mentee <span class="text-red-500">*</span></label>
                            <select name="mentee_id"
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                                <option value="">Select Mentee…</option>
                                @foreach($mentees as $mentee)
                                <option value="{{ $mentee->id }}" {{ old('mentee_id', $session->mentee_id) == $mentee->id ? 'selected' : '' }}>
                                    {{ $mentee->name }} — {{ $mentee->email }}
                                </option>
                                @endforeach
                            </select>
                            @error('mentee_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Session Agenda / Goals</label>
                        <textarea name="agenda" rows="4"
                                  placeholder="What topics will be covered? What does the mentee want to achieve from this session?"
                                  class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 resize-none">{{ old('agenda', $session->agenda) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Schedule --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Schedule</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Date & Time <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="scheduled_at"
                               value="{{ old('scheduled_at', $session->scheduled_at?->format('Y-m-d\TH:i')) }}"
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                        @error('scheduled_at')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Duration (minutes) <span class="text-red-500">*</span></label>
                        <select name="duration_minutes"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                            @foreach([15=>'15 min',30=>'30 min',45=>'45 min',60=>'1 hour',90=>'1.5 hours',120=>'2 hours'] as $val=>$label)
                            <option value="{{ $val }}" {{ old('duration_minutes', $session->duration_minutes ?? 60) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Timezone</label>
                        <select name="timezone"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                            @foreach(['Asia/Kolkata'=>'IST (UTC+5:30)','UTC'=>'UTC','America/New_York'=>'EST','Europe/London'=>'GMT'] as $tz=>$label)
                            <option value="{{ $tz }}" {{ old('timezone', $session->timezone ?? 'Asia/Kolkata') === $tz ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Initial Status</label>
                        <select name="status"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                            <option value="pending"   {{ old('status', $session->status ?? 'pending') === 'pending'   ? 'selected' : '' }}>⏳ Pending (awaiting confirmation)</option>
                            <option value="confirmed" {{ old('status', $session->status) === 'confirmed' ? 'selected' : '' }}>✅ Confirmed</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Meeting --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Meeting Setup</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Video Provider</label>
                        <select name="meeting_provider"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                            <option value="">Not set</option>
                            <option value="agora"  {{ old('meeting_provider', $session->meeting_provider) === 'agora'  ? 'selected' : '' }}>Agora</option>
                            <option value="zoom"   {{ old('meeting_provider', $session->meeting_provider) === 'zoom'   ? 'selected' : '' }}>Zoom</option>
                            <option value="google" {{ old('meeting_provider', $session->meeting_provider) === 'google' ? 'selected' : '' }}>Google Meet</option>
                            <option value="other"  {{ old('meeting_provider', $session->meeting_provider) === 'other'  ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Meeting Link</label>
                        <input type="url" name="meeting_link"
                               value="{{ old('meeting_link', $session->meeting_link) }}"
                               placeholder="https://meet.google.com/abc-defg-hij"
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    </div>
                </div>
            </div>

            {{-- Payment --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Payment</h3>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Amount</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium pointer-events-none">₹</span>
                            <input type="number" name="amount" step="0.01" min="0"
                                   value="{{ old('amount', $session->amount ?? 0) }}"
                                   class="w-full border border-gray-200 rounded-xl pl-8 pr-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">0 = free session</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Currency</label>
                        <select name="currency"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                            @foreach(['INR'=>'INR','USD'=>'USD','EUR'=>'EUR','GBP'=>'GBP'] as $c=>$l)
                            <option value="{{ $c }}" {{ old('currency', $session->currency ?? 'INR') === $c ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Reference</label>
                        <input type="text" name="payment_reference"
                               value="{{ old('payment_reference', $session->payment_reference) }}"
                               placeholder="TXN-ID or order ref"
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    </div>
                </div>
            </div>
        </div>

        {{-- Right sidebar --}}
        <div class="space-y-5">

            {{-- Actions --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-5 sticky top-4">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Actions</h3>
                <div class="space-y-2">
                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        {{ $session->exists ? '💾 Update Session' : '📅 Book Session' }}
                    </button>
                    <a href="{{ route('admin.sessions.index') }}"
                       class="block text-center w-full border border-gray-200 text-gray-600 text-sm font-medium py-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                </div>

                {{-- Quick summary --}}
                <div class="mt-4 pt-4 border-t border-gray-100 space-y-2 text-xs text-gray-500">
                    <div class="flex justify-between">
                        <span>Type</span>
                        <span class="font-medium text-gray-700">1-on-1 Consultation</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Review required</span>
                        <span class="font-medium text-green-600">Yes, post-session</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Notes</span>
                        <span class="font-medium text-gray-700">Available during session</span>
                    </div>
                </div>
            </div>

            {{-- Info box --}}
            <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5">
                <h4 class="text-xs font-bold text-indigo-700 uppercase tracking-wide mb-3">Session Lifecycle</h4>
                <div class="space-y-2">
                    @foreach([
                        ['⏳','Pending',   'Booked, awaiting confirmation'],
                        ['✅','Confirmed', 'Both parties confirmed'],
                        ['🟢','Ongoing',   'Session in progress'],
                        ['🏁','Completed', 'Review window opens'],
                        ['❌','Cancelled', 'Reason documented'],
                    ] as [$emoji, $step, $desc])
                    <div class="flex items-start gap-2">
                        <span class="text-sm leading-none mt-0.5">{{ $emoji }}</span>
                        <div>
                            <span class="text-xs font-semibold text-indigo-800">{{ $step }}</span>
                            <span class="text-xs text-indigo-600"> — {{ $desc }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</form>

@endsection