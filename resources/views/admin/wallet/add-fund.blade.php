@extends('admin.layouts.app')
@section('title', 'Add Fund')
@section('heading', 'Add Fund')
@section('content')

@php
    $selectedId = old('mentee_id', $preselectedMentee->id ?? null);
    $selectedName = old('_mentee_name', $preselectedMentee->name ?? '');
    $selectedEmail = old('_mentee_email', $preselectedMentee->email ?? '');
    $selectedBalance = old('_mentee_balance', $preselectedMentee ? number_format((float) $preselectedMentee->wallet_balance, 2, '.', '') : '');
@endphp

<div class="min-w-0 max-w-full space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h6 class="text-lg font-semibold text-gray-800">Add Fund to Mentee Wallet</h6>
            <p class="text-sm text-gray-500 mt-0.5">
                Credit money into a mentee’s wallet. This creates an audited ledger entry.
            </p>
        </div>
        <nav class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-blue-600 transition-colors">Dashboard</a>
            <span class="text-gray-300">/</span>
            <a href="{{ route('admin.wallet.index') }}" class="text-gray-500 hover:text-blue-600 transition-colors">Wallet</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-700 font-medium">Add Fund</span>
        </nav>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3 rounded-xl">
        <iconify-icon icon="fa-solid:check-circle"></iconify-icon>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">
        <iconify-icon icon="fa-solid:exclamation-circle"></iconify-icon>
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Added Today</p>
                <p class="text-2xl font-bold text-emerald-600">₹{{ number_format($totalAddedToday, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="fa-solid:plus-circle" class="text-emerald-600 text-xl"></iconify-icon>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Recent Credits</p>
                <p class="text-2xl font-bold text-blue-600">{{ $recentFunds->total() }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="fa-solid:history" class="text-blue-600 text-xl"></iconify-icon>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between sm:col-span-2 xl:col-span-1">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Full Ledger</p>
                <a href="{{ route('admin.wallet.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">
                    Open wallet transactions →
                </a>
            </div>
            <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="fa-solid:wallet" class="text-indigo-600 text-xl"></iconify-icon>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

        {{-- Add Fund Form --}}
        <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h6 class="font-semibold text-gray-800">Credit Mentee Wallet</h6>
                <p class="text-xs text-gray-400 mt-0.5">Search a mentee, enter amount and reason</p>
            </div>

            <form method="POST" action="{{ route('admin.wallet.add-fund.store') }}" class="p-5 space-y-5" id="add-fund-form">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Mentee *</label>
                    <div class="relative">
                        <input type="text" id="mentee-search" autocomplete="off"
                               value="{{ $selectedName }}"
                               placeholder="Search by name, email or phone…"
                               class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <div id="mentee-dropdown"
                             class="hidden absolute z-20 left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg"></div>
                    </div>
                    <input type="hidden" name="mentee_id" id="mentee_id" value="{{ $selectedId }}" required>
                    <input type="hidden" name="_mentee_name" id="_mentee_name" value="{{ $selectedName }}">
                    <input type="hidden" name="_mentee_email" id="_mentee_email" value="{{ $selectedEmail }}">
                    <input type="hidden" name="_mentee_balance" id="_mentee_balance" value="{{ $selectedBalance }}">

                    <div id="selected-mentee-card"
                         class="mt-3 {{ $selectedId ? '' : 'hidden' }} rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-800 truncate" id="selected-mentee-name">{{ $selectedName }}</p>
                                <p class="text-xs text-gray-500 truncate" id="selected-mentee-email">{{ $selectedEmail }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-[10px] uppercase tracking-wide text-gray-400">Balance</p>
                                <p class="text-sm font-bold text-emerald-700" id="selected-mentee-balance">
                                    @if($selectedBalance !== '')
                                        ₹{{ number_format((float) $selectedBalance, 2) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <button type="button" id="clear-mentee"
                                class="mt-2 text-xs text-gray-500 hover:text-red-600 transition-colors">
                            Clear selection
                        </button>
                    </div>
                    @error('mentee_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Amount (₹) *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">₹</span>
                        <input type="number" name="amount" step="0.01" min="0.01" max="1000000" required
                               value="{{ old('amount') }}"
                               placeholder="0.00"
                               class="w-full text-sm border border-gray-200 rounded-xl pl-7 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach([100, 250, 500, 1000, 2000] as $quick)
                            <button type="button"
                                    class="quick-amount text-xs font-medium px-2.5 py-1 rounded-lg border border-gray-200 text-gray-600 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 transition-colors"
                                    data-amount="{{ $quick }}">
                                ₹{{ number_format($quick) }}
                            </button>
                        @endforeach
                    </div>
                    @error('amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Reason *</label>
                    <input type="text" name="description" required maxlength="255"
                           value="{{ old('description') }}"
                           placeholder="e.g. Promo credit, support compensation, manual top-up"
                           class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-colors">
                    <iconify-icon icon="fa-solid:plus-circle"></iconify-icon>
                    Add Fund
                </button>
            </form>
        </div>

        {{-- Recent history --}}
        <div class="xl:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden min-w-0">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div>
                    <h6 class="font-semibold text-gray-800">Recent Admin Fund Credits</h6>
                    <p class="text-xs text-gray-400 mt-0.5">Only mentee wallet top-ups done from this screen</p>
                </div>
                <span class="text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full">
                    {{ $recentFunds->total() }} records
                </span>
            </div>

            <div class="overflow-x-auto max-w-full">
                <table class="w-full min-w-[720px] text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Mentee</th>
                            <th class="px-4 py-3 text-left">Reference</th>
                            <th class="px-4 py-3 text-left">Reason</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                            <th class="px-4 py-3 text-right">New Balance</th>
                            <th class="px-4 py-3 text-left">By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($recentFunds as $txn)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-4 py-3 text-nowrap">
                                <p class="text-gray-700 font-medium">{{ $txn->created_at->format('d M Y') }}</p>
                                <p class="text-gray-400 text-xs">{{ $txn->created_at->format('h:i A') }}</p>
                            </td>
                            <td class="px-4 py-3 max-w-[180px]">
                                @if($txn->user)
                                    <a href="{{ route('admin.wallet.customer.show', $txn->user->id) }}"
                                       class="font-medium text-gray-800 hover:text-blue-600 transition-colors block truncate"
                                       title="{{ $txn->user->name }}">
                                        {{ $txn->user->name }}
                                    </a>
                                    <p class="text-xs text-gray-400 truncate">{{ $txn->user->email }}</p>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
                                    {{ $txn->reference ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 max-w-[180px]">
                                <p class="truncate text-xs" title="{{ $txn->description }}">{{ $txn->description ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-600">
                                +₹{{ number_format($txn->amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-700">
                                ₹{{ number_format($txn->balance_after, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs text-gray-700 font-medium">
                                    {{ $txn->performedByAdmin->name ?? 'Admin' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-16 text-gray-400">
                                <iconify-icon icon="solar:inbox-line-broken" class="text-5xl block mb-3 mx-auto"></iconify-icon>
                                <p class="font-medium text-gray-500">No admin fund credits yet</p>
                                <p class="text-xs mt-1">Credits added here will show up in this list</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($recentFunds->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-gray-400">
                    Showing {{ $recentFunds->firstItem() }}–{{ $recentFunds->lastItem() }} of {{ $recentFunds->total() }}
                </p>
                <div class="text-sm">
                    {{ $recentFunds->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const searchEl = document.getElementById('mentee-search');
    const dropdown = document.getElementById('mentee-dropdown');
    const menteeIdEl = document.getElementById('mentee_id');
    const nameHidden = document.getElementById('_mentee_name');
    const emailHidden = document.getElementById('_mentee_email');
    const balanceHidden = document.getElementById('_mentee_balance');
    const card = document.getElementById('selected-mentee-card');
    const nameEl = document.getElementById('selected-mentee-name');
    const emailEl = document.getElementById('selected-mentee-email');
    const balanceEl = document.getElementById('selected-mentee-balance');
    const clearBtn = document.getElementById('clear-mentee');
    const form = document.getElementById('add-fund-form');
    let debounceTimer = null;

    function formatMoney(n) {
        return '₹' + Number(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function selectMentee(user) {
        menteeIdEl.value = user.id;
        nameHidden.value = user.name || '';
        emailHidden.value = user.email || '';
        balanceHidden.value = Number(user.wallet_balance || 0).toFixed(2);
        searchEl.value = user.name || '';
        nameEl.textContent = user.name || '';
        emailEl.textContent = user.email || '';
        balanceEl.textContent = formatMoney(user.wallet_balance);
        card.classList.remove('hidden');
        dropdown.classList.add('hidden');
        dropdown.innerHTML = '';
    }

    function clearMentee() {
        menteeIdEl.value = '';
        nameHidden.value = '';
        emailHidden.value = '';
        balanceHidden.value = '';
        searchEl.value = '';
        nameEl.textContent = '';
        emailEl.textContent = '';
        balanceEl.textContent = '';
        card.classList.add('hidden');
        dropdown.classList.add('hidden');
    }

    async function searchMentees(term) {
        const q = encodeURIComponent(term || '');
        const res = await fetch(`{{ route('admin.wallet.users') }}?type=mentee&search=${q}`);
        if (!res.ok) return [];
        return await res.json();
    }

    function renderDropdown(users) {
        if (!users.length) {
            dropdown.innerHTML = '<div class="px-3 py-3 text-xs text-gray-400">No mentees found</div>';
            dropdown.classList.remove('hidden');
            return;
        }

        dropdown.innerHTML = users.map(u => `
            <button type="button" class="mentee-option w-full text-left px-3 py-2.5 hover:bg-emerald-50 transition-colors border-b border-gray-50 last:border-0"
                    data-id="${u.id}"
                    data-name="${(u.name || '').replace(/"/g, '&quot;')}"
                    data-email="${(u.email || '').replace(/"/g, '&quot;')}"
                    data-balance="${u.wallet_balance}">
                <p class="text-sm font-medium text-gray-800 truncate">${u.name}</p>
                <p class="text-xs text-gray-400 truncate">${u.email || ''} · Balance ${formatMoney(u.wallet_balance)}</p>
            </button>
        `).join('');
        dropdown.classList.remove('hidden');
    }

    searchEl.addEventListener('input', function () {
        const term = this.value.trim();
        if (menteeIdEl.value && term !== nameHidden.value) {
            menteeIdEl.value = '';
            card.classList.add('hidden');
        }
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            if (term.length < 1) {
                dropdown.classList.add('hidden');
                return;
            }
            const users = await searchMentees(term);
            renderDropdown(users);
        }, 250);
    });

    searchEl.addEventListener('focus', async function () {
        const term = this.value.trim();
        const users = await searchMentees(term);
        renderDropdown(users);
    });

    dropdown.addEventListener('click', function (e) {
        const btn = e.target.closest('.mentee-option');
        if (!btn) return;
        selectMentee({
            id: btn.dataset.id,
            name: btn.dataset.name,
            email: btn.dataset.email,
            wallet_balance: btn.dataset.balance,
        });
    });

    clearBtn.addEventListener('click', clearMentee);

    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target) && e.target !== searchEl) {
            dropdown.classList.add('hidden');
        }
    });

    document.querySelectorAll('.quick-amount').forEach(btn => {
        btn.addEventListener('click', function () {
            form.querySelector('input[name="amount"]').value = this.dataset.amount;
        });
    });

    form.addEventListener('submit', function (e) {
        if (!menteeIdEl.value) {
            e.preventDefault();
            alert('Please select a mentee first.');
            searchEl.focus();
        }
    });
})();
</script>
@endpush

@endsection
