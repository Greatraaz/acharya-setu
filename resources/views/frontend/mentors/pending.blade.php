{{-- resources/views/onboarding/mentor/pending.blade.php --}}
@extends('frontend.layouts.app')
@php
    $pendingUser = auth()->user();
    $alreadyApproved = $pendingUser && $pendingUser->mentor_status === 'approved';
    $needsProfile = $pendingUser && ! $pendingUser->onboarding_completed;
@endphp
@section('title', $alreadyApproved ? 'Complete Your Profile — Vedrix' : 'Application Under Review — Vedrix')

@push('styles')
<style>
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
    @media (max-width: 560px) {
        .pending-actions {
            flex-direction: column;
        }
        .pending-actions__btn {
            width: 100%;
            flex: none;
        }
    }
</style>
@endpush

@section('content')
@if($alreadyApproved && ! $needsProfile)
<script>window.location.replace(@json(route('mentor.dashboard')));</script>
@else
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:40px 16px 60px;">
<div style="max-width:520px;width:100%;text-align:center;">
    <div style="font-size:72px;margin-bottom:20px;">{{ $alreadyApproved ? '✅' : '⏳' }}</div>
    <h1 style="font-size:28px;font-weight:800;margin-bottom:12px;">
        @if($alreadyApproved)
            Account Confirmed
        @else
            Application Under Review
        @endif
    </h1>
    <p style="font-size:15px;color:var(--text-2);line-height:1.75;margin-bottom:32px;">
        @if($alreadyApproved && $needsProfile)
            Your {{ $pendingUser->isExpert() ? 'expert' : 'mentor' }} account is already confirmed.
            Complete your profile to go live and start taking sessions — no further approval is needed.
        @elseif($needsProfile)
            Your {{ $pendingUser->isExpert() ? 'expert' : 'mentor' }} account is created. You can finish your profile now, or after we confirm you.
            After confirmation, your profile is required before taking sessions.
        @else
            Thank you for applying to join Vedrix as {{ $pendingUser?->isExpert() ? 'an expert' : 'a mentor' }}! Our team is reviewing your profile.
            You'll receive an email notification within <strong style="color:var(--brand);">24–48 hours</strong>.
        @endif
    </p>
    <div class="card" style="text-align:left;margin-bottom:24px;">
        <div style="font-size:14px;font-weight:700;margin-bottom:14px;">What happens next?</div>
        @foreach(
            $alreadyApproved
                ? [
                    ['📝','Complete Profile','Add your bio, expertise, rates, and preferences.'],
                    ['🚀','Go Live','Your profile is listed and mentees can book sessions.'],
                    ['📅','Take Sessions','Start accepting bookings right away.'],
                ]
                : [
                    ['✅','Profile Review','Our team reviews your credentials, bio, and expertise.'],
                    ['📧','Email Notification','You\'ll get an email as soon as the review is complete.'],
                    ['🚀','Go Live','Once approved, your profile is listed and you can accept bookings.'],
                ]
            as [$i,$t,$d]
        )
        <div style="display:flex;gap:12px;align-items:flex-start;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--border);">
            <span style="font-size:20px;flex-shrink:0;">{{ $i }}</span>
            <div>
                <div style="font-size:13px;font-weight:600;">{{ $t }}</div>
                <div style="font-size:12px;color:var(--text-2);">{{ $d }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @if($pendingUser->mentor_status === 'rejected')
    <div class="alert alert-error" style="margin-bottom:20px;text-align:left;">
        <span class="alert-icon">❌</span>
        <div><strong>Application Rejected</strong><p>Please update your profile and resubmit.</p></div>
    </div>
    <div class="pending-actions pending-actions--single">
        <a href="{{ route('mentor.onboarding', ['step'=>1]) }}" class="btn btn-primary btn-lg pending-actions__btn">Update & Resubmit</a>
    </div>
    @else
    <div class="pending-actions">
        @if($needsProfile)
        <a href="{{ route('mentor.onboarding', ['step' => 1]) }}" class="btn btn-primary btn-lg pending-actions__btn">Complete profile</a>
        @endif
        <a href="{{ route('home') }}" class="btn btn-outline pending-actions__btn">Back to Home</a>
        <a href="mailto:mentors@vedrix.com" class="btn btn-ghost pending-actions__btn">Contact Support</a>
    </div>
    @endif
</div>
</div>
@endif
@endsection
