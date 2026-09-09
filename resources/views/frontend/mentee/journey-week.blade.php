@extends('frontend.layouts.app')
@section('title', ($week->title ?: 'Week '.$week->week_number).' — Journey')

@section('content')
<div class="dash-layout journey-page">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header journey-page__header">
            <nav class="journey-page__breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('mentee.journey.index') }}">My Journey</a>
                <span class="journey-page__breadcrumb-sep">/</span>
                <a href="{{ route('mentee.journey.month', $week->month_id) }}">Month {{ $week->month->month_number ?? '' }}</a>
                <span class="journey-page__breadcrumb-sep">/</span>
                <span class="journey-page__breadcrumb-current">Week {{ $week->week_number }}</span>
            </nav>
            <div class="dash-title journey-page__title">{{ $week->title ?: 'Week '.$week->week_number }}</div>
            <div class="dash-subtitle journey-page__subtitle">{{ $week->focus ?: 'Tasks and quizzes for this week' }}</div>
        </div>

        @if($canViewProgress ?? false)
        <div class="card journey-page__progress-card">
            <div class="journey-page__progress-head">
                <span>Week progress</span>
                <span>{{ (int) ($progress['percent'] ?? 0) }}%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width:{{ (int) ($progress['percent'] ?? 0) }}%"></div>
            </div>
        </div>
        @else
        <div class="alert alert-warning journey-page__alert">
            <span class="alert-icon">🔒</span>
            <div class="journey-page__alert-body">
                You can work on tasks and MCQs. Scores, past submissions, and completion status stay hidden until you upgrade.
                <a href="{{ route('mentee.plans') }}">View plans →</a>
            </div>
        </div>
        @endif

        <div class="card journey-page__section">
            <h3 class="journey-page__section-title">Tasks</h3>
            @forelse($week->tasks as $task)
            @php
                $taskProgress = ($canViewProgress ?? false) ? ($taskProgressById[$task->id] ?? null) : null;
                $done = ($canViewProgress ?? false) && (($taskProgress?->is_completed) || in_array($task->id, $completedTaskIds ?? [], true));
                $awaiting = ($canViewProgress ?? false) && $taskProgress && $taskProgress->submission_status === 'submitted' && ! $taskProgress->is_completed;
                $rejected = ($canViewProgress ?? false) && $taskProgress && $taskProgress->submission_status === 'rejected';
                $needsSubmission = $task->submission_type && $task->submission_type !== 'none';
            @endphp
            <div class="journey-page__item-card" id="task-{{ $task->id }}">
                <div class="journey-page__item-row">
                    <div class="journey-page__item-body">
                        <div class="journey-page__item-head">
                            <span class="journey-page__item-icon">{{ \App\Models\CurriculumTask::TYPE_ICONS[$task->type] ?? '✅' }}</span>
                            <h4 class="journey-page__item-title">{{ $task->title }}</h4>
                            @if($done)<span class="session-status completed">Approved</span>
                            @elseif($awaiting)<span class="session-status pending">Under review</span>
                            @elseif($rejected)<span class="session-status cancelled">Needs revision</span>
                            @endif
                        </div>
                        @if($task->description)
                        <p class="journey-page__item-desc">{{ $task->description }}</p>
                        @endif
                        @if(!empty($task->attachments))
                        <div class="journey-page__task-attachments" style="margin-top:10px;display:flex;flex-wrap:wrap;gap:8px;">
                            @foreach($task->attachments as $attachment)
                                <a href="{{ $attachment['url'] ?? '#' }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">
                                    📎 {{ \Illuminate\Support\Str::limit($attachment['name'] ?? 'Attachment', 36) }}
                                </a>
                            @endforeach
                        </div>
                        @endif
                        <div class="journey-page__item-meta">
                            {{ ucfirst($task->type ?? 'task') }}
                            @if($task->estimated_minutes) · {{ $task->estimated_minutes }} min @endif
                            @if($task->is_required) · Required @endif
                            @if($needsSubmission) · Submit {{ $task->submission_type }} @endif
                        </div>
                        @if($canViewProgress && $taskProgress?->mentor_feedback)
                        <div class="journey-page__mentor-reply" style="margin-top:10px;">
                            <strong>Mentor feedback:</strong>
                            <p>{{ $taskProgress->mentor_feedback }}</p>
                        </div>
                        @endif

                        @if($canViewProgress && $taskProgress && ($taskProgress->submission_text || $taskProgress->submission_url) && ($done || $awaiting || $rejected))
                        <div class="journey-page__your-submission" style="margin-top:12px;padding:12px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--bg);">
                            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-3);margin-bottom:8px;">Your submission</div>
                            @if($taskProgress->submission_text)
                            <div style="font-size:13px;color:var(--text-2);white-space:pre-wrap;margin-bottom:{{ $taskProgress->submission_url ? '10px' : '0' }};">{{ $taskProgress->submission_text }}</div>
                            @endif
                            @if($taskProgress->submission_url)
                            <a href="{{ $taskProgress->submissionLink() }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">
                                📎 Open submitted {{ in_array($task->submission_type, ['link', 'url'], true) ? 'link' : 'file' }}
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                @unless($done || $awaiting)
                    @if($needsSubmission)
                    <div class="journey-page__task-submit" style="margin-top:12px;display:grid;gap:10px;">
                        @if(in_array($task->submission_type, ['text', 'reflection'], true))
                            <textarea id="task-text-{{ $task->id }}" class="form-input" rows="3" placeholder="Write your submission…">{{ $taskProgress->submission_text ?? '' }}</textarea>
                        @elseif(in_array($task->submission_type, ['link', 'url'], true))
                            <input type="url" id="task-url-{{ $task->id }}" class="form-input" placeholder="https://…" value="{{ $taskProgress->submission_url ?? '' }}">
                        @elseif(in_array($task->submission_type, ['file', 'pdf'], true))
                            <input type="file" id="task-file-{{ $task->id }}" class="form-input">
                            @if($taskProgress?->submission_url)
                                <a href="{{ $taskProgress->submissionLink() }}" target="_blank" rel="noopener" style="font-size:12px;color:var(--brand);">Previous file</a>
                            @endif
                        @else
                            <textarea id="task-text-{{ $task->id }}" class="form-input" rows="3" placeholder="Notes / response…">{{ $taskProgress->submission_text ?? '' }}</textarea>
                            <input type="url" id="task-url-{{ $task->id }}" class="form-input" placeholder="Optional link https://…" value="{{ $taskProgress->submission_url ?? '' }}">
                            <input type="file" id="task-file-{{ $task->id }}" class="form-input">
                        @endif
                        <button type="button" class="btn btn-primary btn-sm journey-page__item-action" onclick="submitTask({{ $task->id }}, '{{ $task->submission_type }}', this)">
                            {{ $rejected ? 'Resubmit for review' : 'Submit for review' }}
                        </button>
                    </div>
                    @else
                    <div style="margin-top:12px;">
                        <button type="button" class="btn btn-primary btn-sm journey-page__item-action" onclick="completeTask({{ $task->id }}, this)">
                            {{ ($canViewProgress ?? false) ? 'Mark done' : 'Submit' }}
                        </button>
                    </div>
                    @endif
                @endunless
            </div>
            @empty
            <p class="journey-page__empty">No tasks for this week.</p>
            @endforelse
        </div>

        <div class="card journey-page__section journey-page__section--last">
            <h3 class="journey-page__section-title">Practice MCQs</h3>
            @forelse($week->mcqs as $mcq)
            @php
                $attempt = ($canViewProgress ?? false) ? ($mcqAttempts[$mcq->id] ?? null) : null;
                $mcqProgress = ($canViewProgress ?? false) ? ($mcqProgressById[$mcq->id] ?? null) : null;
                $mcqApproved = $mcqProgress?->is_completed || $mcqProgress?->submission_status === 'approved';
                $mcqAwaiting = $mcqProgress?->submission_status === 'submitted' && ! $mcqProgress?->is_completed;
                $mcqRejected = $mcqProgress?->submission_status === 'rejected';
                $options = is_array($mcq->options) ? $mcq->options : [];
            @endphp
            <div class="journey-page__item-card" id="mcq-{{ $mcq->id }}">
                <div class="journey-page__item-head" style="margin-bottom:8px;">
                    <p class="journey-page__mcq-question" style="margin:0;flex:1;">{{ $mcq->question }}</p>
                    @if($mcqApproved)<span class="session-status completed">Approved</span>
                    @elseif($mcqAwaiting)<span class="session-status pending">Under review</span>
                    @elseif($mcqRejected)<span class="session-status cancelled">Needs revision</span>
                    @endif
                </div>
                <div class="journey-page__mcq-options" data-mcq-options="{{ $mcq->id }}">
                    @foreach($options as $idx => $option)
                    @php
                        $isSelected = $attempt && (int) $attempt->selected_index === (int) $idx;
                        $isCorrectOption = $mcqApproved && (int) $mcq->correct_index === (int) $idx;
                        $showSelected = $isSelected && ($mcqAwaiting || $mcqRejected || $mcqApproved);
                    @endphp
                    <button type="button"
                        class="btn btn-ghost journey-page__mcq-option {{ $isCorrectOption ? 'is-correct' : '' }} {{ $showSelected && ! $isCorrectOption ? 'is-selected' : '' }}"
                        @if($mcqApproved || $mcqAwaiting) disabled @endif
                        onclick="answerMcq({{ $mcq->id }}, {{ (int)$idx }}, this)">
                        <span class="journey-page__mcq-option-label">{{ chr(65 + (int)$idx) }}.</span>
                        <span class="journey-page__mcq-option-text">{{ is_array($option) ? ($option['text'] ?? json_encode($option)) : $option }}</span>
                        @if($isCorrectOption)
                            <span class="journey-page__mcq-option-tag">Correct</span>
                        @elseif($showSelected)
                            <span class="journey-page__mcq-option-tag journey-page__mcq-option-tag--selected">Your answer</span>
                        @endif
                    </button>
                    @endforeach
                </div>
                <div class="journey-page__mcq-result" data-mcq-result="{{ $mcq->id }}">
                    @if($mcqAwaiting)
                        <span class="journey-page__mcq-result--neutral">Answer submitted — waiting for mentor review</span>
                        @if($attempt)
                        <p class="journey-page__mcq-explanation">
                            You selected:
                            <strong>{{ is_array($options[$attempt->selected_index] ?? null) ? ($options[$attempt->selected_index]['text'] ?? '') : ($options[$attempt->selected_index] ?? '—') }}</strong>
                        </p>
                        @endif
                    @elseif($mcqApproved)
                        <span class="journey-page__mcq-result--correct">
                            Approved by mentor
                            @if($attempt && (int) $attempt->points_earned > 0)
                                · +{{ $attempt->points_earned }} pts
                            @endif
                        </span>
                        @if($mcq->explanation)
                        <p class="journey-page__mcq-explanation">{{ $mcq->explanation }}</p>
                        @endif
                    @elseif($mcqRejected)
                        <span class="journey-page__mcq-result--wrong">Mentor requested changes — pick an answer and resubmit</span>
                        @if($attempt)
                        <p class="journey-page__mcq-explanation">
                            Previous answer:
                            <strong>{{ is_array($options[$attempt->selected_index] ?? null) ? ($options[$attempt->selected_index]['text'] ?? '') : ($options[$attempt->selected_index] ?? '—') }}</strong>
                        </p>
                        @endif
                    @endif
                    @if($canViewProgress && $mcqProgress?->mentor_feedback)
                    <div class="journey-page__mentor-reply" style="margin-top:8px;">
                        <strong>Mentor feedback:</strong>
                        <p>{{ $mcqProgress->mentor_feedback }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <p class="journey-page__empty">No MCQs for this week.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const canViewProgress = @json((bool) ($canViewProgress ?? false));

function completeTask(taskId, btn) {
    AjaxPost(`{{ url('/mentee/journey/tasks') }}/${taskId}/complete`, {}, {
        btn, loader: true,
        onSuccess: (data) => {
            showToast('success', data.message || 'Task submitted!');
            if (canViewProgress) {
                setTimeout(() => location.reload(), 700);
            } else if (btn) {
                btn.disabled = true;
                btn.textContent = 'Submitted';
            }
        },
        onError: (err) => showToast('error', err.message || 'Could not complete task.'),
    });
}

function submitTask(taskId, submissionType, btn) {
    const fd = new FormData();
    const textEl = document.getElementById(`task-text-${taskId}`);
    const urlEl = document.getElementById(`task-url-${taskId}`);
    const fileEl = document.getElementById(`task-file-${taskId}`);
    if (textEl?.value?.trim()) fd.append('submission_text', textEl.value.trim());
    if (urlEl?.value?.trim()) fd.append('submission_url', urlEl.value.trim());
    if (fileEl?.files?.[0]) fd.append('submission_file', fileEl.files[0]);

    if (['text'].includes(submissionType) && !fd.has('submission_text')) {
        showToast('error', 'Please write your submission.');
        return;
    }
    if (['link', 'url'].includes(submissionType) && !fd.has('submission_url')) {
        showToast('error', 'Please add a submission link.');
        return;
    }
    if (['file', 'pdf'].includes(submissionType) && !fd.has('submission_file') && !fd.has('submission_url')) {
        showToast('error', 'Please upload a file.');
        return;
    }

    AjaxPost(`{{ url('/mentee/journey/tasks') }}/${taskId}/complete`, fd, {
        btn, loader: true,
        onSuccess: (data) => {
            showToast('success', data.message || 'Submitted for mentor review.');
            setTimeout(() => location.reload(), 700);
        },
        onError: (err) => showToast('error', err.message || 'Could not submit task.'),
    });
}

function answerMcq(mcqId, selectedIndex, btn) {
    AjaxPost(`{{ url('/mentee/journey/mcqs') }}/${mcqId}/answer`, { selected_index: selectedIndex }, {
        btn, loader: true,
        onSuccess: (data) => {
            const box = document.querySelector(`[data-mcq-result="${mcqId}"]`);
            if (!canViewProgress || data.progress_report_enabled === false) {
                if (box) {
                    box.innerHTML = `<span class="journey-page__mcq-result--neutral">Answer submitted. Scores unlock with Progress report.</span>`;
                }
                showToast('success', data.message || 'Answer submitted.');
                return;
            }
            if (box) {
                if (data.awaiting_review || data.submission_status === 'submitted') {
                    box.innerHTML = `<span class="journey-page__mcq-result--neutral">Answer submitted — waiting for mentor review</span>`;
                } else if (data.correct) {
                    box.innerHTML = `<span class="journey-page__mcq-result--correct">Approved by mentor · +${data.points_earned || 0} pts</span>`;
                    if (data.explanation) {
                        box.innerHTML += `<p class="journey-page__mcq-explanation">${data.explanation}</p>`;
                    }
                } else {
                    box.innerHTML = `<span class="journey-page__mcq-result--neutral">Answer submitted — waiting for mentor review</span>`;
                }
            }
            showToast('success', data.message || 'Answer submitted for mentor review.');
            setTimeout(() => location.reload(), 900);
        },
        onError: (err) => showToast('error', err.message || 'Could not submit answer.'),
    });
}
</script>
@endpush
