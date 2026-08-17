@extends('admin.layouts.app')
@section('title', 'Assessment Questions')
@section('heading', 'Assessment Questions')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <p class="text-sm text-gray-500">Questions use a fixed 0–3 scale. Option labels can be edited per question.</p>
        <a href="{{ route('admin.assessment-questions.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition">
            + Add New
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <form method="GET" class="bg-white border border-gray-200 rounded-2xl p-4 flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-48">
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Question text…"
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Assessment</label>
            <select name="assessment_id" class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm bg-white">
                <option value="">All</option>
                @foreach($assessments as $assessment)
                <option value="{{ $assessment->id }}" @selected((string) request('assessment_id') === (string) $assessment->id)>{{ $assessment->title }}</option>
                @endforeach
            </select>
        </div>
        <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl">Filter</button>
        <a href="{{ route('admin.assessment-questions.index') }}" class="text-sm text-gray-500 px-3 py-2 border border-gray-200 rounded-xl">Reset</a>
    </form>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Question</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Assessment</th>
                    <th class="text-right px-5 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($questions as $question)
                <tr class="hover:bg-gray-50/70">
                    <td class="px-5 py-4">
                        <div class="font-medium text-gray-900">{{ $question->question }}</div>
                    </td>
                    <td class="px-5 py-4 text-gray-600">{{ $question->assessment->title ?? '—' }}</td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.assessment-questions.edit', $question) }}"
                               class="px-3 py-1.5 rounded-lg border border-gray-200 text-green-700 hover:bg-green-50">Edit</a>
                            <form method="POST" action="{{ route('admin.assessment-questions.destroy', $question) }}"
                                  onsubmit="return confirm('Delete this question?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-5 py-16 text-center text-gray-400">
                        <p class="font-medium text-gray-600">No questions yet</p>
                        <p class="text-sm mt-1">Create an assessment first, then add questions.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($questions->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $questions->links() }}</div>
        @endif
    </div>
</div>
@endsection
