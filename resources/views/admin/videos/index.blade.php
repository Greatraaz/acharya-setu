@extends('admin.layouts.app')
@section('title', 'Videos')
@section('heading', 'Videos')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <p class="text-sm text-gray-500">Manage YouTube videos for the Vedrix Insights video library.</p>
        <a href="{{ route('admin.videos.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition">
            + Add New
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="admin-table-toolbar px-5 py-4 border-b border-gray-100">
            <div class="admin-table-toolbar__exports" id="videos-export-buttons"></div>
            <form method="GET" class="admin-table-filters">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or slug…"
                       class="border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 w-full sm:min-w-[220px]">
                <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
                    <option value="">All status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Search</button>
                @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('admin.videos.index') }}" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Reset</a>
                @endif
            </form>
        </div>

        @if($videos->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <div class="text-4xl mb-2">🎬</div>
            <p class="font-medium text-gray-600">No videos yet</p>
            <p class="text-sm mt-1">Click Add New to publish your first video.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table id="videos-table" class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-16">Sr. No.</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-24">Thumb</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Title</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Slug</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-28">Status</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600 w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($videos as $item)
                    <tr class="hover:bg-gray-50/70">
                        <td class="px-5 py-4 text-gray-500">{{ $videos->firstItem() + $loop->index }}</td>
                        <td class="px-5 py-4">
                            @if($item->thumbnailUrl())
                                <img src="{{ $item->thumbnailUrl() }}" class="w-14 h-10 rounded object-cover" alt="">
                            @else
                                <div class="w-14 h-10 rounded bg-orange-50 flex items-center justify-center text-lg">🎬</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-semibold text-gray-900 max-w-xs">
                            <div class="line-clamp-2">{{ $item->title }}</div>
                        </td>
                        <td class="px-5 py-4 text-gray-600">{{ $item->slug }}</td>
                        <td class="px-5 py-4">
                            @if($item->isActive())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.videos.edit', $item) }}"
                                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-green-600 text-white hover:bg-green-700" title="Edit">✎</a>
                                <form method="POST" action="{{ route('admin.videos.destroy', $item) }}"
                                      onsubmit="return confirm('Delete this video?')">
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
        @if(method_exists($videos, 'hasPages') && $videos->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $videos->links() }}
            </div>
        @endif
        @endif
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
<style>
    #videos-table_wrapper .dataTables_filter,
    #videos-table_wrapper .dataTables_length,
    #videos-table_wrapper .dataTables_info,
    #videos-table_wrapper .dataTables_paginate { display: none !important; }
</style>
<script>
$(function () {
    if (!$('#videos-table').length) return;
    var table = $('#videos-table').DataTable({
        paging: false, searching: false, info: false, ordering: true, order: [],
        dom: 'Bfrtip',
        buttons: {
            dom: { container: { tag: 'div', className: 'flex flex-wrap gap-1' } },
            buttons: [
                { extend: 'copyHtml5', text: 'Copy', exportOptions: { columns: [0, 2, 3, 4] } },
                { extend: 'excelHtml5', text: 'Excel', exportOptions: { columns: [0, 2, 3, 4] } },
                { extend: 'pdfHtml5', text: 'PDF', orientation: 'landscape', exportOptions: { columns: [0, 2, 3, 4] } },
                { extend: 'colvis', text: 'Column visibility', dropIcon: '' }
            ]
        },
        columnDefs: [{ orderable: false, targets: [1, 5] }]
    });
    table.buttons().container().appendTo('#videos-export-buttons');
});
</script>
@endpush
