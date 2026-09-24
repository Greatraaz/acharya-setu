@extends('admin.layouts.app')
@section('title', 'Mentor Videos')
@section('heading', 'Mentor Videos')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <p class="text-sm text-gray-500">Video collections mentors share with mentees (not curriculum tasks). You can also upload on behalf of a mentor.</p>
        <a href="{{ route('admin.mentor-videos.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition">
            + Upload videos
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="admin-table-toolbar px-5 py-4 border-b border-gray-100">
            <form method="GET" class="admin-table-filters flex flex-wrap gap-2 items-center w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or mentor…"
                       class="border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 w-full sm:min-w-[200px] flex-1">
                <select name="mentor_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
                    <option value="">All mentors</option>
                    @foreach($mentors as $mentor)
                        <option value="{{ $mentor->id }}" @selected((string) request('mentor_id') === (string) $mentor->id)>{{ $mentor->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
                    <option value="">All status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Search</button>
                @if(request()->filled('search') || request()->filled('status') || request()->filled('mentor_id'))
                    <a href="{{ route('admin.mentor-videos.index') }}" class="text-sm text-gray-500 border border-gray-200 px-3 py-2 rounded-lg hover:bg-gray-50">Reset</a>
                @endif
            </form>
        </div>

        @if($collections->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <div class="text-4xl mb-2">🎬</div>
            <p class="font-medium text-gray-600">No mentor videos yet</p>
            <p class="text-sm mt-1">Upload a collection or wait for mentors to add theirs.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-16">Sr.</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Collection</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Mentor</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Files</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Status</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($collections as $item)
                    <tr class="hover:bg-gray-50/70">
                        <td class="px-5 py-4 text-gray-500">{{ $collections->firstItem() + $loop->index }}</td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-gray-900">{{ $item->name }}</div>
                            @if($item->description)
                            <div class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $item->description }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-medium">{{ $item->mentor?->name ?? '—' }}</div>
                            <div class="text-xs text-gray-400">{{ $item->mentor?->email }}</div>
                        </td>
                        <td class="px-5 py-4">{{ $item->files->count() }}</td>
                        <td class="px-5 py-4">
                            @if($item->is_active)
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
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
        <div class="px-5 py-4 border-t border-gray-100">{{ $collections->links() }}</div>
        @endif
        @endif
    </div>
</div>
@endsection
