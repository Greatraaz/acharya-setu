@extends('admin.layouts.app')
@section('title', 'Assessment Categories')
@section('heading', 'Assessment')

@section('content')
<div class="space-y-4">
    @include('admin.assessments.partials.nav')

    <div class="flex items-center justify-between flex-wrap gap-3">
        <p class="text-sm text-gray-500">Manage assessment categories with score bands and status.</p>
        @unless($tableMissing ?? false)
        <a href="{{ route('admin.assessments.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition">
            + Add New
        </a>
        @endunless
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    @if($tableMissing ?? false)
    <div class="bg-amber-50 border border-amber-200 text-amber-900 text-sm px-4 py-3 rounded-xl">
        The assessments tables are missing. Run <code>php artisan migrate</code>.
    </div>
    @else
    <form method="GET" action="{{ route('admin.assessments.index') }}" class="bg-white border border-gray-200 rounded-2xl p-4 flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" name="search" value="{{ $search ?? request('search') }}"
                       placeholder="Title or description…"
                       class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all">
            </div>
        </div>

        <div class="min-w-[140px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status"
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 cursor-pointer">
                <option value="">All</option>
                <option value="active" @selected(($status ?? request('status')) === 'active')>Active</option>
                <option value="inactive" @selected(($status ?? request('status')) === 'inactive')>Inactive</option>
            </select>
        </div>

        <button type="submit"
                class="inline-flex items-center gap-1.5 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
            Search
        </button>

        @if(request()->filled('search') || request()->filled('status'))
        <a href="{{ route('admin.assessments.index') }}"
           class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 px-3 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors">
            Clear
        </a>
        @endif
    </form>

    @if($assessments->total() > 0 || request()->hasAny(['search', 'status']))
    <p class="text-xs text-gray-500">
        {{ $assessments->total() }} {{ \Illuminate\Support\Str::plural('assessment', $assessments->total()) }}
        @if($search ?? request('search'))
            matching “{{ $search ?? request('search') }}”
        @endif
    </p>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 w-16">Sr. No.</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 w-24">Image</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Assessment</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Description</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 w-28">Status</th>
                    <th class="text-right px-5 py-3 font-semibold text-gray-600 w-32">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($assessments as $assessment)
                <tr class="hover:bg-gray-50/70">
                    <td class="px-5 py-4 text-gray-500">{{ method_exists($assessments, 'firstItem') ? $assessments->firstItem() + $loop->index : $loop->iteration }}</td>
                    <td class="px-5 py-4">
                        @if($assessment->imageUrl())
                            <img src="{{ $assessment->imageUrl() }}" class="w-14 h-14 rounded-lg object-cover" alt="">
                        @else
                            <div class="w-14 h-14 rounded-lg bg-orange-50 flex items-center justify-center text-xl">📝</div>
                        @endif
                    </td>
                    <td class="px-5 py-4 font-semibold text-gray-900">{{ $assessment->title }}</td>
                    <td class="px-5 py-4 text-gray-600">
                        <div class="line-clamp-+2 max-w-xl">{{ strip_tags($assessment->description ?? '—') }}</div>
                    </td>
                    <td class="px-5 py-4">
                        @if($assessment->isActive())
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.assessments.edit', $assessment) }}"
                               class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-green-600 text-white hover:bg-green-700" title="Edit">
                                ✎
                            </a>
                            <form method="POST" action="{{ route('admin.assessments.destroy', $assessment) }}"
                                  onsubmit="return confirm('Delete this assessment and all its questions?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-600 text-white hover:bg-red-700" title="Delete">
                                    🗑
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center text-gray-400">
                        <div class="text-4xl mb-2">📝</div>
                        @if(request()->hasAny(['search', 'status']))
                        <p class="font-medium text-gray-600">No assessments match your filters</p>
                        <p class="text-sm mt-1">
                            <a href="{{ route('admin.assessments.index') }}" class="text-blue-600 hover:underline">Clear filters</a>
                            or try a different search term.
                        </p>
                        @else
                        <p class="font-medium text-gray-600">No assessments yet</p>
                        <p class="text-sm mt-1">Add an assessment category, then add questions.</p>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $assessments])
    @endif
</div>
@endsection
