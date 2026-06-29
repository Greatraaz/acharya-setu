@extends('layouts.app')
@section('title', 'Review Your Session')
@section('content')

<style>
    :root {
        --indigo: #4f46e5;
        --indigo-light: #eef2ff;
        --gold: #f59e0b;
    }

    .star-group { display: flex; flex-direction: row-reverse; gap: 4px; justify-content: flex-end; }
    .star-group input { display: none; }
    .star-group label {
        cursor: pointer;
        font-size: 28px;
        color: #d1d5db;
        transition: color .15s, transform .1s;
        line-height: 1;
    }
    .star-group label:hover,
    .star-group label:hover ~ label,
    .star-group input:checked ~ label { color: var(--gold); }
    .star-group label:hover { transform: scale(1.15); }

    .mini-star-group { display: flex; flex-direction: row-reverse; gap: 2px; }
    .mini-star-group input { display: none; }
    .mini-star-group label { cursor: pointer; font-size: 20px; color: #d1d5db; transition: color .12s; line-height: 1; }
    .mini-star-group label:hover,
    .mini-star-group label:hover ~ label,
    .mini-star-group input:checked ~ label { color: var(--gold); }

    .review-card { background: white; border: 1px solid #e5e7eb; border-radius: 20px; padding: 28px; margin-bottom: 20px; }
    .section-label { font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #9ca3af; margin-bottom: 12px; }
</style>

<div class="min-h-screen bg-gray-50 py-10 px-4">
    <div class="max-w-xl mx-auto">

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4
                {{ $role === 'mentee' ? 'bg-indigo-100' : 'bg-emerald-100' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 {{ $role === 'mentee' ? 'text-indigo-600' : 'text-emerald-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">How was your session?</h1>
            <p class="text-sm text-gray-500">
                Your honest feedback helps {{ $role === 'mentee' ? 'your mentor improve' : 'mentees learn better' }}.
            </p>
        </div>

        {{-- Session Summary Card --}}
        <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-2xl p-5 mb-6 text-white">
            <div class="text-xs font-semibold text-indigo-300 uppercase tracking-wide mb-3">Session Summary</div>
            <div class="font-bold text-lg mb-1">{{ $session->title }}</div>
            <div class="text-indigo-200 text-sm mb-3">{{ $session->scheduled_at->format('D, d M Y · H:i') }}</div>
            <div class="flex items-center gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-indigo-400/40 flex items-center justify-center text-xs font-bold">
                        {{ strtoupper(substr($session->mentor->name ?? 'M', 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-semibold leading-tight">{{ $session->mentor->name }}</div>
                        <div class="text-indigo-300 text-xs">Mentor</div>
                    </div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-emerald-400/40 flex items-center justify-center text-xs font-bold">
                        {{ strtoupper(substr($session->mentee->name ?? 'M', 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-semibold leading-tight">{{ $session->mentee->name }}</div>
                        <div class="text-indigo-300 text-xs">Mentee</div>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('sessions.review.store', $session) }}">
            @csrf

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-4 text-sm text-red-700">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
            @endif

            {{-- Overall Rating --}}
            <div class="review-card">
                <div class="section-label">Overall Rating <span class="text-red-400">*</span></div>
                <div class="star-group" id="overall-stars">
                    @for($i = 5; $i >= 1; $i--)
                    <input type="radio" name="overall_rating" id="overall_{{ $i }}" value="{{ $i }}"
                           {{ old('overall_rating') == $i ? 'checked' : '' }} required>
                    <label for="overall_{{ $i }}" title="{{ $i }} star{{ $i > 1 ? 's' : '' }}">★</label>
                    @endfor
                </div>
                <div class="flex gap-4 mt-3 text-xs text-gray-400">
                    <span>1 = Poor</span><span class="ml-auto">5 = Excellent</span>
                </div>
                @error('overall_rating')<p class="text-xs text-red-500 mt-2">{{ $message }}</p>@enderror
            </div>

            {{-- Detailed Ratings --}}
            <div class="review-card">
                <div class="section-label">Detailed Ratings</div>
                <div class="space-y-5">
                    @foreach([
                        ['communication_rating', 'Communication',
                         $role === 'mentee' ? 'Did the mentor explain concepts clearly?' : 'Was the mentee communicative and prepared?'],
                        ['knowledge_rating', $role === 'mentee' ? 'Expertise & Knowledge' : 'Commitment & Focus',
                         $role === 'mentee' ? 'How knowledgeable was the mentor?' : 'Was the mentee focused and engaged?'],
                        ['punctuality_rating', 'Punctuality',
                         'Did the session start and end on time?'],
                        ['helpfulness_rating', $role === 'mentee' ? 'Helpfulness' : 'Receptiveness',
                         $role === 'mentee' ? 'Did the mentor provide actionable guidance?' : 'Was the mentee open to feedback?'],
                    ] as [$field, $label, $hint])
                    <div class="flex items-start gap-4">
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-gray-800 mb-0.5">{{ $label }}</div>
                            <div class="text-xs text-gray-400">{{ $hint }}</div>
                        </div>
                        <div class="mini-star-group flex-shrink-0">
                            @for($i = 5; $i >= 1; $i--)
                            <input type="radio" name="{{ $field }}" id="{{ $field }}_{{ $i }}" value="{{ $i }}"
                                   {{ old($field) == $i ? 'checked' : '' }}>
                            <label for="{{ $field }}_{{ $i }}" title="{{ $i }}">★</label>
                            @endfor
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Written Review --}}
            <div class="review-card">
                <div class="section-label">Your Review</div>
                <textarea name="review_text" rows="5"
                          placeholder="{{ $role === 'mentee'
                            ? 'Share what you learned, how the mentor helped you, and what could have been better…'
                            : 'Share how the mentee engaged, their progress, and any observations…' }}"
                          class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 bg-white outline-none transition-all focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 resize-none leading-relaxed"
                >{{ old('review_text') }}</textarea>
                <div class="flex justify-between mt-1.5">
                    <p class="text-xs text-gray-400">Minimum 20 characters recommended.</p>
                    <span id="char-count" class="text-xs text-gray-400">0 / 2000</span>
                </div>
            </div>

            {{-- Preferences --}}
            <div class="review-card">
                <div class="section-label">Preferences</div>
                <div class="space-y-4">
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <div class="relative flex-shrink-0 mt-0.5">
                            <input type="checkbox" name="would_recommend" value="1"
                                   class="sr-only peer"
                                   {{ old('would_recommend', '1') == '1' ? 'checked' : '' }}>
                            <div class="w-5 h-5 border-2 border-gray-300 rounded-md peer-checked:border-indigo-500 peer-checked:bg-indigo-500 transition-all flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white hidden peer-checked:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-800">
                                I would recommend {{ $role === 'mentee' ? 'this mentor' : 'this mentee' }}
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                This helps {{ $role === 'mentee' ? 'other mentees find great mentors' : 'mentors gauge mentee quality' }}.
                            </div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <div class="relative flex-shrink-0 mt-0.5">
                            <input type="checkbox" name="is_public" value="1"
                                   class="sr-only peer"
                                   {{ old('is_public', '1') == '1' ? 'checked' : '' }}>
                            <div class="w-5 h-5 border-2 border-gray-300 rounded-md peer-checked:border-indigo-500 peer-checked:bg-indigo-500 transition-all flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white hidden peer-checked:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-800">Make this review public</div>
                            <div class="text-xs text-gray-400 mt-0.5">Visible on the mentor's public profile.</div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold py-4 rounded-2xl text-base transition-colors shadow-lg shadow-indigo-200 mt-2">
                Submit Review
            </button>
            <p class="text-center text-xs text-gray-400 mt-3">
                Your review can be edited within 48 hours of submission.
            </p>
        </form>
    </div>
</div>

<script>
// Character counter
const textarea = document.querySelector('[name="review_text"]');
const counter  = document.getElementById('char-count');
textarea?.addEventListener('input', () => {
    const len = textarea.value.length;
    counter.textContent = `${len} / 2000`;
    counter.classList.toggle('text-red-500', len > 1800);
});

// Make checkboxes work visually (native peer won't auto-toggle inner svg without JS in most setups)
document.querySelectorAll('.sr-only.peer').forEach(checkbox => {
    checkbox.addEventListener('change', function () {
        const svg = this.nextElementSibling?.querySelector('svg');
        if (svg) svg.classList.toggle('hidden', !this.checked);
    });
    // Initial state
    const svg = checkbox.nextElementSibling?.querySelector('svg');
    if (svg) svg.classList.toggle('hidden', !checkbox.checked);
});
</script>

@endsection