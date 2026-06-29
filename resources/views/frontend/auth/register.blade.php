@extends('frontend.layouts.app')
@section('title', 'Create Account — AcharyaSetu')

@section('content')
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:calc(var(--nav-h) + 40px) 16px 40px;">
<div style="width:100%;max-width:480px;">

    {{-- Logo --}}
    <div class="text-center" style="margin-bottom:32px;">
        <img src="{{ asset('images/logo.png') }}" alt="AcharyaSetu" style="height:40px;margin:0 auto 12px;">
        <h1 style="font-size:24px;font-weight:800;">Create your account</h1>
        <p style="font-size:14px;color:var(--text-2);">Join 45,000+ learners & mentors</p>
    </div>

    {{-- Progress Steps --}}
    <div class="steps-bar" style="margin-bottom:32px;">
        <div class="step-item active" data-step-indicator="1">
            <div class="step-circle">1</div>
        </div>
        <div class="step-line" data-line="1"></div>
        <div class="step-item" data-step-indicator="2">
            <div class="step-circle">2</div>
        </div>
        <div class="step-line" data-line="2"></div>
        <div class="step-item" data-step-indicator="3">
            <div class="step-circle">3</div>
        </div>
    </div>

    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius-xl);padding:32px;">

        {{-- STEP 1: Role --}}
        <div data-step="1">
            <h2 style="font-size:18px;margin-bottom:6px;">I want to join as a…</h2>
            <p style="font-size:13px;color:var(--text-2);margin-bottom:20px;">Choose your role to get started</p>

            <input type="hidden" name="role" id="role-input" value="mentee">
            <div class="role-grid">
                <div class="role-card selected" onclick="selectRole(this,'mentee')">
                    <div class="role-icon">🎓</div>
                    <h4>Mentee</h4>
                    <p>I want to find a mentor & grow my career</p>
                </div>
                <div class="role-card" onclick="selectRole(this,'mentor')">
                    <div class="role-icon">👨‍💼</div>
                    <h4>Mentor</h4>
                    <p>I want to share my expertise & earn</p>
                </div>
            </div>

            <button class="btn btn-primary btn-full" style="margin-top:8px;" onclick="FormStepper.next()">
                Continue →
            </button>
            <p style="text-align:center;margin-top:16px;font-size:13px;color:var(--text-2);">
                Already have an account? <a href="{{ route('login') }}" style="color:var(--brand);font-weight:600;">Sign in</a>
            </p>
        </div>

        {{-- STEP 2: Details --}}
        <div data-step="2" class="hidden">
            <h2 style="font-size:18px;margin-bottom:6px;">Your account details</h2>
            <p style="font-size:13px;color:var(--text-2);margin-bottom:20px;">Fill in your basic information</p>

            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" id="reg-name" class="form-input" placeholder="Rohit Sharma" autocomplete="name" data-required="Please enter your name">
                <div class="form-error" data-error-for="name" style="display:none;"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input type="email" id="reg-email" class="form-input" placeholder="rohit@example.com" autocomplete="email" data-required="Please enter a valid email">
                <div class="form-error" data-error-for="email" style="display:none;"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Phone Number *</label>
                <div class="input-prefix">
                    <span class="input-prefix-label">🇮🇳 +91</span>
                    <input type="tel" id="reg-phone" class="form-input" placeholder="98765 43210" maxlength="10" data-required="Please enter your phone number">
                </div>
                <div class="form-error" data-error-for="phone" style="display:none;"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" id="reg-password" class="form-input" placeholder="Min. 8 characters" autocomplete="new-password" data-required="Please set a password">
                <div class="form-hint">Must include uppercase, lowercase, and a number.</div>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:13px;color:var(--text-2);">
                    <input type="checkbox" id="reg-terms" style="margin-top:2px;accent-color:var(--brand);" required>
                    I agree to the <a href="{{ route('terms') }}" target="_blank" style="color:var(--brand);">Terms & Conditions</a> and <a href="{{ route('privacy') }}" target="_blank" style="color:var(--brand);">Privacy Policy</a>
                </label>
            </div>

            <div style="display:flex;gap:10px;margin-top:20px;">
                <button class="btn btn-ghost" onclick="FormStepper.back()">← Back</button>
                <button class="btn btn-primary" style="flex:1;" id="send-otp-btn" onclick="sendOtpStep()">
                    Send OTP →
                </button>
            </div>
        </div>

        {{-- STEP 3: OTP --}}
        <div data-step="3" class="hidden">
            <h2 style="font-size:18px;margin-bottom:6px;">Verify your account</h2>
            <p style="font-size:13px;color:var(--text-2);margin-bottom:20px;">We sent OTPs to your email and phone. Enter both to complete registration.</p>

            {{-- Email OTP --}}
            <div class="form-group">
                <label class="form-label">📧 Email OTP</label>
                <div class="otp-grid" id="email-otp-grid">
                    @for($i = 0; $i < 6; $i++)
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" id="email-otp-{{ $i }}">
                    @endfor
                </div>
            </div>

            {{-- Mobile OTP --}}
            <div class="form-group">
                <label class="form-label">📱 Mobile OTP</label>
                <div class="otp-grid" id="phone-otp-grid">
                    @for($i = 0; $i < 6; $i++)
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" id="phone-otp-{{ $i }}">
                    @endfor
                </div>
            </div>

            {{-- Resend --}}
            <div data-resend-wrap style="font-size:12px;color:var(--text-2);margin-bottom:16px;">
                Resend in <span id="resend-count">30</span>s &nbsp;|&nbsp;
            </div>
            <div style="font-size:12px;margin-bottom:20px;">
                <a href="#" id="resend-link" onclick="resendOtp()" style="color:var(--brand);font-weight:600;">Resend OTPs</a>
            </div>

            <div style="display:flex;gap:10px;">
                <button class="btn btn-ghost" onclick="FormStepper.back()">← Back</button>
                <button class="btn btn-primary" style="flex:1;" id="verify-btn" onclick="verifyAndRegister()">
                    ✓ Create Account
                </button>
            </div>
        </div>

    </div>{{-- /card --}}
</div>
</div>
@endsection

@push('scripts')
<script>
FormStepper.init(3);

// OTP init on step 3
const _origShow = FormStepper.show.bind(FormStepper);
FormStepper.show = function(n) {
    _origShow(n);
    // Update step indicators
    document.querySelectorAll('[data-step-indicator]').forEach(el => {
        const num = parseInt(el.dataset.stepIndicator);
        el.classList.toggle('done',   num < n);
        el.classList.toggle('active', num === n);
    });
    if (n === 3) {
        initOtpInputs('#email-otp-grid');
        initOtpInputs('#phone-otp-grid');
        startResendTimer('#resend-link', '#resend-count', 30);
    }
};

function validateStep2() {
    const name     = document.getElementById('reg-name').value.trim();
    const email    = document.getElementById('reg-email').value.trim();
    const phone    = document.getElementById('reg-phone').value.trim();
    const password = document.getElementById('reg-password').value;
    const terms    = document.getElementById('reg-terms').checked;

    if (!name)                            { showToast('error','Please enter your full name.'); return false; }
    if (!email || !email.includes('@'))   { showToast('error','Please enter a valid email address.'); return false; }
    if (!phone || phone.length < 10)      { showToast('error','Please enter a valid 10-digit phone number.'); return false; }
    if (!password || password.length < 8) { showToast('error','Password must be at least 8 characters.'); return false; }
    if (!terms)                            { showToast('warning','Please agree to the Terms & Conditions.'); return false; }
    return true;
}

function sendOtpStep() {
    if (!validateStep2()) return;

    const btn = document.getElementById('send-otp-btn');
    AjaxPost('/auth/send-otp', {
        email: document.getElementById('reg-email').value,
        phone: '+91' + document.getElementById('reg-phone').value,
    }, {
        btn, loader: true,
        onSuccess: () => {
            FormStepper.show(3);
        },
        onError: err => {
            showToast('error', err.message || 'Could not send OTP. Try again.');
        }
    });
}

function verifyAndRegister() {
    const emailOtp = collectOtp('#email-otp-grid');
    const phoneOtp = collectOtp('#phone-otp-grid');

    if (emailOtp.length < 6) { showToast('error','Please enter the complete email OTP.'); return; }
    if (phoneOtp.length < 6) { showToast('error','Please enter the complete mobile OTP.'); return; }

    const btn = document.getElementById('verify-btn');
    AjaxPost('/register', {
        name:            document.getElementById('reg-name').value,
        email:           document.getElementById('reg-email').value,
        phone:           '+91' + document.getElementById('reg-phone').value,
        password:        document.getElementById('reg-password').value,
        password_confirmation: document.getElementById('reg-password').value,
        role:            document.getElementById('role-input').value,
        email_otp:       emailOtp,
        phone_otp:       phoneOtp,
    }, {
        btn, loader: true,
        onSuccess: data => {
            showToast('success', '🎉 Account created! Redirecting…');
            setTimeout(() => window.location.href = data.redirect || '/dashboard', 1500);
        },
        onError: err => {
            showToast('error', err.message || 'Verification failed. Please check the OTPs.');
        }
    });
}

function resendOtp() {
    AjaxPost('/auth/send-otp', {
        email: document.getElementById('reg-email').value,
        phone: '+91' + document.getElementById('reg-phone').value,
    }, {
        onSuccess: () => {
            showToast('success', 'OTPs resent!');
            startResendTimer('#resend-link', '#resend-count', 30);
        }
    });
}
</script>
@endpush