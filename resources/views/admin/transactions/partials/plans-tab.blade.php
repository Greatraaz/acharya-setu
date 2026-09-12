@php $summary = $planSummary ?? []; @endphp

<div class="grid grid-cols-2 gap-3">
    <div class="bg-white border border-gray-200 rounded-2xl p-4">
        <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Invoices</p>
        <p class="mt-1 text-xl font-bold text-gray-900">{{ number_format($summary['count'] ?? 0) }}</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-2xl p-4">
        <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Total Collected</p>
        <p class="mt-1 text-xl font-bold text-indigo-600">₹{{ number_format($summary['total'] ?? 0, 2) }}</p>
    </div>
</div>

<div class="bg-white border border-gray-200 rounded-2xl p-4">
    <form method="GET" class="admin-filter-form flex flex-wrap gap-3 items-end">
        <input type="hidden" name="tab" value="plans">
        <div class="flex-1 min-w-[160px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice, plan, email…"
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm">
        </div>
        <div class="min-w-[120px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <input type="text" name="status" value="{{ request('status') }}" placeholder="paid / …"
                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
        </div>
        <div class="min-w-[140px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
            <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
        </div>
        <div class="min-w-[140px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
            <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
        </div>
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-xl">Filter</button>
        <a href="{{ route('admin.transactions.index', ['tab' => 'plans']) }}" class="text-sm border border-gray-200 px-3 py-2 rounded-xl hover:bg-gray-50">Reset</a>
    </form>
</div>

<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
    <div class="flex items-center justify-between px-4 sm:px-5 py-3.5 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-800">Plan / subscription invoices</h3>
        <span class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full">{{ $planInvoices->total() ?? 0 }} results</span>
    </div>

    <div class="md:hidden divide-y divide-gray-100">
        @forelse($planInvoices ?? [] as $inv)
            <div class="p-4 flex justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-medium text-gray-900 truncate">{{ $inv->invoice_number }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $inv->user?->name }} · {{ $inv->plan_name }}</p>
                </div>
                <div class="text-right">
                    <p class="font-semibold">₹{{ number_format($inv->total_amount, 2) }}</p>
                    <p class="text-[11px] text-gray-400">{{ optional($inv->invoice_date)->format('d M Y') }}</p>
                </div>
            </div>
        @empty
            <div class="py-14 text-center text-gray-400 text-sm">No plan invoices found.</div>
        @endforelse
    </div>

    <div class="hidden md:block overflow-x-auto">
        <table class="w-full min-w-[800px] text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-4 py-3 text-left">Invoice</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">User</th>
                    <th class="px-4 py-3 text-left">Plan</th>
                    <th class="px-4 py-3 text-right">Tax</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-left">Reference</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($planInvoices ?? [] as $inv)
                    <tr class="hover:bg-gray-50/70">
                        <td class="px-4 py-3 font-mono text-xs">{{ $inv->invoice_number }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ optional($inv->invoice_date)->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $inv->user?->name ?? $inv->billing_name }}</p>
                            <p class="text-xs text-gray-400">{{ $inv->user?->email ?? $inv->billing_email }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $inv->plan_name }}</td>
                        <td class="px-4 py-3 text-right">₹{{ number_format($inv->tax_total ?? 0, 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold">₹{{ number_format($inv->total_amount, 2) }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $inv->payment_reference ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">{{ ucfirst($inv->status ?? '—') }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-16 text-center text-gray-400">No plan invoices found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(($planInvoices ?? null) && method_exists($planInvoices, 'hasPages') && $planInvoices->hasPages())
        <div class="px-4 sm:px-5 py-4 border-t border-gray-100 flex flex-wrap justify-between gap-3">
            <p class="text-xs text-gray-400">Showing {{ $planInvoices->firstItem() }}–{{ $planInvoices->lastItem() }} of {{ $planInvoices->total() }}</p>
            <div>{{ $planInvoices->links() }}</div>
        </div>
    @endif
</div>
