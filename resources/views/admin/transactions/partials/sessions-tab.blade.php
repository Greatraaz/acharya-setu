@php
    use App\Support\SessionPayoutBreakdown;
    $summary = $sessionSummary ?? [];
    $feePct = (int) round(SessionPayoutBreakdown::FEE_RATE * 100);
@endphp

<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
    @foreach([
        ['Invoices', number_format($summary['count'] ?? 0), 'text-gray-900'],
        ['Mentee Paid', '₹'.number_format($summary['total'] ?? 0, 2), 'text-indigo-600'],
        ['Coupon Subsidy', '₹'.number_format($summary['coupon_total'] ?? $summary['platform_subsidy'] ?? 0, 2), 'text-amber-600'],
        ['Admin Commission ('.$feePct.'% of list)', '₹'.number_format($summary['commission'] ?? 0, 2), 'text-rose-600'],
        ['Mentor Earnings', '₹'.number_format($summary['mentor_net'] ?? 0, 2), 'text-emerald-600'],
        ['Via Wallet / Razorpay', '₹'.number_format(($summary['wallet'] ?? 0) + ($summary['razorpay'] ?? 0), 2), 'text-slate-700'],
    ] as [$label, $value, $color])
        <div class="bg-white border border-gray-200 rounded-2xl p-4">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">{{ $label }}</p>
            <p class="mt-1 text-lg sm:text-xl font-bold {{ $color }} break-all">{{ $value }}</p>
        </div>
    @endforeach
</div>

<div class="bg-white border border-gray-200 rounded-2xl p-4">
    <form method="GET" class="admin-filter-form flex flex-wrap gap-3 items-end">
        <input type="hidden" name="tab" value="sessions">
        <div class="flex-1 min-w-[160px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice, session, booking ref, email…"
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm">
        </div>
        <div class="min-w-[140px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Payment method</label>
            <select name="payment_method" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white">
                <option value="">All</option>
                @foreach(['wallet','razorpay','hybrid','plan','free'] as $m)
                    <option value="{{ $m }}" @selected(request('payment_method') === $m)>{{ ucfirst($m) }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[120px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <input type="text" name="status" value="{{ request('status') }}" placeholder="issued / …"
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
        <a href="{{ route('admin.transactions.index', ['tab' => 'sessions']) }}" class="text-sm border border-gray-200 px-3 py-2 rounded-xl hover:bg-gray-50">Reset</a>
    </form>
</div>

<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
    <div class="flex items-center justify-between px-4 sm:px-5 py-3.5 border-b border-gray-100">
        <div>
            <h3 class="text-sm font-semibold text-gray-800">Session payment invoices</h3>
            <p class="text-xs text-gray-400 mt-0.5">Mentor earns {{ $feePct === 20 ? '80' : (100 - $feePct) }}% of full session price · Coupon discount is borne by admin/platform</p>
        </div>
        <span class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full">{{ $sessionInvoices->total() ?? 0 }} results</span>
    </div>

    <div class="md:hidden divide-y divide-gray-100">
        @forelse($sessionInvoices ?? [] as $inv)
            @php $bd = $inv->payoutBreakdown(); @endphp
            <div class="p-4 space-y-2">
                <div class="flex justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 truncate">{{ $inv->invoice_number }}</p>
                        <p class="text-sm text-gray-800 truncate mt-0.5">{{ $inv->sessionTitle() }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $inv->user?->name }} → {{ $inv->mentor?->name }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $inv->paymentMethodLabel() }} · {{ $inv->duration_minutes ? $inv->duration_minutes.' min' : '—' }}</p>
                        @if(($bd['coupon_discount'] ?? 0) > 0)
                            <p class="text-[11px] text-amber-600 mt-1">Coupon −₹{{ number_format($bd['coupon_discount'], 2) }} (admin) · Paid ₹{{ number_format($bd['mentee_paid'], 2) }}</p>
                        @endif
                    </div>
                    <div class="text-right shrink-0">
                        <p class="font-semibold text-gray-900">₹{{ number_format($bd['gross'], 2) }}</p>
                        <p class="text-[11px] text-rose-600">Fee ₹{{ number_format($bd['platform_fee'], 2) }}</p>
                        <p class="text-[11px] text-emerald-600">Mentor ₹{{ number_format($bd['net'], 2) }}</p>
                        <p class="text-[11px] text-gray-400 mt-1">{{ optional($inv->invoice_date)->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-14 text-center text-gray-400 text-sm">No session invoices found.</div>
        @endforelse
    </div>

    <div class="hidden md:block overflow-x-auto">
        <table class="w-full min-w-[1280px] text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-4 py-3 text-left">Invoice</th>
                    <th class="px-4 py-3 text-left">Session</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Mentee</th>
                    <th class="px-4 py-3 text-left">Mentor</th>
                    <th class="px-4 py-3 text-left">Method</th>
                    <th class="px-4 py-3 text-right">Duration</th>
                    <th class="px-4 py-3 text-right">Session Gross</th>
                    <th class="px-4 py-3 text-right">Coupon (Admin)</th>
                    <th class="px-4 py-3 text-right">Admin Commission</th>
                    <th class="px-4 py-3 text-right">Mentor Earned</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($sessionInvoices ?? [] as $inv)
                    @php $bd = $inv->payoutBreakdown(); @endphp
                    <tr class="hover:bg-gray-50/70">
                        <td class="px-4 py-3 font-mono text-xs whitespace-nowrap">{{ $inv->invoice_number }}</td>
                        <td class="px-4 py-3 max-w-[200px]">
                            <p class="font-medium text-gray-800 truncate" title="{{ $inv->sessionTitle() }}">{{ $inv->sessionTitle() }}</p>
                            @if($inv->booking_ref)
                                <p class="text-[11px] text-gray-400 truncate">{{ $inv->booking_ref }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ optional($inv->session_at ?? $inv->invoice_date)->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $inv->user?->name ?? '—' }}</p>
                            <p class="text-xs text-gray-400">{{ $inv->user?->email }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $inv->mentor?->name ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full">{{ $inv->paymentMethodLabel() }}</span></td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">{{ $inv->duration_minutes ? $inv->duration_minutes.' min' : '—' }}</td>
                        <td class="px-4 py-3 text-right font-semibold">₹{{ number_format($bd['gross'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-amber-600">
                            @if(($bd['coupon_discount'] ?? 0) > 0)
                                −₹{{ number_format($bd['coupon_discount'], 2) }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-rose-600">₹{{ number_format($bd['platform_fee'], 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-emerald-600">₹{{ number_format($bd['net'], 2) }}</td>
                        <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">{{ ucfirst($inv->status ?? '—') }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="py-16 text-center text-gray-400">No session invoices found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(($sessionInvoices ?? null) && method_exists($sessionInvoices, 'hasPages') && $sessionInvoices->hasPages())
        <div class="px-4 sm:px-5 py-4 border-t border-gray-100 flex flex-wrap justify-between gap-3">
            <p class="text-xs text-gray-400">Showing {{ $sessionInvoices->firstItem() }}–{{ $sessionInvoices->lastItem() }} of {{ $sessionInvoices->total() }}</p>
            <div>{{ $sessionInvoices->links() }}</div>
        </div>
    @endif
</div>
