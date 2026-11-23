@extends('frontend.layouts.app')
@section('title', 'Wallet — Vedrix')

@push('styles')
<style>
.vw-hub{
    --vw: var(--brand, #f59e0b);
    --vw-dark: var(--brand-dark, #d97706);
    --vw-soft: var(--brand-muted, rgba(245,158,11,.12));
    font-family: var(--font-body, "DM Sans", sans-serif);
}
.vw-hub .dash-title{font-family:var(--font-head,"Sora",sans-serif);letter-spacing:-.02em;}
.vw-hub__intro{
    position:relative;overflow:hidden;margin:4px 0 22px;padding:20px 22px;
    border:1px solid var(--border,rgba(0,0,0,.07));border-radius:var(--radius-lg,16px);
    background:
        radial-gradient(520px 180px at 100% 0%,color-mix(in srgb,var(--vw) 18%,transparent),transparent 68%),
        var(--card-bg,#fff);
    box-shadow:var(--shadow,0 4px 24px rgba(0,0,0,.06));
}
.vw-hub__intro::before{
    content:"";position:absolute;left:0;top:0;bottom:0;width:4px;
    background:linear-gradient(180deg,var(--vw),var(--vw-dark));
}
.vw-hub__intro-row{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:16px;}
.vw-hub__eyebrow{
    display:inline-flex;align-items:center;gap:8px;
    font-family:var(--font-head,"Sora",sans-serif);font-size:11px;font-weight:700;
    letter-spacing:.12em;text-transform:uppercase;color:var(--vw-dark);
    background:var(--vw-soft);padding:6px 11px;border-radius:8px;
}
.vw-hub__intro h2{
    margin:12px 0 6px;font-family:var(--font-head,"Sora",sans-serif);
    font-size:clamp(22px,3vw,28px);line-height:1.15;font-weight:800;letter-spacing:-.03em;color:var(--text,#09090b);
}
.vw-hub__intro p{margin:0;max-width:520px;font-size:14px;line-height:1.55;color:var(--text-2,#52525b);}
.vw-hub .wallet-card{
    border-color:color-mix(in srgb,var(--vw) 38%,transparent);
    box-shadow:0 16px 40px rgba(28,25,23,.22),inset 0 1px 0 rgba(255,255,255,.06);
    background:linear-gradient(135deg,#1c1917 0%,#292524 42%,color-mix(in srgb,var(--vw) 42%,#431407) 100%);
}
.vw-hub .vw-stat{
    position:relative;overflow:hidden;
    background:
        radial-gradient(300px 140px at 100% 0%,color-mix(in srgb,var(--vw) 12%,transparent),transparent 62%),
        var(--card-bg,#fff);
    border:1px solid color-mix(in srgb,var(--vw) 14%,var(--border,rgba(0,0,0,.07)));
    transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease;
}
.vw-hub .vw-stat:hover{
    transform:translateY(-2px);
    border-color:color-mix(in srgb,var(--vw) 35%,transparent);
    box-shadow:0 12px 28px color-mix(in srgb,var(--vw) 14%,transparent);
}
.vw-hub .vw-stat .stat-card-icon{
    background:var(--vw-soft);border-radius:10px;width:40px;height:40px;
    display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:10px;
}
.vw-hub__callout{
    display:flex;gap:12px;align-items:flex-start;margin-bottom:24px;padding:14px 16px;
    border-radius:var(--radius,12px);border:1px solid color-mix(in srgb,var(--vw) 22%,var(--border,rgba(0,0,0,.08)));
    background:linear-gradient(90deg,var(--vw-soft),transparent 55%);
}
.vw-hub__panel .vw-hub__panel-head{
    display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;padding-bottom:12px;
    border-bottom:1px solid var(--border,rgba(0,0,0,.07));
}
.vw-hub__panel .vw-hub__panel-head h3{
    margin:0;font-family:var(--font-head,"Sora",sans-serif);font-size:16px;font-weight:800;letter-spacing:-.02em;
}
</style>
@endpush

@section('content')
<div class="dash-layout vw-hub">

    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">My Wallet</div>
            <div class="dash-subtitle">Top up balance and track session payments.</div>
        </div>

        <div class="vw-hub__intro">
            <div class="vw-hub__intro-row">
                <div>
                    <span class="vw-hub__eyebrow">Wallet</span>
                    <h2>Ready for your next session</h2>
                    <p>Top up securely with Razorpay, pay mentors per minute, and download invoices from your ledger.</p>
                </div>
                <button type="button" class="btn btn-primary" onclick="openModal('topup-modal')">Add Money →</button>
            </div>
        </div>

        <div class="dash-stats-grid">
            <div class="wallet-card">
                <div style="position:relative;z-index:1;">
                    <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Available Balance</div>
                    <div class="wallet-balance-value" id="wallet-balance-display">
                        ₹{{ number_format($stats['balance'] ?? auth()->user()->wallet_balance ?? 0, 2) }}
                    </div>
                    <button type="button" class="btn btn-primary" style="margin-top:14px;" onclick="openModal('topup-modal')">Add Money →</button>
                </div>
            </div>
            <div class="card vw-stat">
                <div class="stat-card-icon">📉</div>
                <div class="stat-card-label">Total Spent</div>
                <div class="stat-card-value">₹{{ number_format($stats['spent'] ?? 0, 0) }}</div>
                <div class="stat-card-delta">On sessions & bookings</div>
            </div>
            <div class="card vw-stat">
                <div class="stat-card-icon">↩️</div>
                <div class="stat-card-label">Refunded</div>
                <div class="stat-card-value" style="color:var(--success);">₹{{ number_format($stats['refunded'] ?? 0, 0) }}</div>
                <div class="stat-card-delta">From cancelled sessions</div>
            </div>
        </div>

        <div class="vw-hub__callout" role="note">
            <span class="alert-icon" aria-hidden="true">ℹ️</span>
            <div style="font-size:13px;line-height:1.5;color:var(--text-2);">
                Session fees are deducted from your wallet at the mentor’s per-minute rate.
                Top-ups are secured via Razorpay. Minimum top-up is ₹100.
            </div>
        </div>

        <div class="card vw-hub__panel">
            <div class="vw-hub__panel-head">
                <h3>Transaction History</h3>
            </div>
            <div class="table-scroll">
            <table class="data-table mentee-wallet-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Details</th>
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                    @php
                        $isCredit = in_array($txn->type, ['credit', 'refund', 'transfer_in'], true);
                        $amountColor = $isCredit ? 'var(--success)' : 'var(--error)';
                        $amountPrefix = $isCredit ? '+' : '-';
                        $session = $txn->transactionable instanceof \App\Models\ConsultationSession
                            ? $txn->transactionable
                            : null;
                        $invoice = $txn->resolved_invoice
                            ?? $session?->sessionInvoice;
                        $meta = is_array($txn->meta) ? $txn->meta : [];
                        $bookingRef = $meta['booking_ref']
                            ?? $session?->booking_ref
                            ?? $invoice?->booking_ref
                            ?? (str_starts_with((string) $txn->reference, 'WAL-')
                                ? substr((string) $txn->reference, 4)
                                : null);
                        $sessionTitle = $invoice
                            ? $invoice->sessionTitle()
                            : ($session?->title ?: null);
                        $mentorName = $session?->mentor?->name;
                        $category = $txn->category;
                        $detailTitle = $txn->description ?: $txn->type_label;
                        if ($sessionTitle) {
                            $detailTitle = $sessionTitle;
                        } elseif ($category === 'topup') {
                            $detailTitle = 'Wallet top-up';
                        } elseif ($category === 'bonus') {
                            $detailTitle = $txn->description ?: 'Welcome bonus';
                        }
                        $referenceLabel = $invoice?->invoice_number
                            ?? ($bookingRef ?: null)
                            ?? ($txn->reference ?: '—');
                        $referenceSub = null;
                        if ($invoice?->invoice_number && $bookingRef) {
                            $referenceSub = $bookingRef;
                        } elseif ($category === 'topup' && $txn->reference) {
                            $referenceSub = 'Payment ID';
                        } elseif ($session) {
                            $referenceSub = 'Session #'.$session->id;
                        }
                    @endphp
                    <tr>
                        <td style="font-size:12px;white-space:nowrap;">{{ $txn->created_at?->format('d M Y, h:i A') ?? '—' }}</td>
                        <td style="min-width:160px;max-width:260px;">
                            <div style="font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $detailTitle }}">{{ $detailTitle }}</div>
                            @if($mentorName)
                                <div style="font-size:12px;color:var(--text-2);margin-top:2px;">with {{ $mentorName }}</div>
                            @elseif($txn->description && $detailTitle !== $txn->description)
                                <div style="font-size:12px;color:var(--text-2);margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $txn->description }}</div>
                            @endif
                        </td>
                        <td style="font-size:12px;">
                            <div style="font-family:ui-monospace,monospace;white-space:nowrap;">{{ $referenceLabel }}</div>
                            @if($referenceSub)
                                <div style="font-size:11px;color:var(--text-3);margin-top:2px;white-space:nowrap;">{{ $referenceSub }}</div>
                            @endif
                        </td>
                        <td><span class="session-status {{ $txn->type_badge_color === 'success' ? 'completed' : ($txn->type_badge_color === 'danger' ? 'cancelled' : 'pending') }}">{{ $txn->type_label }}</span></td>
                        <td style="font-weight:700;color:{{ $amountColor }};white-space:nowrap;">{{ $amountPrefix }}₹{{ number_format((float) $txn->amount, 2) }}</td>
                        <td style="white-space:nowrap;">₹{{ number_format((float) ($txn->balance_after ?? 0), 2) }}</td>
                        <td><span class="session-status {{ $txn->status }}">{{ ucfirst($txn->status ?? 'completed') }}</span></td>
                        <td>
                            @if($invoice)
                                <a href="{{ route('mentee.session-invoices.download', $invoice) }}" class="btn btn-outline btn-sm" style="white-space:nowrap;">Download</a>
                            @else
                                <span style="font-size:12px;color:var(--text-3);">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px 16px;">
                            <div style="font-size:15px;font-weight:700;margin-bottom:6px;">No transactions yet</div>
                            <div style="font-size:13px;color:var(--text-2);margin-bottom:14px;">Top up your wallet to book mentor sessions.</div>
                            <button type="button" class="btn btn-primary btn-sm" onclick="openModal('topup-modal')">Add Money</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            @if($transactions->hasPages())
            @include('frontend.partials.pagination', ['paginator' => $transactions])
            @endif
        </div>
    </div>
</div>

{{-- Top-up modal --}}
<div class="modal-overlay" id="topup-modal">
    <div class="modal" style="max-width:420px;">
        <div class="modal-header">
            <h3>Add Money to Wallet</h3>
            <button type="button" class="modal-close" onclick="closeModal('topup-modal')">✕</button>
        </div>
        <div class="modal-body">
            <p style="font-size:13px;color:var(--text-2);margin:0 0 14px;">Secure payment powered by Razorpay.</p>
            <div class="form-group">
                <label class="form-label">Amount (₹)</label>
                <input type="number" id="topup-amount" class="form-input" placeholder="Minimum ₹100" min="100" max="100000" step="1" value="500">
                <div class="form-hint">Min ₹100 · Max ₹1,00,000</div>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:4px;">
                @foreach([200, 500, 1000, 2000, 5000] as $quick)
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('topup-amount').value={{ $quick }}">₹{{ number_format($quick) }}</button>
                @endforeach
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="closeModal('topup-modal')">Cancel</button>
            <button type="button" class="btn btn-primary" style="flex:1;" id="topup-pay-btn" onclick="startWalletTopup()">Pay Securely →</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function startWalletTopup() {
    const amount = parseInt(document.getElementById('topup-amount').value, 10);
    if (!amount || amount < 100) {
        showToast('error', 'Enter at least ₹100.');
        return;
    }
    if (amount > 100000) {
        showToast('error', 'Maximum top-up is ₹1,00,000.');
        return;
    }

    const btn = document.getElementById('topup-pay-btn');
    AjaxPost('{{ route('mentee.wallet.topup') }}', { amount }, {
        btn,
        loader: true,
        onSuccess: (order) => {
            const options = {
                key: order.key,
                amount: order.amount,
                currency: order.currency || 'INR',
                name: order.name || 'Vedrix',
                description: order.description || 'Wallet Top-up',
                order_id: order.order_id,
                prefill: order.prefill || {},
                theme: { color: '#f59e0b' },
                handler: function (response) {
                    AjaxPost('{{ route('mentee.wallet.topup.verify') }}', {
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_signature: response.razorpay_signature,
                        amount: amount,
                    }, {
                        loader: true,
                        onSuccess: (data) => {
                            showToast('success', data.message || 'Wallet topped up!');
                            closeModal('topup-modal');
                            if (data.balance != null) {
                                document.getElementById('wallet-balance-display').textContent =
                                    '₹' + Number(data.balance).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                            setTimeout(() => location.reload(), 1000);
                        },
                        onError: (err) => showToast('error', err.message || 'Could not verify payment.'),
                    });
                },
            };
            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function () {
                showToast('error', 'Payment failed. Please try again.');
            });
            rzp.open();
        },
        onError: (err) => showToast('error', err.message || 'Could not start payment.'),
    });
}
</script>
@endpush
