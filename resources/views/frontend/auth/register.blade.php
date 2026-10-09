@extends('frontend.layouts.app')
@section('title', 'Create Account — Vedrix')

@push('styles')
<style>
    .register-page {
        min-height: 100vh;
        min-height: 100dvh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: calc(var(--nav-h) + 40px) 16px 40px;
        box-sizing: border-box;
    }
    .register-page__inner {
        width: 100%;
        max-width: 480px;
    }
    .register-page__hero {
        text-align: center;
        margin-bottom: 32px;
    }
    .register-page__hero h1 {
        font-size: 24px;
        font-weight: 800;
    }
    .register-page__hero img {
        height: 48px;
        width: auto;
        max-width: 180px;
        object-fit: contain;
        margin: 0 auto 12px;
        display: block;
    }
    .register-page__card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 32px;
    }
    .register-terms {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        cursor: pointer;
        font-size: 13px;
        color: var(--text-2);
        line-height: 1.45;
    }
    .register-terms input {
        margin-top: 2px;
        flex-shrink: 0;
        accent-color: var(--brand);
    }
    .register-terms__text {
        flex: 1;
        min-width: 0;
    }
    .register-terms__text a {
        color: var(--brand);
        white-space: nowrap;
    }
    .register-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }
    .register-actions .btn-primary {
        flex: 1;
    }
    @media (max-width: 640px) {
        .register-page {
            align-items: flex-start;
            padding: calc(var(--nav-h) + 20px) 14px 28px;
        }
        .register-page__hero {
            margin-bottom: 20px;
        }
        .register-page__hero img {
            height: 40px;
            margin-bottom: 8px;
        }
        .register-page__hero h1 {
            font-size: 20px;
        }
        .register-page__steps {
            margin-bottom: 20px !important;
        }
        .register-page__card {
            padding: 20px 16px;
        }
        .register-terms__text a {
            white-space: normal;
        }
        .register-actions {
            gap: 8px;
        }
        .register-actions .btn-ghost {
            flex-shrink: 0;
            padding-left: 14px;
            padding-right: 14px;
        }
        .register-page__card .otp-grid {
            gap: 5px;
            max-width: 100%;
        }
        .register-page__card .otp-input {
            font-size: 16px;
            max-width: none;
        }
    }
</style>
@endpush

@section('content')
<div class="register-page">
<div class="register-page__inner">

    {{-- Logo --}}
    <div class="register-page__hero">
        <img src="{{ asset('images/logo.png') }}" alt="Vedrix">
        <h1>Create your account</h1>
        <p style="font-size:14px;color:var(--text-2);">Join 45,000+ learners & mentors</p>
    </div>

    {{-- Progress Steps --}}
    <div class="steps-bar register-page__steps" style="margin-bottom:32px;">
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

    <div class="register-page__card">

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
                <input type="text" id="reg-name" class="form-input" placeholder="Rohit Sharma" autocomplete="name" maxlength="100" data-required="Please enter your name">
                <div class="form-error" data-error-for="name" style="display:none;"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input type="email" id="reg-email" class="form-input" placeholder="rohit@example.com" autocomplete="email" maxlength="255" inputmode="email" data-required="Please enter a valid email">
                <div class="form-error" data-error-for="email" style="display:none;"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Phone Number *</label>
                <div class="input-prefix">
                    <span class="input-prefix-label">🇮🇳 +91</span>
                    <input type="tel" id="reg-phone" class="form-input" placeholder="98765 43210" maxlength="10" inputmode="numeric" autocomplete="tel" data-required="Please enter your phone number">
                </div>
                <div class="form-error" data-error-for="phone" style="display:none;"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Password *</label>
                <div style="position:relative;">
                    <input type="password" id="reg-password" class="form-input" placeholder="Min. 8 characters" autocomplete="new-password" minlength="8" maxlength="100" data-required="Please set a password" style="padding-right:72px;">
                    <button type="button" id="reg-password-toggle" onclick="toggleRegPassword()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:var(--brand);font-size:12px;font-weight:700;cursor:pointer;">Show</button>
                </div>
                <div class="form-hint">At least 8 characters.</div>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="register-terms" for="reg-terms">
                    <input type="checkbox" id="reg-terms" required>
                    <span class="register-terms__text">
                        I agree to the <a href="{{ route('terms') }}" target="_blank">Terms &amp; Conditions</a> and <a href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>
                    </span>
                </label>
            </div>

            <div class="register-actions">
                <button class="btn btn-ghost" onclick="FormStepper.back()">← Back</button>
                <button class="btn btn-primary" id="send-otp-btn" onclick="sendOtpStep()">
                    Send OTP →
                </button>
            </div>
        </div>

        {{-- STEP 3: OTP --}}
        <div data-step="3" class="hidden">
            <h2 style="font-size:18px;margin-bottom:6px;">Verify your account</h2>
            <p style="font-size:13px;color:var(--text-2);margin-bottom:20px;">We sent an OTP to your email. Enter it to complete registration.</p>

            {{-- Email OTP --}}
            <div class="form-group">
                <label class="form-label">📧 Email OTP</label>
                <div class="otp-grid" id="email-otp-grid">
                    @for($i = 0; $i < 6; $i++)
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" id="email-otp-{{ $i }}">
                    @endfor
                </div>
            </div>

            {{-- Mobile OTP hidden from UI
            <div class="form-group">
                <label class="form-label">📱 Mobile OTP</label>
                <div class="otp-grid" id="phone-otp-grid">
                    @for($i = 0; $i < 6; $i++)
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" id="phone-otp-{{ $i }}">
                    @endfor
                </div>
            </div>
            --}}

            {{-- Resend --}}
            <div data-resend-wrap style="font-size:12px;color:var(--text-2);margin-bottom:16px;">
                Resend in <span id="resend-count">30</span>s &nbsp;|&nbsp;
            </div>
            <div style="font-size:12px;margin-bottom:20px;">
                <a href="#" id="resend-link" onclick="resendOtp()" style="color:var(--brand);font-weight:600;">Resend OTP</a>
            </div>

            <div class="register-actions" style="margin-top:0;">
                <button class="btn btn-ghost" onclick="FormStepper.back()">← Back</button>
                <button class="btn btn-primary" id="verify-btn" onclick="verifyAndRegister()">
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
        startResendTimer('#resend-link', '#resend-count', 30);
    }
};

let registerInFlight = false;

function toggleRegPassword() {
    const input = document.getElementById('reg-password');
    const btn = document.getElementById('reg-password-toggle');
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.textContent = show ? 'Hide' : 'Show';
}

function registrationPayload() {
    return {
        name: document.getElementById('reg-name').value.trim().replace(/\s+/g, ' '),
        email: document.getElementById('reg-email').value.trim().toLowerCase(),
        phoneDigits: document.getElementById('reg-phone').value.replace(/\D/g, ''),
        password: document.getElementById('reg-password').value,
        terms: document.getElementById('reg-terms').checked,
        role: document.getElementById('role-input').value,
    };
}

function validateStep2() {
    const data = registrationPayload();
    const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email);

    if (!data.name)                         { showToast('error','Please enter your full name.'); return false; }
    if (data.name.length > 100)             { showToast('error','Name must be 100 characters or fewer.'); return false; }
    if (!emailOk)                           { showToast('error','Please enter a valid email address.'); return false; }
    if (!/^[6-9]\d{9}$/.test(data.phoneDigits)) { showToast('error','Enter a 10-digit mobile number starting with 6–9.'); return false; }
    if (!data.password || data.password.length < 8) { showToast('error','Password must be at least 8 characters.'); return false; }
    if (!data.terms)                        { showToast('error','Please agree to the Terms & Conditions and Privacy Policy.'); return false; }
    return true;
}

function sendOtpStep() {
    if (!validateStep2()) return;

    const data = registrationPayload();
    const btn = document.getElementById('send-otp-btn');
    AjaxPost('/auth/send-otp', {
        email: data.email,
        phone: '+91' + data.phoneDigits,
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
    if (registerInFlight) return;
    if (!validateStep2()) return;

    const emailOtp = collectOtp('#email-otp-grid');
    if (emailOtp.length < 6) { showToast('error','Please enter the complete email OTP.'); return; }

    const data = registrationPayload();
    const btn = document.getElementById('verify-btn');
    registerInFlight = true;
    AjaxPost('/register', {
        name:            data.name,
        email:           data.email,
        phone:           '+91' + data.phoneDigits,
        password:        data.password,
        password_confirmation: data.password,
        role:            data.role,
        email_otp:       emailOtp,
        accepted_terms:  true,
    }, {
        btn, loader: true,
        onSuccess: data => {
            showToast('success', 'Account created! Redirecting…');
            setTimeout(() => window.location.href = data.redirect || '/dashboard', 1500);
        },
        onError: err => {
            registerInFlight = false;
            showToast('error', err.message || 'Verification failed. Please check the OTP.');
        }
    });
}

function resendOtp() {
    AjaxPost('/auth/send-otp', {
        email: registrationPayload().email,
        phone: '+91' + registrationPayload().phoneDigits,
    }, {
        onSuccess: () => {
            showToast('success', 'OTP resent!');
            startResendTimer('#resend-link', '#resend-count', 30);
        }
    });
}
</script>
@endpush