@extends('admin.layouts.app')
@section('title', 'Invoices')
@section('heading', 'Plan Invoices')
@section('content')

<div class="space-y-6">
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <p class="text-sm text-gray-500">Issued invoices for mentee plan purchases.</p>
        <a href="{{ route('admin.subscriptions.index') }}" class="text-sm font-medium text-blue-600 hover:underline">← Subscriptions</a>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-2xl p-4 flex gap-3 flex-wrap">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search invoice / mentee / plan"
               class="flex-1 min-w-[220px] border border-gray-200 rounded-xl px-3 py-2 text-sm">
        <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl px-4 py-2">Search</button>
    </form>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Invoice</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Mentee</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Plan</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Total</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($invoices as $invoice)
                    <tr>
                        <td class="px-5 py-3 text-sm font-semibold">{{ $invoice->invoice_number }}</td>
                        <td class="px-4 py-3 text-sm">
                            <div>{{ $invoice->billing_name }}</div>
                            <div class="text-xs text-gray-500">{{ $invoice->billing_email }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $invoice->plan_name }}</td>
                        <td class="px-4 py-3 text-sm">₹{{ number_format((float) $invoice->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-sm">{{ $invoice->invoice_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-sm text-blue-600 hover:underline">View</a>
                            <a href="{{ route('admin.invoices.download', $invoice) }}" class="text-sm text-green-700 hover:underline">Download</a>
                            <a href="{{ route('admin.invoices.print', $invoice) }}" class="text-sm text-amber-700 hover:underline">Print</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">No invoices yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>
@endsection
