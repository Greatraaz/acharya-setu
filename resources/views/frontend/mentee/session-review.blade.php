@extends('frontend.layouts.app')
@section('title', 'Review Session — Vedrix')

@section('content')
<style>
    .review-page .review-star-group {
        display: flex;
        flex-direction: row-reverse;
        gap: 6px;
        justify-content: flex-end;
        width: fit-content;
    }
    .review-page .review-star-group input { display: none; }
    .review-page .review-star-group label {
        cursor: pointer;
        font-size: clamp(28px, 5vw, 40px);
        color: #d1d5db;
        transition: color .15s, transform .1s;
        line-height: 1;
    }
    .review-page .review-star-group label:hover,
    .review-page .review-star-group label:hover ~ label,
    .review-page .review-star-group input:checked ~ label { color: #f59e0b; }
    .review-page .review-star-group label:hover { transform: scale(1.1); }

    .review-page .review-mini-stars {
        display: flex;
        flex-direction: row-reverse;
        gap: 2px;
        flex-shrink: 0;
    }
    .review-page .review-mini-stars input { display: none; }
    .review-page .review-mini-stars label {
        cursor: pointer;
        font-size: 22px;
        color: #d1d5db;
        transition: color .12s;
        line-height: 1;
    }
    .review-page .review-mini-stars label:hover,
    .review-page .review-mini-stars label:hover ~ label,
    .review-page .review-mini-stars input:checked ~ label { color: #f59e0b; }

    .review-page .review-overall-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }
    .review-page .review-overall-meta { min-width: min(100%, 180px); }
    .review-page .review-overall-scale {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
        font-size: 12px;
        color: var(--text-3);
        max-width: 220px;
    }

    .review-page .review-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
    .review-page .review-detail-item {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 14px;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        background: var(--bg);
        min-width: 0;
    }
    .review-page .review-detail-item__head { min-width: 0; }
    .review-page .review-detail-item__label {
        font-size: 14px;
        font-weight: 600;
        color: var(--text);
    }
    .review-page .review-detail-item__hint {
        font-size: 12px;
        color: var(--text-3);
        margin-top: 2px;
        line-height: 1.45;
    }
    .review-page .review-textarea {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 12px 14px;
        font-size: 14px;
        color: var(--text);
        background: var(--bg);
        outline: none;
        resize: vertical;
        line-height: 1.6;
        font-family: inherit;
        min-height: 140px;
    }
    .review-page .review-textarea:focus {
        border-color: var(--brand);
        box-shadow: 0 0 0 3px var(--brand-muted);
    }
    .review-page .review-sidebar-sticky {
        position: sticky;
        top: calc(var(--nav-h, 64px) + 16px);
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .review-page .review-submit-card .btn {
        width: 100%;
        justify-content: center;
        padding: 14px;
    }
    .review-page .review-section-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--text-3);
        margin-bottom: 12px;
    }

    @media (max-width: 900px) {
        .review-page .review-detail-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 768px) {
        .review-page .review-overall-card {
            flex-direction: column;
            align-items: flex-start;
        }
        .review-page .review-sidebar-sticky {
            position: static;
        }
        .review-page .review-detail-item {
            flex-direction: row;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }
        .review-page .review-mini-stars label {
            font-size: 20px;
        }
    }
    @media (max-width: 480px) {
        .review-page .review-detail-item {
            flex-direction: column;
        }
        .review-page .review-star-group {
            gap: 4px;
        }
    }
</style>

<div class="dash-layout review-page">
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

            <div class="session-detail-layout">
                <div class="session-detail-main">
                    <div class="card session-detail-card">
                        <div class="review-section-label">
                            Overall Rating <span style="color:var(--error);">*</span>
                        </div>
                        <div class="review-overall-card">
                            <div class="review-overall-meta">
                                <div style="font-size:15px;font-weight:700;margin-bottom:4px;">Rate this session</div>
                                <div style="font-size:13px;color:var(--text-2);line-height:1.5;">Tap a star to set your overall score.</div>
                                <div class="review-overall-scale">
                                    <span>1 = Poor</span>
                                    <span>5 = Excellent</span>
                                </div>
                            </div>
                            <div class="review-star-group" aria-label="Overall rating">
                                @for($i = 5; $i >= 1; $i--)
                                <input type="radio" name="overall_rating" id="overall_{{ $i }}" value="{{ $i }}"
                                       {{ (string) old('overall_rating') === (string) $i ? 'checked' : '' }} required>
                                <label for="overall_{{ $i }}" title="{{ $i }} star{{ $i > 1 ? 's' : '' }}">★</label>
                                @endfor
                            </div>
                        </div>
                    </div>

                    <div class="card session-detail-card">
                        <div class="review-section-label">Detailed Ratings</div>
                        <div class="review-detail-grid">
                            @foreach([
                                ['communication_rating', 'Communication', 'Did the mentor explain concepts clearly?'],
                                ['knowledge_rating', 'Expertise & Knowledge', 'How knowledgeable was the mentor?'],
                                ['punctuality_rating', 'Punctuality', 'Did the session start and end on time?'],
                                ['helpfulness_rating', 'Helpfulness', 'Did the mentor provide actionable guidance?'],
                            ] as [$field, $label, $hint])
                            <div class="review-detail-item">
                                <div class="review-detail-item__head">
                                    <div class="review-detail-item__label">{{ $label }}</div>
                                    <div class="review-detail-item__hint">{{ $hint }}</div>
                                </div>
                                <div class="review-mini-stars" aria-label="{{ $label }} rating">
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

                    <div class="card session-detail-card" style="margin-bottom:0;">
                        <div class="review-section-label">Your Review</div>
                        <textarea name="review_text" rows="6" maxlength="1000" class="review-textarea"
                                  placeholder="Share what you learned, how the mentor helped you, and what could have been better…">{{ old('review_text') }}</textarea>
                        <div style="display:flex;justify-content:flex-end;margin-top:8px;">
                            <span id="char-count" style="font-size:12px;color:var(--text-3);">0 / 1000</span>
                        </div>
                    </div>
                </div>

                <div class="session-detail-sidebar">
                    <div class="review-sidebar-sticky">
                        <div class="card">
                            <h3 style="font-size:14px;font-weight:700;margin-bottom:14px;">👤 Mentor</h3>
                            <div class="session-detail-person">
                                <div class="session-detail-person__avatar mentor-avatar-lg" style="width:48px;height:48px;border-radius:50%;overflow:hidden;display:flex;align-items:center;justify-content:center;background:var(--brand-muted);font-weight:700;">
                                    @if($session->mentor->avatar_url ?? false)
                                        <img src="{{ $session->mentor->avatar_url }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        {{ strtoupper(substr($session->mentor->name ?? '?', 0, 1)) }}
                                    @endif
                                </div>
                                <div class="session-detail-person__info">
                                    <div class="session-detail-person__name">{{ $session->mentor->name ?? 'Mentor' }}</div>
                                    <div class="session-detail-person__email">{{ $session->mentor->designation ?? 'Mentor' }}</div>
                                </div>
                            </div>
                            @if($session->mentor?->slug)
                            <a href="{{ route('mentors.show', $session->mentor->slug) }}" class="btn btn-outline btn-sm" style="width:100%;margin-top:14px;justify-content:center;">View Profile</a>
                            @endif
                        </div>

                        <div class="card">
                            <h3 style="font-size:13px;font-weight:700;margin-bottom:10px;">🧾 Session</h3>
                            <div class="booking-summary" style="padding:0;border:none;background:transparent;">
                                <div class="booking-summary-row">
                                    <span>Title</span>
                                    <strong style="text-align:right;max-width:55%;">{{ $session->title ?: 'Mentoring Session' }}</strong>
                                </div>
                                <div class="booking-summary-row">
                                    <span>When</span>
                                    <strong style="text-align:right;">{{ $session->scheduled_at?->format('d M Y · g:i A') ?? '—' }}</strong>
                                </div>
                                <div class="booking-summary-row">
                                    <span>Duration</span>
                                    <strong>{{ $session->duration_minutes ?? 30 }} min</strong>
                                </div>
                                <div class="booking-summary-row">
                                    <span>Amount</span>
                                    <strong>₹{{ number_format((float) ($session->amount ?? 0), 0) }}</strong>
                                </div>
                                @if($session->booking_ref)
                                <div class="booking-summary-row">
                                    <span>Ref</span>
                                    <strong style="font-size:11px;word-break:break-all;">{{ $session->booking_ref }}</strong>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="card review-submit-card">
                            <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;margin-bottom:16px;">
                                <input type="checkbox" name="would_recommend" value="1"
                                       {{ old('would_recommend', '1') == '1' ? 'checked' : '' }}
                                       style="margin-top:3px;width:16px;height:16px;accent-color:var(--brand);flex-shrink:0;">
                                <span>
                                    <span style="display:block;font-size:14px;font-weight:600;">I would recommend this mentor</span>
                                    <span style="display:block;font-size:12px;color:var(--text-3);margin-top:2px;">Helps other mentees find great mentors.</span>
                                </span>
                            </label>
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                            <p style="text-align:center;font-size:12px;color:var(--text-3);margin:12px 0 0;">
                                <a href="{{ route('mentee.sessions.show', $session->id) }}" style="color:var(--brand);">Cancel and go back</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const textarea = document.querySelector('.review-page [name="review_text"]');
const counter = document.getElementById('char-count');
function updateCount() {
    if (!textarea || !counter) return;
    counter.textContent = `${textarea.value.length} / 1000`;
}
textarea?.addEventListener('input', updateCount);
updateCount();
</script>
@endpush
