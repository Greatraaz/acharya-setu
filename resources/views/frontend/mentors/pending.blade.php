{{-- resources/views/frontend/mentors/pending.blade.php --}}
@extends('frontend.layouts.app')
@php
    $pendingUser = auth()->user();
    $roleLabel = $pendingUser?->isExpert() ? 'expert' : 'mentor';
    $alreadyApproved = $pendingUser && $pendingUser->mentor_status === 'approved';
    $isRejected = $pendingUser && $pendingUser->mentor_status === 'rejected';
    $isPending = $pendingUser && $pendingUser->mentor_status === 'pending';
    $needsProfile = $pendingUser && ! $pendingUser->onboarding_completed;
@endphp
@section('title', $alreadyApproved ? 'Complete Your Profile — Vedrix' : 'Approval Pending — Vedrix')

@push('styles')
<style>
    .pending-wrap {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 16px 60px;
    }
    .pending-card {
        max-width: 560px;
        width: 100%;
        text-align: center;
    }
    .pending-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .02em;
        margin-bottom: 18px;
    }
    .pending-badge--wait {
        color: #b45309;
        background: #fffbeb;
        border: 1px solid #fde68a;
    }
    .pending-badge--ok {
        color: #047857;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
    }
    .pending-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        width: 100%;
    }
    .pending-actions--single {
        max-width: 280px;
        margin: 0 auto;
    }
    .pending-actions__btn {
        flex: 1 1 0;
        min-width: 0;
        min-height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        padding: 12px 14px;
        font-size: 14px;
        font-weight: 700;
        white-space: nowrap;
    }
    .pending-steps {
        text-align: left;
        margin-bottom: 24px;
    }
    .pending-step {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 14px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--border);
    }
    .pending-step:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: 0;
    }
    @media (max-width: 560px) {
        .pending-actions { flex-direction: column; }
        .pending-actions__btn { width: 100%; flex: none; }
    }
</style>
@endpush

@section('content')
@if($alreadyApproved && ! $needsProfile)
<script>window.location.replace(@json(route('mentor.dashboard')));</script>
@else
<div class="pending-wrap">
<div class="pending-card">
    <div style="font-size:72px;margin-bottom:12px;">{{ $alreadyApproved ? '✅' : ($isRejected ? '❌' : '⏳') }}</div>

    @if($isPending)
    <div class="pending-badge pending-badge--wait">Approval pending · Waiting for admin</div>
    @elseif($alreadyApproved)
    <div class="pending-badge pending-badge--ok">Account confirmed</div>
    @endif

    <h1 style="font-size:28px;font-weight:800;margin-bottom:12px;">
        @if($alreadyApproved)
            Complete your profile
        @elseif($isRejected)
            Application needs updates
        @else
            Approval is pending
        @endif
    </h1>

    <p style="font-size:15px;color:var(--text-2);line-height:1.75;margin-bottom:28px;">
        @if($alreadyApproved && $needsProfile)
            Your {{ $roleLabel }} account is confirmed.
            Finish your profile to go live and start taking sessions — no further approval is needed.
        @elseif($isRejected)
            Your {{ $roleLabel }} application was not approved yet.
            Update your profile and resubmit so our team can review it again.
        @else
            Thanks for joining Vedrix with Google as {{ $pendingUser?->isExpert() ? 'an expert' : 'a mentor' }}.
            Your account is created, but <strong style="color:var(--text);">admin approval is still pending</strong>.
            Please wait while our team reviews your application — usually within
            <strong style="color:var(--brand);">24–48 hours</strong>.
            You’ll get an email when you’re approved. After that, complete your profile before taking sessions.
        @endif
    </p>

    <div class="card pending-steps">
        <div style="font-size:14px;font-weight:700;margin-bottom:14px;">What happens next?</div>
        @foreach(
            $alreadyApproved
                ? [
                    ['📝', 'Complete profile', 'Add your bio, expertise, rates, and preferences.'],
                    ['🚀', 'Go live', 'Your profile is listed and mentees can book sessions.'],
                    ['📅', 'Take sessions', 'Start accepting bookings right away.'],
                ]
                : ($isRejected
                    ? [
                        ['✏️', 'Update profile', 'Fix the details our team flagged and resubmit.'],
                        ['👀', 'Re-review', 'Admins will check your updated application.'],
                        ['📧', 'Email update', 'You’ll hear back once a decision is made.'],
                    ]
                    : [
                        ['⏳', 'Wait for admin approval', 'Our team is reviewing your Google signup and credentials.'],
                        ['📧', 'Email notification', 'You’ll get an email as soon as you’re approved or if anything is needed.'],
                        ['📝', 'Complete profile after approval', 'Once approved, finish your profile before taking sessions.'],
                        ['🚀', 'Go live', 'Your profile is listed and you can accept bookings.'],
                    ])
            as [$icon, $title, $desc]
        )
        <div class="pending-step">
            <span style="font-size:20px;flex-shrink:0;">{{ $icon }}</span>
            <div>
                <div style="font-size:13px;font-weight:600;">{{ $title }}</div>
                <div style="font-size:12px;color:var(--text-2);">{{ $desc }}</div>
            </div>
        </div>
        @endforeach
    </div>

    @if($isRejected)
    <div class="alert alert-error" style="margin-bottom:20px;text-align:left;">
        <span class="alert-icon">❌</span>
        <div>
            <strong>Application rejected</strong>
            <p>{{ $pendingUser->rejection_reason ?: 'Please update your profile and resubmit.' }}</p>
        </div>
    </div>
    <div class="pending-actions pending-actions--single">
        <a href="{{ route('mentor.onboarding', ['step' => 1]) }}" class="btn btn-primary btn-lg pending-actions__btn">Update &amp; resubmit</a>
    </div>
    @elseif($alreadyApproved && $needsProfile)
    <div class="pending-actions">
        <a href="{{ route('mentor.onboarding', ['step' => 1]) }}" class="btn btn-primary btn-lg pending-actions__btn">Complete profile</a>
        <a href="{{ route('home') }}" class="btn btn-outline pending-actions__btn">Back to Home</a>
    </div>
    @else
    {{-- Still pending approval: no forced profile CTA — wait for admin --}}
    <div class="pending-actions">
        <a href="{{ route('home') }}" class="btn btn-primary pending-actions__btn">Back to Home</a>
        <a href="mailto:mentors@vedrix.com" class="btn btn-outline pending-actions__btn">Contact Support</a>
        @if($needsProfile)
        <a href="{{ route('mentor.onboarding', ['step' => 1]) }}" class="btn btn-ghost pending-actions__btn">Start profile early</a>
        @endif
    </div>
    <p style="font-size:12px;color:var(--text-3);margin-top:16px;line-height:1.6;">
        You can optionally start your profile now, but sessions stay locked until an admin approves your account.
    </p>
    @endif
</div>
</div>
@endif
@endsection
