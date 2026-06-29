{{-- resources/views/auth/forgot-password.blade.php --}}
@extends('layouts.app')
@section('title', 'Forgot Password — AcharyaSetu')
@section('content')
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:calc(var(--nav-h)+20px) 16px 40px;">
<div style="width:100%;max-width:400px;">
    <div class="text-center" style="margin-bottom:32px;">
        <img src="{{ asset('images/logo.png') }}" alt="" style="height:38px;margin:0 auto 12px;">
        <h1 style="font-size:22px;font-weight:800;">Reset your password</h1>
        <p style="font-size:13px;color:var(--text-2);">Enter your email and we'll send a reset link.</p>
    </div>
    <div class="card">
        @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;"><span class="alert-icon">✅</span>{{ session('success') }}</div>@endif
        @error('email')<div class="alert alert-error" style="margin-bottom:16px;"><span class="alert-icon">❌</span>{{ $message }}</div>@enderror

        <form action="{{ route('password.email') }}" method="POST" data-ajax-form="{{ route('password.email') }}" data-success="Reset link sent! Check your inbox.">
            @csrf
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" placeholder="rohit@example.com" required value="{{ old('email') }}">
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg">Send Reset Link →</button>
        </form>
        <p style="text-align:center;margin-top:16px;font-size:13px;color:var(--text-2);">
            Remember your password? <a href="{{ route('login') }}" style="color:var(--brand);font-weight:600;">Sign in</a>
        </p>
    </div>
</div>
</div>
@endsection