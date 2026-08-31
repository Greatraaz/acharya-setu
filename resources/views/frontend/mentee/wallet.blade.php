@extends('frontend.layouts.app')
@section('title', 'Wallet — Vedrix')

@section('content')
<div class="dash-layout">

    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between">
            <div>
                <div class="dash-title">My Wallet</div>
                <div class="dash-subtitle">Top up balance and track session payments.</div>
            </div>
            <button type="button" class="btn btn-primary" onclick="openModal('topup-modal')">➕ Add Money</button>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
            <div class="wallet-card">
                <div style="position:relative;z-index:1;">
                    <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Available Balance</div>
                    <div style="font-size:36px;font-weight:800;font-family:var(--font-head);color:white;" id="wallet-balance-display">
                        ₹{{ number_format($stats['balance'] ?? auth()->user()->wallet_balance ?? 0, 2) }}
                    </div>
                    <button type="button" class="btn btn-primary" style="margin-top:14px;" onclick="openModal('topup-modal')">Add Money →</button>
                </div>
            </div>
            <div class="card">
                <div class="stat-card-icon">📉</div>
                <div class="stat-card-label">Total Spent</div>
                <div class="stat-card-value">₹{{ number_format($stats['spent'] ?? 0, 0) }}</div>
                <div class="stat-card-delta">On sessions & bookings</div>
            </div>
            <div class="card">
                <div class="stat-card-icon">↩️</div>
                <div class="stat-card-label">Refunded</div>
                <div class="stat-card-value" style="color:var(--success);">₹{{ number_format($stats['refunded'] ?? 0, 0) }}</div>
                <div class="stat-card-delta">From cancelled sessions</div>
            </div>
        </div>

        <div class="alert alert-info" style="margin-bottom:24px;">
            <span class="alert-icon">ℹ️</span>
            <div style="font-size:13px;">
                Session fees are deducted from your wallet at the mentor’s per-minute rate.
                Top-ups are secured via Razorpay. Minimum top-up is ₹100.
            </div>
        </div>

        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="font-size:15px;font-weight:700;">Transaction History</h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Balance After</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                    @php
                        $isCredit = in_array($txn->type, ['credit', 'refund', 'transfer_in'], true);
                        $amountColor = $isCredit ? 'var(--success)' : 'var(--error)';
                        $amountPrefix = $isCredit ? '+' : '-';
                    @endphp
                    <tr>
                        <td style="font-size:12px;white-space:nowrap;">{{ $txn->created_at?->format('d M Y, h:i A') ?? '—' }}</td>
                        <td style="font-weight:600;">{{ $txn->description ?: $txn->type_label }}</td>
                        <td><span class="session-status {{ $txn->type_badge_color === 'success' ? 'completed' : ($txn->type_badge_color === 'danger' ? 'cancelled' : 'pending') }}">{{ $txn->type_label }}</span></td>
                        <td style="font-weight:700;color:{{ $amountColor }};">{{ $amountPrefix }}₹{{ number_format((float) $txn->amount, 0) }}</td>
                        <td>₹{{ number_format((float) ($txn->balance_after ?? 0), 0) }}</td>
                        <td><span class="session-status {{ $txn->status }}">{{ ucfirst($txn->status ?? 'completed') }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:40px 16px;">
                            <div style="font-size:15px;font-weight:700;margin-bottom:6px;">No transactions yet</div>
                            <div style="font-size:13px;color:var(--text-2);margin-bottom:14px;">Top up your wallet to book mentor sessions.</div>
                            <button type="button" class="btn btn-primary btn-sm" onclick="openModal('topup-modal')">Add Money</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

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
