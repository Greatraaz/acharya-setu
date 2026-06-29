{{-- resources/views/frontend/mentor/wallet.blade.php --}}
@extends('frontend.layouts.app')
@section('title', 'Earnings — AcharyaSetu Mentor')

@section('content')
<div class="dash-layout">
    <aside class="sidebar">
        <div class="sidebar-section-label">Overview</div>
        <a href="{{ route('mentor.dashboard') }}" class="sidebar-item"><span class="si-icon">📊</span> Dashboard</a>
        <div class="sidebar-section-label">Sessions</div>
        <a href="{{ route('mentor.sessions') }}" class="sidebar-item"><span class="si-icon">📅</span> My Sessions</a>
        <div class="sidebar-section-label">Account</div>
        <a href="{{ route('mentor.wallet') }}" class="sidebar-item active"><span class="si-icon">💰</span> Earnings</a>
        <a href="{{ route('mentor.profile.edit') }}" class="sidebar-item"><span class="si-icon">✏️</span> Edit Profile</a>
        <form action="{{ route('logout') }}" method="POST" style="margin-top:auto;">@csrf<button class="sidebar-item w-full" style="background:none;cursor:pointer;color:var(--error);"><span class="si-icon">🚪</span> Sign Out</button></form>
    </aside>

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Earnings & Payouts</div>
            <div class="dash-subtitle">Your complete earnings history and withdrawal options.</div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
            <div class="wallet-card">
                <div style="position:relative;z-index:1;">
                    <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Available Balance</div>
                    <div style="font-size:36px;font-weight:800;font-family:var(--font-head);color:white;">₹{{ number_format(auth()->user()->wallet_balance ?? 0, 2) }}</div>
                    <button class="btn btn-primary" style="margin-top:14px;" onclick="openWithdrawModal()">Withdraw →</button>
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

        {{-- Rate info --}}
        <div class="alert alert-info" style="margin-bottom:24px;">
            <span class="alert-icon">ℹ️</span>
            <div style="font-size:13px;">
                <strong>Your rate: ₹{{ auth()->user()->rate_per_minute ?? 0 }}/min</strong> ·
                AcharyaSetu retains a <strong>20% platform fee</strong>. You receive ₹{{ number_format((auth()->user()->rate_per_minute ?? 0) * 0.8, 1) }}/min.
                <a href="{{ route('mentor.profile.edit') }}" style="color:var(--brand);margin-left:8px;">Change rate →</a>
            </div>
        </div>

        {{-- Transaction Table --}}
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="font-size:15px;font-weight:700;">Earnings History</h3>
                <a href="{{ route('mentor.wallet.export') ?? '#' }}" class="btn btn-outline btn-sm">⬇ Export CSV</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
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
                    @forelse($transactions ?? [] as $txn)
                    <tr>
                        <td style="font-weight:600;">{{ $txn->session->title ?? 'Session' }}</td>
                        <td>{{ $txn->session->mentee->name ?? '—' }}</td>
                        <td style="font-size:12px;white-space:nowrap;">{{ $txn->created_at->format('d M Y') }}</td>
                        <td>{{ $txn->session->duration_minutes ?? 0 }} min</td>
                        <td>₹{{ number_format($txn->gross_amount ?? 0, 0) }}</td>
                        <td style="color:var(--text-3);">-₹{{ number_format($txn->platform_fee ?? 0, 0) }}</td>
                        <td style="color:var(--success);font-weight:700;">₹{{ number_format($txn->net_amount ?? 0, 0) }}</td>
                        <td><span class="session-status {{ $txn->status }}">{{ ucfirst($txn->status) }}</span></td>
                    </tr>
                    @empty
                    @foreach([
                        ['Product Strategy Deep Dive','Rahul S.','2 Jan 2025','60','900','180','720','completed'],
                        ['DSA Interview Prep','Priya M.','28 Dec 2024','45','675','135','540','completed'],
                        ['Career Transition Planning','Arjun K.','20 Dec 2024','30','450','90','360','completed'],
                    ] as [$title,$mentee,$date,$dur,$gross,$fee,$net,$status])
                    <tr>
                        <td style="font-weight:600;">{{ $title }}</td>
                        <td>{{ $mentee }}</td>
                        <td style="font-size:12px;">{{ $date }}</td>
                        <td>{{ $dur }} min</td>
                        <td>₹{{ $gross }}</td>
                        <td style="color:var(--text-3);">-₹{{ $fee }}</td>
                        <td style="color:var(--success);font-weight:700;">₹{{ $net }}</td>
                        <td><span class="session-status {{ $status }}">{{ ucfirst($status) }}</span></td>
                    </tr>
                    @endforeach
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Withdraw Modal --}}
<div id="withdraw-modal" class="modal-overlay" style="display:none;">
    <div class="modal">
        <div class="modal-title">Withdraw Earnings</div>
        <div class="modal-sub">Funds will be transferred within 2–3 business days.</div>
        <form action="{{ route('mentor.wallet.withdraw') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Amount to Withdraw (₹)</label>
                <input type="number" name="amount" class="form-input" placeholder="Minimum ₹500" min="500" max="{{ auth()->user()->wallet_balance ?? 0 }}" required>
                <div class="form-hint">Available: ₹{{ number_format(auth()->user()->wallet_balance ?? 0, 0) }}</div>
            </div>
            <div class="form-group">
                <label class="form-label">Bank Account / UPI ID</label>
                <input type="text" name="bank_details" class="form-input" placeholder="UPI: yourname@bank or account number" value="{{ auth()->user()->bank_details ?? '' }}" required>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('withdraw-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;">Request Withdrawal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openWithdrawModal() {
    document.getElementById('withdraw-modal').style.display = 'flex';
}
document.getElementById('withdraw-modal')?.addEventListener('click', function(e) {
    if(e.target === this) this.style.display = 'none';
});
</script>
@endpush
@endsection