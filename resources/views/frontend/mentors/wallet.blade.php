@extends('frontend.layouts.app')
@section('title', 'Earnings — Vedrix Mentor')

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
.vw-hub__intro p{margin:0;max-width:540px;font-size:14px;line-height:1.55;color:var(--text-2,#52525b);}
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
.vw-hub__callout a{color:var(--brand);font-weight:600;}
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
@php
    $feeRate = 0.20;
    $netRate = 1 - $feeRate;
@endphp
<div class="dash-layout vw-hub">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Earnings & Payouts</div>
            <div class="dash-subtitle">Your complete earnings history and withdrawal options.</div>
        </div>

        <div class="vw-hub__intro">
            <div class="vw-hub__intro-row">
                <div>
                    <span class="vw-hub__eyebrow">Earnings</span>
                    <h2>Turn sessions into payouts</h2>
                    <p>Track lifetime earnings, request withdrawals to your bank or UPI, and review every invoice in one place.</p>
                </div>
                <button type="button" class="btn btn-primary" onclick="openModal('withdraw-modal')">Withdraw →</button>
            </div>
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
            <div class="card vw-stat">
                <div class="stat-card-icon">📅</div>
                <div class="stat-card-label">This Month</div>
                <div class="stat-card-value" style="color:var(--success);">₹{{ number_format($stats['this_month_earnings'] ?? 0, 0) }}</div>
                <div class="stat-card-delta">{{ $stats['this_month_sessions'] ?? 0 }} sessions</div>
            </div>
            <div class="card vw-stat">
                <div class="stat-card-icon">💵</div>
                <div class="stat-card-label">Total Earned (Lifetime)</div>
                <div class="stat-card-value">₹{{ number_format($stats['total_earnings'] ?? 0, 0) }}</div>
                <div class="stat-card-delta">Platform fee deducted</div>
            </div>
        </div>

        <div class="vw-hub__callout" role="note">
            <span class="alert-icon" aria-hidden="true">ℹ️</span>
            <div style="font-size:13px;line-height:1.5;color:var(--text-2);">
                <strong style="color:var(--text);">Your rate: ₹{{ number_format(auth()->user()->rate_per_minute ?? 0, 2) }}/min</strong> ·
                Vedrix charges a <strong style="color:var(--text);">20% platform fee</strong>.
                You receive ₹{{ number_format((auth()->user()->rate_per_minute ?? 0) * $netRate, 1) }}/min.
                <a href="{{ route('mentor.profile.edit') }}">Change rate →</a>
            </div>
        </div>

        <div class="card vw-hub__panel" style="margin-bottom:24px;">
            <div class="vw-hub__panel-head">
                <h3>Withdrawal Requests</h3>
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

        <div class="card vw-hub__panel">
            <div class="vw-hub__panel-head">
                <h3>Earnings History</h3>
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
                            $session
                                ? (float) $session->listAmount()
                                : ($invoice?->payoutBreakdown()['gross']
                                    ?? ($invoice?->total_amount !== null ? (float) $invoice->total_amount : null)),
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
