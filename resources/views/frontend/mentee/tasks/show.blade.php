@extends('frontend.layouts.app')
@section('title', $task->title.' — Task')

@section('content')
@include('frontend.partials.tasks-premium-ui')

@php
    $status = $task->uiStatus($progress);
    $statusLabel = match ($status) {
        'awaiting_review' => 'Awaiting review',
        'completed' => 'Completed',
        'rejected' => 'Changes requested',
        'in_progress' => 'In progress',
        default => 'To do',
    };
    $chipClass = match ($status) {
        'completed' => 'learn-chip--done',
        'awaiting_review' => 'learn-chip--wait',
        'rejected' => 'learn-chip--error',
        default => 'learn-chip--todo',
    };
    $isOverdue = $task->due_at && $task->due_at->isPast() && ! in_array($status, ['completed', 'awaiting_review'], true);
@endphp

<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-page tasks-premium" style="display:flex;flex-direction:column;gap:1.15rem;">

            <div class="learn-intro fu">
                <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-start;justify-content:space-between;">
                    <div>
                        <a href="{{ route('mentee.tasks.index') }}" class="dash-link" style="display:inline-flex;margin-bottom:.55rem;">← My Tasks</a>
                        <span class="learn-eyebrow">Learning · Task detail</span>
                        <h2>{{ $task->title }}</h2>
                        <p>
                            {{ \App\Models\MenteeTask::TYPES[$task->type] ?? 'Task' }}
                            · {{ \App\Models\MenteeTask::SUBMISSION_TYPES[$task->submission_type] ?? $task->submission_type }}
                            @if($task->mentor) · Mentor {{ $task->mentor->name }} @endif
                        </p>
                    </div>
                    <span class="learn-chip {{ $chipClass }}" style="align-self:flex-start;">{{ $statusLabel }}</span>
                </div>
            </div>

            @if(session('success'))
            <div class="alert-premium fu" style="border-color:rgba(34,197,94,.3);background:rgba(34,197,94,.1);">
                <span>✓</span>
                <div><p>{{ session('success') }}</p></div>
            </div>
            @endif
            @if(session('error'))
            <div class="alert-premium fu" style="border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.1);">
                <span>!</span>
                <div><p>{{ session('error') }}</p></div>
            </div>
            @endif
            @if($errors->any())
            <div class="alert-premium fu" style="border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.1);">
                <span>!</span>
                <div><p>{{ $errors->first() }}</p></div>
            </div>
            @endif

            <div class="tp-detail-grid fu">
                <div style="display:flex;flex-direction:column;gap:1rem;">
                    <div class="learn-panel panel-pad">
                        <div class="tp-section-label">Description</div>
                        @if($task->description)
                            <p class="tp-body-text">{{ $task->description }}</p>
                        @else
                            <p class="tp-body-text" style="color:var(--text-3);">No description provided.</p>
                        @endif

                        @if(!empty($task->attachments))
                            <div style="margin-top:1.1rem;">
                                <div class="tp-section-label">Attachments</div>
                                <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                                    @foreach($task->attachments as $file)
                                        <a href="{{ $file['url'] ?? '#' }}" target="_blank" rel="noopener" class="tp-file">
                                            📎 {{ $file['name'] ?? 'File' }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($progress?->mentor_feedback || $progress?->submission_text || $progress?->submissionLink())
                    <div class="learn-panel panel-pad">
                        <div class="tp-section-label">Your progress</div>

                        @if($progress?->mentor_feedback)
                            <div class="alert-premium" style="margin-bottom:.85rem;">
                                <span>💬</span>
                                <div>
                                    <strong>Mentor feedback</strong>
                                    <p style="white-space:pre-wrap;">{{ $progress->mentor_feedback }}</p>
                                </div>
                            </div>
                        @endif

                        @if($progress?->submission_text)
                            <div style="margin-bottom:.75rem;">
                                <div class="tp-section-label">Your text</div>
                                <p class="tp-body-text">{{ $progress->submission_text }}</p>
                            </div>
                        @endif

                        @if($progress?->submissionLink())
                            <a href="{{ $progress->submissionLink() }}" target="_blank" rel="noopener" class="tp-file">
                                📎 View your submission
                            </a>
                        @endif
                    </div>
                    @endif

                    @if(! in_array($status, ['completed', 'awaiting_review'], true))
                    <div class="learn-panel panel-pad tp-submit">
                        <div class="panel-head" style="margin-bottom:.85rem;">
                            <div>
                                <h3 class="dash-section-title">
                                    {{ $status === 'rejected' ? 'Resubmit your work' : 'Submit your work' }}
                                </h3>
                                <p class="dash-section-sub">
                                    @if($task->submission_type === 'none' || ! $task->submission_type)
                                        Mark this task as done when you’ve completed it.
                                    @else
                                        Your mentor will review the submission.
                                    @endif
                                </p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('mentee.tasks.submit', $task) }}" enctype="multipart/form-data" style="display:grid;gap:.85rem;">
                            @csrf
                            @if($task->submission_type === 'none' || ! $task->submission_type)
                                <button type="submit" class="learn-btn-primary" style="justify-self:start;">Mark complete</button>
                            @else
                                @if(in_array($task->submission_type, ['text'], true))
                                    <div>
                                        <label class="form-label">Your answer</label>
                                        <textarea name="submission_text" class="form-input" rows="4" required>{{ old('submission_text', $progress->submission_text ?? '') }}</textarea>
                                    </div>
                                @endif
                                @if(in_array($task->submission_type, ['link'], true))
                                    <div>
                                        <label class="form-label">Submission URL</label>
                                        <input type="url" name="submission_url" class="form-input" value="{{ old('submission_url') }}" required placeholder="https://…">
                                    </div>
                                @endif
                                @if(in_array($task->submission_type, ['file', 'pdf', 'video'], true))
                                    <div>
                                        <label class="form-label">Upload file</label>
                                        <input type="file" name="submission_file" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label">Notes (optional)</label>
                                        <textarea name="submission_text" class="form-input" rows="3">{{ old('submission_text') }}</textarea>
                                    </div>
                                @endif
                                <button type="submit" class="learn-btn-primary" style="justify-self:start;">
                                    {{ $status === 'rejected' ? 'Resubmit for review' : 'Submit for review' }}
                                </button>
                            @endif
                        </form>
                    </div>
                    @elseif($status === 'awaiting_review')
                    <div class="alert-premium">
                        <span>👀</span>
                        <div>
                            <strong>Waiting on mentor review</strong>
                            <p>Your submission was received. You’ll see feedback here once it’s reviewed.</p>
                        </div>
                    </div>
                    @endif
                </div>

                <aside class="learn-panel panel-pad">
                    <div class="tp-section-label">Task details</div>
                    <div class="tp-side-stat">
                        <span class="tp-side-stat__label">Status</span>
                        <span class="learn-chip {{ $chipClass }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="tp-side-stat">
                        <span class="tp-side-stat__label">Type</span>
                        <span class="tp-side-stat__value">{{ \App\Models\MenteeTask::TYPES[$task->type] ?? ucfirst((string) $task->type) }}</span>
                    </div>
                    <div class="tp-side-stat">
                        <span class="tp-side-stat__label">Submission</span>
                        <span class="tp-side-stat__value">{{ \App\Models\MenteeTask::SUBMISSION_TYPES[$task->submission_type] ?? $task->submission_type }}</span>
                    </div>
                    <div class="tp-side-stat">
                        <span class="tp-side-stat__label">Mentor</span>
                        <span class="tp-side-stat__value">{{ $task->mentor->name ?? '—' }}</span>
                    </div>
                    @if($task->due_at)
                    <div class="tp-side-stat">
                        <span class="tp-side-stat__label">Due date</span>
                        <span class="tp-side-stat__value {{ $isOverdue ? 'tp-due is-overdue' : 'tp-due' }}">
                            {{ $task->due_at->format('d M Y') }}
                            @if($isOverdue) · Overdue @endif
                        </span>
                    </div>
                    @endif
                    @if($progress?->submitted_at || $progress?->completed_at || $progress?->reviewed_at)
                    <div class="tp-side-stat">
                        <span class="tp-side-stat__label">Last update</span>
                        <span class="tp-side-stat__value">
                            {{ optional($progress->reviewed_at ?? $progress->completed_at ?? $progress->updated_at)->format('d M Y, H:i') ?: '—' }}
                        </span>
                    </div>
                    @endif

                    <a href="{{ route('mentee.tasks.index') }}" class="learn-btn-ghost" style="width:100%;margin-top:1rem;">Back to all tasks</a>
                </aside>
            </div>
        </div>
    </div>
</div>
@endsection
