@extends('admin.layouts.app')
@section('title', $meta['label'])
@section('heading', $meta['label'])

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <p class="text-sm text-gray-500">
            Manage {{ strtolower($meta['label']) }} for the Vedrix Insights library.
            @if(!empty($meta['gated'])) Downloads are gated behind a lead form.@endif
        </p>
        <div class="flex items-center gap-2">
            @if(!empty($meta['gated']))
            <a href="{{ route($meta['admin'].'.downloads') }}"
               class="inline-flex items-center gap-2 border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2.5 rounded-lg transition">
                Download leads
            </a>
            @endif
            <a href="{{ route($meta['admin'].'.create') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition">
                + Add New
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="admin-table-toolbar px-5 py-4 border-b border-gray-100">
            <div class="admin-table-toolbar__exports" id="{{ $meta['table_id'] }}-export-buttons"></div>
            <form method="GET" class="admin-table-filters">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title…"
                       class="border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 w-full sm:min-w-[220px]">
                <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
                    <option value="">All status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Search</button>
                @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route($meta['admin'].'.index') }}" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Reset</a>
                @endif
            </form>
        </div>

        @if($items->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <div class="text-4xl mb-2">{{ $meta['icon'] }}</div>
            <p class="font-medium text-gray-600">No {{ strtolower($meta['label']) }} yet</p>
            <p class="text-sm mt-1">Click Add New to create your first {{ strtolower($meta['singular']) }}.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table id="{{ $meta['table_id'] }}" class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-16">Sr. No.</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-24">Image</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Title</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Description</th>
                        @if(!empty($meta['gated']))
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Downloads</th>
                        @endif
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 whitespace-nowrap">Date</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-28">Status</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600 w-36">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($items as $row)
                    <tr class="hover:bg-gray-50/70">
                        <td class="px-5 py-4 text-gray-500">{{ $items->firstItem() + $loop->index }}</td>
                        <td class="px-5 py-4">
                            @if($row->imageUrl())
                                <img src="{{ $row->imageUrl() }}" class="w-14 h-14 rounded-lg object-cover" alt="">
                            @else
                                <div class="w-14 h-14 rounded-lg bg-orange-50 flex items-center justify-center text-xl">{{ $meta['icon'] }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-semibold text-gray-900 max-w-xs">
                            <div class="line-clamp-2">{{ $row->title }}</div>
                        </td>
                        <td class="px-5 py-4 text-gray-600 max-w-md">
                            <div class="line-clamp-3 text-xs leading-relaxed">{{ $row->excerpt(40) }}</div>
                        </td>
                        @if(!empty($meta['gated']))
                        <td class="px-5 py-4">
                            <a href="{{ route($meta['admin'].'.downloads', ['item_id' => $row->id]) }}"
                               class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-orange-50 text-orange-700 hover:bg-orange-100">
                                {{ $row->downloads_count }} {{ \Illuminate\Support\Str::plural('lead', $row->downloads_count) }}
                            </a>
                        </td>
                        @endif
                        <td class="px-5 py-4 text-gray-600 whitespace-nowrap">{{ optional($row->created_at)->format('Y-m-d H:i') ?: '—' }}</td>
                        <td class="px-5 py-4">
                            @if($row->isActive())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route($meta['admin'].'.edit', $row) }}"
                                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-green-600 text-white hover:bg-green-700" title="Edit">✎</a>
                                <form method="POST" action="{{ route($meta['admin'].'.destroy', $row) }}"
                                      onsubmit="return confirm('Delete this {{ strtolower($meta['singular']) }}?')">
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
        @include('admin.partials.pagination', ['paginator' => $items])
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
    #{{ $meta['table_id'] }}_wrapper .dataTables_filter,
    #{{ $meta['table_id'] }}_wrapper .dataTables_length,
    #{{ $meta['table_id'] }}_wrapper .dataTables_info,
    #{{ $meta['table_id'] }}_wrapper .dataTables_paginate { display: none !important; }
</style>
<script>
$(function () {
    var tableId = @json('#'.$meta['table_id']);
    if (!$(tableId).length) return;
    var gated = @json(!empty($meta['gated']));
    var exportCols = gated ? [0, 2, 3, 4, 5, 6] : [0, 2, 3, 4, 5];
    var noOrder = gated ? [1, 7] : [1, 6];

    var table = $(tableId).DataTable({
        paging: false,
        searching: false,
        info: false,
        ordering: true,
        order: [],
        dom: 'Bfrtip',
        buttons: {
            dom: { container: { tag: 'div', className: 'flex flex-wrap gap-1' } },
            buttons: [
                { extend: 'copyHtml5', text: 'Copy', exportOptions: { columns: exportCols } },
                { extend: 'excelHtml5', text: 'Excel', exportOptions: { columns: exportCols } },
                { extend: 'pdfHtml5', text: 'PDF', orientation: 'landscape', exportOptions: { columns: exportCols } },
                { extend: 'colvis', text: 'Column visibility', dropIcon: '' }
            ]
        },
        columnDefs: [
            { orderable: false, targets: noOrder }
        ]
    });

    table.buttons().container().appendTo(@json('#'.$meta['table_id'].'-export-buttons'));
});
</script>
@endpush
