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
    $methodLabel = $item->invoice?->paymentMethodLabel()
        ?? match ($item->payment_method) {
            'wallet' => 'Wallet',
            'hybrid' => 'Wallet + Razorpay',
            'razorpay' => 'Razorpay',
            'plan' => 'Plan benefit',
            default => ucfirst((string) ($item->payment_method ?: $item->payment_status ?: '—')),
        };
@endphp

@push('styles')
<style>
.cs-show{
    --cs: var(--brand, #f59e0b);
    --cs-dark: var(--brand-dark, #d97706);
    --cs-soft: var(--brand-muted, rgba(245,158,11,.12));
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
    font-family: var(--font-body, "DM Sans", sans-serif);
    max-width: 980px;
}
.cs-show .cs-back{
    display:inline-flex; align-items:center; gap:6px;
    font-size:13px; font-weight:700; color:var(--cs); text-decoration:none; margin-bottom:12px;
}
.cs-show .cs-back svg{ width:16px; height:16px; }
.cs-show__layout{
    display:grid;
    grid-template-columns:minmax(0,1.4fr) minmax(260px,.85fr);
    gap:18px; align-items:start;
}
@media (max-width:900px){ .cs-show__layout{ grid-template-columns:1fr; } }
.cs-show__main,.cs-show__side{ display:grid; gap:14px; min-width:0; }
.cs-show__side{ position:sticky; top:calc(var(--nav-h, 64px) + 16px); }
@media (max-width:900px){ .cs-show__side{ position:static; } }

.cs-show .cs-hero{
    display:flex; align-items:center; gap:14px;
    padding:20px 22px;
    border:1px solid var(--cs-line);
    border-radius:var(--cs-rl);
    background:
        radial-gradient(420px 140px at 100% 0%, color-mix(in srgb, var(--cs) 14%, transparent), transparent 70%),
        var(--cs-surface);
    box-shadow:var(--cs-sh);
}
.cs-show .cs-hero__icon{
    width:48px; height:48px; border-radius:var(--cs-r); flex:none;
    display:inline-flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg, var(--cs), var(--cs-dark));
    color:#fff; box-shadow:0 10px 22px -8px color-mix(in srgb, var(--cs) 35%, transparent);
}
.cs-show .cs-hero__icon svg{ width:20px; height:20px; }
.cs-show .cs-hero__title{
    margin:0;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:clamp(20px, 2.4vw, 26px); font-weight:800;
    letter-spacing:-.03em; color:var(--cs-ink); line-height:1.15;
}
.cs-show .cs-hero__meta{
    display:flex; flex-wrap:wrap; gap:8px; align-items:center; margin-top:8px;
}
.cs-show .cs-chip{
    display:inline-flex; align-items:center; gap:6px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; font-weight:700; padding:5px 10px; border-radius:999px;
    background:var(--cs-soft); color:var(--cs);
}
.cs-show .cs-meta{ font-size:12.5px; color:var(--cs-faint); font-weight:600; }

.cs-show .cs-card{
    background:var(--cs-surface);
    border:1px solid var(--cs-line);
    border-radius:var(--cs-rl);
    padding:18px 20px;
    box-shadow:var(--cs-sh);
}
.cs-show .cs-card__head{
    margin:0 0 10px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:14px; font-weight:800; letter-spacing:-.02em; color:var(--cs-ink);
}
.cs-show .cs-card__hint{ margin:0; font-size:13px; line-height:1.55; color:var(--cs-muted); }
.cs-show .cs-card--ready{
    border-color:color-mix(in srgb, var(--cs-ok) 28%, var(--cs-line));
    background:
        radial-gradient(280px 120px at 100% 0%, color-mix(in srgb, var(--cs-ok) 12%, transparent), transparent 70%),
        var(--cs-surface);
}
.cs-show .cs-card--wait{
    border-color:color-mix(in srgb, var(--cs-warn) 28%, var(--cs-line));
    background:
        radial-gradient(280px 120px at 100% 0%, color-mix(in srgb, var(--cs-warn) 12%, transparent), transparent 70%),
        var(--cs-surface);
}
.cs-show .cs-card--pay{
    border-color:color-mix(in srgb, var(--cs-bad) 28%, var(--cs-line));
    background:
        radial-gradient(280px 120px at 100% 0%, color-mix(in srgb, var(--cs-bad) 10%, transparent), transparent 70%),
        var(--cs-surface);
}
.cs-show .cs-wait-row{ display:flex; gap:12px; align-items:flex-start; }
.cs-show .cs-pulse{
    width:10px; height:10px; border-radius:999px; flex:none; margin-top:5px;
    background:var(--cs-warn); box-shadow:0 0 0 0 color-mix(in srgb, var(--cs-warn) 45%, transparent);
    animation:csPulse 1.6s ease-out infinite;
}
@keyframes csPulse{
    0%{ box-shadow:0 0 0 0 color-mix(in srgb, var(--cs-warn) 45%, transparent); }
    70%{ box-shadow:0 0 0 10px transparent; }
    100%{ box-shadow:0 0 0 0 transparent; }
}
.cs-show .cs-rows{ display:grid; gap:10px; }
.cs-show .cs-row{
    display:flex; justify-content:space-between; gap:12px; align-items:flex-start;
    font-size:13px; color:var(--cs-muted);
}
.cs-show .cs-row strong{
    color:var(--cs-ink); font-weight:700; text-align:right; word-break:break-word;
}
.cs-show .cs-row code{
    font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size:12px; font-weight:600;
}
.cs-show .cs-file{
    display:flex; align-items:center; gap:12px; text-decoration:none;
    padding:12px 13px; border-radius:var(--cs-r);
    border:1px solid var(--cs-line); background:var(--cs-surface-2);
    transition: border-color .15s ease, transform .15s ease;
}
.cs-show .cs-file:hover{
    border-color:color-mix(in srgb, var(--cs) 35%, var(--cs-line));
    transform:translateY(-1px);
}
.cs-show .cs-file__icon{
    width:40px; height:40px; border-radius:10px; flex:none;
    display:inline-flex; align-items:center; justify-content:center;
    background:var(--cs-soft); color:var(--cs);
}
.cs-show .cs-file__icon svg{ width:18px; height:18px; }
.cs-show .cs-file strong{
    display:block; font-family: var(--font-head, "Sora", sans-serif);
    font-size:13px; font-weight:800; color:var(--cs-ink);
}
.cs-show .cs-file small{ display:block; margin-top:2px; font-size:12px; color:var(--cs-faint); }
.cs-show .cs-field{ margin-top:12px; }
.cs-show .cs-field__label{
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; font-weight:700; letter-spacing:.08em;
    text-transform:uppercase; color:var(--cs-faint); margin-bottom:6px;
}
.cs-show .cs-field__body,.cs-show .cs-link{
    font-size:13.5px; line-height:1.55; color:var(--cs-ink); word-break:break-word;
}
.cs-show .cs-link{ color:var(--cs); font-weight:700; text-decoration:none; }
.cs-show .cs-link:hover{ text-decoration:underline; }
.cs-show .cs-rating{
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:28px; font-weight:800; color:var(--cs); letter-spacing:-.03em; margin:0;
}
.cs-show .form-label{
    display:block;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; font-weight:700; letter-spacing:.08em;
    text-transform:uppercase; color:var(--cs-muted); margin-bottom:8px;
}
.cs-show .form-input, .cs-show textarea.form-input, .cs-show select.form-input{
    border-radius:var(--cs-r)!important;
    border:1px solid var(--cs-line);
    background:var(--cs-surface);
    color:var(--cs-ink);
    min-height:46px;
}
.cs-show textarea.form-input{ min-height:100px; resize:vertical; }
.cs-show .form-input:focus{
    border-color:var(--cs);
    box-shadow:0 0 0 3px color-mix(in srgb, var(--cs) 18%, transparent);
    outline:none;
}
.cs-show .btn{ border-radius:var(--cs-r)!important; }
.cs-show .cs-actions{ display:flex; flex-wrap:wrap; gap:8px; margin-top:14px; }
.cs-show .cs-side-note{
    display:flex; gap:10px; align-items:flex-start;
    padding:12px 13px; border-radius:var(--cs-r);
    background:var(--cs-soft);
    border:1px solid color-mix(in srgb, var(--cs) 22%, transparent);
    color:var(--cs-muted); font-size:12.5px; line-height:1.45;
}
.cs-show .cs-side-note svg{ width:16px; height:16px; flex:none; margin-top:1px; color:var(--cs); }
</style>
@endpush

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content cs-show">
        <a href="{{ route('mentee.career-services.index') }}" class="cs-back">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Career Services
        </a>

        @if(session('success'))
        <div class="alert alert-success" style="margin:0 0 14px;">
            <span class="alert-icon">✓</span>
            <div style="font-size:13px;">{{ session('success') }}</div>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-error" style="margin:0 0 14px;">
            <span class="alert-icon">!</span>
            <div style="font-size:13px;">{{ session('error') }}</div>
        </div>
        @endif

        <div class="cs-hero">
            <div class="cs-hero__icon" aria-hidden="true">
                @if($isResume)
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                @else
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                @endif
            </div>
            <div>
                <h1 class="cs-hero__title">{{ $item->typeLabel() }}</h1>
                <div class="cs-hero__meta">
                    <span class="cs-status cs-status--{{ $statusTone }}">{{ $item->statusLabel() }}</span>
                    <span class="cs-meta">#{{ $item->id }} · {{ $item->created_at?->format('d M Y') }}</span>
                    @if(!$item->is_paid_addon && $item->plan_slug)
                    <span class="cs-chip">{{ ucfirst($item->plan_slug) }} plan</span>
                    @elseif($item->is_paid_addon)
                    <span class="cs-chip">Paid · ₹{{ number_format((float) $item->amount, 0) }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="cs-show__layout" style="margin-top:18px;">
            <div class="cs-show__main">
                @if($item->status === 'completed')
                <div class="cs-card cs-card--ready">
                    <h2 class="cs-card__head">Your deliverable is ready</h2>
                    @if($item->admin_notes)
                    <p class="cs-card__hint" style="white-space:pre-wrap;margin-bottom:14px;">{{ $item->admin_notes }}</p>
                    @else
                    <p class="cs-card__hint" style="margin-bottom:14px;">Completed {{ $item->completed_at?->format('d M Y, h:i A') }}</p>
                    @endif
                    @if($item->deliverableUrl())
                    <a href="{{ $item->deliverableUrl() }}" target="_blank" class="btn btn-primary">
                        {{ $isResume ? 'Download updated resume' : 'Download LinkedIn guide' }}
                    </a>
                    @endif
                </div>

                @if($item->mentee_rating)
                <div class="cs-card">
                    <h2 class="cs-card__head">Your rating</h2>
                    <p class="cs-rating">★ {{ $item->mentee_rating }}/5</p>
                    @if($item->mentee_review)
                    <p class="cs-card__hint" style="margin-top:8px;white-space:pre-wrap;">{{ $item->mentee_review }}</p>
                    @endif
                </div>
                @elseif($item->canBeRatedBy(auth()->user()))
                <div class="cs-card">
                    <h2 class="cs-card__head">Rate this service</h2>
                    <p class="cs-card__hint" style="margin-bottom:14px;">Quick feedback helps us improve resume &amp; LinkedIn reviews.</p>
                    <form method="POST" action="{{ route('mentee.career-services.rate', $item) }}" style="display:grid;gap:14px;">
                        @csrf
                        <div>
                            <label class="form-label">Score</label>
                            <select name="mentee_rating" class="form-input" required>
                                <option value="">Select 1–5</option>
                                @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected(old('mentee_rating') == $i)>{{ $i }} — {{ ['','Poor','Fair','Good','Very good','Excellent'][$i] }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Note (optional)</label>
                            <textarea name="mentee_review" class="form-input" rows="3" maxlength="1000" placeholder="What worked well? What could be better?">{{ old('mentee_review') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit rating</button>
                    </form>
                </div>
                @endif

                @elseif($item->status === 'submitted')
                <div class="cs-card cs-card--wait">
                    <div class="cs-wait-row">
                        <span class="cs-pulse" aria-hidden="true"></span>
                        <div>
                            <h2 class="cs-card__head" style="margin-bottom:6px;">Under review</h2>
                            <p class="cs-card__hint">We’ll post your {{ $isResume ? 'updated resume' : 'LinkedIn guide' }} here when it’s ready. Nothing else needed from you.</p>
                        </div>
                    </div>
                </div>

                @elseif($item->status === 'pending_payment')
                <div class="cs-card cs-card--pay">
                    <h2 class="cs-card__head">Payment incomplete</h2>
                    <p class="cs-card__hint">Amount due: <strong>₹{{ number_format((float) $item->amount, 0) }}</strong>
                        · Wallet: ₹{{ number_format((float) auth()->user()->wallet_balance, 0) }}</p>
                    <div class="cs-actions">
                        <button type="button" class="btn btn-primary" id="career-pay-now-btn">Pay now</button>
                        <a href="{{ route('mentee.wallet') }}" class="btn btn-outline">Top up wallet</a>
                    </div>
                </div>
                @endif

                <div class="cs-card">
                    <h2 class="cs-card__head">Your submission</h2>
                    @if($item->resumeUrl())
                    <a href="{{ $item->resumeUrl() }}" target="_blank" class="cs-file">
                        <span class="cs-file__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </span>
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
                        <p class="cs-field__body" style="white-space:pre-wrap;margin:0;">{{ $item->mentee_notes }}</p>
                    </div>
                    @elseif(!$item->resumeUrl() && !$item->linkedin_url)
                    <p class="cs-card__hint">No submission details available.</p>
                    @endif
                </div>
            </div>

            <aside class="cs-show__side">
                @if($item->is_paid_addon || $item->invoice || in_array($item->payment_status, ['paid', 'free'], true))
                <div class="cs-card">
                    <h2 class="cs-card__head">Payment receipt</h2>
                    <div class="cs-rows">
                        <div class="cs-row"><span>Amount</span><strong>@if($item->is_paid_addon) ₹{{ number_format((float) $item->amount, 0) }} @else Included @endif</strong></div>
                        <div class="cs-row"><span>Method</span><strong>{{ $methodLabel }}</strong></div>
                        @if($item->payment_reference)
                        <div class="cs-row"><span>Reference</span><strong><code>{{ $item->payment_reference }}</code></strong></div>
                        @endif
                        @if($item->invoice)
                        <div class="cs-row"><span>Invoice</span><strong><code>{{ $item->invoice->invoice_number }}</code></strong></div>
                        @endif
                    </div>
                    @if($item->invoice)
                    <div class="cs-actions">
                        <a href="{{ route('mentee.career-service-invoices.download', $item->invoice) }}" class="btn btn-outline btn-sm">Download invoice</a>
                    </div>
                    @endif
                </div>
                @endif

                <div class="cs-card">
                    <h2 class="cs-card__head">Request details</h2>
                    <div class="cs-rows">
                        <div class="cs-row"><span>Type</span><strong>{{ $item->typeLabel() }}</strong></div>
                        <div class="cs-row"><span>Status</span><strong>{{ $item->statusLabel() }}</strong></div>
                        <div class="cs-row"><span>Submitted</span><strong>{{ $item->created_at?->format('d M Y, h:i A') }}</strong></div>
                        @if($item->completed_at)
                        <div class="cs-row"><span>Completed</span><strong>{{ $item->completed_at->format('d M Y, h:i A') }}</strong></div>
                        @endif
                    </div>
                </div>

                <div class="cs-side-note">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>
                    <div>Track progress here. You’ll also get a notification when your deliverable is ready.</div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function () {
    const requestId = @json(encrypt_id($item->id));
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
