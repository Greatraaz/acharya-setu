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
            @php $done = ($canViewProgress ?? false) && in_array($task->id, $completedTaskIds ?? [], true); @endphp
            <div class="journey-page__item-card" id="task-{{ $task->id }}">
                <div class="journey-page__item-row">
                    <div class="journey-page__item-body">
                        <div class="journey-page__item-head">
                            <span class="journey-page__item-icon">{{ \App\Models\CurriculumTask::TYPE_ICONS[$task->type] ?? '✅' }}</span>
                            <h4 class="journey-page__item-title">{{ $task->title }}</h4>
                            @if($done)<span class="session-status completed">Done</span>@endif
                        </div>
                        @if($task->description)
                        <p class="journey-page__item-desc">{{ $task->description }}</p>
                        @endif
                        <div class="journey-page__item-meta">
                            {{ ucfirst($task->type ?? 'task') }}
                            @if($task->estimated_minutes) · {{ $task->estimated_minutes }} min @endif
                            @if($task->is_required) · Required @endif
                        </div>
                    </div>
                    @unless($done)
                    <button type="button" class="btn btn-primary btn-sm journey-page__item-action" onclick="completeTask({{ $task->id }}, this)">
                        {{ ($canViewProgress ?? false) ? 'Mark done' : 'Submit' }}
                    </button>
                    @endunless
                </div>
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
                $options = is_array($mcq->options) ? $mcq->options : [];
            @endphp
            <div class="journey-page__item-card" id="mcq-{{ $mcq->id }}">
                <p class="journey-page__mcq-question">{{ $mcq->question }}</p>
                <div class="journey-page__mcq-options" data-mcq-options="{{ $mcq->id }}">
                    @foreach($options as $idx => $option)
                    <button type="button"
                        class="btn btn-ghost journey-page__mcq-option"
                        @if($attempt && (int)$attempt->selected_index === (int)$idx) style="border-color:var(--brand);" @endif
                        @if($attempt && $attempt->is_correct) disabled @endif
                        onclick="answerMcq({{ $mcq->id }}, {{ (int)$idx }}, this)">
                        <span class="journey-page__mcq-option-label">{{ chr(65 + (int)$idx) }}.</span>
                        <span class="journey-page__mcq-option-text">{{ is_array($option) ? ($option['text'] ?? json_encode($option)) : $option }}</span>
                    </button>
                    @endforeach
                </div>
                <div class="journey-page__mcq-result" data-mcq-result="{{ $mcq->id }}">
                    @if($attempt)
                        @if($attempt->is_correct)
                            <span class="journey-page__mcq-result--correct">Correct · +{{ $attempt->points_earned }} pts</span>
                        @else
                            <span class="journey-page__mcq-result--wrong">Incorrect — try again</span>
                        @endif
                        @if($mcq->explanation)
                        <p class="journey-page__mcq-explanation">{{ $mcq->explanation }}</p>
                        @endif
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
                box.innerHTML = data.correct
                    ? `<span class="journey-page__mcq-result--correct">Correct · +${data.points_earned || 0} pts</span>`
                    : `<span class="journey-page__mcq-result--wrong">Incorrect — try again</span>`;
                if (data.explanation) {
                    box.innerHTML += `<p class="journey-page__mcq-explanation">${data.explanation}</p>`;
                }
            }
            showToast(data.correct ? 'success' : 'error', data.correct ? 'Correct!' : 'Not quite — try again.');
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
