@extends('admin.layouts.app')
@section('title', 'Offers')
@section('heading', 'Offers')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <p class="text-sm text-gray-500">Wallet credits for new joinees and session coupons for selected mentees.</p>
        <a href="{{ route('admin.offers.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition">
            + Add Offer
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="admin-table-toolbar px-5 py-4 border-b border-gray-100">
            <form method="GET" class="admin-table-filters">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or coupon…"
                       class="border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 w-full sm:min-w-[220px]">
                <select name="audience" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white">
                    <option value="">All types</option>
                    <option value="new_joinee" @selected(request('audience') === 'new_joinee')>New joinee</option>
                    <option value="selected_mentees" @selected(request('audience') === 'selected_mentees')>Coupon</option>
                </select>
                <select name="status" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white">
                    <option value="">All status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Search</button>
                @if(request()->filled('search') || request()->filled('audience') || request()->filled('status'))
                    <a href="{{ route('admin.offers.index') }}" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Reset</a>
                @endif
            </form>
        </div>

        @if($offers->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <div class="text-4xl mb-2">🎁</div>
            <p class="font-medium text-gray-600">No offers yet</p>
            <p class="text-sm mt-1">Create a new joinee wallet credit or mentee coupon.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-16">#</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Title</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Type</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Amount</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Coupon</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Validity</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Usage</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Status</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600 w-28">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($offers as $offer)
                    <tr class="hover:bg-gray-50/70">
                        <td class="px-5 py-4 text-gray-500">{{ $offers->firstItem() + $loop->index }}</td>
                        <td class="px-5 py-4 font-semibold text-gray-900">{{ $offer->title }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $offer->audienceLabel() }}</td>
                        <td class="px-5 py-4 text-gray-900">₹{{ number_format((float) $offer->amount, 0) }}</td>
                        <td class="px-5 py-4">
                            @if($offer->coupon_code)
                                <code class="text-xs bg-orange-50 text-orange-800 px-2 py-1 rounded-lg">{{ $offer->coupon_code }}</code>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-gray-600 whitespace-nowrap">
                            {{ $offer->starts_at->format('d M Y') }} – {{ $offer->expires_at->format('d M Y') }}
                        </td>
                        <td class="px-5 py-4 text-gray-600">
                            {{ $offer->usage_count }}@if($offer->usage_limit)/{{ $offer->usage_limit }}@else/∞@endif
                            @if($offer->isCouponOffer())
                                <span class="text-gray-400"> · {{ $offer->mentees_count }} mentee(s)</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($offer->isActiveNow())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Live</span>
                            @elseif($offer->is_active)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Scheduled</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.offers.edit', $offer) }}"
                                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-green-600 text-white hover:bg-green-700" title="Edit">✎</a>
                                <form method="POST" action="{{ route('admin.offers.destroy', $offer) }}"
                                      onsubmit="return confirm('Delete this offer?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-600 text-white hover:bg-red-700" title="Delete">🗑</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $offers])
        @endif
    </div>
</div>
@endsection
