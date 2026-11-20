@extends('admin.layouts.app')
@section('title', 'Assessment Questions')
@section('heading', 'Assessment Questions')

@section('content')
@include('admin.partials.learning-ui')

<div class="learn-page space-y-4">
    @include('admin.assessments.partials.nav')

    <div class="learn-intro">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="learn-eyebrow">Learning · Questions</span>
                <h2>Assessment Questions</h2>
                <p>Questions use a fixed 0–3 scale. Option labels can be edited per question.</p>
            </div>
            <a href="{{ route('admin.assessment-questions.create') }}" class="learn-btn-primary self-start">+ Add New</a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="learn-panel overflow-hidden">
        <div class="admin-table-toolbar admin-table-toolbar--end px-4 sm:px-5 py-4 border-b border-slate-100">
            <form method="GET" action="{{ route('admin.assessment-questions.index') }}" class="admin-table-filters">
                <input type="search" name="search" value="{{ request('search') }}"
                       placeholder="Search question text…"
                       class="learn-focus border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm outline-none">
                <select name="assessment_id" class="learn-focus border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white outline-none">
                    <option value="">All assessments</option>
                    @foreach($assessments as $assessment)
                    <option value="{{ $assessment->id }}" @selected((string) request('assessment_id') === (string) $assessment->id)>{{ $assessment->title }}</option>
                    @endforeach
                </select>
                <button type="submit" class="learn-btn-primary !py-2">Search</button>
                @if(request()->filled('search') || request()->filled('assessment_id'))
                <a href="{{ route('admin.assessment-questions.index') }}" class="learn-btn-ghost">Reset</a>
                @endif
            </form>
        </div>

        @if($questions->isEmpty())
        <div class="py-16 text-center text-slate-400">
            <div class="text-4xl mb-2">❓</div>
            @if(request()->filled('search') || request()->filled('assessment_id'))
            <p class="font-medium text-slate-600">No questions match your filters</p>
            <p class="text-sm mt-1">
                <a href="{{ route('admin.assessment-questions.index') }}" class="text-amber-700 hover:underline">Clear filters</a>
                or try a different search term.
            </p>
            @else
            <p class="font-medium text-slate-600">No questions yet</p>
            <p class="text-sm mt-1">Create an assessment first, then add questions.</p>
            @endif
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-slate-600 w-16">Sr. No.</th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-600">Question</th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-600">Assessment</th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-600">Group</th>
                        <th class="text-right px-5 py-3 font-semibold text-slate-600 w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($questions as $question)
                    <tr class="hover:bg-amber-50/40 transition-colors">
                        <td class="px-5 py-4 text-slate-500">{{ method_exists($questions, 'firstItem') ? $questions->firstItem() + $loop->index : $loop->iteration }}</td>
                        <td class="px-5 py-4 font-medium text-slate-900 max-w-md">
                            <div class="line-clamp-2">{{ $question->question }}</div>
                        </td>
                        <td class="px-5 py-4 text-slate-600">{{ $question->assessment->title ?? '—' }}</td>
                        <td class="px-5 py-4 text-slate-500">{{ $question->category->name ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.assessment-questions.edit', $question) }}"
                                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-amber-500 text-white hover:bg-amber-600" title="Edit">✎</a>
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
