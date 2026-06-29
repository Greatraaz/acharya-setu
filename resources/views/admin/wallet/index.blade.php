@extends('admin.layouts.app')
@section('content')

<div class="p-6">

    {{-- Page Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h6 class="text-lg font-semibold text-gray-800">Wallet Transactions</h6>
            <p class="text-sm text-gray-500 mt-0.5">Manage and monitor all wallet activity</p>
        </div>
        <nav class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-1 text-gray-500 hover:text-blue-600 transition-colors">
                <iconify-icon icon="solar:home-smile-angle-outline" class="text-base"></iconify-icon>
                Dashboard
            </a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-700 font-medium">Wallet Transactions</span>
        </nav>
    </div>

    {{-- Summary Cards --}}
    @php
        $allTxns       = $transactions->getCollection();
        $totalCredit   = $allTxns->whereIn('type', ['credit','refund','transfer_in'])->sum('amount');
        $totalDebit    = $allTxns->whereIn('type', ['debit','transfer_out'])->sum('amount');
        $totalRefund   = $allTxns->where('type', 'refund')->sum('amount');
        $totalTransfer = $allTxns->whereIn('type', ['transfer_in','transfer_out'])->count();
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

        {{-- Total Credited --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Credited</p>
                <p class="text-2xl font-bold text-emerald-600">₹{{ number_format($totalCredit, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="fa-solid:plus-circle" class="text-emerald-600 text-xl"></iconify-icon>
            </div>
        </div>

        {{-- Total Debited --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Debited</p>
                <p class="text-2xl font-bold text-red-500">₹{{ number_format($totalDebit, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="fa-solid:minus-circle" class="text-red-500 text-xl"></iconify-icon>
            </div>
        </div>

        {{-- Total Refunded --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Refunded</p>
                <p class="text-2xl font-bold text-sky-500">₹{{ number_format($totalRefund, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-sky-100 flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="fa-solid:undo" class="text-sky-500 text-xl"></iconify-icon>
            </div>
        </div>

        {{-- Total Transfers --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Transfers</p>
                <p class="text-2xl font-bold text-violet-600">{{ $totalTransfer }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-violet-100 flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="fa-solid:exchange-alt" class="text-violet-600 text-xl"></iconify-icon>
            </div>
        </div>

    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('admin.wallet.index') }}">
            <div class="flex flex-wrap items-end gap-3">

                <div class="flex flex-col gap-1 min-w-[140px]">
                    <label class="text-xs font-medium text-gray-600">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}"
                        class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="flex flex-col gap-1 min-w-[140px]">
                    <label class="text-xs font-medium text-gray-600">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}"
                        class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="flex flex-col gap-1 min-w-[150px]">
                    <label class="text-xs font-medium text-gray-600">Type</label>
                    <select name="type"
                        class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                        <option value="">All Types</option>
                        @foreach(['credit'=>'Credit','debit'=>'Debit','refund'=>'Refund','transfer_in'=>'Transfer In','transfer_out'=>'Transfer Out'] as $val => $label)
                            <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1 flex-1 min-w-[180px]">
                    <label class="text-xs font-medium text-gray-600">Search Reference</label>
                    <div class="relative">
                        <iconify-icon icon="fa-solid:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></iconify-icon>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="TXN-XXXX / TRF-XXXX"
                            class="w-full text-sm border border-gray-200 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <div class="flex items-end gap-2 pb-0">
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        <iconify-icon icon="fa-solid:filter"></iconify-icon> Filter
                    </button>
                    <a href="{{ route('admin.wallet.index') }}"
                        class="inline-flex items-center gap-1.5 border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        <iconify-icon icon="fa-solid:times"></iconify-icon> Reset
                    </a>
                    <button type="button" onclick="document.getElementById('transferModal').classList.remove('hidden')"
                        class="inline-flex items-center gap-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        <iconify-icon icon="fa-solid:exchange-alt"></iconify-icon> Transfer
                    </button>
                </div>

            </div>
        </form>
    </div>

    {{-- Transactions Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Table Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h6 class="font-semibold text-gray-800">All Transactions</h6>
            <span class="text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full">
                {{ $transactions->total() }} records
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Date & Time</th>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Reference</th>
                        <th class="px-4 py-3 text-left">Transaction Type</th>
                        <th class="px-4 py-3 text-left">Description</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-right">Bal. Before</th>
                        <th class="px-4 py-3 text-right">Bal. After</th>
                        <th class="px-4 py-3 text-left">Performed By</th>
                        <th class="px-4 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($transactions as $index => $txn)
                    <tr class="hover:bg-gray-50/60 transition-colors">

                        {{-- # --}}
                        <td class="px-4 py-3 text-gray-400 text-xs">
                            {{ $transactions->firstItem() + $index }}
                        </td>

                        {{-- Date --}}
                        <td class="px-4 py-3 text-nowrap">
                            <p class="text-gray-700 font-medium">{{ $txn->created_at->format('d M Y') }}</p>
                            <p class="text-gray-400 text-xs">{{ $txn->created_at->format('h:i A') }}</p>
                        </td>

                        {{-- User --}}
                        <td class="px-4 py-3">
                            @if($txn->walletable)
                                @php
                                    $isCustomer = $txn->walletable_type === \App\Models\Customer::class;
                                    $routeName  = $isCustomer ? 'admin.wallet.customer.show' : 'admin.wallet.admin.show';
                                @endphp
                                <a href="{{ route($routeName, $txn->walletable->id) }}"
                                    class="font-medium text-gray-800 hover:text-blue-600 transition-colors">
                                    {{ $txn->walletable->name }}
                                </a>
                                <p class="text-xs text-gray-400">{{ $txn->walletable->email ?? '' }}</p>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        {{-- User Type Badge --}}
                        <td class="px-4 py-3">
                            @if($txn->walletable_type === \App\Models\Customer::class)
                                <span class="inline-flex items-center gap-1 text-xs font-medium bg-cyan-100 text-cyan-700 px-2 py-0.5 rounded-full">
                                    <iconify-icon icon="fa-solid:user"></iconify-icon> Customer
                                </span>
                            @elseif($txn->walletable_type === \App\Models\Admin::class)
                                <span class="inline-flex items-center gap-1 text-xs font-medium bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full">
                                    <iconify-icon icon="fa-solid:user-shield"></iconify-icon> Admin
                                </span>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Reference --}}
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
                                {{ $txn->reference ?? '—' }}
                            </span>
                            @if($txn->transfer_pair_id)
                                <p class="text-xs text-gray-400 mt-0.5">Pair #{{ $txn->transfer_pair_id }}</p>
                            @endif
                        </td>

                        {{-- Transaction Type --}}
                        <td class="px-4 py-3">
                            @php
                                $typeStyles = [
                                    'credit'       => 'bg-emerald-100 text-emerald-700',
                                    'debit'        => 'bg-red-100 text-red-600',
                                    'refund'       => 'bg-sky-100 text-sky-600',
                                    'transfer_in'  => 'bg-blue-100 text-blue-700',
                                    'transfer_out' => 'bg-amber-100 text-amber-700',
                                ];
                                $typeIcons = [
                                    'credit'       => 'fa-solid:arrow-down',
                                    'debit'        => 'fa-solid:arrow-up',
                                    'refund'       => 'fa-solid:undo',
                                    'transfer_in'  => 'fa-solid:arrow-circle-down',
                                    'transfer_out' => 'fa-solid:arrow-circle-up',
                                ];
                            @endphp
                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full {{ $typeStyles[$txn->type] ?? 'bg-gray-100 text-gray-600' }}">
                                <iconify-icon icon="{{ $typeIcons[$txn->type] ?? 'fa-solid:circle' }}"></iconify-icon>
                                {{ $txn->type_label }}
                            </span>
                        </td>

                        {{-- Description --}}
                        <td class="px-4 py-3 text-gray-600 max-w-[160px]">
                            <p class="truncate text-xs" title="{{ $txn->description }}">
                                {{ $txn->description ?? '—' }}
                            </p>
                        </td>

                        {{-- Amount --}}
                        <td class="px-4 py-3 text-right font-semibold {{ $txn->is_debit ? 'text-red-500' : 'text-emerald-600' }}">
                            {{ $txn->is_debit ? '−' : '+' }}₹{{ number_format($txn->amount, 2) }}
                        </td>

                        {{-- Balance Before --}}
                        <td class="px-4 py-3 text-right text-gray-400 text-xs">
                            ₹{{ number_format($txn->balance_before, 2) }}
                        </td>

                        {{-- Balance After --}}
                        <td class="px-4 py-3 text-right font-medium text-gray-700">
                            ₹{{ number_format($txn->balance_after, 2) }}
                        </td>

                        {{-- Performed By --}}
                        <td class="px-4 py-3">
                            @if($txn->performedByAdmin)
                                <span class="text-xs text-gray-700 font-medium">{{ $txn->performedByAdmin->name }}</span>
                            @else
                                <span class="text-xs text-gray-400 italic">System</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-3">
                            @php
                                $statusStyles = [
                                    'completed' => 'bg-emerald-100 text-emerald-700',
                                    'pending'   => 'bg-amber-100 text-amber-700',
                                    'failed'    => 'bg-red-100 text-red-600',
                                ];
                            @endphp
                            <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full {{ $statusStyles[$txn->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($txn->status) }}
                            </span>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-16 text-gray-400">
                            <iconify-icon icon="solar:inbox-line-broken" class="text-5xl block mb-3 mx-auto"></iconify-icon>
                            <p class="font-medium text-gray-500">No transactions found</p>
                            <p class="text-xs mt-1">Try adjusting your filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($transactions->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <p class="text-xs text-gray-400">
                Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of {{ $transactions->total() }} transactions
            </p>
            <div class="text-sm">
                {{ $transactions->withQueryString()->links() }}
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Transfer Modal --}}
<div id="transferModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"
         onclick="document.getElementById('transferModal').classList.add('hidden')"></div>

    {{-- Modal Box --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10">
        <form method="POST" action="{{ route('admin.wallet.transfer') }}">
            @csrf

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                        <iconify-icon icon="fa-solid:exchange-alt" class="text-emerald-600 text-sm"></iconify-icon>
                    </div>
                    <h6 class="font-semibold text-gray-800">Transfer Wallet Balance</h6>
                </div>
                <button type="button" onclick="document.getElementById('transferModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <iconify-icon icon="fa-solid:times" class="text-lg"></iconify-icon>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 space-y-5">

                {{-- From --}}
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">From (Sender)</p>
                    <div class="grid grid-cols-5 gap-3">
                        <div class="col-span-2">
                            <label class="text-xs font-medium text-gray-600 mb-1 block">Type</label>
                            <select name="sender_type" id="senderType"
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                <option value="customer">Customer</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-span-3">
                            <label class="text-xs font-medium text-gray-600 mb-1 block">User</label>
                            <select name="sender_id" id="senderId"
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                <option value="">— Select —</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Arrow --}}
                <div class="flex items-center gap-3">
                    <div class="flex-1 border-t border-dashed border-gray-200"></div>
                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                        <iconify-icon icon="fa-solid:arrow-down" class="text-gray-400 text-sm"></iconify-icon>
                    </div>
                    <div class="flex-1 border-t border-dashed border-gray-200"></div>
                </div>

                {{-- To --}}
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">To (Receiver)</p>
                    <div class="grid grid-cols-5 gap-3">
                        <div class="col-span-2">
                            <label class="text-xs font-medium text-gray-600 mb-1 block">Type</label>
                            <select name="receiver_type" id="receiverType"
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                <option value="customer">Customer</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-span-3">
                            <label class="text-xs font-medium text-gray-600 mb-1 block">User</label>
                            <select name="receiver_id" id="receiverId"
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                <option value="">— Select —</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Amount & Note --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-medium text-gray-600 mb-1 block">Amount (₹)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">₹</span>
                            <input type="number" name="amount" step="0.01" min="0.01"
                                class="w-full text-sm border border-gray-200 rounded-lg pl-7 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-600 mb-1 block">Note <span class="text-gray-400">(optional)</span></label>
                        <input type="text" name="note" placeholder="Reason for transfer"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                <button type="button" onclick="document.getElementById('transferModal').classList.add('hidden')"
                    class="text-sm font-medium text-gray-600 hover:text-gray-800 px-4 py-2 rounded-lg border border-gray-200 hover:bg-white transition-colors">
                    Cancel
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                    <iconify-icon icon="fa-solid:paper-plane"></iconify-icon>
                    Send Transfer
                </button>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
const userCache = {};

async function loadUsers(type) {
    if (userCache[type]) return userCache[type];
    const res  = await fetch(`/admin/wallet/users?type=${type}`);
    const data = await res.json();
    userCache[type] = data;
    return data;
}

function populateSelect(selectEl, users) {
    selectEl.innerHTML = '<option value="">— Select —</option>';
    users.forEach(u => {
        const opt = document.createElement('option');
        opt.value       = u.id;
        opt.textContent = `${u.name}  (₹${parseFloat(u.wallet_balance).toFixed(2)})`;
        selectEl.appendChild(opt);
    });
}

['sender', 'receiver'].forEach(role => {
    const typeEl = document.getElementById(`${role}Type`);
    const idEl   = document.getElementById(`${role}Id`);

    // Load on type change
    typeEl.addEventListener('change', async function () {
        const users = await loadUsers(this.value);
        populateSelect(idEl, users);
    });
});

// Load defaults when modal opens
document.getElementById('transferModal').addEventListener('transitionend', async function () {
    if (!this.classList.contains('hidden')) {
        for (const role of ['sender', 'receiver']) {
            const typeEl = document.getElementById(`${role}Type`);
            const idEl   = document.getElementById(`${role}Id`);
            const users  = await loadUsers(typeEl.value);
            populateSelect(idEl, users);
        }
    }
});

// Also load on button click
document.querySelector('[onclick*="transferModal"]').addEventListener('click', async function() {
    await new Promise(r => setTimeout(r, 50)); // let modal render
    for (const role of ['sender', 'receiver']) {
        const typeEl = document.getElementById(`${role}Type`);
        const idEl   = document.getElementById(`${role}Id`);
        const users  = await loadUsers(typeEl.value);
        populateSelect(idEl, users);
    }
});
</script>
@endpush

@endsection