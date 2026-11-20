@extends('frontend.layouts.app')
@section('title', 'Request mock interview — Vedrix')

@php
    $walletBalance = (float) ($quote['wallet_balance'] ?? auth()->user()->wallet_balance ?? 0);
    $selectedDuration = (int) old('duration_minutes', $duration ?? 60);
    $ent = $quote['entitlement'] ?? [];
    $rate = (float) ($quote['rate_per_minute'] ?? 0);
    $amountDue = (float) ($quote['amount'] ?? 0);
    $isFreeQuote = (bool) ($quote['is_free'] ?? false);
@endphp

@push('styles')
<style>
.mi-request{
    --mi: var(--brand, #f59e0b);
    --mi-dark: var(--brand-dark, #d97706);
    --mi-soft: var(--brand-muted, rgba(245,158,11,.12));
    --mi-ink: var(--text, #09090b);
    --mi-muted: var(--text-2, #52525b);
    --mi-faint: var(--text-3, #71717a);
    --mi-line: var(--border, rgba(0,0,0,.07));
    --mi-surface: var(--card-bg, #fff);
    --mi-surface-2: var(--bg-2, #f4f4f5);
    --mi-r: var(--radius-btn, 8px);
    --mi-rl: var(--radius-lg, 16px);
    --mi-ok: var(--success, #22c55e);
    --mi-ok-bg: var(--success-muted, rgba(34,197,94,.1));
    --mi-bad: var(--error, #ef4444);
    --mi-bad-bg: var(--error-muted, rgba(239,68,68,.1));
    --mi-sh: var(--shadow, 0 4px 24px rgba(0,0,0,.08));
    font-family: var(--font-body, "DM Sans", sans-serif);
    max-width: 980px;
}
.mi-request .mi-back{
    display:inline-flex; align-items:center; gap:6px;
    font-size:13px; font-weight:700; color:var(--mi); text-decoration:none;
}
.mi-request .mi-back:hover{ opacity:.85; }
.mi-request .mi-back svg{ width:16px; height:16px; }
.mi-request .dash-title{
    margin-top:12px;
    font-family: var(--font-head, "Sora", sans-serif);
    display:flex; align-items:center; gap:12px;
    letter-spacing:-.02em;
}
.mi-request .dash-title__icon{
    width:42px; height:42px; border-radius:var(--mi-r); flex:none;
    display:inline-flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg, var(--mi), var(--mi-dark));
    color:#fff; box-shadow:0 10px 22px -8px color-mix(in srgb, var(--mi) 35%, transparent);
}
.mi-request .dash-title__icon svg{ width:18px; height:18px; }
.mi-request .dash-subtitle{ color:var(--mi-muted); margin-top:6px; max-width:560px; }

.mi-request__layout{
    display:grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(260px, .85fr);
    gap:18px; margin-top:18px; align-items:start;
}
@media (max-width:900px){ .mi-request__layout{ grid-template-columns:1fr; } }

.mi-request .mi-card{
    position:relative; overflow:hidden;
    background:var(--mi-surface);
    border:1px solid var(--mi-line);
    border-radius:var(--mi-rl);
    padding:22px;
    box-shadow:var(--mi-sh);
}
.mi-request .mi-card__glow{
    position:absolute; inset:auto -18% -45% auto; width:220px; height:220px;
    border-radius:50%; pointer-events:none;
    background:radial-gradient(circle, color-mix(in srgb, var(--mi) 20%, transparent), transparent 68%);
}
.mi-request .mi-price{
    position:relative; z-index:1;
    display:grid; gap:12px; padding:16px; border-radius:12px; margin-bottom:20px;
    border:1px solid color-mix(in srgb, var(--mi) 18%, var(--mi-line));
    background:
        radial-gradient(280px 120px at 100% 0%, color-mix(in srgb, var(--mi) 14%, transparent), transparent 70%),
        var(--mi-surface-2);
}
.mi-request .mi-price__top{ display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
.mi-request .mi-price__badge{
    display:inline-flex; align-items:center; gap:6px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; font-weight:700; padding:5px 10px; border-radius:999px;
}
.mi-request .mi-price__badge--free{ background:var(--mi-ok-bg); color:var(--mi-ok); }
.mi-request .mi-price__badge--paid{ background:var(--mi-soft); color:var(--mi); }
.mi-request .mi-price__amount{
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:30px; font-weight:800; letter-spacing:-.03em; color:var(--mi); line-height:1;
}
.mi-request .mi-price__amount small{
    display:block; margin-top:6px;
    font-family: var(--font-body, "DM Sans", sans-serif);
    font-size:12px; font-weight:600; color:var(--mi-faint); letter-spacing:0;
}
.mi-request .mi-price__rows{ display:grid; gap:8px; }
.mi-request .mi-price__row{
    display:flex; align-items:center; justify-content:space-between; gap:12px;
    font-size:13px; color:var(--mi-muted);
}
.mi-request .mi-price__row strong{ color:var(--mi-ink); font-weight:700; }

.mi-request .form-group{ margin:0; position:relative; z-index:1; }
.mi-request .form-label{
    display:block;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; font-weight:700; letter-spacing:.08em;
    text-transform:uppercase; color:var(--mi-muted); margin-bottom:8px;
}
.mi-request .form-input,
.mi-request textarea.form-input,
.mi-request select.form-input{
    border-radius:var(--mi-r)!important;
    border:1px solid var(--mi-line);
    background:var(--mi-surface);
    color:var(--mi-ink);
    min-height:46px;
}
.mi-request textarea.form-input{ min-height:110px; resize:vertical; }
.mi-request .form-input:focus,
.mi-request textarea.form-input:focus,
.mi-request select.form-input:focus{
    border-color:var(--mi);
    box-shadow:0 0 0 3px color-mix(in srgb, var(--mi) 18%, transparent);
    outline:none;
}
.mi-request .form-hint{ margin-top:7px; font-size:12px; color:var(--mi-faint); line-height:1.45; }
.mi-request .mi-form{ display:grid; gap:16px; position:relative; z-index:1; }
.mi-request .btn{ border-radius:var(--mi-r)!important; }
.mi-request .mi-submit{
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    width:100%; min-height:48px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-weight:700;
}
.mi-request .mi-submit svg{ width:16px; height:16px; }

.mi-request .mi-side{ display:grid; gap:14px; }
.mi-request .mi-side-card{
    background:var(--mi-surface);
    border:1px solid var(--mi-line);
    border-radius:var(--mi-rl);
    padding:18px;
    box-shadow:var(--mi-sh);
}
.mi-request .mi-side-card h3{
    margin:0 0 10px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:14px; font-weight:800; letter-spacing:-.02em; color:var(--mi-ink);
}
.mi-request .mi-side-list{ display:grid; gap:10px; margin:0; padding:0; list-style:none; }
.mi-request .mi-side-list li{
    display:flex; gap:10px; align-items:flex-start;
    font-size:12.5px; line-height:1.45; color:var(--mi-muted);
}
.mi-request .mi-side-list span{
    width:22px; height:22px; border-radius:999px; flex:none;
    display:inline-flex; align-items:center; justify-content:center;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:10px; font-weight:800; color:#fff;
    background:linear-gradient(135deg, var(--mi), var(--mi-dark));
}
.mi-request .mi-side-note{
    display:flex; gap:10px; align-items:flex-start;
    padding:12px 13px; border-radius:var(--mi-r);
    background:var(--mi-soft);
    border:1px solid color-mix(in srgb, var(--mi) 22%, transparent);
    color:var(--mi-muted); font-size:12.5px; line-height:1.45;
}
.mi-request .mi-side-note svg{
    width:16px; height:16px; flex:none; margin-top:1px; color:var(--mi);
}
.mi-request .mi-duration-grid{
    display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:8px;
}
@media (max-width:520px){ .mi-request .mi-duration-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); } }
.mi-request .mi-duration{
    position:relative;
}
.mi-request .mi-duration input{
    position:absolute; opacity:0; pointer-events:none;
}
.mi-request .mi-duration label{
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    gap:2px; min-height:56px; padding:10px 6px;
    border:1px solid var(--mi-line); border-radius:var(--mi-r);
    background:var(--mi-surface-2); cursor:pointer;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:14px; font-weight:800; color:var(--mi-ink);
    transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
}
.mi-request .mi-duration label small{
    font-family: var(--font-body, "DM Sans", sans-serif);
    font-size:10px; font-weight:600; color:var(--mi-faint);
}
.mi-request .mi-duration input:checked + label{
    border-color:var(--mi);
    background:var(--mi-soft);
    box-shadow:0 0 0 3px color-mix(in srgb, var(--mi) 16%, transparent);
    color:var(--mi);
}
</style>
@endpush

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content mi-request">
        <a href="{{ route('mentee.mock-interviews.index') }}" class="mi-back">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Mock Interviews
        </a>

        <div class="dash-title">
            <span class="dash-title__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
            </span>
            Request mock interview
        </div>
        <p class="dash-subtitle">{{ $ent['label'] ?? 'Choose a preferred slot and we’ll confirm with a Vedrix interviewer.' }}</p>

        @if(session('error'))
        <div class="alert alert-error" style="margin:16px 0;"><span class="alert-icon">!</span><div style="font-size:13px;">{{ session('error') }}</div></div>
        @endif
        @if(session('success'))
        <div class="alert alert-success" style="margin:16px 0;"><span class="alert-icon">✓</span><div style="font-size:13px;">{{ session('success') }}</div></div>
        @endif

        <div class="mi-request__layout">
            <div class="mi-card">
                <div class="mi-card__glow" aria-hidden="true"></div>

                <div class="mi-price">
                    <div class="mi-price__top">
                        @if($isFreeQuote)
                            <span class="mi-price__badge mi-price__badge--free">Included in plan</span>
                        @else
                            <span class="mi-price__badge mi-price__badge--paid">Paid add-on</span>
                        @endif
                    </div>
                    <div class="mi-price__amount" id="mock-price-estimate">
                        <span id="mock-price-estimate-text">Calculating…</span>
                        <small id="mock-wallet-line" style="display:none;">Wallet balance: ₹{{ number_format($walletBalance, 0) }}</small>
                    </div>
                    <div class="mi-price__rows">
                        <div class="mi-price__row">
                            <span>Rate</span>
                            <strong>₹{{ number_format($rate, 0) }}/min</strong>
                        </div>
                        <div class="mi-price__row">
                            <span>Wallet balance</span>
                            <strong>₹{{ number_format($walletBalance, 0) }}</strong>
                        </div>
                    </div>
                </div>

                <form id="mock-interview-form" method="POST" action="{{ route('mentee.mock-interviews.store') }}" class="mi-form">
                    @csrf
                    <input type="hidden" name="timezone" value="Asia/Kolkata">
                    <input type="hidden" name="payment_method" id="mock_payment_method" value="">

                    <div class="form-group">
                        <label class="form-label">Preferred date &amp; time <span style="color:var(--mi-bad)">*</span></label>
                        <input type="datetime-local" name="preferred_at" id="preferred_at" class="form-input"
                               value="{{ old('preferred_at') }}" required>
                        <p class="form-hint">India Standard Time (Asia/Kolkata). Must be at least 2 hours from now.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Duration <span style="color:var(--mi-bad)">*</span></label>
                        <div class="mi-duration-grid">
                            @foreach([30, 45, 60, 90] as $mins)
                            <div class="mi-duration">
                                <input type="radio" name="duration_minutes" id="dur_{{ $mins }}" value="{{ $mins }}"
                                       @checked($selectedDuration === $mins) required>
                                <label for="dur_{{ $mins }}">{{ $mins }}<small>minutes</small></label>
                            </div>
                            @endforeach
                        </div>
                        {{-- Keep select id for existing JS price refresh --}}
                        <select name="duration_minutes_mirror" id="duration_minutes" class="form-input" hidden aria-hidden="true" tabindex="-1">
                            @foreach([30, 45, 60, 90] as $mins)
                            <option value="{{ $mins }}" @selected($selectedDuration === $mins)>{{ $mins }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Target role (optional)</label>
                        <input type="text" name="target_role" class="form-input" value="{{ old('target_role') }}"
                               placeholder="e.g. Product Manager, SDE-1">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes for the interviewer (optional)</label>
                        <textarea name="mentee_notes" class="form-input" rows="3" placeholder="Focus areas, company prep, anything we should know…">{{ old('mentee_notes') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg mi-submit" id="mock-submit-btn">
                        Submit request
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </form>
            </div>

            <aside class="mi-side">
                <div class="mi-side-card">
                    <h3>What happens next</h3>
                    <ul class="mi-side-list">
                        <li><span>1</span><div>You pick a preferred time and duration.</div></li>
                        <li><span>2</span><div>Vedrix reviews and confirms your slot.</div></li>
                        <li><span>3</span><div>Join the live interview from your bookings.</div></li>
                    </ul>
                </div>
                <div class="mi-side-card">
                    <h3>Prep tips</h3>
                    <ul class="mi-side-list">
                        <li><span>•</span><div>Share the role you’re targeting.</div></li>
                        <li><span>•</span><div>Mention companies or interview rounds.</div></li>
                        <li><span>•</span><div>Call out weak areas you want drilled.</div></li>
                    </ul>
                </div>
                <div class="mi-side-note">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <div>
                        @if($isFreeQuote)
                            This duration uses your plan credit — no payment step required.
                        @else
                            You’ll choose Wallet, Razorpay, or hybrid payment before submission.
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const MOCK_QUOTE = @json($quote);
const MOCK_WALLET_URL = @json(route('mentee.wallet'));
const MOCK_STORE_URL = @json(route('mentee.mock-interviews.store'));
const MOCK_INDEX_URL = @json(route('mentee.mock-interviews.index'));
const MOCK_VERIFY_TEMPLATE = @json(route('mentee.mock-interviews.verify', ['mockInterview' => '__ID__']));
const MOCK_PAY_TEMPLATE = @json(route('mentee.mock-interviews.pay', ['mockInterview' => '__ID__']));
const MOCK_SHOW_TEMPLATE = @json(route('mentee.mock-interviews.show', ['mockInterview' => '__ID__']));
const MOCK_FLASH_PAY = @json(session('pay'));
const MOCK_PENDING_ID = @json(session('pending_request_id') ? encrypt_id(session('pending_request_id')) : null);

function mockUrl(template, id) {
    return template.replace('__ID__', String(id));
}

function getSelectedDuration() {
    const radio = document.querySelector('input[name="duration_minutes"]:checked');
    if (radio) return parseInt(radio.value || '60', 10);
    return parseInt(document.getElementById('duration_minutes')?.value || '60', 10);
}

function syncDurationMirror() {
    const sel = document.getElementById('duration_minutes');
    if (sel) sel.value = String(getSelectedDuration());
}

function computeMockPricing(durationMinutes) {
    const ent = MOCK_QUOTE.entitlement || {};
    const rate = Number(MOCK_QUOTE.rate_per_minute || 0);
    const maxFreeRaw = ent.max_free_duration;
    const maxFree = maxFreeRaw === null || maxFreeRaw === undefined || maxFreeRaw === ''
        ? null
        : Number(maxFreeRaw);
    const remaining = Number(ent.remaining ?? 0);
    const included = Boolean(ent.included);
    const isFree = included && remaining > 0 && maxFree !== null && !Number.isNaN(maxFree) && durationMinutes <= maxFree;
    const amount = isFree ? 0 : Math.round(rate * durationMinutes);
    return { isFree, amount, rate, maxFree };
}

function refreshMockPriceEstimate() {
    syncDurationMirror();
    const duration = getSelectedDuration();
    const { isFree, amount, rate, maxFree } = computeMockPricing(duration);
    const textEl = document.getElementById('mock-price-estimate-text');
    const walletLine = document.getElementById('mock-wallet-line');
    const btn = document.getElementById('mock-submit-btn');

    if (isFree) {
        textEl.innerHTML = '₹0';
        if (walletLine) {
            walletLine.style.display = 'block';
            walletLine.textContent = 'No charge — plan entitlement up to ' + maxFree + ' minutes';
        }
        if (btn) btn.childNodes[0].textContent = 'Submit request ';
    } else {
        textEl.innerHTML = '₹' + Number(amount).toLocaleString();
        if (walletLine) {
            walletLine.style.display = 'block';
            walletLine.textContent = '₹' + Number(rate).toLocaleString() + '/min × ' + duration + ' min';
        }
        if (btn) btn.childNodes[0].textContent = 'Continue ';
    }
}

function openMockPaymentChoice(info, onPick) {
    let box = document.getElementById('mock-payment-choice-modal');
    if (!box) {
        box = document.createElement('div');
        box.id = 'mock-payment-choice-modal';
        box.className = 'modal-overlay open';
        box.innerHTML = `
          <div class="modal" style="max-width:420px;">
            <div class="modal-header"><h3>Choose payment method</h3><button type="button" class="modal-close" onclick="closeMockPaymentChoice()">×</button></div>
            <div class="modal-body" id="mock-payment-choice-body"></div>
          </div>`;
        document.body.appendChild(box);
    }
    box.classList.add('open');
    const amount = info.amount ?? 0;
    const bal = info.wallet_balance ?? MOCK_QUOTE.wallet_balance ?? 0;
    const shortfall = info.shortfall ?? Math.max(0, amount - bal);
    const opts = info.payment_options || ['wallet', 'razorpay'];
    const body = document.getElementById('mock-payment-choice-body');
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
        ${shortfall > 0 ? `<a class="btn btn-ghost" href="${MOCK_WALLET_URL}">Top up wallet</a>` : ''}
      </div>`;
    body.querySelectorAll('[data-method]').forEach((btn) => {
        btn.addEventListener('click', () => {
            closeMockPaymentChoice();
            onPick(btn.getAttribute('data-method'));
        });
    });
}

function closeMockPaymentChoice() {
    document.getElementById('mock-payment-choice-modal')?.classList.remove('open');
}

function openMockRazorpay(order, requestId) {
    const rzp = new Razorpay({
        key: order.key,
        amount: order.amount_paise,
        currency: order.currency || 'INR',
        name: order.name || 'Vedrix',
        description: order.description || 'Mock interview',
        order_id: order.order_id,
        prefill: order.prefill || {},
        theme: { color: '#f59e0b' },
        handler: function (response) {
            AjaxPost(mockUrl(MOCK_VERIFY_TEMPLATE, requestId), {
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_order_id: response.razorpay_order_id,
                razorpay_signature: response.razorpay_signature,
            }, {
                loader: true,
                onSuccess: (data) => {
                    showToast('success', data.message || 'Payment successful!');
                    setTimeout(() => {
                        location.href = MOCK_INDEX_URL + '?submitted=1';
                    }, 800);
                },
                onError: (err) => showToast('error', err.message || 'Payment verify failed.'),
            });
        },
    });
    rzp.on('payment.failed', () => showToast('error', 'Payment failed. You can retry from Mock Interviews.'));
    rzp.open();
}

function handleMockSubmitResult(res, formEl) {
    const requestId = res.data?.request?.id;

    if (res.requires_payment_choice && res.data?.payment_choice) {
        openMockPaymentChoice(res.data.payment_choice, (method) => {
            if (requestId) {
                AjaxPost(mockUrl(MOCK_PAY_TEMPLATE, requestId), { payment_method: method }, {
                    loader: true,
                    onSuccess: (r) => handleMockSubmitResult(r, formEl),
                    onError: (err) => showToast('error', err.message || 'Could not start payment.'),
                });
                return;
            }
            const form = formEl || document.getElementById('mock-interview-form');
            document.getElementById('mock_payment_method').value = method;
            const next = new FormData(form);
            next.set('payment_method', method);
            next.set('duration_minutes', String(getSelectedDuration()));
            AjaxPost(MOCK_STORE_URL, next, {
                loader: true,
                onSuccess: (r) => handleMockSubmitResult(r, form),
                onError: (err) => showToast('error', err.message || 'Could not submit.'),
            });
        });
        return;
    }

    if (!requestId) {
        showToast('error', res.message || 'Could not create request.');
        return;
    }

    if (res.requires_payment && res.data?.payment) {
        openMockRazorpay(res.data.payment, requestId);
        return;
    }

    showToast('success', res.message || 'Submitted!');
    setTimeout(() => location.href = mockUrl(MOCK_SHOW_TEMPLATE, requestId), 800);
}

function buildPaymentChoiceForDuration(durationMinutes) {
    const { isFree, amount } = computeMockPricing(durationMinutes);
    if (isFree) return null;
    const bal = Number(MOCK_QUOTE.wallet_balance ?? 0);
    const shortfall = Math.max(0, amount - bal);
    const opts = [];
    if (bal >= amount) opts.push('wallet');
    if (amount > 0) opts.push('razorpay');
    if (bal > 0 && shortfall > 0) opts.push('hybrid');
    if (!opts.length) opts.push('razorpay');
    return {
        amount,
        wallet_balance: bal,
        shortfall,
        payment_options: [...new Set(opts)],
    };
}

document.querySelectorAll('input[name="duration_minutes"]').forEach((el) => {
    el.addEventListener('change', refreshMockPriceEstimate);
});
document.getElementById('duration_minutes')?.addEventListener('change', refreshMockPriceEstimate);
refreshMockPriceEstimate();

document.getElementById('mock-interview-form')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const form = e.target;
    const fd = new FormData(form);
    const duration = getSelectedDuration();
    fd.set('duration_minutes', String(duration));
    const btn = document.getElementById('mock-submit-btn');
    const { isFree } = computeMockPricing(duration);
    const payInfo = buildPaymentChoiceForDuration(duration);

    if (!isFree && !fd.get('payment_method') && payInfo) {
        openMockPaymentChoice(payInfo, (method) => {
            document.getElementById('mock_payment_method').value = method;
            const next = new FormData(form);
            next.set('payment_method', method);
            next.set('duration_minutes', String(duration));
            AjaxPost(MOCK_STORE_URL, next, {
                btn,
                loader: true,
                onSuccess: (r) => handleMockSubmitResult(r, form),
                onError: (err) => showToast('error', err.message || 'Could not submit.'),
            });
        });
        return;
    }

    AjaxPost(MOCK_STORE_URL, fd, {
        btn,
        loader: true,
        onSuccess: (r) => handleMockSubmitResult(r, form),
        onError: (err) => showToast('error', err.message || 'Could not submit.'),
    });
});

if (MOCK_FLASH_PAY && MOCK_PENDING_ID) {
    openMockRazorpay(MOCK_FLASH_PAY, MOCK_PENDING_ID);
}
</script>
@endpush
