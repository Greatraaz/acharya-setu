@extends('admin.layouts.app')
@section('title', 'Mentee Task #'.$task->id)
@section('heading', $task->title)

@section('content')
<div class="max-w-3xl space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.mentee-tasks.index') }}" class="text-sm text-blue-600">← Back</a>
        <a href="{{ route('admin.mentee-tasks.edit', $task) }}" class="text-sm font-medium text-blue-600">Edit</a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl p-5 space-y-3">
        <div class="text-sm text-gray-500">Mentee: <strong class="text-gray-900">{{ $task->mentee->name ?? '—' }}</strong></div>
        <div class="text-sm text-gray-500">Mentor: <strong class="text-gray-900">{{ $task->mentor->name ?? '—' }}</strong></div>
        <div class="text-sm text-gray-500">Submission: {{ \App\Models\MenteeTask::SUBMISSION_TYPES[$task->submission_type] ?? $task->submission_type }}</div>
        <div class="text-sm text-gray-500">Status: <strong>{{ str_replace('_', ' ', ucfirst($task->uiStatus($progress))) }}</strong></div>
        @if($task->description)
        <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed pt-2 border-t border-gray-100">{{ $task->description }}</p>
        @endif
    </div>

    @if($progress && $progress->submission_status === 'submitted')
    <div class="bg-white border border-gray-200 rounded-2xl p-5 space-y-3">
        <h3 class="text-sm font-semibold text-gray-900">Review submission</h3>
        @if($progress->submission_text)<p class="text-sm text-gray-700">{{ $progress->submission_text }}</p>@endif
        @if($progress->submissionLink())<a href="{{ $progress->submissionLink() }}" target="_blank" class="text-sm text-blue-600">Open submission</a>@endif
        <form method="POST" action="{{ route('admin.mentee-tasks.review', $progress->id) }}" class="space-y-3">
            @csrf
            <textarea name="mentor_feedback" rows="3" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm" placeholder="Feedback">{{ old('mentor_feedback') }}</textarea>
            <div class="flex gap-2">
                <button type="submit" name="submission_status" value="approved" class="bg-blue-600 text-white text-sm px-4 py-2 rounded-xl">Approve</button>
                <button type="submit" name="submission_status" value="rejected" class="border border-gray-200 text-sm px-4 py-2 rounded-xl">Reject</button>
            </div>
        </form>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.mentee-tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm text-red-600">Delete task</button>
    </form>
</div>
@endsection
