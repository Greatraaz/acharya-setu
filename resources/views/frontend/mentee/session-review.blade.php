@extends('frontend.layouts.app')
@section('title', 'Review Session — Vedrix')

@section('content')
<style>
    .review-star-group { display: flex; flex-direction: row-reverse; gap: 4px; justify-content: flex-end; }
    .review-star-group input { display: none; }
    .review-star-group label {
        cursor: pointer;
        font-size: 32px;
        color: #d1d5db;
        transition: color .15s, transform .1s;
        line-height: 1;
    }
    .review-star-group label:hover,
    .review-star-group label:hover ~ label,
    .review-star-group input:checked ~ label { color: #f59e0b; }
    .review-star-group label:hover { transform: scale(1.12); }

    .review-mini-stars { display: flex; flex-direction: row-reverse; gap: 2px; }
    .review-mini-stars input { display: none; }
    .review-mini-stars label { cursor: pointer; font-size: 22px; color: #d1d5db; transition: color .12s; line-height: 1; }
    .review-mini-stars label:hover,
    .review-mini-stars label:hover ~ label,
    .review-mini-stars input:checked ~ label { color: #f59e0b; }
</style>

<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="session-detail-breadcrumb">
            <a href="{{ route('mentee.sessions') }}" style="color:var(--brand);">← Sessions</a>
            <span>/</span>
            <a href="{{ route('mentee.sessions.show', $session->id) }}" style="color:var(--brand);">{{ $session->booking_ref ?? ('Session #'.$session->id) }}</a>
            <span>/</span>
            <span>Review</span>
        </div>

        <div class="dash-header" style="margin-bottom:24px;">
            <div class="dash-title">How was your session?</div>
            <div class="dash-subtitle">Your feedback helps your mentor improve and helps other mentees choose well.</div>
        </div>

        <div style="max-width:640px;">
            <div class="card" style="margin-bottom:20px;">
                <div style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-3);margin-bottom:10px;">Session</div>
                <div style="font-size:16px;font-weight:700;margin-bottom:4px;">{{ $session->title ?: 'Mentoring Session' }}</div>
                <div style="font-size:13px;color:var(--text-2);margin-bottom:14px;">
                    {{ $session->scheduled_at?->format('D, d M Y · g:i A') ?? '—' }}
                    · {{ $session->duration_minutes ?? 30 }} min
                </div>
                <div style="display:flex;align-items:center;gap:12px;">
                    <div class="mentor-avatar-lg" style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:var(--brand-muted);font-weight:700;overflow:hidden;">
                        @if($session->mentor->avatar_url ?? false)
                            <img src="{{ $session->mentor->avatar_url }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            {{ strtoupper(substr($session->mentor->name ?? '?', 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <div style="font-weight:700;">{{ $session->mentor->name ?? 'Mentor' }}</div>
                        <div style="font-size:12px;color:var(--text-3);">{{ $session->mentor->designation ?? 'Mentor' }}</div>
                    </div>
                </div>
            </div>

            @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:16px;">
                <span class="alert-icon">❌</span>
                <div>
                    @foreach($errors->all() as $e)
                        <p style="margin:0;">{{ $e }}</p>
                    @endforeach
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('mentee.sessions.review.post', $session->id) }}">
                @csrf

                <div class="card" style="margin-bottom:16px;">
                    <div style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-3);margin-bottom:12px;">
                        Overall Rating <span style="color:var(--error);">*</span>
                    </div>
                    <div class="review-star-group">
                        @for($i = 5; $i >= 1; $i--)
                        <input type="radio" name="overall_rating" id="overall_{{ $i }}" value="{{ $i }}"
                               {{ (string) old('overall_rating') === (string) $i ? 'checked' : '' }} required>
                        <label for="overall_{{ $i }}" title="{{ $i }} star{{ $i > 1 ? 's' : '' }}">★</label>
                        @endfor
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-top:10px;font-size:12px;color:var(--text-3);">
                        <span>1 = Poor</span>
                        <span>5 = Excellent</span>
                    </div>
                </div>

                <div class="card" style="margin-bottom:16px;">
                    <div style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-3);margin-bottom:14px;">Detailed Ratings</div>
                    <div style="display:flex;flex-direction:column;gap:18px;">
                        @foreach([
                            ['communication_rating', 'Communication', 'Did the mentor explain concepts clearly?'],
                            ['knowledge_rating', 'Expertise & Knowledge', 'How knowledgeable was the mentor?'],
                            ['punctuality_rating', 'Punctuality', 'Did the session start and end on time?'],
                            ['helpfulness_rating', 'Helpfulness', 'Did the mentor provide actionable guidance?'],
                        ] as [$field, $label, $hint])
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;">
                            <div>
                                <div style="font-size:14px;font-weight:600;">{{ $label }}</div>
                                <div style="font-size:12px;color:var(--text-3);margin-top:2px;">{{ $hint }}</div>
                            </div>
                            <div class="review-mini-stars" style="flex-shrink:0;">
                                @for($i = 5; $i >= 1; $i--)
                                <input type="radio" name="{{ $field }}" id="{{ $field }}_{{ $i }}" value="{{ $i }}"
                                       {{ (string) old($field) === (string) $i ? 'checked' : '' }}>
                                <label for="{{ $field }}_{{ $i }}" title="{{ $i }}">★</label>
                                @endfor
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="card" style="margin-bottom:16px;">
                    <div style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-3);margin-bottom:12px;">Your Review</div>
                    <textarea name="review_text" rows="5" maxlength="1000"
                              placeholder="Share what you learned, how the mentor helped you, and what could have been better…"
                              style="width:100%;border:1px solid var(--border);border-radius:var(--radius);padding:12px 14px;font-size:14px;color:var(--text);background:var(--bg);outline:none;resize:vertical;line-height:1.6;font-family:inherit;">{{ old('review_text') }}</textarea>
                    <div style="display:flex;justify-content:flex-end;margin-top:6px;">
                        <span id="char-count" style="font-size:12px;color:var(--text-3);">0 / 1000</span>
                    </div>
                </div>

                <div class="card" style="margin-bottom:20px;">
                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
                        <input type="checkbox" name="would_recommend" value="1"
                               {{ old('would_recommend', '1') == '1' ? 'checked' : '' }}
                               style="margin-top:3px;width:16px;height:16px;accent-color:var(--brand);">
                        <span>
                            <span style="display:block;font-size:14px;font-weight:600;">I would recommend this mentor</span>
                            <span style="display:block;font-size:12px;color:var(--text-3);margin-top:2px;">Helps other mentees find great mentors.</span>
                        </span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;">
                    Submit Review
                </button>
                <p style="text-align:center;font-size:12px;color:var(--text-3);margin-top:12px;">
                    <a href="{{ route('mentee.sessions.show', $session->id) }}" style="color:var(--brand);">Cancel and go back</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const textarea = document.querySelector('[name="review_text"]');
const counter = document.getElementById('char-count');
function updateCount() {
    if (!textarea || !counter) return;
    const len = textarea.value.length;
    counter.textContent = `${len} / 1000`;
}
textarea?.addEventListener('input', updateCount);
updateCount();
</script>
@endpush
