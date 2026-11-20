@extends('admin.layouts.app')
@section('title', 'Mentor Videos')
@section('heading', 'Mentor Videos')

@section('content')
@include('admin.partials.learning-ui')

<div class="learn-page space-y-5">
    <div class="learn-intro">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="learn-eyebrow">Learning · Videos</span>
                <h2>Mentor Videos</h2>
                <p>Video collections mentors share with mentees. Upload on behalf of a mentor or manage existing libraries.</p>
            </div>
            <a href="{{ route('admin.mentor-videos.create') }}" class="learn-btn-primary self-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Upload videos
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
        @foreach([
            ['Total', $stats['total'], '🎬'],
            ['Active', $stats['active'], '✅'],
            ['Inactive', $stats['inactive'], '⏸'],
            ['Files', $stats['files'], '📁'],
        ] as [$label, $count, $icon])
        <div class="learn-stat flex items-start justify-between gap-3">
            <div>
                <div class="learn-stat__value">{{ number_format($count) }}</div>
                <div class="learn-stat__label">{{ $label }}</div>
            </div>
            <span class="learn-stat__icon text-base">{{ $icon }}</span>
        </div>
        @endforeach
    </div>

    <div class="learn-panel p-4">
        <form method="GET" class="admin-filter-form">
            <div class="admin-filter-search">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or mentor…"
                           class="learn-focus w-full border border-gray-200 rounded-lg pl-9 pr-3 text-sm outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Mentor</label>
                <select name="mentor_id" class="learn-focus w-full border border-gray-200 rounded-lg px-3 text-sm bg-white outline-none">
                    <option value="">All mentors</option>
                    @foreach($mentors as $mentor)
                        <option value="{{ $mentor->id }}" @selected((string) request('mentor_id') === (string) $mentor->id)>{{ $mentor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="learn-focus w-full border border-gray-200 rounded-lg px-3 text-sm bg-white outline-none">
                    <option value="">All statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="admin-filter-actions">
                <button type="submit" class="learn-btn-primary !py-0 !px-4 h-10">Filter</button>
                @if(request()->filled('search') || request()->filled('status') || request()->filled('mentor_id'))
                <a href="{{ route('admin.mentor-videos.index') }}" class="learn-btn-ghost h-10">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="learn-panel overflow-hidden">
        @if($collections->isEmpty())
        <div class="py-16 text-center text-slate-400">
            <div class="text-4xl mb-2">🎬</div>
            <p class="font-medium text-slate-600">No mentor videos yet</p>
            <p class="text-sm mt-1">Upload a collection or wait for mentors to add theirs.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider w-16">Sr.</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Collection</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Mentor</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Files</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($collections as $item)
                    <tr class="hover:bg-amber-50/40 transition-colors">
                        <td class="px-5 py-4 text-slate-500">{{ $collections->firstItem() + $loop->index }}</td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900">{{ $item->name }}</div>
                            @if($item->description)
                            <div class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $item->description }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-slate-700">{{ $item->mentor->name ?? '—' }}</td>
                        <td class="px-4 py-4 text-slate-600">{{ $item->files_count ?? $item->files->count() }}</td>
                        <td class="px-4 py-4">
                            @if($item->is_active)
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-100">Active</span>
                            @else
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.mentor-videos.show', $item->id) }}" class="text-amber-700 hover:text-amber-900 text-xs font-semibold">View</a>
                                <a href="{{ route('admin.mentor-videos.edit', $item->id) }}" class="text-slate-600 hover:text-slate-900 text-xs font-semibold">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $collections])
        @endif
    </div>
</div>
@endsection
