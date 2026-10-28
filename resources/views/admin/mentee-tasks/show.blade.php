@extends('admin.layouts.app')
@section('title', $task->title)
@section('heading', $task->title)

@php
    $uiStatus = $task->uiStatus($progress);
    $statusLabel = str_replace('_', ' ', ucfirst($uiStatus));
    $statusStyles = [
        'pending'         => 'bg-amber-50 text-amber-700 ring-amber-200',
        'in_progress'     => 'bg-blue-50 text-blue-700 ring-blue-200',
        'awaiting_review' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'completed'       => 'bg-green-50 text-green-700 ring-green-200',
        'rejected'        => 'bg-red-50 text-red-700 ring-red-200',
    ];
    $attachments = is_array($task->attachments) ? $task->attachments : [];
@endphp

@section('content')
<div class="space-y-5 max-w-5xl">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.mentee-tasks.index') }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to tasks
        </a>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold ring-1 ring-inset {{ $statusStyles[$uiStatus] ?? 'bg-gray-100 text-gray-600 ring-gray-200' }}">
                {{ $statusLabel }}
            </span>
            <a href="{{ route('admin.mentee-tasks.edit', $task) }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium px-3.5 py-2 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                Edit
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="grid lg:grid-cols-5 gap-5 items-start">
        <div class="lg:col-span-3 space-y-5">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-start gap-3">
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center text-xl flex-shrink-0">
                        {{ \App\Models\MenteeTask::TYPE_ICONS[$task->type] ?? '✅' }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-base font-semibold text-gray-900">{{ $task->title }}</div>
                        <div class="text-sm text-gray-500 mt-0.5">
                            {{ \App\Models\MenteeTask::TYPES[$task->type] ?? ucfirst((string) $task->type) }}
                            · {{ \App\Models\MenteeTask::SUBMISSION_TYPES[$task->submission_type] ?? $task->submission_type }}
                        </div>
                    </div>
                </div>

                <div class="p-5 grid sm:grid-cols-2 gap-3">
                    <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Type</div>
                        <div class="text-sm font-semibold text-gray-900">{{ \App\Models\MenteeTask::TYPES[$task->type] ?? ucfirst((string) $task->type) }}</div>
                    </div>
                    <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Submission</div>
                        <div class="text-sm font-semibold text-gray-900">{{ \App\Models\MenteeTask::SUBMISSION_TYPES[$task->submission_type] ?? $task->submission_type }}</div>
                    </div>
                    <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Required</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $task->is_required ? 'Yes' : 'No' }}</div>
                    </div>
                    <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Created</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $task->created_at?->format('d M Y, h:i A') }}</div>
                    </div>
                </div>

                @if($task->description)
                <div class="px-5 pb-5">
                    <div class="rounded-xl border border-gray-100 bg-white px-4 py-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-2">Description</div>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $task->description }}</p>
                    </div>
                </div>
                @endif
            </div>

            @if(count($attachments))
            <div class="bg-white border border-gray-200 rounded-2xl p-5 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900">Attachments</h3>
                <div class="space-y-2">
                    @foreach($attachments as $file)
                    @php
                        $fileUrl = is_array($file) ? ($file['url'] ?? null) : null;
                        $fileName = is_array($file) ? ($file['name'] ?? 'Attachment') : (string) $file;
                    @endphp
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-slate-50 px-4 py-3">
                        <div class="text-sm font-medium text-gray-800 truncate">{{ $fileName }}</div>
                        @if($fileUrl)
                        <a href="{{ $fileUrl }}" target="_blank" class="text-xs font-semibold text-blue-600 hover:underline flex-shrink-0">Open</a>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($progress && $progress->submission_status === 'submitted')
            <div class="bg-white border border-gray-200 rounded-2xl p-5 space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Review submission</h3>
                    <p class="text-xs text-gray-400 mt-1">Approve or reject this mentee’s work and leave feedback.</p>
                </div>
                @if($progress->submission_text)
                <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Written answer</div>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $progress->submission_text }}</p>
                </div>
                @endif
                @if($progress->submissionLink())
                <a href="{{ $progress->submissionLink() }}" target="_blank"
                   class="inline-flex items-center gap-2 bg-slate-50 border border-gray-200 hover:border-blue-300 hover:bg-blue-50 text-blue-700 text-sm font-medium px-3.5 py-2 rounded-lg transition-colors">
                    Open submission
                </a>
                @endif
                <form method="POST" action="{{ route('admin.mentee-tasks.review', $progress->id) }}" class="space-y-3">
                    @csrf
                    <textarea name="mentor_feedback" rows="3" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100" placeholder="Feedback for the mentee">{{ old('mentor_feedback') }}</textarea>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" name="submission_status" value="approved" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-xl">Approve</button>
                        <button type="submit" name="submission_status" value="rejected" class="border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-xl">Reject</button>
                    </div>
                </form>
            </div>
            @elseif($progress && in_array($progress->submission_status, ['approved', 'rejected'], true))
            <div class="bg-white border border-gray-200 rounded-2xl p-5 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900">Review</h3>
                <div class="text-sm text-gray-600">
                    Marked as <span class="font-semibold text-gray-900">{{ ucfirst($progress->submission_status) }}</span>
                    @if($progress->reviewed_at)
                        on {{ $progress->reviewed_at->format('d M Y, h:i A') }}
                    @endif
                </div>
                @if($progress->mentor_feedback)
                <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Feedback</div>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $progress->mentor_feedback }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>

        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800">People</h3>
                </div>
                <div class="p-5 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center text-sm font-bold flex-shrink-0">
                            {{ strtoupper(substr($task->mentee->name ?? 'M', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Mentee</div>
                            <div class="text-sm font-semibold text-gray-900 truncate">{{ $task->mentee->name ?? '—' }}</div>
                            <div class="text-xs text-gray-400 truncate">{{ $task->mentee->email ?? '' }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-700 flex items-center justify-center text-sm font-bold flex-shrink-0">
                            {{ strtoupper(substr($task->mentor->name ?? 'R', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Mentor</div>
                            <div class="text-sm font-semibold text-gray-900 truncate">{{ $task->mentor->name ?? '—' }}</div>
                            <div class="text-xs text-gray-400 truncate">{{ $task->mentor->email ?? '' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.mentee-tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 border border-red-200 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-medium px-4 py-2.5 rounded-xl transition-colors">
                    Delete task
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
