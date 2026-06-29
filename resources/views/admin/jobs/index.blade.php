@extends('admin.layouts.app')
@section('title','Job Listings')
@section('heading','Job Listings')
@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Manage open positions and track applications.</p>
        <a href="{{ route('admin.jobs.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Post New Job
        </a>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-5 gap-4">
        @foreach([
            ['Total',        $stats['total'],               'bg-slate-50',  'text-slate-700',  'border-slate-200'],
            ['Active',       $stats['active'],              'bg-green-50',  'text-green-700',  'border-green-200'],
            ['Draft',        $stats['draft'],               'bg-yellow-50', 'text-yellow-700', 'border-yellow-200'],
            ['Closed',       $stats['closed'],              'bg-gray-50',   'text-gray-500',   'border-gray-200'],
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
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                       placeholder="Job title, department…">
            </div>
            @foreach([
                ['status','Status',[''=>'All','active'=>'Active','draft'=>'Draft','paused'=>'Paused','closed'=>'Closed']],
                ['job_type','Type',[''=>'All Types','full_time'=>'Full Time','part_time'=>'Part Time','contract'=>'Contract','internship'=>'Internship','freelance'=>'Freelance']],
                ['location_type','Location',[''=>'All','onsite'=>'On-site','remote'=>'Remote','hybrid'=>'Hybrid']],
            ] as [$name, $label, $opts])
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ $label }}</label>
                <select name="{{ $name }}" class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 bg-white appearance-none pr-8" style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236b7280' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E\");background-repeat:no-repeat;background-position:right 10px center;">
                    @foreach($opts as $val => $text)
                    <option value="{{ $val }}" {{ request($name) === $val ? 'selected' : '' }}>{{ $text }}</option>
                    @endforeach
                </select>
            </div>
            @endforeach
            @if($departments->count())
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Department</label>
                <select name="department" class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 bg-white">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition">Filter</button>
            <a href="{{ route('admin.jobs.index') }}" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">All Listings</h3>
            <span class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full">{{ $jobs->total() }} results</span>
        </div>

        @if($jobs->isEmpty())
        <div class="py-20 text-center">
            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <p class="text-gray-600 font-medium">No job listings found</p>
            <p class="text-gray-400 text-sm mt-1">Create your first listing to start accepting applications.</p>
            <a href="{{ route('admin.jobs.create') }}" class="inline-flex items-center gap-2 mt-4 bg-blue-600 text-white text-sm font-medium px-5 py-2.5 rounded-xl hover:bg-blue-700 transition">Post a Job</a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Position</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type & Level</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Salary</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Applications</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Deadline</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($jobs as $job)
                    @php
                    $statusColors = ['active'=>'bg-green-50 text-green-700','draft'=>'bg-yellow-50 text-yellow-700','paused'=>'bg-orange-50 text-orange-700','closed'=>'bg-gray-100 text-gray-500'];
                    $statusDots   = ['active'=>'bg-green-500','draft'=>'bg-yellow-400','paused'=>'bg-orange-400','closed'=>'bg-gray-400'];
                    $ltColors     = ['remote'=>'bg-teal-50 text-teal-700','hybrid'=>'bg-purple-50 text-purple-700','onsite'=>'bg-slate-100 text-slate-600'];
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors {{ $job->is_expired ? 'opacity-60' : '' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.jobs.show', $job) }}" class="text-sm font-semibold text-gray-900 hover:text-blue-600 transition">{{ $job->title }}</a>
                                        @if($job->is_featured)
                                        <span class="text-xs bg-amber-50 text-amber-600 px-1.5 py-0.5 rounded font-medium">⭐</span>
                                        @endif
                                        @if($job->is_expired)
                                        <span class="text-xs bg-red-50 text-red-600 px-1.5 py-0.5 rounded font-medium">Expired</span>
                                        @endif
                                    </div>
                                    @if($job->department)
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $job->department }}</div>
                                    @endif
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $job->openings }} opening{{ $job->openings > 1 ? 's' : '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-xs font-medium text-gray-700">{{ $job->job_type_label }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $job->experience_label }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-xs text-gray-700">{{ $job->location }}</div>
                            <span class="inline-block mt-1 text-xs font-medium px-2 py-0.5 rounded-full {{ $ltColors[$job->location_type] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ $job->location_type_label }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-xs font-semibold text-gray-800">{{ $job->salary_range }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <a href="{{ route('admin.jobs.show', $job) }}" class="flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:underline">
                                {{ $job->applications_count }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                        <td class="px-4 py-4">
                            @if($job->deadline)
                            <div class="text-xs {{ $job->is_expired ? 'text-red-500 font-semibold' : 'text-gray-700' }}">
                                {{ $job->deadline->format('d M Y') }}
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                {{ $job->is_expired ? 'Expired ' . $job->deadline->diffForHumans() : $job->deadline->diffForHumans() }}
                            </div>
                            @else
                            <span class="text-xs text-gray-400">No deadline</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusColors[$job->status] ?? 'bg-gray-100 text-gray-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusDots[$job->status] ?? 'bg-gray-400' }}"></span>
                                {{ ucfirst($job->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-1">
                                <form method="POST" action="{{ route('admin.jobs.toggle-status', $job) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium px-2.5 py-1.5 rounded-lg transition
                                        {{ $job->status === 'active' ? 'bg-orange-50 text-orange-700 hover:bg-orange-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                        {{ $job->status === 'active' ? 'Pause' : 'Publish' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.jobs.edit', $job) }}"
                                   class="text-xs font-medium px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition">Edit</a>
                                <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}" onsubmit="return confirm('Delete this job listing?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-medium px-2.5 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100">
            <div class="text-xs text-gray-500">Showing {{ $jobs->firstItem() }}–{{ $jobs->lastItem() }} of {{ $jobs->total() }}</div>
            {{ $jobs->links() }}
        </div>
        @endif
    </div>
</div>

@endsection