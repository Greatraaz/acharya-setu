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
@endphp

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content cs-detail">
        <a href="{{ route('mentee.mock-interviews.index') }}" class="cs-detail__back">← Mock Interviews</a>

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

        @if(session('success'))
        <div class="alert alert-success" style="margin:0 0 14px;">
            <span class="alert-icon">✓</span>
            <div style="font-size:13px;">{{ session('success') }}</div>
        </div>
        @endif

        <div class="cs-detail__stack">
            @if($item->status === 'completed')
            <div class="card cs-panel cs-panel--ready cs-panel--tight">
                <div class="cs-ready">
                    <div>
                        <div class="cs-panel__head" style="margin-bottom:4px;">Interview complete</div>
                        @if($item->feedback)
                        <p class="cs-panel__notes" style="margin-bottom:12px;white-space:pre-wrap;">{{ $item->feedback }}</p>
                        @else
                        <p class="cs-panel__hint" style="margin:0 0 12px;">Completed {{ $item->completed_at?->timezone($tz)->format('d M Y, h:i A') }}</p>
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

            @if($item->assigner)
            <div class="card cs-panel cs-panel--tight">
                <div class="cs-panel__head">Interviewer</div>
                <div style="display:flex;align-items:center;gap:12px;margin-top:8px;">
                    @if($item->assigner->avatar_url)
                    <img src="{{ $item->assigner->avatar_url }}" alt="" style="width:44px;height:44px;border-radius:50%;object-fit:cover;">
                    @else
                    <div style="width:44px;height:44px;border-radius:50%;background:var(--bg-3);display:flex;align-items:center;justify-content:center;font-weight:700;">
                        {{ strtoupper(substr($item->assigner->name ?? 'A', 0, 1)) }}
                    </div>
                    @endif
                    <div>
                        <div style="font-weight:700;">{{ $item->assigner->name }}</div>
                        <div style="font-size:12px;color:var(--text-3);">Vedrix Admin</div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card cs-panel cs-panel--tight">
                <div class="cs-panel__head">Booking details</div>
                <div class="cs-fields cs-fields--compact">
                    <div class="cs-field">
                        <div class="cs-field__label">Preferred time</div>
                        <p class="cs-field__body">{{ $item->preferred_at?->timezone($tz)->format('d M Y, h:i A') }} ({{ $tz }})</p>
                    </div>

                    @if($item->target_role)
                    <div class="cs-field">
                        <div class="cs-field__label">Target role</div>
                        <p class="cs-field__body">{{ $item->target_role }}</p>
                    </div>
                    @endif

                    @if($item->mentee_notes)
                    <div class="cs-field">
                        <div class="cs-field__label">Your notes</div>
                        <p class="cs-field__body">{{ $item->mentee_notes }}</p>
                    </div>
                    @endif

                    @if($item->admin_notes && $item->status !== 'cancelled')
                    <div class="cs-field">
                        <div class="cs-field__label">Message from Vedrix</div>
                        <p class="cs-field__body">{{ $item->admin_notes }}</p>
                    </div>
                    @endif

                    <div class="cs-field">
                        <div class="cs-field__label">Submitted</div>
                        <p class="cs-field__body">{{ $item->created_at?->timezone($tz)->format('d M Y, h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
