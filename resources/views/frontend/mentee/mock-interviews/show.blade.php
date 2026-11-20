@extends('frontend.layouts.app')
@section('title', 'Mock interview #'.$item->id.' — Vedrix')

@php
    $statusTone = match ($item->status) {
        'completed' => 'success',
        'confirmed' => 'success',
        'submitted' => 'warning',
        'cancelled' => 'muted',
        default => 'muted',
    };
    $tz = $item->timezone ?: 'Asia/Kolkata';
    $sharedNotes = ($item->notes ?? collect())->where('is_shared', true);
@endphp

@push('styles')
<style>
    .mi-show.cs-detail {
        --mi: var(--brand, #f59e0b);
        --mi-dark: var(--brand-dark, #d97706);
        --mi-soft: var(--brand-muted, rgba(245,158,11,.12));
        --mi-ink: var(--text, #09090b);
        --mi-muted: var(--text-2, #52525b);
        --mi-faint: var(--text-3, #71717a);
        --mi-line: var(--border, rgba(0,0,0,.07));
        --mi-surface: var(--card-bg, #fff);
        --mi-surface-2: var(--bg-2, #f4f4f5);
        --mi-r: var(--radius-btn, 8px);
        --mi-rl: var(--radius-lg, 16px);
        --mi-sh: var(--shadow, 0 4px 24px rgba(0,0,0,.08));
        max-width: 1120px;
        width: 100%;
        font-family: var(--font-body, "DM Sans", sans-serif);
    }
    .mi-show__layout {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) minmax(280px, 1fr);
        gap: 16px 20px;
        align-items: start;
    }
    .mi-show__main,
    .mi-show__side {
        display: grid;
        gap: 14px;
        min-width: 0;
    }
    .mi-show__side {
        position: sticky;
        top: calc(var(--nav-h) + 16px);
    }
    .mi-show .cs-detail__back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 14px;
        font-size: 13px;
        font-weight: 700;
        color: var(--mi);
        text-decoration: none;
    }
    .mi-show .cs-detail__hero {
        margin-bottom: 0;
        padding: 18px 20px;
        background:
            radial-gradient(420px 140px at 100% 0%, color-mix(in srgb, var(--mi) 14%, transparent), transparent 70%),
            var(--mi-surface);
        border: 1px solid var(--mi-line);
        border-radius: var(--mi-rl);
        align-items: center;
        box-shadow: var(--mi-sh);
    }
    .mi-show .cs-detail__icon {
        width: 44px;
        height: 44px;
        border-radius: var(--mi-r);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--mi), var(--mi-dark));
        color: #fff;
        box-shadow: 0 10px 22px -8px color-mix(in srgb, var(--mi) 35%, transparent);
        font-size: 0;
    }
    .mi-show .cs-detail__icon svg {
        width: 18px;
        height: 18px;
    }
    .mi-show .cs-detail__title {
        font-family: var(--font-head, "Sora", sans-serif);
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -.02em;
        color: var(--mi-ink);
    }
    .mi-show .card.cs-panel {
        margin: 0;
        padding: 16px 18px;
        box-shadow: var(--mi-sh);
        border: 1px solid var(--mi-line);
        border-radius: var(--mi-rl);
        background: var(--mi-surface);
    }
    .mi-show .card.cs-panel:hover {
        border-color: color-mix(in srgb, var(--mi) 28%, var(--mi-line));
        box-shadow: var(--mi-sh);
    }
    .mi-show .cs-panel__head {
        margin-bottom: 8px;
        font-family: var(--font-head, "Sora", sans-serif);
        font-size: 14px;
        font-weight: 800;
        letter-spacing: -.02em;
        color: var(--mi-ink);
    }
    .mi-show .mi-receipt {
        display: grid;
        gap: 10px;
    }
    .mi-show .mi-receipt__row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: 13px;
        color: var(--mi-muted);
    }
    .mi-show .mi-receipt__row strong {
        color: var(--mi-ink);
        font-weight: 700;
        text-align: right;
        word-break: break-word;
    }
    .mi-show .mi-receipt__row code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 12px;
        font-weight: 600;
    }
    .mi-show .mi-rating-score {
        margin: 0;
        font-family: var(--font-head, "Sora", sans-serif);
        font-size: 28px;
        font-weight: 800;
        color: var(--mi);
        letter-spacing: -.03em;
    }
    .mi-show .form-label {
        display: block;
        font-family: var(--font-head, "Sora", sans-serif);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--mi-muted);
        margin-bottom: 8px;
    }
    .mi-show .form-input,
    .mi-show textarea.form-input,
    .mi-show select.form-input {
        border-radius: var(--mi-r) !important;
        border: 1px solid var(--mi-line);
        background: var(--mi-surface);
        color: var(--mi-ink);
        min-height: 46px;
    }
    .mi-show textarea.form-input { min-height: 100px; resize: vertical; }
    .mi-show .form-input:focus {
        border-color: var(--mi);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--mi) 18%, transparent);
        outline: none;
    }
    .mi-show .cs-chip {
        display: inline-flex;
        align-items: center;
        font-family: var(--font-head, "Sora", sans-serif);
        font-size: 11px;
        font-weight: 700;
        padding: 5px 10px;
        border-radius: 999px;
        background: var(--mi-soft);
        color: var(--mi);
    }
    .mi-show__interviewer {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .mi-show__avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        flex-shrink: 0;
        background: var(--brand-muted);
        color: var(--brand);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 15px;
        overflow: hidden;
    }
    .mi-show__avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .mi-show__note {
        padding: 12px 14px;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        margin-top: 10px;
        background: var(--bg-3);
    }
    .mi-show__note-meta {
        font-size: 12px;
        color: var(--text-3);
        margin-bottom: 6px;
    }
    .mi-show__note-body {
        font-size: 14px;
        color: var(--text-2);
        line-height: 1.65;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .mi-show .mi-booking {
        display: grid;
        gap: 0;
    }
    .mi-show .mi-booking__row {
        display: grid;
        grid-template-columns: 110px minmax(0, 1fr);
        gap: 10px 14px;
        align-items: start;
        padding: 11px 0;
        border-bottom: 1px solid var(--border);
    }
    .mi-show .mi-booking__row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .mi-show .mi-booking__row:first-child {
        padding-top: 2px;
    }
    .mi-show .mi-booking__label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--text-3);
        line-height: 1.4;
        padding-top: 1px;
    }
    .mi-show .mi-booking__value {
        margin: 0;
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        line-height: 1.45;
        text-align: left;
        white-space: normal;
        word-break: break-word;
    }
    .mi-show .mi-booking__value small {
        display: block;
        margin-top: 3px;
        font-size: 11px;
        font-weight: 500;
        color: var(--text-3);
    }
    .mi-show #my-mock-notes {
        margin-bottom: 0;
    }
    .mi-show #my-mock-notes textarea {
        min-height: 180px;
        width: 100%;
        resize: vertical;
        box-sizing: border-box;
    }
    .mi-show #my-mock-notes .session-detail-card-head {
        margin-bottom: 10px;
    }
    .mi-show #my-mock-notes .session-detail-card-head h3 {
        font-size: 13px;
        font-weight: 800;
    }
    @media (max-width: 960px) {
        .mi-show.cs-detail {
            max-width: none;
        }
        .mi-show__layout {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .mi-show__side {
            position: static;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .mi-show__side > .card:last-child {
            grid-column: 1 / -1;
        }
    }
    @media (max-width: 640px) {
        .mi-show .cs-detail__hero {
            padding: 14px;
            gap: 10px;
        }
        .mi-show .cs-detail__title {
            font-size: 18px;
        }
        .mi-show .cs-detail__icon {
            width: 38px;
            height: 38px;
            font-size: 17px;
            border-radius: 10px;
        }
        .mi-show .card.cs-panel {
            padding: 14px;
        }
        .mi-show__side {
            grid-template-columns: 1fr;
        }
        .mi-show .mi-booking__row {
            grid-template-columns: 96px minmax(0, 1fr);
            gap: 8px 12px;
            padding: 10px 0;
        }
        .mi-show #my-mock-notes textarea {
            min-height: 140px;
        }
        .mi-show #my-mock-note-save {
            width: 100%;
            margin-left: 0 !important;
            justify-content: center;
        }
        .mi-show .cs-ready .btn,
        .mi-show .cs-wait .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content cs-detail mi-show">
        <a href="{{ route('mentee.mock-interviews.index') }}" class="cs-detail__back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Mock Interviews
        </a>

        @if(session('success'))
        <div class="alert alert-success" style="margin:0 0 14px;">
            <span class="alert-icon">✓</span>
            <div style="font-size:13px;">{{ session('success') }}</div>
        </div>
        @endif

        <div class="mi-show__layout">
            <div class="mi-show__main">
                <div class="cs-detail__hero cs-detail__hero--compact">
                    <div class="cs-detail__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                    </div>
                    <div class="cs-detail__hero-text">
                        <h1 class="cs-detail__title">Mock interview</h1>
                        <div class="cs-detail__meta-row">
                            <span class="cs-status cs-status--{{ $statusTone }}">{{ $item->statusLabel() }}</span>
                            <span class="cs-detail__meta">#{{ $item->id }} · {{ $item->duration_minutes }} min</span>
                            @if(!$item->is_paid_addon && $item->plan_slug)
                            <span class="cs-chip">{{ ucfirst($item->plan_slug) }} plan</span>
                            @elseif($item->is_paid_addon)
                            <span class="cs-detail__meta">Paid · ₹{{ number_format((float) $item->amount, 0) }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($item->is_paid_addon || $item->invoice || in_array($item->payment_status, ['paid', 'free'], true))
                <div class="card cs-panel cs-panel--tight">
                    <div class="cs-panel__head">Payment receipt</div>
                    <div class="mi-receipt">
                        <div class="mi-receipt__row"><span>Amount</span><strong>@if($item->is_paid_addon) ₹{{ number_format((float) $item->amount, 0) }} @else Included @endif</strong></div>
                        <div class="mi-receipt__row"><span>Method</span><strong>{{ $item->invoice?->paymentMethodLabel() ?? ucfirst((string) ($item->payment_method ?: $item->payment_status)) }}</strong></div>
                        @if($item->payment_reference)
                        <div class="mi-receipt__row"><span>Reference</span><strong><code>{{ $item->payment_reference }}</code></strong></div>
                        @endif
                        @if($item->invoice)
                        <div class="mi-receipt__row"><span>Invoice</span><strong><code>{{ $item->invoice->invoice_number }}</code></strong></div>
                        @endif
                    </div>
                    @if($item->invoice)
                    <div style="margin-top:14px;">
                        <a href="{{ route('mentee.mock-interview-invoices.download', $item->invoice) }}" class="btn btn-outline btn-sm">Download invoice</a>
                    </div>
                    @endif
                </div>
                @endif

                @if($item->status === 'completed')
                <div class="card cs-panel cs-panel--ready cs-panel--tight">
                    <div class="cs-ready">
                        <div>
                            <div class="cs-panel__head" style="margin-bottom:4px;">Interview complete</div>
                            @if($item->feedback)
                            <p class="cs-panel__notes" style="margin-bottom:0;white-space:pre-wrap;">{{ $item->feedback }}</p>
                            @else
                            <p class="cs-panel__hint" style="margin:0;">Completed {{ $item->completed_at?->timezone($tz)->format('d M Y, h:i A') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                @if($item->mentee_rating)
                <div class="card cs-panel cs-panel--tight">
                    <div class="cs-panel__head">Your rating</div>
                    <p class="mi-rating-score">★ {{ $item->mentee_rating }}/5</p>
                    @if($item->mentee_review)
                    <p class="cs-panel__hint" style="margin:8px 0 0;white-space:pre-wrap;">{{ $item->mentee_review }}</p>
                    @endif
                </div>
                @elseif($item->canBeRatedBy(auth()->user()))
                <div class="card cs-panel cs-panel--tight">
                    <div class="cs-panel__head">Rate this interview</div>
                    <p class="cs-panel__hint" style="margin:0 0 14px;">A short score helps us improve Interview Gym quality.</p>
                    <form method="POST" action="{{ route('mentee.mock-interviews.rate', $item) }}" style="display:grid;gap:14px;">
                        @csrf
                        <div>
                            <label class="form-label">Score</label>
                            <select name="mentee_rating" class="form-input" required>
                                <option value="">Select 1–5</option>
                                @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected(old('mentee_rating') == $i)>{{ $i }} — {{ ['','Poor','Fair','Good','Very good','Excellent'][$i] }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Note (optional)</label>
                            <textarea name="mentee_review" class="form-input" rows="3" maxlength="1000" placeholder="What helped most? What should we improve?">{{ old('mentee_review') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit rating</button>
                    </form>
                </div>
                @endif

                @elseif($item->status === 'confirmed')
                <div class="card cs-panel cs-panel--wait cs-panel--tight">
                    <div class="cs-wait">
                        <div class="cs-wait__pulse" aria-hidden="true"></div>
                        <div>
                            <strong>Confirmed</strong>
                            <p>Your mock interview is confirmed. A Vedrix admin will conduct the session.</p>
                            @if($item->confirmed_at)
                            <p class="cs-panel__hint" style="margin:8px 0 0;">Confirmed {{ $item->confirmed_at->timezone($tz)->format('d M Y, h:i A') }}</p>
                            @endif
                            @if($item->canJoinCall())
                                <a href="{{ route('mock-interviews.call', $item->id) }}" class="btn btn-primary" style="margin-top:12px;display:inline-flex;">Join interview</a>
                                <p class="cs-panel__hint" style="margin:10px 0 0;">Preferred: {{ $item->preferred_at?->timezone($tz)->format('d M Y, h:i A') }} · {{ $item->duration_minutes }} min</p>
                            @else
                                <p class="cs-panel__hint" style="margin:10px 0 0;">This interview window has ended.</p>
                            @endif
                        </div>
                    </div>
                </div>

                @elseif($item->status === 'submitted')
                <div class="card cs-panel cs-panel--wait cs-panel--tight">
                    <div class="cs-wait">
                        <div class="cs-wait__pulse" aria-hidden="true"></div>
                        <div>
                            <strong>Awaiting confirmation</strong>
                            <p>Our team is reviewing your request and will confirm your slot soon.</p>
                        </div>
                    </div>
                </div>

                @elseif($item->status === 'cancelled')
                <div class="card cs-panel cs-panel--error cs-panel--tight">
                    <strong>Cancelled</strong>
                    @if($item->admin_notes)
                    <p style="margin:6px 0 0;white-space:pre-wrap;">{{ $item->admin_notes }}</p>
                    @endif
                </div>
                @endif

                <div class="card cs-panel cs-panel--tight">
                    <div class="cs-panel__head">Shared notes from interviewer</div>
                    @forelse($sharedNotes as $note)
                    <div class="mi-show__note">
                        <div class="mi-show__note-meta">
                            {{ $note->author?->name ?? 'Interviewer' }}
                            · {{ $note->created_at?->format('d M Y') }}
                        </div>
                        <div class="mi-show__note-body">{{ $note->content ?? '' }}</div>
                    </div>
                    @empty
                    <p class="cs-panel__hint" style="margin:8px 0 0;">No shared notes yet. Notes from your interviewer appear here after the session.</p>
                    @endforelse
                </div>

                @include('frontend.mock-interviews.partials.my-notes', ['item' => $item])
            </div>

            <aside class="mi-show__side">
                @if($item->assigner)
                <div class="card cs-panel cs-panel--tight">
                    <div class="cs-panel__head">Interviewer</div>
                    <div class="mi-show__interviewer">
                        @if($item->assigner->avatar_url)
                        <div class="mi-show__avatar"><img src="{{ $item->assigner->avatar_url }}" alt=""></div>
                        @else
                        <div class="mi-show__avatar">{{ strtoupper(substr($item->assigner->name ?? 'A', 0, 1)) }}</div>
                        @endif
                        <div>
                            <div style="font-weight:700;line-height:1.3;">{{ $item->assigner->name }}</div>
                            <div style="font-size:12px;color:var(--text-3);margin-top:2px;">Vedrix Admin</div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="card cs-panel cs-panel--tight">
                    <div class="cs-panel__head">Booking details</div>
                    <div class="mi-booking">
                        <div class="mi-booking__row">
                            <div class="mi-booking__label">Preferred</div>
                            <p class="mi-booking__value">{{ $item->preferred_at?->timezone($tz)->format('d M Y, h:i A') }}<small>{{ str_replace('_', ' ', $tz) }}</small></p>
                        </div>

                        <div class="mi-booking__row">
                            <div class="mi-booking__label">Duration</div>
                            <p class="mi-booking__value">{{ $item->duration_minutes }} minutes</p>
                        </div>

                        @if($item->target_role)
                        <div class="mi-booking__row">
                            <div class="mi-booking__label">Target role</div>
                            <p class="mi-booking__value">{{ $item->target_role }}</p>
                        </div>
                        @endif

                        @if($item->mentee_notes)
                        <div class="mi-booking__row">
                            <div class="mi-booking__label">Your notes</div>
                            <p class="mi-booking__value">{{ $item->mentee_notes }}</p>
                        </div>
                        @endif

                        @if($item->admin_notes && $item->status !== 'cancelled')
                        <div class="mi-booking__row">
                            <div class="mi-booking__label">From Vedrix</div>
                            <p class="mi-booking__value">{{ $item->admin_notes }}</p>
                        </div>
                        @endif

                        <div class="mi-booking__row">
                            <div class="mi-booking__label">Submitted</div>
                            <p class="mi-booking__value">{{ $item->created_at?->timezone($tz)->format('d M Y, h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
