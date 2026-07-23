@extends('admin.layouts.app')
@section('title', 'Mentees')
@section('heading', 'Mentees')
@section('content')

@php
$stats = [
    'total'     => \App\Models\User::where('role','mentee')->count(),
    'active'    => \App\Models\User::where('role','mentee')->where('is_active',true)->count(),
    'with_mentor' => \App\Models\User::where('role','mentee')->whereNotNull('assigned_mentor_id')->count(),
    'onboarded' => \App\Models\User::where('role','mentee')->where('onboarding_completed',true)->count(),
    'deleted'   => \App\Models\User::where('role','mentee')->onlyTrashed()->count(),
];
@endphp

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Manage all registered mentees, assignments, and account status.</p>
        <div class="flex gap-2">
            <a href="{{ route('admin.mentees.create') }}"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 px-4 py-2 rounded-xl transition-colors">
                + Add Mentee
            </a>
            <a href="{{ route('admin.mentees.trashed') }}"
               class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 border border-gray-200 bg-white hover:bg-gray-50 px-3 py-2 rounded-xl transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Deleted
                @if($stats['deleted'] > 0)
                <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none">{{ $stats['deleted'] }}</span>
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
    <div class="grid grid-cols-5 gap-3">
        @foreach([
            ['Total',        $stats['total'],       'bg-slate-50',   'text-slate-700',   'border-slate-200'],
            ['Active',       $stats['active'],      'bg-green-50',   'text-green-700',   'border-green-200'],
            ['Onboarded',    $stats['onboarded'],   'bg-blue-50',    'text-blue-700',    'border-blue-200'],
            ['Has Mentor',   $stats['with_mentor'], 'bg-violet-50',  'text-violet-700',  'border-violet-200'],
            ['Deleted',      $stats['deleted'],     'bg-red-50',     'text-red-600',     'border-red-200'],
        ] as [$label, $count, $bg, $tc, $bc])
        <div class="{{ $bg }} border {{ $bc }} rounded-xl p-4">
            <div class="text-2xl font-bold {{ $tc }}">{{ number_format($count) }}</div>
            <div class="text-xs font-medium text-gray-500 mt-0.5">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Name, email…"
                           class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <div class="relative">
                    <select name="status" class="border border-gray-200 rounded-xl px-3.5 py-2 pr-8 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 appearance-none cursor-pointer transition-all">
                        <option value="">All</option>
                        <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Onboarding</label>
                <div class="relative">
                    <select name="onboarded" class="border border-gray-200 rounded-xl px-3.5 py-2 pr-8 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 appearance-none cursor-pointer transition-all">
                        <option value="">All</option>
                        <option value="1" {{ request('onboarded') === '1' ? 'selected' : '' }}>Completed</option>
                        <option value="0" {{ request('onboarded') === '0' ? 'selected' : '' }}>Pending</option>
                    </select>
                    <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Mentor Assigned</label>
                <div class="relative">
                    <select name="assigned" class="border border-gray-200 rounded-xl px-3.5 py-2 pr-8 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 appearance-none cursor-pointer transition-all">
                        <option value="">All</option>
                        <option value="yes" {{ request('assigned') === 'yes' ? 'selected' : '' }}>Assigned</option>
                        <option value="no"  {{ request('assigned') === 'no'  ? 'selected' : '' }}>Unassigned</option>
                    </select>
                    <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </div>
            </div>

            <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">Filter</button>
            <a href="{{ route('admin.mentees.index') }}" class="text-sm text-gray-500 border border-gray-200 px-3 py-2 rounded-xl hover:bg-gray-50 transition-colors">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">All Mentees</h3>
            <span class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full font-medium">{{ $mentees->total() }} records</span>
        </div>

        @if($mentees->isEmpty())
        <div class="py-20 text-center">
            <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <p class="text-gray-500 font-medium text-sm">No mentees found</p>
            <p class="text-gray-400 text-xs mt-1">Try adjusting your filters.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-left">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Mentee</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Education</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Mentor</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Onboarding</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($mentees as $mentee)
                    <tr class="hover:bg-gray-50 transition-colors group">

                        {{-- Mentee info --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if($mentee->avatar_url)
                                <img src="{{ $mentee->avatar_url }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                                @else
                                <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-700 font-bold text-sm flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($mentee->name, 0, 1)) }}
                                </div>
                                @endif
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $mentee->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $mentee->email }}</div>
                                    @if($mentee->phone)
                                    <div class="text-xs text-gray-400">{{ $mentee->phone }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Education --}}
                        <td class="px-4 py-4">
                            @if($mentee->college)
                            <div class="text-sm text-gray-800 font-medium">{{ $mentee->college }}</div>
                            <div class="text-xs text-gray-400">{{ $mentee->field ?? '' }}{{ $mentee->year ? ' · Year '.$mentee->year : '' }}</div>
                            @elseif($mentee->field)
                            <div class="text-xs text-gray-600 bg-gray-100 px-2 py-0.5 rounded-full w-fit">{{ $mentee->field }}</div>
                            @else
                            <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>

                        {{-- Mentor assigned --}}
                        <td class="px-4 py-4">
                            @if($mentee->assignedMentor)
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-bold flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($mentee->assignedMentor->name, 0, 1)) }}
                                </div>
                                <span class="text-xs font-medium text-gray-700">{{ $mentee->assignedMentor->name }}</span>
                            </div>
                            @else
                            <button onclick="openAssignModal({{ $mentee->id }}, '{{ addslashes($mentee->name) }}')"
                                    class="text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 px-2 py-1 rounded-lg transition-colors border border-blue-100">
                                + Assign Mentor
                            </button>
                            @endif
                        </td>

                        {{-- Onboarding --}}
                        <td class="px-4 py-4">
                            @if($mentee->onboarding_completed)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-50 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>Complete
                            </span>
                            @else
                            <div class="flex items-center gap-2">
                                <div class="w-16 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-amber-400 rounded-full" style="width: {{ $mentee->onboarding_progress }}%"></div>
                                </div>
                                <span class="text-xs text-amber-600 font-medium">Step {{ $mentee->onboarding_step }}/4</span>
                            </div>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full
                                {{ $mentee->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $mentee->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                {{ $mentee->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        {{-- Joined --}}
                        <td class="px-4 py-4">
                            <div class="text-xs text-gray-600">{{ $mentee->created_at->format('d M Y') }}</div>
                            <div class="text-[10px] text-gray-400">{{ $mentee->created_at->diffForHumans() }}</div>
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('admin.mentees.show', $mentee) }}"
                                   class="text-xs font-medium px-2.5 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg transition-colors">
                                    View
                                </a>
                                <a href="{{ route('admin.mentees.journey', $mentee) }}"
                                   class="text-xs font-medium px-2.5 py-1.5 bg-violet-50 text-violet-700 hover:bg-violet-100 rounded-lg transition-colors"
                                   title="Open 6-Month Journey">
                                    Journey
                                </a>
                                <a href="{{ route('admin.mentee.edit', $mentee) }}"
                                   class="text-xs font-medium px-2.5 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg transition-colors">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.mentees.toggle-status', $mentee) }}">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs font-medium px-2.5 py-1.5 rounded-lg transition-colors
                                            {{ $mentee->is_active ? 'bg-gray-100 text-gray-600 hover:bg-gray-200' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                        {{ $mentee->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.mentees.destroy', $mentee) }}"
                                      onsubmit="return confirm('Delete {{ addslashes($mentee->name) }}? They can be restored later.')">
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
            <div class="text-xs text-gray-500">Showing {{ $mentees->firstItem() }}–{{ $mentees->lastItem() }} of {{ $mentees->total() }}</div>
            {{ $mentees->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Assign Mentor Modal --}}
<div id="assign-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <h3 class="text-sm font-bold text-gray-900 mb-1">Assign Mentor</h3>
        <p class="text-xs text-gray-500 mb-4" id="assign-modal-mentee">Select a mentor for this mentee.</p>
        <form id="assign-form" method="POST">
            @csrf
            <div class="relative mb-4">
                <select name="mentor_id" required
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 pr-8 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 appearance-none cursor-pointer">
                    <option value="">Select a mentor…</option>
                    @foreach(\App\Models\User::mentors()->active()->approved()->orderBy('name')->get() as $mentor)
                    <option value="{{ $mentor->id }}">{{ $mentor->name }} — {{ $mentor->designation ?? $mentor->field ?? 'Mentor' }}</option>
                    @endforeach
                </select>
                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </span>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">Assign</button>
                <button type="button" onclick="document.getElementById('assign-modal').classList.add('hidden')"
                        class="flex-1 border border-gray-200 text-gray-600 text-sm py-2.5 rounded-xl hover:bg-gray-50 transition-colors">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAssignModal(menteeId, name) {
    document.getElementById('assign-modal-mentee').textContent = 'Assigning mentor to: ' + name;
    document.getElementById('assign-form').action = `/admin/mentees/${menteeId}/assign-mentor`;
    document.getElementById('assign-modal').classList.remove('hidden');
}
</script>

@endsection