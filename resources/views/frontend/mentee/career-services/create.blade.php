@extends('frontend.layouts.app')
@section('title', 'Request '.$quote['type'].' — Vedrix')

@php
    $pay = $quote['payment'] ?? null;
    $walletBalance = (float) ($quote['wallet_balance'] ?? auth()->user()->wallet_balance ?? 0);
    $openRequest = $quote['open_request'] ?? null;
    $canSubmit = (bool) ($quote['can_submit'] ?? true);
    $isResume = $type === 'resume';
    $title = $isResume ? 'Resume development' : 'LinkedIn optimisation';
    $ent = $quote['entitlement'] ?? [];
    $amountDue = $quote['is_free'] ? 0.0 : (float) ($quote['amount'] ?? 0);
    $walletShort = ! $quote['is_free'] && $walletBalance < $amountDue;
    $nextFree = $ent['next_free_at'] ?? null;
    $nextFreeLabel = null;
    if ($nextFree) {
        try { $nextFreeLabel = \Illuminate\Support\Carbon::parse($nextFree)->format('d M Y'); } catch (\Throwable $e) { $nextFreeLabel = $nextFree; }
    }
@endphp

@push('styles')
<style>
.cs-request{
    --cs: var(--brand, #f59e0b);
    --cs-light: var(--brand-light, #fcd34d);
    --cs-dark: var(--brand-dark, #d97706);
    --cs-soft: var(--brand-muted, rgba(245,158,11,.12));
    --cs-ring: color-mix(in srgb, var(--cs) 28%, transparent);
    --cs-ink: var(--text, #09090b);
    --cs-muted: var(--text-2, #52525b);
    --cs-faint: var(--text-3, #71717a);
    --cs-line: var(--border, rgba(0,0,0,.07));
    --cs-surface: var(--card-bg, #fff);
    --cs-surface-2: var(--bg-2, #f4f4f5);
    --cs-r: var(--radius-btn, 8px);
    --cs-rl: var(--radius-lg, 16px);
    --cs-ok: var(--success, #22c55e);
    --cs-ok-bg: var(--success-muted, rgba(34,197,94,.1));
    --cs-warn: var(--warning, #f59e0b);
    --cs-warn-bg: var(--warning-muted, rgba(245,158,11,.12));
    --cs-bad: var(--error, #ef4444);
    --cs-bad-bg: var(--error-muted, rgba(239,68,68,.1));
    --cs-sh: var(--shadow, 0 4px 24px rgba(0,0,0,.08));
    --cs-shl: var(--shadow-lg, 0 16px 48px rgba(0,0,0,.12));
    --accent: var(--cs);
    --accent-soft: var(--cs-soft);
    font-family: var(--font-body, "DM Sans", sans-serif);
    max-width: 980px;
}

.cs-request .cs-back{
    display:inline-flex; align-items:center; gap:6px;
    font-size:13px; font-weight:700; color:var(--cs); text-decoration:none;
}
.cs-request .cs-back:hover{ opacity:.85; }
.cs-request .cs-back svg{ width:16px; height:16px; }

.cs-request .dash-title{
    margin-top:12px;
    font-family: var(--font-head, "Sora", sans-serif);
    display:flex; align-items:center; gap:12px;
    letter-spacing:-.02em;
}
.cs-request .dash-title__icon{
    width:42px; height:42px; border-radius:var(--cs-r); flex:none;
    display:inline-flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg, var(--accent), color-mix(in srgb, var(--accent) 65%, #000));
    color:#fff; box-shadow:0 10px 22px -8px color-mix(in srgb, var(--accent) 35%, transparent);
}
.cs-request .dash-title__icon svg{ width:18px; height:18px; }
.cs-request .dash-subtitle{ color:var(--cs-muted); margin-top:6px; max-width:560px; }

.cs-request__layout{
    display:grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(260px, .85fr);
    gap:18px;
    margin-top:18px;
    align-items:start;
}
@media (max-width:900px){
    .cs-request__layout{ grid-template-columns:1fr; }
}

.cs-request .cs-card{
    position:relative; overflow:hidden;
    background:var(--cs-surface);
    border:1px solid var(--cs-line);
    border-radius:var(--cs-rl);
    padding:22px;
    box-shadow:var(--cs-sh);
}
.cs-request .cs-card__glow{
    position:absolute; inset:auto -18% -45% auto; width:220px; height:220px;
    border-radius:50%; pointer-events:none;
    background:radial-gradient(circle, color-mix(in srgb, var(--accent) 20%, transparent), transparent 68%);
}

.cs-request .cs-price{
    position:relative; z-index:1;
    display:grid; gap:12px;
    padding:16px;
    border-radius:12px;
    margin-bottom:20px;
    border:1px solid color-mix(in srgb, var(--accent) 18%, var(--cs-line));
    background:
        radial-gradient(280px 120px at 100% 0%, color-mix(in srgb, var(--accent) 14%, transparent), transparent 70%),
        var(--cs-surface-2);
}
.cs-request .cs-price__top{
    display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;
}
.cs-request .cs-price__badge{
    display:inline-flex; align-items:center; gap:6px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; font-weight:700; padding:5px 10px; border-radius:999px;
}
.cs-request .cs-price__badge--free{ background:var(--cs-ok-bg); color:var(--cs-ok); }
.cs-request .cs-price__badge--paid{ background:var(--accent-soft); color:var(--accent); }
.cs-request .cs-price__badge--low{ background:var(--cs-bad-bg); color:var(--cs-bad); }
.cs-request .cs-price__amount{
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:30px; font-weight:800; letter-spacing:-.03em; color:var(--accent); line-height:1;
}
.cs-request .cs-price__amount small{
    display:block; margin-top:6px;
    font-family: var(--font-body, "DM Sans", sans-serif);
    font-size:12px; font-weight:600; color:var(--cs-faint); letter-spacing:0;
}
.cs-request .cs-price__rows{ display:grid; gap:8px; }
.cs-request .cs-price__row{
    display:flex; align-items:center; justify-content:space-between; gap:12px;
    font-size:13px; color:var(--cs-muted);
}
.cs-request .cs-price__row strong{ color:var(--cs-ink); font-weight:700; }

.cs-request .form-group{ margin:0; position:relative; z-index:1; }
.cs-request .form-label{
    display:block;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; font-weight:700; letter-spacing:.08em;
    text-transform:uppercase; color:var(--cs-muted); margin-bottom:8px;
}
.cs-request .form-input,
.cs-request textarea.form-input{
    border-radius:var(--cs-r)!important;
    border:1px solid var(--cs-line);
    background:var(--cs-surface);
    color:var(--cs-ink);
    min-height:46px;
}
.cs-request textarea.form-input{ min-height:110px; resize:vertical; }
.cs-request .form-input:focus,
.cs-request textarea.form-input:focus{
    border-color:var(--accent);
    box-shadow:0 0 0 3px color-mix(in srgb, var(--accent) 18%, transparent);
    outline:none;
}
.cs-request .form-hint{ margin-top:7px; font-size:12px; color:var(--cs-faint); line-height:1.45; }

.cs-request .cs-upload{
    position:relative;
    border:1.5px dashed color-mix(in srgb, var(--accent) 28%, var(--cs-line));
    border-radius:12px;
    background:
        radial-gradient(240px 100px at 50% 0%, color-mix(in srgb, var(--accent) 10%, transparent), transparent 70%),
        var(--cs-surface-2);
    padding:28px 18px;
    text-align:center;
    transition:border-color .15s ease, background .15s ease, transform .15s ease;
    cursor:pointer;
}
.cs-request .cs-upload:hover,
.cs-request .cs-upload.is-drag{
    border-color:var(--accent);
    background:var(--accent-soft);
    transform:translateY(-1px);
}
.cs-request .cs-upload__icon{
    width:48px; height:48px; margin:0 auto 12px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    background:linear-gradient(145deg, var(--accent-soft), transparent);
    color:var(--accent);
    border:1px solid color-mix(in srgb, var(--accent) 20%, transparent);
}
.cs-request .cs-upload__icon svg{ width:22px; height:22px; }
.cs-request .cs-upload__title{
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:14px; font-weight:800; color:var(--cs-ink); letter-spacing:-.01em;
}
.cs-request .cs-upload__meta{ font-size:12px; color:var(--cs-faint); margin-top:5px; }
.cs-request .cs-upload__name{
    margin-top:12px; font-size:12px; font-weight:700; color:var(--accent);
    word-break:break-all;
}
.cs-request .cs-upload input[type="file"]{
    position:absolute; inset:0; opacity:0; cursor:pointer;
}

.cs-request .cs-form{ display:grid; gap:16px; position:relative; z-index:1; }
.cs-request .btn{ border-radius:var(--cs-r)!important; }
.cs-request .cs-submit{
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    width:100%; min-height:48px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-weight:700;
}
.cs-request .cs-submit svg{ width:16px; height:16px; }

.cs-request .cs-side{ display:grid; gap:14px; }
.cs-request .cs-side-card{
    background:var(--cs-surface);
    border:1px solid var(--cs-line);
    border-radius:var(--cs-rl);
    padding:18px;
    box-shadow:var(--cs-sh);
}
.cs-request .cs-side-card h3{
    margin:0 0 10px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:14px; font-weight:800; letter-spacing:-.02em; color:var(--cs-ink);
}
.cs-request .cs-side-card p{
    margin:0; font-size:13px; line-height:1.55; color:var(--cs-muted);
}
.cs-request .cs-side-list{ display:grid; gap:10px; margin:0; padding:0; list-style:none; }
.cs-request .cs-side-list li{
    display:flex; gap:10px; align-items:flex-start;
    font-size:12.5px; line-height:1.45; color:var(--cs-muted);
}
.cs-request .cs-side-list span{
    width:22px; height:22px; border-radius:999px; flex:none;
    display:inline-flex; align-items:center; justify-content:center;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:10px; font-weight:800; color:#fff;
    background:linear-gradient(135deg, var(--accent), color-mix(in srgb, var(--accent) 65%, #000));
}
.cs-request .cs-side-note{
    display:flex; gap:10px; align-items:flex-start;
    padding:12px 13px; border-radius:var(--cs-r);
    background:var(--accent-soft);
    border:1px solid color-mix(in srgb, var(--accent) 22%, transparent);
    color:var(--cs-muted); font-size:12.5px; line-height:1.45;
}
.cs-request .cs-side-note svg{
    width:16px; height:16px; flex:none; margin-top:1px; color:var(--accent);
}

.cs-request .cs-open-card{
    border-color:color-mix(in srgb, var(--cs-warn) 35%, var(--cs-line));
    background:
        radial-gradient(320px 140px at 100% 0%, color-mix(in srgb, var(--cs-warn) 14%, transparent), transparent 70%),
        var(--cs-warn-bg);
}
.cs-request .cs-open-card h3{
    margin:0 0 8px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:16px; font-weight:800; color:var(--cs-ink);
}
.cs-request .cs-open-card p{
    margin:0 0 16px; font-size:13px; line-height:1.55; color:var(--cs-muted);
}
</style>
@endpush

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content cs-request">
        <a href="{{ route('mentee.career-services.index') }}" class="cs-back">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Career Services
        </a>

        <div class="dash-title">
            <span class="dash-title__icon" aria-hidden="true">
                @if($isResume)
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                @else
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                @endif
            </span>
            {{ $title }}
        </div>
        <p class="dash-subtitle">{{ $ent['label'] ?? ($isResume ? 'Upload your current resume for a Vedrix review.' : 'Share your LinkedIn profile for optimisation guidance.') }}</p>

        @if(session('error'))
        <div class="alert alert-error" style="margin:16px 0;"><span class="alert-icon">!</span><div style="font-size:13px;">{{ session('error') }}</div></div>
        @endif

        @if($openRequest)
        <div class="cs-card cs-open-card" style="margin-top:18px;">
            <h3>You already have an open request</h3>
            <p>
                Status: <strong>{{ $openRequest['status_label'] ?? 'Under review' }}</strong>
                @if(!empty($openRequest['plan_slug']))
                    · Plan when submitted: <strong>{{ ucfirst($openRequest['plan_slug']) }}</strong>
                @endif
                <br>Finish or wait for this request before starting a new one.
            </p>
            <a href="{{ route('mentee.career-services.show', $openRequest['id']) }}" class="btn btn-primary cs-submit">
                View open request
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        @else
        <div class="cs-request__layout">
            <div class="cs-card">
                <div class="cs-card__glow" aria-hidden="true"></div>

                <div class="cs-price">
                    <div class="cs-price__top">
                        @if($quote['is_free'])
                            <span class="cs-price__badge cs-price__badge--free">Included in plan</span>
                        @else
                            <span class="cs-price__badge cs-price__badge--paid">Paid add-on</span>
                        @endif
                        @if($walletShort)
                            <span class="cs-price__badge cs-price__badge--low">Top-up may be needed</span>
                        @endif
                    </div>
                    <div class="cs-price__amount">
                        @if($quote['is_free']) ₹0 @else ₹{{ number_format($amountDue, 0) }} @endif
                        <small>
                            @if($quote['is_free'])
                                Uses your plan benefit
                            @elseif($nextFreeLabel)
                                Next free credit on {{ $nextFreeLabel }}
                            @else
                                One-time career service fee
                            @endif
                        </small>
                    </div>
                    <div class="cs-price__rows">
                        <div class="cs-price__row">
                            <span>Amount due now</span>
                            <strong>@if($quote['is_free']) Free @else ₹{{ number_format($amountDue, 0) }} @endif</strong>
                        </div>
                        @unless($quote['is_free'])
                        <div class="cs-price__row">
                            <span>Wallet balance</span>
                            <strong>₹{{ number_format($walletBalance, 0) }}</strong>
                        </div>
                        @endunless
                    </div>
                </div>

                <form id="career-service-form" method="POST" action="{{ route('mentee.career-services.store') }}" enctype="multipart/form-data" class="cs-form">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="payment_method" id="payment_method" value="">

                    @if($isResume)
                    <div class="form-group">
                        <label class="form-label">Upload current resume <span style="color:var(--cs-bad)">*</span></label>
                        <div class="cs-upload" id="cs-upload-zone">
                            <input type="file" name="resume" id="cs-resume-input" accept=".pdf,.doc,.docx" required>
                            <div class="cs-upload__icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </div>
                            <div class="cs-upload__title">Drop your resume here</div>
                            <div class="cs-upload__meta">or click to browse · PDF / DOC · max 10 MB</div>
                            <div class="cs-upload__name" id="cs-resume-name" hidden></div>
                        </div>
                        <p class="form-hint">We’ll use this file as the base for your reviewed deliverable.</p>
                    </div>
                    @else
                    <div class="form-group">
                        <label class="form-label">LinkedIn profile URL <span style="color:var(--cs-bad)">*</span></label>
                        <input type="url" name="linkedin_url" class="form-input" value="{{ old('linkedin_url') }}"
                               placeholder="https://www.linkedin.com/in/your-profile" required>
                        <p class="form-hint">Use a public profile link so our team can open and review it.</p>
                    </div>
                    @endif

                    <div class="form-group">
                        <label class="form-label">Notes for reviewer (optional)</label>
                        <textarea name="mentee_notes" class="form-input" rows="4" placeholder="Role you’re targeting, strengths to highlight, companies you’re applying to…">{{ old('mentee_notes') }}</textarea>
                        <p class="form-hint">A short brief helps us tailor the review to your goals.</p>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg cs-submit" id="career-submit-btn" @disabled(! $canSubmit)>
                        {{ $quote['is_free'] ? 'Submit request' : 'Continue to payment' }}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </form>
            </div>

            <aside class="cs-side">
                <div class="cs-side-card">
                    <h3>What happens next</h3>
                    <ul class="cs-side-list">
                        <li>
                            <span>1</span>
                            <div>You submit {{ $isResume ? 'your resume file' : 'your LinkedIn URL' }} and optional notes.</div>
                        </li>
                        <li>
                            <span>2</span>
                            <div>Vedrix reviews it and prepares a polished deliverable.</div>
                        </li>
                        <li>
                            <span>3</span>
                            <div>You download it from Career Services when status is Completed.</div>
                        </li>
                    </ul>
                </div>

                <div class="cs-side-card">
                    <h3>{{ $isResume ? 'Resume tips' : 'LinkedIn tips' }}</h3>
                    <ul class="cs-side-list">
                        @if($isResume)
                        <li><span>•</span><div>Use your latest version — even if imperfect.</div></li>
                        <li><span>•</span><div>Mention target role / industry in notes.</div></li>
                        <li><span>•</span><div>Prefer PDF for clean formatting.</div></li>
                        @else
                        <li><span>•</span><div>Make sure the profile is publicly viewable.</div></li>
                        <li><span>•</span><div>Tell us the role narrative you want.</div></li>
                        <li><span>•</span><div>Call out achievements you want emphasised.</div></li>
                        @endif
                    </ul>
                </div>

                <div class="cs-side-note">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <div>
                        @if($quote['is_free'])
                            This request uses your plan benefit — no payment step required.
                        @else
                            You’ll choose Wallet, Razorpay, or hybrid payment on the next step.
                        @endif
                    </div>
                </div>
            </aside>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if(! $openRequest)
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const CAREER_IS_FREE = @json((bool) $quote['is_free']);
const CAREER_PAY = @json($pay);
const CAREER_WALLET_URL = @json(route('mentee.wallet'));

(function setupResumeUpload() {
    const input = document.getElementById('cs-resume-input');
    const zone = document.getElementById('cs-upload-zone');
    const nameEl = document.getElementById('cs-resume-name');
    if (!input || !zone || !nameEl) return;

    const showName = () => {
        const file = input.files && input.files[0];
        if (!file) {
            nameEl.hidden = true;
            nameEl.textContent = '';
            return;
        }
        nameEl.hidden = false;
        nameEl.textContent = file.name;
    };
    input.addEventListener('change', showName);
    ;['dragenter','dragover'].forEach((evt) => {
        zone.addEventListener(evt, (e) => { e.preventDefault(); zone.classList.add('is-drag'); });
    });
    ;['dragleave','drop'].forEach((evt) => {
        zone.addEventListener(evt, (e) => { e.preventDefault(); zone.classList.remove('is-drag'); });
    });
    zone.addEventListener('drop', (e) => {
        const file = e.dataTransfer?.files?.[0];
        if (!file) return;
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        showName();
    });
})();

function openCareerPaymentChoice(info, onPick) {
    let box = document.getElementById('career-payment-choice-modal');
    if (!box) {
        box = document.createElement('div');
        box.id = 'career-payment-choice-modal';
        box.className = 'modal-overlay open';
        box.innerHTML = `
          <div class="modal" style="max-width:420px;">
            <div class="modal-header"><h3>Choose payment method</h3><button type="button" class="modal-close" onclick="closeCareerPaymentChoice()">×</button></div>
            <div class="modal-body" id="career-payment-choice-body"></div>
          </div>`;
        document.body.appendChild(box);
    }
    box.classList.add('open');
    const amount = info.amount ?? 0;
    const bal = info.wallet_balance ?? 0;
    const shortfall = info.shortfall ?? Math.max(0, amount - bal);
    const opts = info.payment_options || ['wallet','razorpay'];
    const body = document.getElementById('career-payment-choice-body');
    body.innerHTML = `
      <p style="font-size:13px;color:var(--text-2);margin-bottom:12px;">
        Amount due: <strong>₹${Number(amount).toLocaleString()}</strong><br>
        Wallet balance: <strong>₹${Number(bal).toLocaleString()}</strong>
        ${shortfall > 0 ? `<br>Shortfall: <strong>₹${Number(shortfall).toLocaleString()}</strong>` : ''}
      </p>
      <div style="display:grid;gap:8px;">
        ${opts.includes('wallet') ? `<button type="button" class="btn btn-primary" data-method="wallet">Pay with Wallet</button>` : ''}
        ${opts.includes('razorpay') ? `<button type="button" class="btn btn-ghost" data-method="razorpay">Pay with Razorpay</button>` : ''}
        ${opts.includes('hybrid') ? `<button type="button" class="btn btn-ghost" data-method="hybrid">Wallet + Razorpay (₹${Number(shortfall).toLocaleString()} online)</button>` : ''}
        ${shortfall > 0 ? `<a class="btn btn-ghost" href="${CAREER_WALLET_URL}">Top up wallet</a>` : ''}
      </div>`;
    body.querySelectorAll('[data-method]').forEach((btn) => {
        btn.addEventListener('click', () => {
            closeCareerPaymentChoice();
            onPick(btn.getAttribute('data-method'));
        });
    });
}

function closeCareerPaymentChoice() {
    document.getElementById('career-payment-choice-modal')?.classList.remove('open');
}

function openCareerRazorpay(order, requestId) {
    const rzp = new Razorpay({
        key: order.key,
        amount: order.amount_paise,
        currency: order.currency || 'INR',
        name: order.name || 'Vedrix',
        description: order.description || 'Career service',
        order_id: order.order_id,
        prefill: order.prefill || {},
        theme: { color: '#f59e0b' },
        handler: function (response) {
            AjaxPost(`{{ url('/mentee/career-services') }}/${requestId}/verify`, {
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_order_id: response.razorpay_order_id,
                razorpay_signature: response.razorpay_signature,
            }, {
                loader: true,
                onSuccess: (data) => {
                    showToast('success', data.message || 'Submitted!');
                    setTimeout(() => location.href = `{{ url('/mentee/career-services') }}/${requestId}`, 800);
                },
                onError: (err) => showToast('error', err.message || 'Payment verify failed.'),
            });
        },
    });
    rzp.on('payment.failed', () => showToast('error', 'Payment failed. You can retry from Your requests.'));
    rzp.open();
}

function handleCareerSubmitResult(res) {
    const requestId = res.data?.request?.id;
    if (res.requires_payment_choice && res.data?.payment_choice) {
        openCareerPaymentChoice(res.data.payment_choice, (method) => {
            AjaxPost(`{{ url('/mentee/career-services') }}/${requestId}/pay`, { payment_method: method }, {
                loader: true,
                onSuccess: handleCareerSubmitResult,
                onError: (err) => showToast('error', err.message || 'Could not start payment.'),
            });
        });
        return;
    }
    if (res.requires_payment && res.data?.payment) {
        openCareerRazorpay(res.data.payment, requestId);
        return;
    }
    showToast('success', res.message || 'Submitted!');
    setTimeout(() => location.href = `{{ url('/mentee/career-services') }}/${requestId}`, 800);
}

document.getElementById('career-service-form')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const form = e.target;
    const fd = new FormData(form);
    const btn = document.getElementById('career-submit-btn');

    if (!CAREER_IS_FREE && !fd.get('payment_method') && CAREER_PAY) {
        openCareerPaymentChoice(CAREER_PAY, (method) => {
            document.getElementById('payment_method').value = method;
            const next = new FormData(form);
            next.set('payment_method', method);
            AjaxPost(form.action, next, {
                btn,
                loader: true,
                onSuccess: handleCareerSubmitResult,
                onError: (err) => showToast('error', err.message || 'Could not submit.'),
            });
        });
        return;
    }

    AjaxPost(form.action, fd, {
        btn,
        loader: true,
        onSuccess: handleCareerSubmitResult,
        onError: (err) => showToast('error', err.message || 'Could not submit.'),
    });
});
</script>
@endif
@endpush
