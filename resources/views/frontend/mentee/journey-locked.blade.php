@extends('frontend.layouts.app')
@section('title', 'Progress Locked — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">My Journey</div>
            <div class="dash-subtitle">Progress reporting depends on your subscription plan.</div>
        </div>

        <div class="empty-state" style="padding:56px 20px;">
            <div style="font-size:48px;margin-bottom:12px;">🔒</div>
            <div style="font-size:18px;font-weight:700;margin-bottom:8px;">Progress report not included</div>
            <p style="font-size:13px;color:var(--text-2);max-width:440px;margin:0 auto 18px;">
                Your current plan does not include journey progress reports. Upgrade to a plan with
                <strong>Progress report</strong> enabled to track months, weeks, and tasks.
            </p>
            <a href="{{ route('mentee.plans') }}" class="btn btn-primary">View subscription plans</a>
        </div>
    </div>
</div>
@endsection
