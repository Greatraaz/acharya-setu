@extends('frontend.layouts.app')
@section('title', ($week->title ?: 'Week '.$week->week_number).' — Journey')

@section('content')
@include('frontend.partials.journey-premium-ui')

<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-page journey-premium" style="display:flex;flex-direction:column;gap:1.15rem;">

            <div class="learn-intro fu">
                <nav class="jp-crumb" aria-label="Breadcrumb">
                    <a href="{{ route('mentee.journey.index') }}">My Journey</a>
                    <span class="jp-crumb__sep">/</span>
                    <a href="{{ route('mentee.journey.month', $week->month_id) }}">Month {{ $week->month->month_number ?? '' }}</a>
                    <span class="jp-crumb__sep">/</span>
                    <span class="jp-crumb__now">Week {{ $week->week_number }}</span>
                </nav>
                <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-start;justify-content:space-between;">
                    <div>
                        <span class="learn-eyebrow">Week {{ $week->week_number }}</span>
                        <h2>{{ $week->title ?: 'Week '.$week->week_number }}</h2>
                        <p>{{ $week->focus ?: 'Tasks and practice quizzes for this week — submit work for mentor review.' }}</p>
                    </div>
                    <a href="{{ route('mentee.journey.month', $week->month_id) }}" class="learn-btn-ghost">← Month</a>
                </div>
            </div>

            @if($canViewProgress ?? false)
            <div class="jp-hero fu">
                <div class="jp-hero__kicker">Week progress</div>
                <h3 class="jp-hero__title">{{ (int) ($progress['percent'] ?? 0) }}% complete</h3>
                <p class="jp-hero__sub">Keep finishing tasks and MCQs to move the needle this week.</p>
                <div class="jp-hero__progress">
                    <div class="jp-hero__bar"><span style="width:{{ (int) ($progress['percent'] ?? 0) }}%"></span></div>
                </div>
            </div>

            <div class="dash-grid dash-grid-4 fu" style="grid-template-columns:repeat(3,minmax(0,1fr));">
                <div class="learn-stat">
                    <div class="learn-stat__value">{{ $week->tasks->count() }}</div>
                    <div class="learn-stat__label">Tasks</div>
                </div>
                <div class="learn-stat">
                    <div class="learn-stat__value">{{ $week->mcqs->count() }}</div>
                    <div class="learn-stat__label">Practice MCQs</div>
                </div>
                <div class="learn-stat">
                    <div class="learn-stat__value" style="color:var(--ln-dark);">{{ (int) ($progress['percent'] ?? 0) }}%</div>
                    <div class="learn-stat__label">Done</div>
                </div>
            </div>
            @else
            <div class="alert-premium fu">
                <span>🔒</span>
                <div>
                    <strong>Scores stay hidden until you upgrade</strong>
                    <p>You can still work on tasks and MCQs.
                        <a href="{{ route('mentee.plans') }}" class="dash-link">View plans →</a>
                    </p>
                </div>
            </div>
            @endif

            <div class="learn-panel panel-pad fu">
                <div class="panel-head">
                    <div>
                        <h3 class="dash-section-title">Tasks</h3>
                        <p class="dash-section-sub">Submit work for mentor review</p>
                    </div>
                </div>

                @forelse($week->tasks as $task)
                @php
                    $taskEid = encrypt_id($task->id);
                    $taskProgress = ($canViewProgress ?? false) ? ($taskProgressById[$task->id] ?? null) : null;
                    $done = ($canViewProgress ?? false) && (($taskProgress?->is_completed) || in_array($task->id, $completedTaskIds ?? [], true));
                    $awaiting = ($canViewProgress ?? false) && $taskProgress && $taskProgress->submission_status === 'submitted' && ! $taskProgress->is_completed;
                    $rejected = ($canViewProgress ?? false) && $taskProgress && $taskProgress->submission_status === 'rejected';
                    $needsSubmission = $task->submission_type && $task->submission_type !== 'none';
                @endphp
                <div class="jp-item" id="task-{{ $task->id }}">
                    <div class="jp-item__head">
                        <span class="jp-item__icon">{{ \App\Models\CurriculumTask::TYPE_ICONS[$task->type] ?? '✅' }}</span>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;">
                                <h4 class="jp-item__title">{{ $task->title }}</h4>
                                @if($done)<span class="learn-chip learn-chip--done">Approved</span>
                                @elseif($awaiting)<span class="learn-chip learn-chip--wait">Under review</span>
                                @elseif($rejected)<span class="learn-chip learn-chip--error">Needs revision</span>
                                @endif
                            </div>
                            @if($task->description)
                                <p class="jp-item__desc">{{ $task->description }}</p>
                            @endif
                            @if(!empty($task->attachments))
                                <div style="margin-top:.65rem;display:flex;flex-wrap:wrap;gap:.45rem;">
                                    @foreach($task->attachments as $attachment)
                                        <a href="{{ $attachment['url'] ?? '#' }}" target="_blank" rel="noopener" class="learn-btn-ghost" style="padding:.35rem .65rem;font-size:.72rem;">
                                            📎 {{ \Illuminate\Support\Str::limit($attachment['name'] ?? 'Attachment', 36) }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                            <div class="jp-item__meta">
                                {{ ucfirst($task->type ?? 'task') }}
                                @if($task->estimated_minutes) · {{ $task->estimated_minutes }} min @endif
                                @if($task->is_required) · Required @endif
                                @if($needsSubmission) · Submit {{ $task->submission_type }} @endif
                            </div>

                            @if($canViewProgress && $taskProgress?->mentor_feedback)
                                <div class="jp-feedback">
                                    <strong>Mentor feedback</strong>
                                    {{ $taskProgress->mentor_feedback }}
                                </div>
                            @endif

                            @if($canViewProgress && $taskProgress && ($taskProgress->submission_text || $taskProgress->submission_url) && ($done || $awaiting || $rejected))
                                <div class="jp-feedback" style="background:var(--bg-2);border-color:var(--card-border);">
                                    <strong>Your submission</strong>
                                    @if($taskProgress->submission_text)
                                        <div style="white-space:pre-wrap;margin-bottom:{{ $taskProgress->submission_url ? '.5rem' : '0' }};">{{ $taskProgress->submission_text }}</div>
                                    @endif
                                    @if($taskProgress->submission_url)
                                        <a href="{{ $taskProgress->submissionLink() }}" target="_blank" rel="noopener" class="learn-btn-ghost" style="padding:.35rem .65rem;font-size:.72rem;">
                                            📎 Open submitted {{ in_array($task->submission_type, ['link', 'url'], true) ? 'link' : 'file' }}
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    @unless($done || $awaiting)
                        @if($needsSubmission)
                            <div class="jp-submit-box">
                                @if(in_array($task->submission_type, ['text', 'reflection'], true))
                                    <textarea id="task-text-{{ $taskEid }}" class="form-input" rows="3" placeholder="Write your submission…">{{ $taskProgress->submission_text ?? '' }}</textarea>
                                @elseif(in_array($task->submission_type, ['link', 'url'], true))
                                    <input type="url" id="task-url-{{ $taskEid }}" class="form-input" placeholder="https://…" value="{{ $taskProgress->submission_url ?? '' }}">
                                @elseif(in_array($task->submission_type, ['file', 'pdf'], true))
                                    <input type="file" id="task-file-{{ $taskEid }}" class="form-input">
                                    @if($taskProgress?->submission_url)
                                        <a href="{{ $taskProgress->submissionLink() }}" target="_blank" rel="noopener" class="dash-link">Previous file</a>
                                    @endif
                                @else
                                    <textarea id="task-text-{{ $taskEid }}" class="form-input" rows="3" placeholder="Notes / response…">{{ $taskProgress->submission_text ?? '' }}</textarea>
                                    <input type="url" id="task-url-{{ $taskEid }}" class="form-input" placeholder="Optional link https://…" value="{{ $taskProgress->submission_url ?? '' }}">
                                    <input type="file" id="task-file-{{ $taskEid }}" class="form-input">
                                @endif
                                <button type="button" class="learn-btn-primary" style="justify-self:start;" onclick="submitTask('{{ $taskEid }}', '{{ $task->submission_type }}', this)">
                                    {{ $rejected ? 'Resubmit for review' : 'Submit for review' }}
                                </button>
                            </div>
                        @else
                            <div style="margin-top:.85rem;">
                                <button type="button" class="learn-btn-primary" onclick="completeTask('{{ $taskEid }}', this)">
                                    {{ ($canViewProgress ?? false) ? 'Mark done' : 'Submit' }}
                                </button>
                            </div>
                        @endif
                    @endunless
                </div>
                @empty
                <div class="empty-block" style="padding:1.25rem;">
                    <p>No tasks for this week.</p>
                </div>
                @endforelse
            </div>

            <div class="learn-panel panel-pad fu">
                <div class="panel-head">
                    <div>
                        <h3 class="dash-section-title">Practice MCQs</h3>
                        <p class="dash-section-sub">Choose an answer and submit for mentor review</p>
                    </div>
                </div>

                @forelse($week->mcqs as $mcq)
                @php
                    $mcqEid = encrypt_id($mcq->id);
                    $attempt = ($canViewProgress ?? false) ? ($mcqAttempts[$mcq->id] ?? null) : null;
                    $mcqProgress = ($canViewProgress ?? false) ? ($mcqProgressById[$mcq->id] ?? null) : null;
                    $mcqApproved = $mcqProgress?->is_completed || $mcqProgress?->submission_status === 'approved';
                    $mcqAwaiting = $mcqProgress?->submission_status === 'submitted' && ! $mcqProgress?->is_completed;
                    $mcqRejected = $mcqProgress?->submission_status === 'rejected';
                    $options = is_array($mcq->options) ? $mcq->options : [];
                @endphp
                <div class="jp-item" id="mcq-{{ $mcq->id }}">
                    <div class="jp-item__head" style="margin-bottom:.65rem;">
                        <span class="jp-item__icon">🧠</span>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;flex-wrap:wrap;align-items:flex-start;gap:.5rem;">
                                <p class="jp-item__title" style="flex:1;">{{ $mcq->question }}</p>
                                @if($mcqApproved)<span class="learn-chip learn-chip--done">Approved</span>
                                @elseif($mcqAwaiting)<span class="learn-chip learn-chip--wait">Under review</span>
                                @elseif($mcqRejected)<span class="learn-chip learn-chip--error">Needs revision</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div data-mcq-options="{{ $mcqEid }}">
                        @foreach($options as $idx => $option)
                        @php
                            $isSelected = $attempt && (int) $attempt->selected_index === (int) $idx;
                            $isCorrectOption = $mcqApproved && (int) $mcq->correct_index === (int) $idx;
                            $showSelected = $isSelected && ($mcqAwaiting || $mcqRejected || $mcqApproved);
                        @endphp
                        <button type="button"
                            class="jp-mcq-option {{ $isCorrectOption ? 'is-correct' : '' }} {{ $showSelected && ! $isCorrectOption ? 'is-selected' : '' }}"
                            @if($mcqApproved || $mcqAwaiting) disabled @endif
                            onclick="answerMcq('{{ $mcqEid }}', {{ (int)$idx }}, this)">
                            <span class="jp-mcq-letter">{{ chr(65 + (int)$idx) }}</span>
                            <span>{{ is_array($option) ? ($option['text'] ?? json_encode($option)) : $option }}</span>
                            @if($isCorrectOption)
                                <span class="jp-mcq-tag">Correct</span>
                            @elseif($showSelected)
                                <span class="jp-mcq-tag">Your answer</span>
                            @endif
                        </button>
                        @endforeach
                    </div>

                    <div class="jp-result" data-mcq-result="{{ $mcqEid }}">
                        @if($mcqAwaiting)
                            <span class="jp-result--wait">Answer submitted — waiting for mentor review</span>
                            @if($attempt)
                            <p style="margin:.35rem 0 0;font-size:.78rem;color:var(--text-2);">
                                You selected:
                                <strong>{{ is_array($options[$attempt->selected_index] ?? null) ? ($options[$attempt->selected_index]['text'] ?? '') : ($options[$attempt->selected_index] ?? '—') }}</strong>
                            </p>
                            @endif
                        @elseif($mcqApproved)
                            <span class="jp-result--ok">
                                Approved by mentor
                                @if($attempt && (int) $attempt->points_earned > 0)
                                    · +{{ $attempt->points_earned }} pts
                                @endif
                            </span>
                            @if($mcq->explanation)
                                <p style="margin:.35rem 0 0;font-size:.78rem;color:var(--text-2);">{{ $mcq->explanation }}</p>
                            @endif
                        @elseif($mcqRejected)
                            <span class="jp-result--bad">Mentor requested changes — pick an answer and resubmit</span>
                            @if($attempt)
                            <p style="margin:.35rem 0 0;font-size:.78rem;color:var(--text-2);">
                                Previous answer:
                                <strong>{{ is_array($options[$attempt->selected_index] ?? null) ? ($options[$attempt->selected_index]['text'] ?? '') : ($options[$attempt->selected_index] ?? '—') }}</strong>
                            </p>
                            @endif
                        @endif
                        @if($canViewProgress && $mcqProgress?->mentor_feedback)
                            <div class="jp-feedback">
                                <strong>Mentor feedback</strong>
                                {{ $mcqProgress->mentor_feedback }}
                            </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="empty-block" style="padding:1.25rem;">
                    <p>No MCQs for this week.</p>
                </div>
                @endforelse
            </div>
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
                    box.innerHTML = `<span class="jp-result--wait">Answer submitted. Scores unlock with Progress report.</span>`;
                }
                showToast('success', data.message || 'Answer submitted.');
                return;
            }
            if (box) {
                if (data.awaiting_review || data.submission_status === 'submitted') {
                    box.innerHTML = `<span class="jp-result--wait">Answer submitted — waiting for mentor review</span>`;
                } else if (data.correct) {
                    box.innerHTML = `<span class="jp-result--ok">Approved by mentor · +${data.points_earned || 0} pts</span>`;
                    if (data.explanation) {
                        box.innerHTML += `<p style="margin:.35rem 0 0;font-size:.78rem;color:var(--text-2);">${data.explanation}</p>`;
                    }
                } else {
                    box.innerHTML = `<span class="jp-result--wait">Answer submitted — waiting for mentor review</span>`;
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
