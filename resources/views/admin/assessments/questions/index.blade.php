@extends('admin.layouts.app')
@section('title', 'Assessment Questions')
@section('heading', 'Assessment Questions')

@section('content')
<div class="space-y-4">
    @include('admin.assessments.partials.nav')

    <div class="flex items-center justify-between flex-wrap gap-3">
        <p class="text-sm text-gray-500">Questions use a fixed 0–3 scale. Option labels can be edited per question.</p>
        <a href="{{ route('admin.assessment-questions.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition">
            + Add New
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="admin-table-toolbar px-4 sm:px-5 py-4 border-b border-gray-100">
            <div></div>
            <form method="GET" action="{{ route('admin.assessment-questions.index') }}" class="admin-table-filters">
                <input type="search" name="search" value="{{ request('search') }}"
                       placeholder="Search question text…"
                       class="border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 w-full sm:min-w-[220px]">
                <select name="assessment_id" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white">
                    <option value="">All assessments</option>
                    @foreach($assessments as $assessment)
                    <option value="{{ $assessment->id }}" @selected((string) request('assessment_id') === (string) $assessment->id)>{{ $assessment->title }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Search</button>
                @if(request()->filled('search') || request()->filled('assessment_id'))
                <a href="{{ route('admin.assessment-questions.index') }}" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Reset</a>
                @endif
            </form>
        </div>

        @if($questions->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <div class="text-4xl mb-2">❓</div>
            @if(request()->filled('search') || request()->filled('assessment_id'))
            <p class="font-medium text-gray-600">No questions match your filters</p>
            <p class="text-sm mt-1">
                <a href="{{ route('admin.assessment-questions.index') }}" class="text-blue-600 hover:underline">Clear filters</a>
                or try a different search term.
            </p>
            @else
            <p class="font-medium text-gray-600">No questions yet</p>
            <p class="text-sm mt-1">Create an assessment first, then add questions.</p>
            @endif
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-16">Sr. No.</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Question</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Assessment</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600 w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($questions as $question)
                    <tr class="hover:bg-gray-50/70">
                        <td class="px-5 py-4 text-gray-500">{{ method_exists($questions, 'firstItem') ? $questions->firstItem() + $loop->index : $loop->iteration }}</td>
                        <td class="px-5 py-4 font-medium text-gray-900 max-w-md">
                            <div class="line-clamp-2">{{ $question->question }}</div>
                        </td>
                        <td class="px-5 py-4 text-gray-600">{{ $question->assessment->title ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.assessment-questions.edit', $question) }}"
                                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-green-600 text-white hover:bg-green-700" title="Edit">✎</a>
                                <form method="POST" action="{{ route('admin.assessment-questions.destroy', $question) }}"
                                      onsubmit="return confirm('Delete this question?')">
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
        @include('admin.partials.pagination', ['paginator' => $questions])
        @endif
    </div>
</div>
@endsection
