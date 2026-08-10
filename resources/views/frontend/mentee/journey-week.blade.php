@extends('frontend.layouts.app')
@section('title', ($week->title ?: 'Week '.$week->week_number).' — Journey')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div style="font-size:12px;margin-bottom:8px;">
                <a href="{{ route('mentee.journey.index') }}" style="color:var(--brand);">My Journey</a>
                <span style="color:var(--text-3);"> / </span>
                <a href="{{ route('mentee.journey.month', $week->month_id) }}" style="color:var(--brand);">Month {{ $week->month->month_number ?? '' }}</a>
                <span style="color:var(--text-3);"> / </span>
                <span style="color:var(--text-2);">Week {{ $week->week_number }}</span>
            </div>
            <div class="dash-title">{{ $week->title ?: 'Week '.$week->week_number }}</div>
            <div class="dash-subtitle">{{ $week->focus ?: 'Tasks, quizzes, and weekly check-in' }}</div>
        </div>

        @if($canViewProgress ?? false)
        <div class="card" style="margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-2);margin-bottom:8px;">
                <span>Week progress</span>
                <span>{{ (int) ($progress['percent'] ?? 0) }}%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width:{{ (int) ($progress['percent'] ?? 0) }}%"></div>
            </div>
        </div>
        @else
        <div class="alert alert-warning" style="margin-bottom:16px;">
            <span class="alert-icon">🔒</span>
            <div style="font-size:13px;">
                You can work on tasks and MCQs. Scores, past submissions, and completion status stay hidden until you upgrade.
                <a href="{{ route('mentee.plans') }}" style="color:var(--brand);font-weight:600;">View plans →</a>
            </div>
        </div>
        @endif

        {{-- Tasks --}}
        <div class="card" style="margin-bottom:20px;">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">Tasks</h3>
            @forelse($week->tasks as $task)
            @php $done = ($canViewProgress ?? false) && in_array($task->id, $completedTaskIds ?? [], true); @endphp
            <div class="card" style="margin-bottom:12px;padding:14px 16px;" id="task-{{ $task->id }}">
                <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                    <div style="flex:1;">
                        <div style="display:flex;gap:8px;align-items:center;margin-bottom:4px;">
                            <span>{{ \App\Models\CurriculumTask::TYPE_ICONS[$task->type] ?? '✅' }}</span>
                            <div style="font-size:14px;font-weight:700;">{{ $task->title }}</div>
                            @if($done)<span class="session-status completed">Done</span>@endif
                        </div>
                        @if($task->description)
                        <div style="font-size:13px;color:var(--text-2);line-height:1.55;">{{ $task->description }}</div>
                        @endif
                        <div style="font-size:11px;color:var(--text-3);margin-top:6px;">
                            {{ ucfirst($task->type ?? 'task') }}
                            @if($task->estimated_minutes) · {{ $task->estimated_minutes }} min @endif
                            @if($task->is_required) · Required @endif
                        </div>
                    </div>
                    @unless($done)
                    <button type="button" class="btn btn-primary btn-sm" onclick="completeTask({{ $task->id }}, this)">
                        {{ ($canViewProgress ?? false) ? 'Mark done' : 'Submit' }}
                    </button>
                    @endunless
                </div>
            </div>
            @empty
            <div style="font-size:13px;color:var(--text-2);">No tasks for this week.</div>
            @endforelse
        </div>

        {{-- MCQs --}}
        <div class="card" style="margin-bottom:20px;">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">Practice MCQs</h3>
            @forelse($week->mcqs as $mcq)
            @php
                $attempt = ($canViewProgress ?? false) ? ($mcqAttempts[$mcq->id] ?? null) : null;
                $options = is_array($mcq->options) ? $mcq->options : [];
            @endphp
            <div class="card" style="margin-bottom:12px;padding:14px 16px;" id="mcq-{{ $mcq->id }}">
                <div style="font-size:14px;font-weight:700;margin-bottom:10px;">{{ $mcq->question }}</div>
                <div style="display:grid;gap:8px;" data-mcq-options="{{ $mcq->id }}">
                    @foreach($options as $idx => $option)
                    <button type="button"
                        class="btn btn-ghost"
                        style="justify-content:flex-start;text-align:left;{{ $attempt && (int)$attempt->selected_index === (int)$idx ? 'border-color:var(--brand);' : '' }}"
                        @if($attempt && $attempt->is_correct) disabled @endif
                        onclick="answerMcq({{ $mcq->id }}, {{ (int)$idx }}, this)">
                        {{ chr(65 + (int)$idx) }}. {{ is_array($option) ? ($option['text'] ?? json_encode($option)) : $option }}
                    </button>
                    @endforeach
                </div>
                <div data-mcq-result="{{ $mcq->id }}" style="margin-top:10px;font-size:13px;">
                    @if($attempt)
                        @if($attempt->is_correct)
                            <span style="color:var(--success);font-weight:600;">Correct · +{{ $attempt->points_earned }} pts</span>
                        @else
                            <span style="color:var(--error);font-weight:600;">Incorrect — try again</span>
                        @endif
                        @if($mcq->explanation)
                        <div style="color:var(--text-2);margin-top:4px;">{{ $mcq->explanation }}</div>
                        @endif
                    @endif
                </div>
            </div>
            @empty
            <div style="font-size:13px;color:var(--text-2);">No MCQs for this week.</div>
            @endforelse
        </div>

        {{-- Check-in --}}
        <div class="card">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:12px;">Weekly Check-in</h3>
            @if(($canViewProgress ?? false) && $checkin)
            <div class="alert alert-success" style="margin-bottom:14px;">
                <span class="alert-icon">✅</span>
                <div style="font-size:13px;">Submitted {{ $checkin->submitted_at?->format('d M Y') ?? '' }}. Mood: {{ $checkin->mood_score ?? '—' }}/5</div>
            </div>
            @if($checkin->mentor_response)
            <div style="font-size:13px;background:var(--bg-2);border:1px solid var(--border);border-radius:var(--radius);padding:12px;margin-bottom:12px;">
                <strong>Mentor reply:</strong>
                <div style="margin-top:4px;color:var(--text-2);">{{ $checkin->mentor_response }}</div>
            </div>
            @endif
            @endif

            <div class="form-group">
                <label class="form-label">Mood (1–5)</label>
                <input type="number" id="checkin-mood" class="form-input" min="1" max="5" value="{{ ($canViewProgress ?? false) ? ($checkin->mood_score ?? 3) : 3 }}">
            </div>
            <div class="form-group">
                <label class="form-label">Wins this week</label>
                <textarea id="checkin-wins" class="form-input" rows="2" placeholder="What went well?">{{ ($canViewProgress ?? false) ? ($checkin->wins ?? '') : '' }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Challenges</label>
                <textarea id="checkin-challenges" class="form-input" rows="2" placeholder="What was hard?">{{ ($canViewProgress ?? false) ? ($checkin->challenges ?? '') : '' }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Questions for mentor</label>
                <textarea id="checkin-questions" class="form-input" rows="2" placeholder="Anything you want help with?">{{ ($canViewProgress ?? false) ? ($checkin->questions ?? '') : '' }}</textarea>
            </div>
            <button type="button" class="btn btn-primary" id="checkin-btn" onclick="submitCheckin()">
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
                    box.innerHTML = `<span style="color:var(--text-2);font-weight:600;">Answer submitted. Scores unlock with Progress report.</span>`;
                }
                showToast('success', data.message || 'Answer submitted.');
                return;
            }
            if (box) {
                box.innerHTML = data.correct
                    ? `<span style="color:var(--success);font-weight:600;">Correct · +${data.points_earned || 0} pts</span>`
                    : `<span style="color:var(--error);font-weight:600;">Incorrect — try again</span>`;
                if (data.explanation) {
                    box.innerHTML += `<div style="color:var(--text-2);margin-top:4px;">${data.explanation}</div>`;
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
