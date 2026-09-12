{{-- Wallet ledger tab --}}
@php $summary = $walletSummary ?? []; @endphp

<div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
    @foreach([
        ['Money In', '₹'.number_format($summary['total_in'] ?? 0, 2), 'text-emerald-600', 'bg-emerald-50'],
        ['Money Out', '₹'.number_format($summary['total_out'] ?? 0, 2), 'text-red-500', 'bg-red-50'],
        ['Refunds', '₹'.number_format($summary['total_refund'] ?? 0, 2), 'text-sky-600', 'bg-sky-50'],
        ['Net', '₹'.number_format($summary['net'] ?? 0, 2), 'text-indigo-600', 'bg-indigo-50'],
        ['Records', number_format($summary['total_count'] ?? 0), 'text-gray-800', 'bg-gray-50'],
    ] as [$label, $value, $color, $bg])
        <div class="bg-white border border-gray-200 rounded-2xl p-4">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">{{ $label }}</p>
            <p class="mt-1 text-lg sm:text-xl font-bold {{ $color }} break-all">{{ $value }}</p>
            <div class="mt-2 h-1.5 rounded-full {{ $bg }}"></div>
        </div>
    @endforeach
</div>

<div class="bg-white border border-gray-200 rounded-2xl p-4">
    <form method="GET" action="{{ route('admin.transactions.index') }}" class="space-y-3">
        <input type="hidden" name="tab" value="wallet">
        <div class="admin-filter-form flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Name, email, ref, description…"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
            </div>
            <div class="min-w-[130px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                <select name="type" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white">
                    <option value="">All</option>
                    @foreach(['credit'=>'Credit','debit'=>'Debit','refund'=>'Refund','transfer_in'=>'Transfer In','transfer_out'=>'Transfer Out'] as $val => $label)
                        <option value="{{ $val }}" @selected(request('type') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                <select name="category" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white">
                    <option value="">All</option>
                    @foreach(\App\Models\WalletTransaction::categoryOptions() as $val => $label)
                        <option value="{{ $val }}" @selected(request('category') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Role</label>
                <select name="role" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white">
                    <option value="">All</option>
                    @foreach(['mentee'=>'Mentee','mentor'=>'Mentor','admin'=>'Admin'] as $val => $label)
                        <option value="{{ $val }}" @selected(request('role') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white">
                    <option value="">All</option>
                    @foreach(['completed','pending','failed'] as $val)
                        <option value="{{ $val }}" @selected(request('status') === $val)>{{ ucfirst($val) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
            </div>
            <div class="min-w-[100px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Min ₹</label>
                <input type="number" step="0.01" name="min_amount" value="{{ request('min_amount') }}"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
            </div>
            <div class="min-w-[100px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Max ₹</label>
                <input type="number" step="0.01" name="max_amount" value="{{ request('max_amount') }}"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-xl">Filter</button>
            <a href="{{ route('admin.transactions.index', ['tab' => 'wallet']) }}"
               class="text-sm text-gray-600 border border-gray-200 px-3 py-2 rounded-xl hover:bg-gray-50">Reset</a>
        </div>
    </form>
</div>

<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
    <div class="flex items-center justify-between px-4 sm:px-5 py-3.5 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-800">Wallet transactions</h3>
        <span class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full">{{ $walletTransactions->total() }} results</span>
    </div>

    {{-- Mobile cards --}}
    <div class="md:hidden divide-y divide-gray-100">
        @forelse($walletTransactions as $txn)
            <a href="{{ route('admin.transactions.show', $txn) }}" class="block p-4 hover:bg-gray-50">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 truncate">{{ $txn->user?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $txn->reference ?? 'No ref' }} · {{ $txn->category_label }}</p>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $txn->description ?? '—' }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="font-semibold {{ $txn->is_debit ? 'text-red-500' : 'text-emerald-600' }}">
                            {{ $txn->is_debit ? '−' : '+' }}₹{{ number_format($txn->amount, 2) }}
                        </p>
                        <p class="text-[11px] text-gray-400 mt-1">{{ $txn->created_at?->format('d M, h:i A') }}</p>
                    </div>
                </div>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $txn->type_label }}</span>
                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ ucfirst($txn->user?->role ?? '—') }}</span>
                    <span class="text-[11px] px-2 py-0.5 rounded-full {{ $txn->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($txn->status) }}</span>
                </div>
            </a>
        @empty
            <div class="py-14 text-center text-gray-400 text-sm">No wallet transactions found.</div>
        @endforelse
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full min-w-[980px] text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">User</th>
                    <th class="px-4 py-3 text-left">Category</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Reference</th>
                    <th class="px-4 py-3 text-left">Description</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                    <th class="px-4 py-3 text-right">Balance</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($walletTransactions as $txn)
                    <tr class="hover:bg-gray-50/70">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <p class="text-gray-800 font-medium">{{ $txn->created_at?->format('d M Y') }}</p>
                            <p class="text-xs text-gray-400">{{ $txn->created_at?->format('h:i A') }}</p>
                        </td>
                        <td class="px-4 py-3 max-w-[180px]">
                            @if($txn->user)
                                <a href="{{ route('admin.wallet.customer.show', $txn->user->id) }}" class="font-medium text-gray-900 hover:text-indigo-600 truncate block">{{ $txn->user->name }}</a>
                                <p class="text-xs text-gray-400 truncate">{{ $txn->user->email }} · {{ ucfirst($txn->user->role) }}</p>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">{{ $txn->category_label }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                {{ $txn->type === 'credit' || $txn->type === 'refund' || $txn->type === 'transfer_in' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                                {{ $txn->type_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $txn->reference ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 max-w-[220px]">
                            <p class="truncate text-xs" title="{{ $txn->description }}">{{ $txn->description ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold whitespace-nowrap {{ $txn->is_debit ? 'text-red-500' : 'text-emerald-600' }}">
                            {{ $txn->is_debit ? '−' : '+' }}₹{{ number_format($txn->amount, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-500 whitespace-nowrap">
                            ₹{{ number_format($txn->balance_before, 2) }} → ₹{{ number_format($txn->balance_after, 2) }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $txn->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($txn->status === 'failed' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700') }}">
                                {{ ucfirst($txn->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.transactions.show', $txn) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="py-16 text-center text-gray-400">No wallet transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($walletTransactions->hasPages())
        <div class="px-4 sm:px-5 py-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <p class="text-xs text-gray-400">Showing {{ $walletTransactions->firstItem() }}–{{ $walletTransactions->lastItem() }} of {{ $walletTransactions->total() }}</p>
            <div>{{ $walletTransactions->links() }}</div>
        </div>
    @endif
</div>
