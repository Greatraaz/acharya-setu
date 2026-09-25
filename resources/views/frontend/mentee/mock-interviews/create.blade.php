@extends('frontend.layouts.app')
@section('title', 'Request mock interview — Vedrix')

@php
    $walletBalance = (float) ($quote['wallet_balance'] ?? auth()->user()->wallet_balance ?? 0);
    $selectedDuration = (int) old('duration_minutes', $duration ?? 60);
@endphp

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content" style="max-width:560px;">
        <a href="{{ route('mentee.mock-interviews.index') }}" style="font-size:13px;color:var(--brand);">← Mock Interviews</a>
        <div class="dash-title" style="margin-top:10px;">Request mock interview</div>
        <p class="dash-subtitle">{{ $quote['entitlement']['label'] ?? '' }}</p>

        @if(session('error'))
        <div class="alert alert-error" style="margin:16px 0;"><span class="alert-icon">!</span><div style="font-size:13px;">{{ session('error') }}</div></div>
        @endif
        @if(session('success'))
        <div class="alert alert-success" style="margin:16px 0;"><span class="alert-icon">✓</span><div style="font-size:13px;">{{ session('success') }}</div></div>
        @endif

        <div class="card" style="padding:20px;margin-top:16px;">
            <div id="mock-price-estimate" style="font-size:13px;margin-bottom:16px;padding:12px;border-radius:10px;background:var(--bg-3);">
                <span id="mock-price-estimate-text">Calculating…</span>
                <div id="mock-wallet-line" style="margin-top:6px;color:var(--text-2);display:none;">
                    Wallet balance: ₹{{ number_format($walletBalance, 0) }}
                </div>
            </div>

            <form id="mock-interview-form" method="POST" action="{{ route('mentee.mock-interviews.store') }}" class="space-y-4" style="display:grid;gap:14px;">
                @csrf
                <input type="hidden" name="timezone" value="Asia/Kolkata">
                <input type="hidden" name="payment_method" id="mock_payment_method" value="">

                <div class="form-group" style="margin:0;">
                    <label class="form-label">Preferred date & time <span style="color:var(--error)">*</span></label>
                    <input type="datetime-local" name="preferred_at" id="preferred_at" class="form-input"
                           value="{{ old('preferred_at') }}" required>
                    <p class="form-hint">Times are in India Standard Time (Asia/Kolkata).</p>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label">Duration <span style="color:var(--error)">*</span></label>
                    <select name="duration_minutes" id="duration_minutes" class="form-input" required>
                        @foreach([30, 45, 60, 90] as $mins)
                        <option value="{{ $mins }}" @selected($selectedDuration === $mins)>{{ $mins }} minutes</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label">Target role (optional)</label>
                    <input type="text" name="target_role" class="form-input" value="{{ old('target_role') }}"
                           placeholder="e.g. Product Manager, SDE-1">
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label">Notes for the interviewer (optional)</label>
                    <textarea name="mentee_notes" class="form-input" rows="3" placeholder="Focus areas, company prep, anything we should know…">{{ old('mentee_notes') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" id="mock-submit-btn">Submit request</button>
            </form>
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
const MOCK_PENDING_ID = @json(session('pending_request_id'));

function mockUrl(template, id) {
    return template.replace('__ID__', String(id));
}

function computeMockPricing(durationMinutes) {
    const ent = MOCK_QUOTE.entitlement || {};
    const rate = Number(MOCK_QUOTE.rate_per_minute || 0);
    const maxFree = Number(ent.max_free_duration ?? 60);
    const remaining = Number(ent.remaining ?? 0);
    const included = Boolean(ent.included);
    const isFree = included && remaining > 0 && durationMinutes <= maxFree;
    const amount = isFree ? 0 : Math.round(rate * durationMinutes);
    return { isFree, amount, rate };
}

function refreshMockPriceEstimate() {
    const sel = document.getElementById('duration_minutes');
    const duration = parseInt(sel?.value || '60', 10);
    const { isFree, amount, rate } = computeMockPricing(duration);
    const textEl = document.getElementById('mock-price-estimate-text');
    const walletLine = document.getElementById('mock-wallet-line');
    const btn = document.getElementById('mock-submit-btn');

    if (isFree) {
        textEl.innerHTML = '<strong>No charge</strong> — this uses your plan entitlement for up to ' + (MOCK_QUOTE.entitlement?.max_free_duration ?? 60) + ' minutes.';
        walletLine.style.display = 'none';
        btn.textContent = 'Submit request';
    } else {
        textEl.innerHTML = '<strong>Estimated total:</strong> ₹' + Number(amount).toLocaleString() + ' <span style="color:var(--text-2);">(₹' + Number(rate).toLocaleString() + '/min × ' + duration + ' min)</span>';
        walletLine.style.display = 'block';
        btn.textContent = 'Continue';
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
            // No unpaid row was stored — resubmit booking with chosen method.
            const form = formEl || document.getElementById('mock-interview-form');
            document.getElementById('mock_payment_method').value = method;
            const next = new FormData(form);
            next.set('payment_method', method);
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

document.getElementById('duration_minutes')?.addEventListener('change', refreshMockPriceEstimate);
refreshMockPriceEstimate();

document.getElementById('mock-interview-form')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const form = e.target;
    const fd = new FormData(form);
    const btn = document.getElementById('mock-submit-btn');
    const duration = parseInt(fd.get('duration_minutes') || '60', 10);
    const { isFree } = computeMockPricing(duration);
    const payInfo = buildPaymentChoiceForDuration(duration);

    if (!isFree && !fd.get('payment_method') && payInfo) {
        openMockPaymentChoice(payInfo, (method) => {
            document.getElementById('mock_payment_method').value = method;
            const next = new FormData(form);
            next.set('payment_method', method);
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
