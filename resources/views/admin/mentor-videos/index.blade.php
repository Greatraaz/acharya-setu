@extends('admin.layouts.app')
@section('title', 'Mentor Videos')
@section('heading', 'Mentor Videos')

@section('content')
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500">Video collections mentors share with mentees (not curriculum tasks). You can also upload on behalf of a mentor.</p>
        <a href="{{ route('admin.mentor-videos.create') }}"
           class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Upload videos
        </a>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
        @foreach([
            ['Total', $stats['total'], 'bg-slate-50', 'text-slate-700', 'border-slate-200'],
            ['Active', $stats['active'], 'bg-green-50', 'text-green-700', 'border-green-200'],
            ['Inactive', $stats['inactive'], 'bg-gray-50', 'text-gray-600', 'border-gray-200'],
            ['Files', $stats['files'], 'bg-indigo-50', 'text-indigo-700', 'border-indigo-200'],
        ] as [$label, $count, $bg, $tc, $bc])
        <div class="{{ $bg }} border {{ $bc }} rounded-xl p-4">
            <div class="text-2xl font-bold {{ $tc }}">{{ number_format($count) }}</div>
            <div class="text-xs font-medium text-gray-500 mt-0.5">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl p-4">
        <form method="GET" class="admin-filter-form">
            <div class="admin-filter-search">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or mentor…"
                           class="w-full border border-gray-200 rounded-lg pl-9 pr-3 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Mentor</label>
                <select name="mentor_id" class="w-full border border-gray-200 rounded-lg px-3 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                    <option value="">All mentors</option>
                    @foreach($mentors as $mentor)
                        <option value="{{ $mentor->id }}" @selected((string) request('mentor_id') === (string) $mentor->id)>{{ $mentor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-lg px-3 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                    <option value="">All statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="admin-filter-actions">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 rounded-lg">Filter</button>
                @if(request()->filled('search') || request()->filled('status') || request()->filled('mentor_id'))
                <a href="{{ route('admin.mentor-videos.index') }}" class="inline-flex items-center border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium px-4 rounded-lg">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        @if($collections->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <div class="text-4xl mb-2">🎬</div>
            <p class="font-medium text-gray-600">No mentor videos yet</p>
            <p class="text-sm mt-1">Upload a collection or wait for mentors to add theirs.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">Sr.</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Collection</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Mentor</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Files</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($collections as $item)
                    <tr class="hover:bg-gray-50/80">
                        <td class="px-5 py-4 text-gray-500">{{ $collections->firstItem() + $loop->index }}</td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-gray-900">{{ $item->name }}</div>
                            @if($item->description)
                            <div class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $item->description }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-medium text-gray-900">{{ $item->mentor?->name ?? '—' }}</div>
                            @if($item->mentor?->email)
                            <div class="text-xs text-gray-400 mt-0.5">{{ $item->mentor->email }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-gray-700">{{ $item->files->count() }}</td>
                        <td class="px-4 py-4">
                            @if($item->is_active)
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">Active</span>
                            @else
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.mentor-videos.show', $item->id) }}" class="text-blue-600 hover:underline text-xs font-medium">View</a>
                                <a href="{{ route('admin.mentor-videos.edit', $item->id) }}" class="text-indigo-600 hover:underline text-xs font-medium">Edit</a>
                                <form method="POST" action="{{ route('admin.mentor-videos.destroy', $item->id) }}" onsubmit="return confirm('Delete this collection?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-xs font-medium">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($collections->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $collections->links() }}</div>
        @endif
        @endif
    </div>
</div>
@endsection
