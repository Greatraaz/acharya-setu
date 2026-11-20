@extends('admin.layouts.app')
@section('title', 'Assessment Categories')
@section('heading', 'Assessment Categories')

@section('content')
@include('admin.partials.learning-ui')

<div class="learn-page space-y-4">
    @include('admin.assessments.partials.nav')

    <div class="learn-intro">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="learn-eyebrow">Learning · Assessments</span>
                <h2>Assessment Categories</h2>
                <p>Manage categories with score bands and status. Add questions from Assessment Questions next.</p>
            </div>
            @unless($tableMissing ?? false)
            <a href="{{ route('admin.assessments.create') }}" class="learn-btn-primary self-start">+ Add New</a>
            @endunless
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    @if($tableMissing ?? false)
    <div class="bg-amber-50 border border-amber-200 text-amber-900 text-sm px-4 py-3 rounded-xl">
        The assessments tables are missing. Run <code>php artisan migrate</code>.
    </div>
    @else
    <div class="learn-panel overflow-hidden">
        <div class="admin-table-toolbar admin-table-toolbar--end px-4 sm:px-5 py-4 border-b border-slate-100">
            <form method="GET" action="{{ route('admin.assessments.index') }}" class="admin-table-filters">
                <input type="search" name="search" value="{{ $search ?? request('search') }}"
                       placeholder="Search title or description…"
                       class="learn-focus border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm outline-none">
                <select name="status" class="learn-focus border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white outline-none">
                    <option value="">All status</option>
                    <option value="active" @selected(($status ?? request('status')) === 'active')>Active</option>
                    <option value="inactive" @selected(($status ?? request('status')) === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="learn-btn-primary !py-2">Search</button>
                @if(request()->filled('search') || request()->filled('status'))
                <a href="{{ route('admin.assessments.index') }}" class="learn-btn-ghost">Reset</a>
                @endif
            </form>
        </div>

        @if($assessments->isEmpty())
        <div class="py-16 text-center text-slate-400">
            <div class="text-4xl mb-2">📝</div>
            @if(request()->hasAny(['search', 'status']))
            <p class="font-medium text-slate-600">No assessments match your filters</p>
            <p class="text-sm mt-1">
                <a href="{{ route('admin.assessments.index') }}" class="text-amber-700 hover:underline">Clear filters</a>
                or try a different search term.
            </p>
            @else
            <p class="font-medium text-slate-600">No assessments yet</p>
            <p class="text-sm mt-1">Add an assessment category, then add questions.</p>
            @endif
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[720px]">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-slate-600 w-16">Sr. No.</th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-600 w-24">Image</th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-600">Assessment</th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-600">Description</th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-600 w-28">Status</th>
                        <th class="text-right px-5 py-3 font-semibold text-slate-600 w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($assessments as $assessment)
                    <tr class="hover:bg-amber-50/40 transition-colors">
                        <td class="px-5 py-4 text-slate-500">{{ method_exists($assessments, 'firstItem') ? $assessments->firstItem() + $loop->index : $loop->iteration }}</td>
                        <td class="px-5 py-4">
                            @if($assessment->imageUrl())
                                <img src="{{ $assessment->imageUrl() }}" class="w-14 h-14 rounded-lg object-cover" alt="">
                            @else
                                <div class="w-14 h-14 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-xl">📝</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-semibold text-slate-900 max-w-xs">
                            <div class="line-clamp-2">{{ $assessment->title }}</div>
                        </td>
                        <td class="px-5 py-4 text-slate-600 max-w-md">
                            <div class="line-clamp-2">{{ strip_tags($assessment->description ?? '—') }}</div>
                        </td>
                        <td class="px-5 py-4">
                            @if($assessment->isActive())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 whitespace-nowrap">Active</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 whitespace-nowrap">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.assessments.edit', $assessment) }}"
                                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-amber-500 text-white hover:bg-amber-600" title="Edit">✎</a>
                                <form method="POST" action="{{ route('admin.assessments.destroy', $assessment) }}"
                                      onsubmit="return confirm('Delete this assessment and all its questions?')">
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
        @include('admin.partials.pagination', ['paginator' => $assessments])
        @endif
    </div>
    @endif
</div>
@endsection
