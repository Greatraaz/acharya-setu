@extends('frontend.layouts.app')
@section('title', 'Earnings — Vedrix Mentor')

@section('content')
@php
    $feeRate = $platformFeeRate ?? 0.20;
    $netRate = 1 - $feeRate;
@endphp
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Earnings & Payouts</div>
            <div class="dash-subtitle">Your complete earnings history and withdrawal options.</div>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
        @endif

        @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:16px;">
            <ul style="margin:0;padding-left:16px;font-size:13px;">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="dash-stats-grid">
            <div class="wallet-card">
                <div style="position:relative;z-index:1;">
                    <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Available to Withdraw</div>
                    <div class="wallet-balance-value">₹{{ number_format($stats['available'] ?? $stats['balance'] ?? auth()->user()->wallet_balance ?? 0, 2) }}</div>
                    @if(($stats['pending_hold'] ?? 0) > 0)
                    <div style="font-size:12px;color:rgba(255,255,255,.65);margin-top:4px;">
                        Wallet ₹{{ number_format($stats['balance'] ?? 0, 0) }} · Pending hold ₹{{ number_format($stats['pending_hold'], 0) }}
                    </div>
                    @endif
                    <button type="button" class="btn btn-primary" style="margin-top:14px;" onclick="openModal('withdraw-modal')">Withdraw →</button>
                </div>
            </div>
            <div class="card">
                <div class="stat-card-icon">📅</div>
                <div class="stat-card-label">This Month</div>
                <div class="stat-card-value" style="color:var(--success);">₹{{ number_format($stats['this_month_earnings'] ?? 0, 0) }}</div>
                <div class="stat-card-delta">{{ $stats['this_month_sessions'] ?? 0 }} sessions</div>
            </div>
            <div class="card">
                <div class="stat-card-icon">💵</div>
                <div class="stat-card-label">Total Earned (Lifetime)</div>
                <div class="stat-card-value">₹{{ number_format($stats['total_earnings'] ?? 0, 0) }}</div>
                <div class="stat-card-delta">Platform fee deducted</div>
            </div>
        </div>

        <div class="alert alert-info" style="margin-bottom:24px;">
            <span class="alert-icon">ℹ️</span>
            <div style="font-size:13px;">
                <strong>Your rate: ₹{{ number_format(auth()->user()->rate_per_minute ?? 0, 2) }}/min</strong> ·
                Vedrix retains a <strong>{{ (int) ($feeRate * 100) }}% platform fee</strong>.
                You receive ₹{{ number_format((auth()->user()->rate_per_minute ?? 0) * $netRate, 1) }}/min.
                <a href="{{ route('mentor.profile.edit') }}" style="color:var(--brand);margin-left:8px;">Change rate →</a>
            </div>
        </div>

        <div class="card" style="margin-bottom:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="font-size:15px;font-weight:700;">Withdrawal Requests</h3>
            </div>
            <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Requested</th>
                        <th>Amount</th>
                        <th>Payout To</th>
                        <th>Status</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $wd)
                    <tr>
                        <td style="font-size:12px;white-space:nowrap;">{{ $wd->created_at?->format('d M Y, h:i A') ?? '—' }}</td>
                        <td style="font-weight:700;">₹{{ number_format((float) $wd->amount, 0) }}</td>
                        <td style="font-size:12px;">{{ $wd->bank_details }}</td>
                        <td>
                            @php
                                $statusClass = match($wd->status) {
                                    'paid' => 'completed',
                                    'rejected' => 'cancelled',
                                    default => 'pending',
                                };
                            @endphp
                            <span class="session-status {{ $statusClass }}">{{ $wd->status_label }}</span>
                            @if($wd->processed_at)
                            <div style="font-size:11px;color:var(--text-3);margin-top:4px;">{{ $wd->processed_at->format('d M Y') }}</div>
                            @endif
                        </td>
                        <td style="font-size:12px;color:var(--text-2);">{{ $wd->admin_note ?: '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:28px 16px;">
                            <div style="font-size:14px;font-weight:700;margin-bottom:4px;">No withdrawals yet</div>
                            <div style="font-size:13px;color:var(--text-2);">Your withdrawal requests and status will show here.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            @if($withdrawals->hasPages())
            @include('frontend.partials.pagination', ['paginator' => $withdrawals])
            @endif
        </div>

        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="font-size:15px;font-weight:700;">Earnings History</h3>
            </div>
            <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Session</th>
                        <th>Mentee</th>
                        <th>Date</th>
                        <th>Duration</th>
                        <th>Gross</th>
                        <th>Platform Fee</th>
                        <th style="color:var(--success);">You Earned</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                    @php
                        $session = $txn->transactionable instanceof \App\Models\ConsultationSession
                            ? $txn->transactionable
                            : null;
                        $invoice = $session?->sessionInvoice;
                        $meta = is_array($txn->meta) ? $txn->meta : [];
                        $breakdown = \App\Support\SessionPayoutBreakdown::fromMeta(
                            $meta,
                            $invoice?->total_amount !== null ? (float) $invoice->total_amount : ($session?->amount !== null ? (float) $session->amount : null),
                            (float) $txn->amount
                        );
                        $duration = $meta['duration_minutes']
                            ?? $invoice?->duration_minutes
                            ?? $session?->duration_minutes
                            ?? (isset($session?->actual_duration_seconds) ? (int) ceil($session->actual_duration_seconds / 60) : null);
                        $title = $meta['session_title']
                            ?? $invoice?->sessionTitle()
                            ?? $session?->title
                            ?? $txn->description
                            ?? $txn->type_label;
                        $invoiceNumber = $meta['invoice_number']
                            ?? $invoice?->invoice_number
                            ?? '—';
                        $menteeName = $session?->mentee?->name
                            ?? ($meta['mentee_name'] ?? '—');
                        $earnedDate = $invoice?->session_at
                            ?? $session?->scheduled_at
                            ?? $txn->created_at;
                    @endphp
                    <tr>
                        <td style="font-size:12px;font-family:ui-monospace,monospace;white-space:nowrap;">{{ $invoiceNumber }}</td>
                        <td style="font-weight:600;max-width:220px;">
                            <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $title }}">{{ $title }}</div>
                            @if(!empty($meta['booking_ref']) || $session?->booking_ref)
                            <div style="font-size:11px;color:var(--text-3);margin-top:2px;">{{ $meta['booking_ref'] ?? $session?->booking_ref }}</div>
                            @endif
                        </td>
                        <td>{{ $menteeName }}</td>
                        <td style="font-size:12px;white-space:nowrap;">{{ $earnedDate?->format('d M Y') ?? '—' }}</td>
                        <td>{{ $duration ? $duration.' min' : '—' }}</td>
                        <td>₹{{ number_format($breakdown['gross'], 2) }}</td>
                        <td style="color:var(--text-3);">-₹{{ number_format($breakdown['platform_fee'], 2) }}</td>
                        <td style="color:var(--success);font-weight:700;">₹{{ number_format($breakdown['net'], 2) }}</td>
                        <td><span class="session-status {{ $txn->status }}">{{ ucfirst($txn->status) }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:40px 16px;">
                            <div style="font-size:15px;font-weight:700;margin-bottom:6px;">No earnings yet</div>
                            <div style="font-size:13px;color:var(--text-2);">Completed session payouts will appear here.</div>
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

<div class="modal-overlay" id="withdraw-modal">
    <div class="modal" style="max-width:440px;">
        <div class="modal-header">
            <h3>Withdraw Earnings</h3>
            <button type="button" class="modal-close" onclick="closeModal('withdraw-modal')">✕</button>
        </div>
        <form action="{{ route('mentor.wallet.withdraw') }}" method="POST">
            @csrf
            <div class="modal-body">
                <p style="font-size:13px;color:var(--text-2);margin:0 0 14px;">Funds will be transferred within 2–3 business days.</p>
                <div class="form-group">
                    <label class="form-label">Amount to Withdraw (₹)</label>
                    <input type="number" name="amount" class="form-input" placeholder="Minimum ₹500" min="500" max="{{ $stats['available'] ?? auth()->user()->wallet_balance ?? 0 }}" step="1" required>
                    <div class="form-hint">Available: ₹{{ number_format($stats['available'] ?? auth()->user()->wallet_balance ?? 0, 0) }}</div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Bank Account / UPI ID</label>
                    <input type="text" name="bank_details" class="form-input" placeholder="UPI: yourname@bank or account number" value="{{ auth()->user()->bank_details ?? '' }}" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('withdraw-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;">Request Withdrawal</button>
            </div>
        </form>
    </div>
</div>
@endsection
