@extends('frontend.layouts.app')
@section('title', 'Sign In — AcharyaSetu')

@section('content')
<div style="min-height:100vh;display:grid;grid-template-columns:1fr 1fr;padding-top:var(--nav-h);">

    {{-- Left decorative panel --}}
    <div style="background:var(--bg-2);border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:center;padding:60px;position:relative;overflow:hidden;">
        <div style="position:absolute;inset:0;background:radial-gradient(ellipse 70% 70% at 50% 50%,rgba(245,158,11,.07) 0%,transparent 70%);pointer-events:none;"></div>
        <div style="position:relative;">
            <img src="{{ asset('images/logo.png') }}" alt="AcharyaSetu" style="height:44px;margin-bottom:36px;">
            <h2 style="font-size:32px;font-weight:800;line-height:1.15;margin-bottom:16px;">
                Your career journey<br><span class="text-brand">starts here.</span>
            </h2>
            <p style="font-size:15px;color:var(--text-2);line-height:1.75;margin-bottom:36px;">
                Connect with verified mentors, book sessions at transparent pricing, and track your growth — all in one place.
            </p>
            <div style="display:flex;flex-direction:column;gap:14px;">
                @foreach([
                    ['✅','2,400+ verified mentors across 30+ domains'],
                    ['⏱️','Pay-per-minute — no subscription needed'],
                    ['📊','Track your 6-month career journey'],
                    ['🔒','Secure payments & data privacy'],
                ] as [$icon, $text])
                <div style="display:flex;gap:12px;align-items:center;">
                    <span style="font-size:18px;width:28px;flex-shrink:0;">{{ $icon }}</span>
                    <span style="font-size:14px;color:var(--text-2);">{{ $text }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right login form --}}
    <div style="display:flex;align-items:center;justify-content:center;padding:40px;">
        <div style="width:100%;max-width:400px;">
            <h1 class="auth-title">Welcome back</h1>
            <p class="auth-subtitle">Sign in to continue your journey</p>

            {{-- Validation errors (server-side) --}}
            @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:20px;">
                <span class="alert-icon">❌</span>
                <div>{{ $errors->first() }}</div>
            </div>
            @endif

            {{-- Tabs --}}
            <div class="tab-bar" style="margin-bottom:24px;">
                <button class="tab-btn active" onclick="switchTab(this,'tab-email')">📧 Email</button>
                <button class="tab-btn" onclick="switchTab(this,'tab-phone')">📱 Phone OTP</button>
            </div>

            {{-- Email tab --}}
            <div id="tab-email" class="tab-content active">
                <form action="{{ route('login') }}" method="POST" id="email-login-form" data-ajax-form="{{ route('login') }}" data-redirect="{{ route('home') }}" data-success="Welcome back!">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" placeholder="rohit@example.com"
                               value="{{ old('email') }}" autocomplete="email" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="display:flex;justify-content:space-between;align-items:center;">
                            Password
                            <a href="{{ route('password.request') }}" style="color:var(--brand);font-size:11px;font-weight:600;text-transform:none;letter-spacing:0;">Forgot?</a>
                        </label>
                        <input type="password" name="password" class="form-input" placeholder="Your password" autocomplete="current-password" required>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
                        <input type="checkbox" name="remember" id="remember" style="accent-color:var(--brand);">
                        <label for="remember" style="font-size:13px;color:var(--text-2);cursor:pointer;">Remember me</label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full btn-lg">Sign In →</button>
                </form>
            </div>

            {{-- Phone OTP tab --}}
            <div id="tab-phone" class="tab-content">
                <div id="phone-step-1">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <div class="input-prefix">
                            <span class="input-prefix-label">🇮🇳 +91</span>
                            <input type="tel" id="login-phone" class="form-input" placeholder="98765 43210" maxlength="10">
                        </div>
                    </div>
                    <button class="btn btn-primary btn-full" id="login-send-otp-btn" onclick="loginSendOtp()">
                        Send OTP
                    </button>
                </div>

                <div id="phone-step-2" class="hidden">
                    <div style="background:var(--brand-muted);border:1px solid rgba(245,158,11,.25);border-radius:var(--radius);padding:12px 14px;margin-bottom:20px;font-size:13px;color:var(--text-2);">
                        OTP sent to +91 <span id="login-phone-display"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Enter 6-digit OTP</label>
                        <div class="otp-grid" id="login-otp-grid">
                            @for($i = 0; $i < 6; $i++)
                            <input type="text" class="otp-input" maxlength="1" inputmode="numeric">
                            @endfor
                        </div>
                    </div>
                    <div style="font-size:12px;color:var(--text-2);margin-bottom:16px;">
                        <span data-resend-wrap>Resend in <span id="login-resend-count">30</span>s &nbsp;|&nbsp;</span>
                        <a href="#" id="login-resend" onclick="loginSendOtp(true)" style="color:var(--brand);font-weight:600;">Resend OTP</a>
                    </div>
                    <div style="display:flex;gap:10px;">
                        <button class="btn btn-ghost" onclick="document.getElementById('phone-step-1').classList.remove('hidden');document.getElementById('phone-step-2').classList.add('hidden')">← Back</button>
                        <button class="btn btn-primary" style="flex:1;" id="login-verify-btn" onclick="loginVerifyOtp()">
                            ✓ Verify & Sign In
                        </button>
                    </div>
                </div>
            </div>

            <div class="divider-text" style="margin:24px 0;">
                <span>or</span>
            </div>
            <p style="text-align:center;font-size:13px;color:var(--text-2);">
                Don't have an account? <a href="{{ route('register') }}" style="color:var(--brand);font-weight:600;">Create one free</a>
            </p>
        </div>
    </div>
</div>

@if(request('redirect'))
<input type="hidden" id="login-redirect" value="{{ request('redirect') }}">
@endif
@endsection

@push('scripts')
<script>
function switchTab(btn, tabId) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

function loginSendOtp(resend = false) {
    const phone = document.getElementById('login-phone').value.trim();
    if (!phone || phone.length < 10) { showToast('error','Enter a valid 10-digit phone number.'); return; }

    const btn = resend ? document.getElementById('login-resend') : document.getElementById('login-send-otp-btn');
    AjaxPost('/auth/send-login-otp', { phone: '+91' + phone }, {
        btn, loader: true,
        onSuccess: () => {
            document.getElementById('login-phone-display').textContent = phone;
            document.getElementById('phone-step-1').classList.add('hidden');
            document.getElementById('phone-step-2').classList.remove('hidden');
            initOtpInputs('#login-otp-grid');
            startResendTimer('#login-resend', '#login-resend-count', 30);
            if (resend) showToast('success','OTP resent!');
        },
        onError: err => showToast('error', err.message || 'Could not send OTP.')
    });
}

function loginVerifyOtp() {
    const otp   = collectOtp('#login-otp-grid');
    const phone = document.getElementById('login-phone').value.trim();
    if (otp.length < 6) { showToast('error','Please enter the complete OTP.'); return; }

    AjaxPost('/auth/login-otp', { phone: '+91' + phone, otp }, {
        btn: document.getElementById('login-verify-btn'), loader: true,
        onSuccess: data => {
            showToast('success','Welcome back!');
            const redir = document.getElementById('login-redirect')?.value || data.redirect || '/dashboard';
            setTimeout(() => window.location.href = redir, 1200);
        },
        onError: err => showToast('error', err.message || 'Invalid OTP. Please try again.')
    });
}
</script>
@endpush