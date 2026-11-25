@extends('frontend.layouts.app')
@section('title', 'Progress Locked — Vedrix')

@section('content')
@include('frontend.partials.journey-premium-ui')

<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-page journey-premium" style="display:flex;flex-direction:column;gap:1.15rem;">

            <div class="learn-intro fu">
                <span class="learn-eyebrow">Learning · Curriculum</span>
                <h2>My Journey</h2>
                <p>Progress reporting depends on your subscription plan.</p>
            </div>

            <div class="learn-panel empty-block fu" style="padding:2.5rem 1.25rem;">
                <div class="icon">🔒</div>
                <div style="font-family:var(--font-head);font-size:1.15rem;font-weight:800;color:var(--text);margin-bottom:.4rem;">Progress report not included</div>
                <p style="max-width:28rem;margin:0 auto;">
                    Your current plan does not include journey progress reports. Upgrade to a plan with
                    <strong>Progress report</strong> enabled to track months, weeks, and tasks.
                </p>
                <a href="{{ route('mentee.plans') }}" class="learn-btn-primary" style="margin-top:1rem;">View subscription plans</a>
            </div>
        </div>
    </div>
</div>
@endsection
