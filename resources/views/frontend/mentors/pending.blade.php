{{-- resources/views/onboarding/mentor/pending.blade.php --}}
@extends('layouts.app')
@section('title','Application Under Review — AcharyaSetu')
@section('content')
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:calc(var(--nav-h)+40px) 16px 60px;">
<div style="max-width:520px;width:100%;text-align:center;">
    <div style="font-size:72px;margin-bottom:20px;">⏳</div>
    <h1 style="font-size:28px;font-weight:800;margin-bottom:12px;">Application Under Review</h1>
    <p style="font-size:15px;color:var(--text-2);line-height:1.75;margin-bottom:32px;">
        Thank you for applying to be a mentor on AcharyaSetu! Our team is reviewing your profile.
        You'll receive an email notification within <strong style="color:var(--brand);">24–48 hours</strong>.
    </p>
    <div class="card" style="text-align:left;margin-bottom:24px;">
        <div style="font-size:14px;font-weight:700;margin-bottom:14px;">What happens next?</div>
        @foreach([
            ['✅','Profile Review','Our team reviews your credentials, bio, and expertise.'],
            ['📧','Email Notification','You\'ll get an email as soon as the review is complete.'],
            ['🚀','Go Live','Once approved, your profile is listed and you can accept bookings.'],
        ] as [$i,$t,$d])
        <div style="display:flex;gap:12px;align-items:flex-start;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--border);">
            <span style="font-size:20px;flex-shrink:0;">{{ $i }}</span>
            <div>
                <div style="font-size:13px;font-weight:600;">{{ $t }}</div>
                <div style="font-size:12px;color:var(--text-2);">{{ $d }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @if(auth()->user()->mentor_status === 'rejected')
    <div class="alert alert-error" style="margin-bottom:20px;text-align:left;">
        <span class="alert-icon">❌</span>
        <div><strong>Application Rejected</strong><p>Please update your profile and resubmit.</p></div>
    </div>
    <a href="{{ route('mentor.onboarding', ['step'=>1]) }}" class="btn btn-primary btn-lg">Update & Resubmit</a>
    @else
    <a href="{{ route('home') }}" class="btn btn-outline">Back to Home</a>
    &nbsp;
    <a href="mailto:mentors@acharyasetu.com" class="btn btn-ghost">Contact Support</a>
    @endif
</div>
</div>
@endsection