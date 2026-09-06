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
            <div class="dash-subtitle journey-page__subtitle">{{ $week->focus ?: 'Tasks, quizzes, and weekly check-in' }}</div>
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
                                <a href="{{ $taskProgress->submission_url }}" target="_blank" rel="noopener" style="font-size:12px;color:var(--brand);">Previous file</a>
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

        <div class="card journey-page__section">
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
                    <button type="button"
                        class="btn btn-ghost journey-page__mcq-option"
                        @if($attempt && (int)$attempt->selected_index === (int)$idx) style="border-color:var(--brand);" @endif
                        @if($mcqApproved || $mcqAwaiting) disabled @endif
                        onclick="answerMcq({{ $mcq->id }}, {{ (int)$idx }}, this)">
                        <span class="journey-page__mcq-option-label">{{ chr(65 + (int)$idx) }}.</span>
                        <span class="journey-page__mcq-option-text">{{ is_array($option) ? ($option['text'] ?? json_encode($option)) : $option }}</span>
                    </button>
                    @endforeach
                </div>
                <div class="journey-page__mcq-result" data-mcq-result="{{ $mcq->id }}">
                    @if($mcqAwaiting)
                        <span class="journey-page__mcq-result--correct">Correct — awaiting mentor approval</span>
                    @elseif($mcqApproved)
                        <span class="journey-page__mcq-result--correct">Approved by mentor</span>
                    @elseif($attempt)
                        @if($attempt->is_correct)
                            <span class="journey-page__mcq-result--correct">Correct · +{{ $attempt->points_earned }} pts</span>
                        @else
                            <span class="journey-page__mcq-result--wrong">Incorrect — try again</span>
                        @endif
                        @if($mcq->explanation)
                        <p class="journey-page__mcq-explanation">{{ $mcq->explanation }}</p>
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

        <div class="card journey-page__section journey-page__section--last">
            <h3 class="journey-page__section-title">Weekly Check-in</h3>
            @if(($canViewProgress ?? false) && $checkin)
            <div class="alert alert-success journey-page__alert">
                <span class="alert-icon">✅</span>
                <div class="journey-page__alert-body">
                    Submitted {{ $checkin->submitted_at?->format('d M Y') ?? '' }}. Mood: {{ $checkin->mood_score ?? '—' }}/5
                </div>
            </div>
            @if($checkin->mentor_response)
            <div class="journey-page__mentor-reply">
                <strong>Mentor reply:</strong>
                <p>{{ $checkin->mentor_response }}</p>
            </div>
            @endif
            @endif

            <div class="form-group">
                <label class="form-label" for="checkin-mood">Mood (1–5)</label>
                <input type="number" id="checkin-mood" class="form-input" min="1" max="5" value="{{ ($canViewProgress ?? false) ? ($checkin->mood_score ?? 3) : 3 }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="checkin-wins">Wins this week</label>
                <textarea id="checkin-wins" class="form-input" rows="2" placeholder="What went well?">{{ ($canViewProgress ?? false) ? ($checkin->wins ?? '') : '' }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="checkin-challenges">Challenges</label>
                <textarea id="checkin-challenges" class="form-input" rows="2" placeholder="What was hard?">{{ ($canViewProgress ?? false) ? ($checkin->challenges ?? '') : '' }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="checkin-questions">Questions for mentor</label>
                <textarea id="checkin-questions" class="form-input" rows="2" placeholder="Anything you want help with?">{{ ($canViewProgress ?? false) ? ($checkin->questions ?? '') : '' }}</textarea>
            </div>
            <button type="button" class="btn btn-primary journey-page__checkin-btn" id="checkin-btn" onclick="submitCheckin()">
                {{ (($canViewProgress ?? false) && $checkin) ? 'Update Check-in' : 'Submit Check-in' }}
            </button>
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
                if (data.correct && data.awaiting_review) {
                    box.innerHTML = `<span class="journey-page__mcq-result--correct">Correct — awaiting mentor approval</span>`;
                } else {
                    box.innerHTML = data.correct
                        ? `<span class="journey-page__mcq-result--correct">Correct · +${data.points_earned || 0} pts</span>`
                        : `<span class="journey-page__mcq-result--wrong">Incorrect — try again</span>`;
                }
                if (data.explanation) {
                    box.innerHTML += `<p class="journey-page__mcq-explanation">${data.explanation}</p>`;
                }
            }
            showToast(data.correct ? 'success' : 'error', data.message || (data.correct ? 'Correct!' : 'Not quite — try again.'));
            if (data.correct) setTimeout(() => location.reload(), 900);
        },
        onError: (err) => showToast('error', err.message || 'Could not submit answer.'),
    });
}

function submitCheckin() {
    AjaxPost(`{{ url('/mentee/journey/weeks') }}/{{ $week->id }}/checkin`, {
        mood_score: document.getElementById('checkin-mood').value,
        wins: document.getElementById('checkin-wins').value,
        challenges: document.getElementById('checkin-challenges').value,
        questions: document.getElementById('checkin-questions').value,
    }, {
        btn: document.getElementById('checkin-btn'),
        loader: true,
        onSuccess: (data) => {
            showToast('success', data.message || 'Check-in submitted!');
            if (canViewProgress) {
                setTimeout(() => location.reload(), 800);
            }
        },
        onError: (err) => showToast('error', err.message || 'Could not submit check-in.'),
    });
}
</script>
@endpush
