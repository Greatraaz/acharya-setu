@extends('admin.layouts.app')
@section('content')
<div class="dashboard-main-body">

    {{-- Summary Cards --}}
    <div class="row gy-4 mb-4">
        @foreach([
            ['label'=>'Current Balance',  'value'=>$summary['balance'],        'color'=>'bg-cyan',         'icon'=>'solar:wallet-bold'],
            ['label'=>'Total Credited',   'value'=>$summary['total_credited'],  'color'=>'bg-success-main', 'icon'=>'fa-solid:plus-circle'],
            ['label'=>'Total Debited',    'value'=>$summary['total_debited'],   'color'=>'bg-red',          'icon'=>'fa-solid:minus-circle'],
            ['label'=>'Total Refunded',   'value'=>$summary['total_refunded'],  'color'=>'bg-info',         'icon'=>'fa-solid:undo'],
        ] as $card)
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-none border h-100">
                <div class="card-body p-20 d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">{{ $card['label'] }}</p>
                        <h5 class="mb-0">₹{{ number_format($card['value'], 2) }}</h5>
                    </div>
                    <div class="w-50-px h-50-px {{ $card['color'] }} rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="{{ $card['icon'] }}" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Adjust Wallet --}}
    <div class="card shadow-none border mb-4">
        <div class="card-header fw-semibold">Adjust Wallet</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.wallet.adjust', [$customer instanceof \App\Models\Admin ? 'admin' : 'customer', $customer->id]) }}" class="row g-3">
                @csrf
                <div class="col-md-2">
                    <label class="form-label">Action</label>
                    <select name="action" class="form-select">
                        <option value="credit">Credit</option>
                        <option value="debit">Debit</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Amount (₹)</label>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Reason</label>
                    <input type="text" name="description" class="form-control" required>
                </div>
                <div class="col-md-3 align-self-end">
                    <button type="submit" class="btn btn-primary w-100">Apply Adjustment</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Transaction History --}}
    <div class="card shadow-none border">
        <div class="card-header fw-semibold">Transaction History</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Balance Before</th>
                            <th>Balance After</th>
                            <th>Performed By</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $txn)
                        <tr>
                            <td class="text-nowrap">{{ $txn->created_at->format('d M Y, h:i A') }}</td>
                            <td><small class="text-muted font-monospace">{{ $txn->reference }}</small></td>
                            <td>
                                <span class="badge bg-{{ $txn->type_badge_color }}">
                                    {{ $txn->type_label }}
                                </span>
                            </td>
                            <td>{{ $txn->description }}</td>
                            <td class="fw-semibold {{ $txn->is_debit ? 'text-danger' : 'text-success' }}">
                                {{ $txn->is_debit ? '−' : '+' }}₹{{ number_format($txn->amount, 2) }}
                            </td>
                            <td class="text-muted">₹{{ number_format($txn->balance_before, 2) }}</td>
                            <td class="fw-medium">₹{{ number_format($txn->balance_after, 2) }}</td>
                            <td>{{ $txn->performedByAdmin?->name ?? 'System' }}</td>
                            <td>
                                <span class="badge bg-{{ match($txn->status) {
                                    'completed' => 'success',
                                    'pending'   => 'warning',
                                    default     => 'danger'
                                } }}">{{ ucfirst($txn->status) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">No transactions yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $transactions->links() }}</div>
        </div>
    </div>
</div>
@endsection