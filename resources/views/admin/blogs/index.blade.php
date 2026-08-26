@extends('admin.layouts.app')
@section('title', 'Blogs')
@section('heading', 'Blogs')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <p class="text-sm text-gray-500">Manage blog posts for the Vedrix Insights library.</p>
        <a href="{{ route('admin.blogs.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition">
            + Add New
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-gray-100">
            <div class="flex flex-wrap gap-2" id="blogs-export-buttons"></div>
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title, category, author…"
                       class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 min-w-[220px]">
                <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
                    <option value="">All status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Search</button>
                @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('admin.blogs.index') }}" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Reset</a>
                @endif
            </form>
        </div>

        @if($blogs->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <div class="text-4xl mb-2">📰</div>
            <p class="font-medium text-gray-600">No blogs yet</p>
            <p class="text-sm mt-1">Click Add New to create your first post.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table id="blogs-table" class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-16">Sr. No.</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-24">Image</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Title</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Category</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Author</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Blog Date</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-28">Status</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600 w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($blogs as $index => $blog)
                    <tr class="hover:bg-gray-50/70">
                        <td class="px-5 py-4 text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-5 py-4">
                            @if($blog->imageUrl())
                                <img src="{{ $blog->imageUrl() }}" class="w-14 h-14 rounded-lg object-cover" alt="">
                            @else
                                <div class="w-14 h-14 rounded-lg bg-orange-50 flex items-center justify-center text-xl">📰</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-semibold text-gray-900 max-w-sm">
                            <div class="line-clamp-2">{{ $blog->title }}</div>
                        </td>
                        <td class="px-5 py-4 text-gray-600">{{ $blog->category ?: '—' }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $blog->author ?: '—' }}</td>
                        <td class="px-5 py-4 text-gray-600 whitespace-nowrap">{{ optional($blog->blog_date)->format('Y-m-d') ?: '—' }}</td>
                        <td class="px-5 py-4">
                            @if($blog->isActive())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.blogs.edit', $blog) }}"
                                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-green-600 text-white hover:bg-green-700" title="Edit">✎</a>
                                <form method="POST" action="{{ route('admin.blogs.destroy', $blog) }}"
                                      onsubmit="return confirm('Delete this blog post?')">
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
    #blogs-export-buttons .dt-button {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        padding: 0.4rem 0.85rem !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        color: #334155 !important;
        margin: 0 0.25rem 0 0 !important;
        box-shadow: none !important;
    }
    #blogs-export-buttons .dt-button:hover { background: #eff6ff !important; color: #1d4ed8 !important; border-color: #bfdbfe !important; }
    #blogs-table_wrapper .dataTables_filter,
    #blogs-table_wrapper .dataTables_length,
    #blogs-table_wrapper .dataTables_info,
    #blogs-table_wrapper .dataTables_paginate { display: none !important; }
</style>
<script>
$(function () {
    if (!$('#blogs-table').length) return;

    var table = $('#blogs-table').DataTable({
        paging: false,
        searching: false,
        info: false,
        ordering: true,
        order: [],
        dom: 'Bfrtip',
        buttons: {
            dom: { container: { tag: 'div', className: 'flex flex-wrap gap-1' } },
            buttons: [
                { extend: 'copyHtml5', text: 'Copy', exportOptions: { columns: [0, 2, 3, 4, 5, 6] } },
                { extend: 'excelHtml5', text: 'Excel', exportOptions: { columns: [0, 2, 3, 4, 5, 6] } },
                { extend: 'pdfHtml5', text: 'PDF', orientation: 'landscape', exportOptions: { columns: [0, 2, 3, 4, 5, 6] } },
                { extend: 'colvis', text: 'Column visibility' }
            ]
        },
        columnDefs: [
            { orderable: false, targets: [1, 7] }
        ]
    });

    table.buttons().container().appendTo('#blogs-export-buttons');
});
</script>
@endpush
