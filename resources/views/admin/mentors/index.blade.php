@extends('admin.layouts.app')
@section('title', 'Mentors')
@section('heading', 'Mentors')
@section('content')

@php
$stats = [
    'total'     => \App\Models\User::where('role','mentor')->count(),
    'approved'  => \App\Models\User::where('role','mentor')->where('mentor_status','approved')->count(),
    'pending'   => \App\Models\User::where('role','mentor')->where('mentor_status','pending')->count(),
    'rejected'  => \App\Models\User::where('role','mentor')->where('mentor_status','rejected')->count(),
    'suspended' => \App\Models\User::where('role','mentor')->where('mentor_status','suspended')->count(),
    'deleted'   => \App\Models\User::where('role','mentor')->onlyTrashed()->count(),
    'pending_changes' => \App\Models\MentorPendingChange::where('status','pending')->count(),
];
@endphp

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Manage all mentors — approvals, profile changes, and assignments.</p>
        <div class="flex gap-2">
            @if($stats['pending_changes'] > 0)
            <a href="{{ route('admin.mentors.pending-changes') }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100 px-3 py-2 rounded-xl transition-colors">
                ✏️ Profile Change Requests
                <span class="bg-amber-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $stats['pending_changes'] }}</span>
            </a>
            @endif
            
            <a href="{{ route('admin.mentors.create') }}"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-xl transition-colors">
                + Add Mentor
            </a>
            <a href="{{ route('admin.mentor-approvals.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 px-3 py-2 rounded-xl transition-colors">
                Approval Queue
                @if($stats['pending'] > 0)
                <span class="bg-indigo-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $stats['pending'] }}</span>
                @endif
            </a>
            <a href="{{ route('admin.mentors.trashed') }}"
               class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 border border-gray-200 bg-white hover:bg-gray-50 px-3 py-2 rounded-xl transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Deleted
                @if($stats['deleted'] > 0)
                <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $stats['deleted'] }}</span>
                @endif
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-6 gap-3">
        @foreach([
            ['Total',      $stats['total'],     'bg-slate-50',   'text-slate-700',   'border-slate-200'],
            ['Approved',   $stats['approved'],  'bg-green-50',   'text-green-700',   'border-green-200'],
            ['Pending',    $stats['pending'],   'bg-amber-50',   'text-amber-700',   'border-amber-200'],
            ['Rejected',   $stats['rejected'],  'bg-red-50',     'text-red-600',     'border-red-200'],
            ['Suspended',  $stats['suspended'], 'bg-gray-50',    'text-gray-600',    'border-gray-200'],
            ['Deleted',    $stats['deleted'],   'bg-rose-50',    'text-rose-600',    'border-rose-200'],
        ] as [$label, $count, $bg, $tc, $bc])
        <div class="{{ $bg }} border {{ $bc }} rounded-xl p-4">
            <div class="text-2xl font-bold {{ $tc }}">{{ number_format($count) }}</div>
            <div class="text-xs font-medium text-gray-500 mt-0.5">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    {{-- Status tabs --}}
    <div class="flex gap-1 bg-gray-100 p-1 rounded-xl w-fit">
        @foreach(['' => 'All', 'approved' => 'Approved', 'pending' => 'Pending', 'rejected' => 'Rejected', 'suspended' => 'Suspended'] as $val => $label)
        <a href="{{ route('admin.mentors.index') }}{{ $val ? '?mentor_status=' . $val : '' }}{{ request('search') ? '&search='.request('search') : '' }}"
           class="px-4 py-2 rounded-lg text-xs font-semibold transition-all {{ request('mentor_status', '') === $val ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            @if(request('mentor_status'))
            <input type="hidden" name="mentor_status" value="{{ request('mentor_status') }}">
            @endif
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Name, email, company…"
                           class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Field</label>
                <input type="text" name="field" value="{{ request('field') }}" placeholder="Engineering, Design…"
                       class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all w-36">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Pending Changes</label>
                <div class="relative">
                    <select name="pending_changes" class="border border-gray-200 rounded-xl px-3.5 py-2 pr-8 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 appearance-none cursor-pointer transition-all">
                        <option value="">All</option>
                        <option value="1" {{ request('pending_changes') === '1' ? 'selected' : '' }}>Has Pending</option>
                    </select>
                    <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </div>
            </div>

            <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">Filter</button>
            <a href="{{ route('admin.mentors.index') }}" class="text-sm text-gray-500 border border-gray-200 px-3 py-2 rounded-xl hover:bg-gray-50 transition-colors">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">All Mentors</h3>
            <span class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full font-medium">{{ $mentors->total() }} records</span>
        </div>

        @if($mentors->isEmpty())
        <div class="py-20 text-center">
            <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-gray-500 font-medium text-sm">No mentors found</p>
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
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rating</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($mentors as $mentor)
                    @php
                    $statusColors = ['pending'=>'bg-amber-50 text-amber-700','approved'=>'bg-green-50 text-green-700','rejected'=>'bg-red-50 text-red-700','suspended'=>'bg-gray-100 text-gray-600'];
                    $statusDots   = ['pending'=>'bg-amber-400','approved'=>'bg-green-500','rejected'=>'bg-red-500','suspended'=>'bg-gray-400'];
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors group">

                        {{-- Mentor info --}}
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
                                        <span class="text-[9px] font-bold bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full">Changes</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400">{{ $mentor->email }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Professional --}}
                        <td class="px-4 py-4">
                            <div class="text-sm font-medium text-gray-800">{{ $mentor->designation ?? '—' }}</div>
                            <div class="text-xs text-gray-400">{{ $mentor->company ?? '' }}{{ $mentor->field ? ' · '.$mentor->field : '' }}</div>
                            <div class="text-xs text-gray-400">{{ $mentor->experience_years ?? 0 }} yrs exp</div>
                        </td>

                        {{-- Rate --}}
                        <td class="px-4 py-4">
                            <div class="text-sm font-bold text-gray-800">
                                {{ $mentor->rate_per_minute > 0 ? '₹' . $mentor->rate_per_minute . '/min' : 'Free' }}
                            </div>
                            @if($mentor->rate_per_minute > 0)
                            <div class="text-xs text-gray-400">₹{{ $mentor->rate_per_minute * 60 }}/hr</div>
                            @endif
                        </td>

                        {{-- Mentees count --}}
                        <td class="px-4 py-4">
                            <span class="text-sm font-bold text-gray-700">{{ $mentor->assignedMentees->count() }}</span>
                            <span class="text-xs text-gray-400"> mentees</span>
                        </td>

                        {{-- Rating --}}
                        <td class="px-4 py-4">
                            @if($mentor->rating > 0)
                            <div class="flex items-center gap-1">
                                <span class="text-amber-500 text-sm">★</span>
                                <span class="text-sm font-bold text-gray-700">{{ number_format($mentor->rating, 1) }}</span>
                            </div>
                            <div class="text-xs text-gray-400">{{ $mentor->total_sessions }} sessions</div>
                            @else
                            <span class="text-xs text-gray-400 italic">No ratings</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusColors[$mentor->mentor_status] ?? 'bg-gray-100 text-gray-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusDots[$mentor->mentor_status] ?? 'bg-gray-400' }}"></span>
                                {{ ucfirst($mentor->mentor_status) }}
                            </span>
                            @if(!$mentor->is_active && $mentor->mentor_status === 'approved')
                            <div class="text-[10px] text-gray-400 mt-0.5">Account inactive</div>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('admin.mentors.review', $mentor) }}"
                                   class="text-xs font-medium px-2.5 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg transition-colors">
                                    Review
                                </a>
                                @if($mentor->mentor_status === 'pending')
                                <form method="POST" action="{{ route('admin.mentors.approve', $mentor) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium px-2.5 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 rounded-lg transition-colors">
                                        Approve
                                    </button>
                                </form>
                                @endif
                                @if($mentor->mentor_status === 'approved')
                                <form method="POST" action="{{ route('admin.mentees.toggle-status', $mentor) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium px-2.5 py-1.5 {{ $mentor->is_active ? 'bg-gray-100 text-gray-600 hover:bg-gray-200' : 'bg-green-50 text-green-700 hover:bg-green-100' }} rounded-lg transition-colors">
                                        {{ $mentor->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.mentors.destroy', $mentor) }}"
                                      onsubmit="return confirm('Delete {{ addslashes($mentor->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-medium px-2.5 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex items-center justify-between px-5 py-3.5 border-t border-gray-100">
            <div class="text-xs text-gray-500">Showing {{ $mentors->firstItem() }}–{{ $mentors->lastItem() }} of {{ $mentors->total() }}</div>
            {{ $mentors->links() }}
        </div>
        @endif
    </div>
</div>

@endsection