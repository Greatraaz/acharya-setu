@extends('admin.layouts.app')
@section('title', $meta['label'].' Downloads')
@section('heading', $meta['label'].' Downloads')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <a href="{{ route($meta['admin'].'.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to {{ $meta['label'] }}</a>
            <p class="text-sm text-gray-500 mt-1">People who filled the download form on the website.</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="admin-table-toolbar px-5 py-4 border-b border-gray-100">
            <div class="admin-table-toolbar__exports" id="{{ $meta['leads_table_id'] }}-export-buttons"></div>
            <form method="GET" class="admin-table-filters">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone…"
                       class="border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 w-full sm:min-w-[240px]">
                <select name="item_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
                    <option value="">All {{ strtolower($meta['label']) }}</option>
                    @foreach($items as $row)
                        <option value="{{ $row->id }}" @selected((string) request('item_id') === (string) $row->id)>{{ $row->title }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Search</button>
                @if(request()->filled('search') || request()->filled('item_id'))
                    <a href="{{ route($meta['admin'].'.downloads') }}" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Reset</a>
                @endif
            </form>
        </div>

        @if($downloads->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <div class="text-4xl mb-2">📥</div>
            <p class="font-medium text-gray-600">No download requests yet</p>
            <p class="text-sm mt-1">Leads will appear here after visitors fill the form and download.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table id="{{ $meta['leads_table_id'] }}" class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-16">Sr. No.</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Name</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Email</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Phone</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Organisation</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Role</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">{{ $meta['singular'] }}</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 whitespace-nowrap">Submitted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($downloads as $row)
                    <tr class="hover:bg-gray-50/70">
                        <td class="px-5 py-4 text-gray-500">{{ $downloads->firstItem() + $loop->index }}</td>
                        <td class="px-5 py-4 font-semibold text-gray-900">{{ $row->full_name }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $row->email }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $row->phone ?: '—' }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $row->organization ?: '—' }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $row->role ?: '—' }}</td>
                        <td class="px-5 py-4 text-gray-800 max-w-xs">
                            <div class="line-clamp-2">{{ $row->item->title ?? '—' }}</div>
                        </td>
                        <td class="px-5 py-4 text-gray-600 whitespace-nowrap">{{ optional($row->created_at)->format('Y-m-d H:i') }}</td>
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
    #{{ $meta['leads_table_id'] }}_wrapper .dataTables_filter,
    #{{ $meta['leads_table_id'] }}_wrapper .dataTables_length,
    #{{ $meta['leads_table_id'] }}_wrapper .dataTables_info,
    #{{ $meta['leads_table_id'] }}_wrapper .dataTables_paginate { display: none !important; }
</style>
<script>
$(function () {
    var tableId = @json('#'.$meta['leads_table_id']);
    if (!$(tableId).length) return;

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
                { extend: 'copyHtml5', text: 'Copy', exportOptions: { columns: ':visible' } },
                { extend: 'excelHtml5', text: 'Excel', exportOptions: { columns: ':visible' } },
                { extend: 'pdfHtml5', text: 'PDF', orientation: 'landscape', exportOptions: { columns: ':visible' } },
                { extend: 'colvis', text: 'Column visibility', dropIcon: '' }
            ]
        }
    });

    table.buttons().container().appendTo(@json('#'.$meta['leads_table_id'].'-export-buttons'));
});
</script>
@endpush
