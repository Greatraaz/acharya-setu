@extends('admin.layouts.app')
@section('title', 'Assessment Categories')
@section('heading', 'Assessment Categories')

@section('content')
<div class="space-y-4">
    @include('admin.assessments.partials.nav')

    <div class="flex items-center justify-between flex-wrap gap-3">
        <p class="text-sm text-gray-500">Group questions under a category for each assessment.</p>
        <a href="{{ route('admin.assessment-categories.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition">
            + Add New
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Category</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Assessment</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Questions</th>
                    <th class="text-right px-5 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $category)
                <tr class="hover:bg-gray-50/70">
                    <td class="px-5 py-4 font-semibold text-gray-900">{{ $category->name }}</td>
                    <td class="px-5 py-4 text-gray-600">{{ $category->assessment->title ?? '—' }}</td>
                    <td class="px-5 py-4 text-gray-600">{{ $category->questions_count }}</td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.assessment-categories.edit', $category) }}"
                               class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">Edit</a>
                            <form method="POST" action="{{ route('admin.assessment-categories.destroy', $category) }}"
                                  onsubmit="return confirm('Delete this category and its questions?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-16 text-center text-gray-400">
                        <p class="font-medium text-gray-600">No categories yet</p>
                        <p class="text-sm mt-1">Create an assessment first, then add categories.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($categories->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $categories->links() }}</div>
        @endif
    </div>
</div>
@endsection
