@extends('frontend.layouts.app')
@section('title', 'Request '.$quote['type'].' — Vedrix')

@php
    $pay = $quote['payment'] ?? null;
    $walletBalance = (float) ($quote['wallet_balance'] ?? auth()->user()->wallet_balance ?? 0);
@endphp

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content" style="max-width:560px;">
        <a href="{{ route('mentee.career-services.index') }}" style="font-size:13px;color:var(--brand);">← Career Services</a>
        <div class="dash-title" style="margin-top:10px;">{{ $type === 'linkedin' ? 'LinkedIn optimisation' : 'Resume development' }}</div>
        <p class="dash-subtitle">{{ $quote['entitlement']['label'] }}</p>

        @if(session('error'))
        <div class="alert alert-error" style="margin:16px 0;"><span class="alert-icon">!</span><div style="font-size:13px;">{{ session('error') }}</div></div>
        @endif

        <div class="card" style="padding:20px;margin-top:16px;">
            <div style="font-size:13px;margin-bottom:16px;padding:12px;border-radius:10px;background:var(--bg-3);">
                @if($quote['is_free'])
                    <strong>No charge</strong> — this uses your plan entitlement.
                @else
                    <strong>Paid add-on:</strong> ₹{{ number_format($quote['amount'], 0) }}
                    <div style="margin-top:6px;color:var(--text-2);">Wallet balance: ₹{{ number_format($walletBalance, 0) }}</div>
                @endif
            </div>

            <form id="career-service-form" method="POST" action="{{ route('mentee.career-services.store') }}" enctype="multipart/form-data" class="space-y-4" style="display:grid;gap:14px;">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="payment_method" id="payment_method" value="">

                @if($type === 'resume')
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Upload current resume <span style="color:var(--error)">*</span></label>
                    <input type="file" name="resume" accept=".pdf,.doc,.docx" class="form-input" required>
                    <p class="form-hint">PDF or DOC, max 10 MB.</p>
                </div>
                @else
                <div class="form-group" style="margin:0;">
                    <label class="form-label">LinkedIn profile URL <span style="color:var(--error)">*</span></label>
                    <input type="url" name="linkedin_url" class="form-input" value="{{ old('linkedin_url') }}"
                           placeholder="https://www.linkedin.com/in/your-profile" required>
                </div>
                @endif

                <div class="form-group" style="margin:0;">
                    <label class="form-label">Notes for reviewer (optional)</label>
                    <textarea name="mentee_notes" class="form-input" rows="3" placeholder="Role you’re targeting, anything to emphasise…">{{ old('mentee_notes') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" id="career-submit-btn">
                    {{ $quote['is_free'] ? 'Submit request' : 'Continue' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const CAREER_IS_FREE = @json((bool) $quote['is_free']);
const CAREER_PAY = @json($pay);
const CAREER_WALLET_URL = @json(route('mentee.wallet'));

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
@endpush
