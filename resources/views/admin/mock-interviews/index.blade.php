@extends('admin.layouts.app')
@section('title', 'Mock Interviews')
@section('heading', 'Mock Interviews')

@section('content')
<div class="space-y-4">
    <p class="text-sm text-gray-500">Review mock interview requests, assign mentors, and share feedback with mentees.</p>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">{{ session('error') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="admin-table-toolbar px-5 py-4 border-b border-gray-100">
            <form method="GET" class="admin-table-filters">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search mentee, email, ID, role…"
                       class="border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 w-full sm:min-w-[220px]">
                <select name="status" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white">
                    <option value="">All status</option>
                    <option value="submitted" @selected($status === 'submitted')>Awaiting confirmation</option>
                    <option value="confirmed" @selected($status === 'confirmed')>Confirmed</option>
                    <option value="completed" @selected($status === 'completed')>Completed</option>
                    <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Search</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold">ID</th>
                        <th class="text-left px-5 py-3 font-semibold">Mentee</th>
                        <th class="text-left px-5 py-3 font-semibold">Preferred time</th>
                        <th class="text-left px-5 py-3 font-semibold">Duration</th>
                        <th class="text-left px-5 py-3 font-semibold">Payment</th>
                        <th class="text-left px-5 py-3 font-semibold">Status</th>
                        <th class="text-right px-5 py-3 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-500">#{{ $item->id }}</td>
                        <td class="px-5 py-3">
                            <div class="font-medium text-gray-900">{{ $item->user->name ?? '—' }}</div>
                            <div class="text-xs text-gray-500">{{ $item->user->email ?? '' }}</div>
                        </td>
                        <td class="px-5 py-3 text-gray-700">
                            {{ $item->preferred_at?->timezone($item->timezone ?: 'Asia/Kolkata')->format('d M Y, h:i A') }}
                        </td>
                        <td class="px-5 py-3">{{ $item->duration_minutes }} min</td>
                        <td class="px-5 py-3">
                            @if($item->is_paid_addon)
                                ₹{{ number_format((float) $item->amount, 0) }} · {{ $item->payment_status }}
                            @else
                                Free (plan)
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ $item->status === 'completed' ? 'bg-green-50 text-green-700' : ($item->status === 'confirmed' ? 'bg-blue-50 text-blue-700' : ($item->status === 'submitted' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-600')) }}">
                                {{ $item->statusLabel() }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.mock-interviews.show', $item) }}" class="text-blue-600 hover:text-blue-800 font-medium">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-gray-500">No mock interview requests yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $items->links() }}</div>
        @endif
    </div>
</div>
@endsection
