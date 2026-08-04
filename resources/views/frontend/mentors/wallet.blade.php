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

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
            <div class="wallet-card">
                <div style="position:relative;z-index:1;">
                    <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Available to Withdraw</div>
                    <div style="font-size:36px;font-weight:800;font-family:var(--font-head);color:white;">₹{{ number_format($stats['available'] ?? $stats['balance'] ?? auth()->user()->wallet_balance ?? 0, 2) }}</div>
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
            @if($withdrawals->hasPages())
            <div style="margin-top:16px;display:flex;justify-content:center;">{{ $withdrawals->links() }}</div>
            @endif
        </div>

        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="font-size:15px;font-weight:700;">Earnings History</h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Description</th>
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
                        $meta = is_array($txn->meta) ? $txn->meta : [];
                        $net = (float) ($meta['net_amount'] ?? $txn->amount);
                        $gross = isset($meta['gross_amount'])
                            ? (float) $meta['gross_amount']
                            : ($netRate > 0 ? round($net / $netRate, 2) : $net);
                        $fee = isset($meta['platform_fee'])
                            ? (float) $meta['platform_fee']
                            : round($gross - $net, 2);
                        $duration = $meta['duration_minutes']
                            ?? $session?->duration_minutes
                            ?? (isset($session?->actual_duration_seconds) ? (int) ceil($session->actual_duration_seconds / 60) : null);
                        $title = $session?->title
                            ?? $txn->description
                            ?? $txn->type_label;
                        $menteeName = $session?->mentee?->name
                            ?? ($meta['mentee_name'] ?? '—');
                    @endphp
                    <tr>
                        <td style="font-weight:600;">{{ $title }}</td>
                        <td>{{ $menteeName }}</td>
                        <td style="font-size:12px;white-space:nowrap;">{{ $txn->created_at?->format('d M Y') ?? '—' }}</td>
                        <td>{{ $duration ? $duration.' min' : '—' }}</td>
                        <td>₹{{ number_format($gross, 0) }}</td>
                        <td style="color:var(--text-3);">-₹{{ number_format($fee, 0) }}</td>
                        <td style="color:var(--success);font-weight:700;">₹{{ number_format($net, 0) }}</td>
                        <td><span class="session-status {{ $txn->status }}">{{ ucfirst($txn->status) }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px 16px;">
                            <div style="font-size:15px;font-weight:700;margin-bottom:6px;">No earnings yet</div>
                            <div style="font-size:13px;color:var(--text-2);">Completed session payouts will appear here.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($transactions->hasPages())
            <div style="margin-top:16px;display:flex;justify-content:center;">{{ $transactions->links() }}</div>
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
