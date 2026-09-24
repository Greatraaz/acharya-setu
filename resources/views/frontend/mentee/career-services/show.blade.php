@extends('frontend.layouts.app')
@section('title', $item->typeLabel().' — Vedrix')

@php
    $statusTone = match ($item->status) {
        'completed' => 'success',
        'submitted' => 'warning',
        'pending_payment' => 'error',
        default => 'muted',
    };
    $isResume = $item->type === 'resume';
@endphp

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content cs-detail">
        <a href="{{ route('mentee.career-services.index') }}" class="cs-detail__back">← Career Services</a>

        <div class="cs-detail__hero cs-detail__hero--compact">
            <div class="cs-detail__icon" aria-hidden="true">{{ $isResume ? '📄' : '💼' }}</div>
            <div class="cs-detail__hero-text">
                <h1 class="cs-detail__title">{{ $item->typeLabel() }}</h1>
                <div class="cs-detail__meta-row">
                    <span class="cs-status cs-status--{{ $statusTone }}">{{ $item->statusLabel() }}</span>
                    <span class="cs-detail__meta">#{{ $item->id }} · {{ $item->created_at?->format('d M Y') }}</span>
                    @if(!$item->is_paid_addon && $item->plan_slug)
                    <span class="cs-chip">{{ ucfirst($item->plan_slug) }} plan</span>
                    @elseif($item->is_paid_addon)
                    <span class="cs-detail__meta">Paid · ₹{{ number_format((float) $item->amount, 0) }}</span>
                    @endif
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin:0 0 14px;">
            <span class="alert-icon">✓</span>
            <div style="font-size:13px;">{{ session('success') }}</div>
        </div>
        @endif

        <div class="cs-detail__stack">
            @if($item->invoice)
            <div class="card cs-panel cs-panel--tight" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <div>
                    <div class="cs-panel__head" style="margin-bottom:2px;">Invoice</div>
                    <p class="cs-panel__hint" style="margin:0;">{{ $item->invoice->invoice_number }} · {{ $item->invoice->paymentMethodLabel() }} · ₹{{ number_format((float) $item->invoice->total_amount, 0) }}</p>
                </div>
                <a href="{{ route('mentee.career-service-invoices.download', $item->invoice) }}" class="btn btn-outline btn-sm">Download invoice</a>
            </div>
            @endif

            @if($item->status === 'completed')
            <div class="card cs-panel cs-panel--ready cs-panel--tight">
                <div class="cs-ready">
                    <div>
                        <div class="cs-panel__head" style="margin-bottom:4px;">Your deliverable is ready</div>
                        @if($item->admin_notes)
                        <p class="cs-panel__notes" style="margin-bottom:12px;">{{ $item->admin_notes }}</p>
                        @else
                        <p class="cs-panel__hint" style="margin:0 0 12px;">Completed {{ $item->completed_at?->format('d M Y, h:i A') }}</p>
                        @endif
                    </div>
                    @if($item->deliverableUrl())
                    <a href="{{ $item->deliverableUrl() }}" target="_blank" class="btn btn-primary">
                        {{ $isResume ? 'Download updated resume' : 'Download LinkedIn guide' }}
                    </a>
                    @endif
                </div>
            </div>

            @elseif($item->status === 'submitted')
            <div class="card cs-panel cs-panel--wait cs-panel--tight">
                <div class="cs-wait">
                    <div class="cs-wait__pulse" aria-hidden="true"></div>
                    <div>
                        <strong>Under review</strong>
                        <p>We’ll post your {{ $isResume ? 'updated resume' : 'LinkedIn guide' }} here when it’s ready. Nothing else needed from you.</p>
                    </div>
                </div>
            </div>

            @elseif($item->status === 'pending_payment')
            <div class="card cs-panel cs-panel--error cs-panel--tight">
                <strong>Payment incomplete</strong>
                <p style="margin:6px 0 12px;">Amount due: <strong>₹{{ number_format((float) $item->amount, 0) }}</strong>
                    · Wallet: ₹{{ number_format((float) auth()->user()->wallet_balance, 0) }}</p>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    <button type="button" class="btn btn-primary btn-sm" id="career-pay-now-btn">Pay now</button>
                    <a href="{{ route('mentee.wallet') }}" class="btn btn-outline btn-sm">Top up wallet</a>
                </div>
            </div>
            @endif

            <div class="card cs-panel cs-panel--tight">
                <div class="cs-panel__head">Your submission</div>
                <div class="cs-fields cs-fields--compact">
                    @if($item->resumeUrl())
                    <a href="{{ $item->resumeUrl() }}" target="_blank" class="cs-file">
                        <span class="cs-file__icon">⬇</span>
                        <span>
                            <strong>Original resume</strong>
                            <small>File you uploaded</small>
                        </span>
                    </a>
                    @endif

                    @if($item->linkedin_url)
                    <div class="cs-field">
                        <div class="cs-field__label">LinkedIn</div>
                        <a href="{{ $item->linkedin_url }}" target="_blank" rel="noopener" class="cs-link">{{ $item->linkedin_url }}</a>
                    </div>
                    @endif

                    @if($item->mentee_notes)
                    <div class="cs-field">
                        <div class="cs-field__label">Your notes</div>
                        <p class="cs-field__body">{{ $item->mentee_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function () {
    const requestId = {{ (int) $item->id }};
    const paymentChoice = @json($paymentChoice);
    const flashPay = @json(session('pay'));
    const walletUrl = @json(route('mentee.wallet'));

    function openRzp(order) {
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
                        setTimeout(() => location.reload(), 800);
                    },
                    onError: (err) => showToast('error', err.message || 'Payment verify failed.'),
                });
            },
        });
        rzp.on('payment.failed', () => showToast('error', 'Payment failed. Try again.'));
        rzp.open();
    }

    function openChoice(info) {
        let box = document.getElementById('career-payment-choice-modal');
        if (!box) {
            box = document.createElement('div');
            box.id = 'career-payment-choice-modal';
            box.className = 'modal-overlay';
            box.innerHTML = `
              <div class="modal" style="max-width:420px;">
                <div class="modal-header"><h3>Choose payment method</h3><button type="button" class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('open')">×</button></div>
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
            ${shortfall > 0 ? `<a class="btn btn-ghost" href="${walletUrl}">Top up wallet</a>` : ''}
          </div>`;
        body.querySelectorAll('[data-method]').forEach((btn) => {
            btn.addEventListener('click', () => {
                box.classList.remove('open');
                AjaxPost(`{{ url('/mentee/career-services') }}/${requestId}/pay`, { payment_method: btn.getAttribute('data-method') }, {
                    loader: true,
                    onSuccess: (res) => {
                        if (res.requires_payment && res.data?.payment) {
                            openRzp(res.data.payment);
                            return;
                        }
                        showToast('success', res.message || 'Submitted!');
                        setTimeout(() => location.reload(), 800);
                    },
                    onError: (err) => showToast('error', err.message || 'Could not start payment.'),
                });
            });
        });
    }

    if (flashPay) openRzp(flashPay);

    document.getElementById('career-pay-now-btn')?.addEventListener('click', () => {
        if (paymentChoice) openChoice(paymentChoice);
        else AjaxPost(`{{ url('/mentee/career-services') }}/${requestId}/pay`, {}, {
            loader: true,
            onSuccess: (res) => {
                if (res.requires_payment_choice && res.data?.payment_choice) openChoice(res.data.payment_choice);
                else if (res.requires_payment && res.data?.payment) openRzp(res.data.payment);
                else { showToast('success', res.message || 'Done'); setTimeout(() => location.reload(), 800); }
            },
            onError: (err) => showToast('error', err.message || 'Could not start payment.'),
        });
    });
})();
</script>
@endpush
