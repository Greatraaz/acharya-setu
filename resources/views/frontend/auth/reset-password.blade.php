{{-- resources/views/auth/reset-password.blade.php --}}
@extends('layouts.app')
@section('title','Reset Password — AcharyaSetu')
@section('content')
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:calc(var(--nav-h)+20px) 16px 40px;">
<div style="width:100%;max-width:400px;">
    <div class="text-center" style="margin-bottom:32px;">
        <img src="{{ asset('images/logo.png') }}" alt="" style="height:38px;margin:0 auto 12px;">
        <h1 style="font-size:22px;font-weight:800;">Set new password</h1>
    </div>
    <div class="card">
        @error('email')<div class="alert alert-error" style="margin-bottom:16px;"><span class="alert-icon">❌</span>{{ $message }}</div>@enderror
        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $email ?? '') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-input" placeholder="Min. 8 characters" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-input" placeholder="Repeat password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg">Reset Password →</button>
        </form>
    </div>
</div>
</div>
@endsection