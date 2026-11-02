@extends('admin.layouts.app')
@section('title', 'Experts')
@section('heading', 'Experts')
@section('content')

<div class="space-y-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <p class="text-sm text-gray-500">Session specialists who take 1:1 calls with mentees, keep notes, and earn from their charge.</p>
        <a href="{{ route('admin.experts.create') }}"
           class="inline-flex items-center justify-center gap-1.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2.5 rounded-xl transition-colors">
            + Add Expert
        </a>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-7 gap-3">
        @foreach([
            ['Total', number_format($stats['total']), 'bg-slate-50', 'text-slate-700', 'border-slate-200'],
            ['Approved', number_format($stats['approved']), 'bg-green-50', 'text-green-700', 'border-green-200'],
            ['Pending', number_format($stats['pending']), 'bg-amber-50', 'text-amber-700', 'border-amber-200'],
            ['Rejected', number_format($stats['rejected']), 'bg-red-50', 'text-red-600', 'border-red-200'],
            ['Suspended', number_format($stats['suspended']), 'bg-gray-50', 'text-gray-600', 'border-gray-200'],
            ['Earned (net)', '₹'.number_format((float) $stats['earned'], 0), 'bg-orange-50', 'text-orange-700', 'border-orange-200'],
            ['Wallet unpaid', '₹'.number_format((float) $stats['unpaid'], 0), 'bg-indigo-50', 'text-indigo-700', 'border-indigo-200'],
        ] as [$label, $value, $bg, $tc, $bc])
        <div class="{{ $bg }} border {{ $bc }} rounded-xl p-4">
            <div class="text-xl font-bold {{ $tc }}">{{ $value }}</div>
            <div class="text-xs font-medium text-gray-500 mt-0.5">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    <div class="overflow-x-auto -mx-1 px-1 pb-1">
        <div class="flex gap-1 bg-gray-100 p-1 rounded-xl w-max min-w-full sm:w-fit">
            @foreach(['' => 'All', 'approved' => 'Approved', 'pending' => 'Pending', 'rejected' => 'Rejected', 'suspended' => 'Suspended'] as $val => $label)
            <a href="{{ route('admin.experts.index', array_filter(['mentor_status' => $val ?: null, 'search' => request('search')])) }}"
               class="flex-shrink-0 px-4 py-2.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all {{ request('mentor_status', '') === $val ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5">
        <form method="GET" class="admin-filter-form">
            @if(request('mentor_status'))
            <input type="hidden" name="mentor_status" value="{{ request('mentor_status') }}">
            @endif
            <div class="admin-filter-search min-w-0">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, domain…"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
            </div>
            <div class="min-w-0">
                <label class="block text-xs font-medium text-gray-500 mb-1">Field</label>
                <input type="text" name="field" value="{{ request('field') }}" placeholder="Career, Design…"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
            </div>
            <div class="admin-filter-actions">
                <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium px-4 rounded-xl">Filter</button>
                <a href="{{ route('admin.experts.index') }}" class="text-center text-sm text-gray-500 border border-gray-200 px-3 rounded-xl hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">All Experts</h3>
            <span class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full font-medium">{{ $experts->total() }} records</span>
        </div>

        @if($experts->isEmpty())
        <div class="py-20 text-center">
            <p class="text-gray-500 font-medium text-sm">No experts yet. Create one to start taking paid sessions.</p>
            <a href="{{ route('admin.experts.create') }}" class="inline-flex mt-4 text-sm font-semibold text-indigo-600 hover:text-indigo-700">+ Add Expert</a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-left">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Expert</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Domain</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Charge</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sessions</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Earned</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Wallet</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($experts as $expert)
                    @php
                        $statusColors = ['pending'=>'bg-amber-50 text-amber-700','approved'=>'bg-green-50 text-green-700','rejected'=>'bg-red-50 text-red-700','suspended'=>'bg-gray-100 text-gray-600'];
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if($expert->avatar_url)
                                <img src="{{ $expert->avatar_url }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0" alt="">
                                @else
                                <div class="w-9 h-9 rounded-full bg-violet-100 text-violet-700 font-bold text-sm flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($expert->name, 0, 1)) }}
                                </div>
                                @endif
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $expert->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $expert->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-sm font-medium text-gray-800">{{ $expert->designation ?: '—' }}</div>
                            <div class="text-xs text-gray-400">{{ $expert->field }}{{ $expert->company ? ' · '.$expert->company : '' }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-sm font-bold text-gray-800">{{ $expert->rate_per_minute > 0 ? '₹'.$expert->rate_per_minute.'/min' : 'Free' }}</div>
                            @if($expert->rate_per_minute > 0)
                            <div class="text-xs text-gray-400">₹{{ number_format($expert->rate_per_minute * 60) }}/hr</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm font-bold text-gray-700">{{ $expert->total_sessions ?? 0 }}</td>
                        <td class="px-4 py-4">
                            <div class="text-sm font-semibold {{ (float) $expert->earned > 0 ? 'text-gray-900' : 'text-gray-400' }}">₹{{ number_format((float) $expert->earned, 0) }}</div>
                            <div class="text-xs text-gray-400">₹{{ number_format((float) $expert->paid_out, 0) }} paid{{ (float) $expert->pending_out > 0 ? ' · ₹'.number_format((float) $expert->pending_out, 0).' pending' : '' }}</div>
                        </td>
                        <td class="px-4 py-4 text-sm font-semibold {{ (float) $expert->wallet_balance > 0 ? 'text-orange-600' : 'text-gray-400' }}">
                            ₹{{ number_format((float) $expert->wallet_balance, 0) }}
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusColors[$expert->mentor_status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst($expert->mentor_status) }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-1 flex-wrap">
                                <a href="{{ route('admin.experts.edit', $expert) }}"
                                   class="text-xs font-medium px-2.5 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg">Edit</a>
                                <a href="{{ route('admin.earnings.show', $expert) }}"
                                   class="text-xs font-medium px-2.5 py-1.5 bg-orange-50 text-orange-700 hover:bg-orange-100 rounded-lg">Earnings</a>
                                @if($expert->mentor_status === 'pending')
                                <form method="POST" action="{{ route('admin.experts.approve', $expert) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium px-2.5 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 rounded-lg">Approve</button>
                                </form>
                                @endif
                                @if($expert->mentor_status === 'approved')
                                <form method="POST" action="{{ route('admin.experts.toggle-status', $expert) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium px-2.5 py-1.5 {{ $expert->is_active ? 'bg-gray-100 text-gray-600' : 'bg-green-50 text-green-700' }} rounded-lg">
                                        {{ $expert->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.experts.destroy', $expert) }}" onsubmit="return confirm('Delete {{ addslashes($expert->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-medium px-2.5 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between px-5 py-3.5 border-t border-gray-100">
            <div class="text-xs text-gray-500">Showing {{ $experts->firstItem() }}–{{ $experts->lastItem() }} of {{ $experts->total() }}</div>
            {{ $experts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
