@extends('frontend.layouts.app')
@section('title', 'Finish Google Signup — Vedrix')

@push('styles')
<style>
    .google-complete {
        min-height: 100vh;
        min-height: 100dvh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 16px;
        box-sizing: border-box;
    }
    .google-complete__card {
        width: 100%;
        max-width: 560px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 32px;
    }
    .google-complete__email {
        font-size: 13px;
        color: var(--text-2);
        margin-bottom: 20px;
        word-break: break-all;
    }
</style>
@endpush

@section('content')
<div class="google-complete">
    <div class="google-complete__card">
        <img src="{{ asset('images/logo.png') }}" alt="Vedrix" style="height:40px;width:auto;margin:0 auto 16px;display:block;">
        <h1 style="font-size:22px;font-weight:800;text-align:center;margin-bottom:6px;">Finish Google signup</h1>
        <p class="google-complete__email" style="text-align:center;">
            Signed in as <strong style="color:var(--text);">{{ $googleEmail }}</strong>
            @if($googleName)
                ({{ $googleName }})
            @endif
        </p>

        @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:20px;">
            <span class="alert-icon">❌</span>
            <div>{{ $errors->first() }}</div>
        </div>
        @endif

        <h2 style="font-size:16px;margin-bottom:6px;">I want to join as a…</h2>
        <p style="font-size:13px;color:var(--text-2);margin-bottom:16px;">Choose your role to create your account</p>

        <form action="{{ route('auth.google.complete.post') }}" method="POST" id="google-complete-form">
            @csrf
            <input type="hidden" name="role" id="role-input" value="{{ in_array($defaultRole, ['mentor','mentee','expert'], true) ? $defaultRole : 'mentee' }}">

            <div class="role-grid">
                <div class="role-card {{ ($defaultRole ?? 'mentee') === 'mentee' ? 'selected' : '' }}" onclick="selectRole(this,'mentee')">
                    <div class="role-icon">🎓</div>
                    <h4>Mentee</h4>
                    <p>Find a mentor and grow</p>
                </div>
                <div class="role-card {{ ($defaultRole ?? '') === 'mentor' ? 'selected' : '' }}" onclick="selectRole(this,'mentor')">
                    <div class="role-icon">👨‍💼</div>
                    <h4>Mentor</h4>
                    <p>Guide mentees long-term</p>
                </div>
                <div class="role-card {{ ($defaultRole ?? '') === 'expert' ? 'selected' : '' }}" onclick="selectRole(this,'expert')">
                    <div class="role-icon">🎯</div>
                    <h4>Expert</h4>
                    <p>Coach in focused 1:1 sessions</p>
                </div>
            </div>

            <label class="register-terms" for="google-terms" style="display:flex;align-items:flex-start;gap:10px;margin:20px 0 16px;cursor:pointer;font-size:13px;color:var(--text-2);">
                <input type="checkbox" name="accepted_terms" id="google-terms" value="1" required style="margin-top:2px;accent-color:var(--brand);">
                <span>
                    I agree to the <a href="{{ route('terms') }}" target="_blank" style="color:var(--brand);">Terms &amp; Conditions</a>
                    and <a href="{{ route('privacy') }}" target="_blank" style="color:var(--brand);">Privacy Policy</a>
                </span>
            </label>

            <button type="submit" class="btn btn-primary btn-full btn-lg">Create account →</button>
        </form>

        <p style="text-align:center;margin-top:16px;font-size:13px;color:var(--text-2);">
            Already have an account? <a href="{{ route('login') }}" style="color:var(--brand);font-weight:600;">Sign in</a>
        </p>
    </div>
</div>
@endsection
