@extends('admin.layouts.app')
@section('title', 'Download Centre')
@section('heading', 'Download Centre')

@section('content')
<div class="admin-page space-y-5">
    <div class="learn-intro">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="learn-eyebrow">Utilities · Downloads</span>
                <h2>Download Centre</h2>
                <p>Manage downloadable resources for the Insights library.</p>
            </div>
            <a href="{{ route('admin.download-centres.create') }}" class="learn-btn-primary self-start">
                + Add New
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="learn-panel overflow-hidden">
        <div class="admin-table-toolbar px-4 sm:px-5 py-4 border-b border-gray-100">
            <form method="GET" class="admin-table-filters w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or slug…"
                       class="border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm outline-none learn-focus w-full sm:min-w-[220px]">
                <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
                    <option value="">All status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="learn-btn-primary">Search</button>
                @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('admin.download-centres.index') }}" class="learn-btn-soft">Reset</a>
                @endif
            </form>
        </div>

        @if($downloads->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <div class="text-4xl mb-2">⬇️</div>
            <p class="font-medium text-gray-600">No downloads yet</p>
            <p class="text-sm mt-1">Click Add New to upload your first resource.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-100">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-16">Sr. No.</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-24">Image</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Title</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Slug</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">File</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-28">Status</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600 w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($downloads as $item)
                    <tr class="hover:bg-amber-50/40">
                        <td class="px-5 py-4 text-gray-500">{{ $downloads->firstItem() + $loop->index }}</td>
                        <td class="px-5 py-4">
                            @if($item->imageUrl())
                                <img src="{{ $item->imageUrl() }}" class="w-14 h-14 rounded-lg object-cover" alt="">
                            @else
                                <div class="w-14 h-14 rounded-lg bg-orange-50 flex items-center justify-center text-xl">⬇️</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-semibold text-gray-900 max-w-xs">
                            <div class="line-clamp-2">{{ $item->title }}</div>
                        </td>
                        <td class="px-5 py-4 text-gray-500 text-xs">{{ $item->slug }}</td>
                        <td class="px-5 py-4 text-gray-600 uppercase text-xs font-semibold">{{ $item->documentExtension() }}</td>
                        <td class="px-5 py-4">
                            @if($item->isActive())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.download-centres.edit', $item) }}"
                                   class="learn-btn-edit" title="Edit">✎</a>
                                <form method="POST" action="{{ route('admin.download-centres.destroy', $item) }}"
                                      onsubmit="return confirm('Delete this download item?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="learn-btn-danger" title="Delete">🗑</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $downloads])
        @endif
    </div>
</div>
@endsection
