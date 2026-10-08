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
        max-width: 1120px;
        width: 100%;
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
        margin-bottom: 14px;
    }
    .mi-show .cs-detail__hero {
        margin-bottom: 0;
        padding: 18px 20px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        align-items: center;
    }
    .mi-show .cs-detail__title {
        font-size: 22px;
    }
    .mi-show .card.cs-panel {
        margin: 0;
        padding: 16px 18px;
        box-shadow: none;
    }
    .mi-show .card.cs-panel:hover {
        border-color: var(--border);
        box-shadow: none;
    }
    .mi-show .cs-panel__head {
        margin-bottom: 8px;
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
        <a href="{{ route('mentee.mock-interviews.index') }}" class="cs-detail__back">← Mock Interviews</a>

        @if(session('success'))
        <div class="alert alert-success" style="margin:0 0 14px;">
            <span class="alert-icon">✓</span>
            <div style="font-size:13px;">{{ session('success') }}</div>
        </div>
        @endif

        <div class="mi-show__layout">
            <div class="mi-show__main">
                <div class="cs-detail__hero cs-detail__hero--compact">
                    <div class="cs-detail__icon" aria-hidden="true">🎤</div>
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
