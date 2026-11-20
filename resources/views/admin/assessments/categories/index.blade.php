@extends('admin.layouts.app')
@section('title', 'Assessment Categories')
@section('heading', 'Assessment Categories')

@section('content')
@include('admin.partials.learning-ui')

<div class="learn-page space-y-4">
    @include('admin.assessments.partials.nav')

    <div class="learn-intro">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="learn-eyebrow">Learning · Categories</span>
                <h2>Assessment Categories</h2>
                <p>Group questions under a category for each assessment.</p>
            </div>
            <a href="{{ route('admin.assessment-categories.create') }}" class="learn-btn-primary self-start">
                + Add New
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="learn-panel overflow-hidden">
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
                <tr class="hover:bg-amber-50/40 transition-colors">
                    <td class="px-5 py-4 font-semibold text-gray-900">{{ $category->name }}</td>
                    <td class="px-5 py-4 text-gray-600">{{ $category->assessment->title ?? '—' }}</td>
                    <td class="px-5 py-4 text-gray-600">{{ $category->questions_count }}</td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.assessment-categories.edit', $category) }}"
                               class="learn-btn-ghost !py-1.5 !px-3">Edit</a>
                            <form method="POST" action="{{ route('admin.assessment-categories.destroy', $category) }}"
                                  onsubmit="return confirm('Delete this category and its questions?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="learn-btn-ghost !py-1.5 !px-3 !text-red-600 !border-red-200 hover:!bg-red-50">Delete</button>
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