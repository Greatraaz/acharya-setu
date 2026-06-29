{{-- ============================================================
FILE: resources/views/admin/mentors/approvals.blade.php
============================================================ --}}
@extends('admin.layouts.app')
@section('title', 'Mentor Approvals')
@section('heading', 'Mentor Approvals')
@section('content')

<div class="space-y-5">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Review and manage mentor registrations and profile changes.</p>
        <a href="{{ route('admin.mentors.pending-changes') }}"
           class="inline-flex items-center gap-2 text-sm font-medium {{ $stats['pending_changes'] > 0 ? 'text-amber-700 bg-amber-50 border border-amber-200' : 'text-gray-600 bg-white border border-gray-200' }} px-4 py-2 rounded-xl hover:opacity-90 transition-all">
            Profile Change Requests
            @if($stats['pending_changes'] > 0)
            <span class="bg-amber-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $stats['pending_changes'] }}</span>
            @endif
        </a>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        ✓ {{ session('success') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-4 gap-4">
        @foreach([
            ['Pending',   $stats['pending'],   'bg-amber-50',   'text-amber-700',   'border-amber-200',   '?status=pending'],
            ['Approved',  $stats['approved'],  'bg-green-50',   'text-green-700',   'border-green-200',   '?status=approved'],
            ['Rejected',  $stats['rejected'],  'bg-red-50',     'text-red-700',     'border-red-200',     '?status=rejected'],
            ['Suspended', $stats['suspended'], 'bg-gray-50',    'text-gray-600',    'border-gray-200',    '?status=suspended'],
        ] as [$label, $count, $bg, $tc, $bc, $query])
        <a href="{{ route('admin.mentor-approvals.index') . $query }}"
           class="{{ $bg }} border {{ $bc }} rounded-xl p-4 block hover:shadow-sm transition-shadow {{ request('status') === strtolower($label) ? 'ring-2 ring-offset-1 ring-gray-400' : '' }}">
            <div class="text-2xl font-bold {{ $tc }}">{{ $count }}</div>
            <div class="text-xs font-medium text-gray-500 mt-0.5">{{ $label }}</div>
        </a>
        @endforeach
    </div>

    {{-- Filter tabs --}}
    <div class="flex gap-1 bg-gray-100 p-1 rounded-xl w-fit">
        @foreach([''=>'All', 'pending'=>'Pending', 'approved'=>'Approved', 'rejected'=>'Rejected', 'suspended'=>'Suspended'] as $val => $label)
        <a href="{{ route('admin.mentor-approvals.index') }}{{ $val ? '?status=' . $val : '' }}"
           class="px-4 py-2 rounded-lg text-xs font-semibold transition-all {{ request('status', '') === $val ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Mentor table --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Mentors</h3>
            <span class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full">{{ $mentors->total() }} records</span>
        </div>

        @if($mentors->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <div class="text-4xl mb-3">👤</div>
            <p class="text-sm font-medium">No mentors found</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-left">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Mentor</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Professional</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rate</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Mentees</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Submitted</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($mentors as $mentor)
                    @php
                    $statusColors = [
                        'pending'   => 'bg-amber-50 text-amber-700',
                        'approved'  => 'bg-green-50 text-green-700',
                        'rejected'  => 'bg-red-50 text-red-700',
                        'suspended' => 'bg-gray-100 text-gray-600',
                    ];
                    $statusDots = [
                        'pending'   => 'bg-amber-400',
                        'approved'  => 'bg-green-500',
                        'rejected'  => 'bg-red-500',
                        'suspended' => 'bg-gray-400',
                    ];
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors group">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if($mentor->avatar_url)
                                <img src="{{ $mentor->avatar_url }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                                @else
                                <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 font-bold text-sm flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($mentor->name, 0, 1)) }}
                                </div>
                                @endif
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm font-semibold text-gray-900">{{ $mentor->name }}</span>
                                        @if($mentor->has_pending_changes)
                                        <span class="text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded font-bold">Changes pending</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400">{{ $mentor->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-sm font-medium text-gray-800">{{ $mentor->designation ?? '—' }}</div>
                            <div class="text-xs text-gray-400">{{ $mentor->company ?? '' }}{{ $mentor->field ? ' · ' . $mentor->field : '' }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-sm font-semibold text-gray-800">
                                {{ $mentor->rate_per_minute > 0 ? '₹' . $mentor->rate_per_minute . '/min' : 'Free' }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-sm font-semibold text-gray-700">{{ $mentor->assigned_mentees_count }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusColors[$mentor->mentor_status] ?? 'bg-gray-100 text-gray-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusDots[$mentor->mentor_status] ?? 'bg-gray-400' }}"></span>
                                {{ ucfirst($mentor->mentor_status) }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-xs text-gray-500">{{ $mentor->created_at->format('d M Y') }}</div>
                            <div class="text-[10px] text-gray-400">{{ $mentor->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity justify-end">
                                <a href="{{ route('admin.mentors.review', $mentor) }}"
                                   class="text-xs font-medium px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg transition-colors">
                                    Review
                                </a>
                                @if($mentor->mentor_status === 'pending')
                                <form method="POST" action="{{ route('admin.mentors.approve', $mentor) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium px-3 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 rounded-lg transition-colors">
                                        Approve
                                    </button>
                                </form>
                                @endif
                                @if(in_array($mentor->mentor_status, ['pending','rejected']))
                                <button onclick="openRejectModal({{ $mentor->id }}, '{{ addslashes($mentor->name) }}')"
                                        class="text-xs font-medium px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors">
                                    Reject
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between px-5 py-3.5 border-t border-gray-100">
            <div class="text-xs text-gray-500">{{ $mentors->firstItem() }}–{{ $mentors->lastItem() }} of {{ $mentors->total() }}</div>
            {{ $mentors->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Reject Modal --}}
<div id="reject-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <h3 class="text-sm font-bold text-gray-900 mb-1">Reject Mentor Application</h3>
        <p class="text-xs text-gray-500 mb-4" id="reject-modal-name">Provide a reason for rejection.</p>
        <form method="POST" id="reject-form">
            @csrf
            <textarea name="reason" rows="4" required minlength="10"
                      placeholder="e.g. Insufficient experience — need at least 3 years in relevant field. Please reapply when qualified."
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100 resize-none mb-4"></textarea>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">Reject</button>
                <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')"
                        class="flex-1 border border-gray-200 text-gray-600 text-sm py-2.5 rounded-xl hover:bg-gray-50 transition-colors">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(mentorId, name) {
    document.getElementById('reject-modal-name').textContent = `Rejecting: ${name}`;
    document.getElementById('reject-form').action = `/admin/mentors/${mentorId}/reject`;
    document.getElementById('reject-modal').classList.remove('hidden');
}
</script>

@endsection