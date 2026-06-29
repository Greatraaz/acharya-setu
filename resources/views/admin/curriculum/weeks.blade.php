@extends('layouts.app')
@section('title', 'Week ' . $week->week_number . ' — ' . $week->title)
@section('content')

<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root { --c: {{ $week->month->stream->color ?: '#7c3aed' }}; --cl: {{ $week->month->stream->color ?: '#7c3aed' }}15; }
    body { font-family: 'DM Sans', sans-serif; background: #f8f7ff; }
    .display-font { font-family: 'DM Serif Display', serif; }

    /* MCQ styles */
    .mcq-option { transition: all .15s ease; cursor: pointer; }
    .mcq-option:hover:not(.answered) { border-color: var(--c); background: var(--cl); }
    .mcq-option.selected { border-color: var(--c); background: var(--cl); }
    .mcq-option.correct { border-color: #16a34a !important; background: #f0fdf4 !important; }
    .mcq-option.wrong   { border-color: #dc2626 !important; background: #fef2f2 !important; }
    .mcq-option.answered { cursor: default; }

    /* Task checkbox */
    .task-check-btn { transition: all .2s; }
    .task-check-btn.done { background: #16a34a !important; border-color: #16a34a !important; }

    @keyframes pop { 0%{transform:scale(1)} 50%{transform:scale(1.15)} 100%{transform:scale(1)} }
    .pop { animation: pop .3s ease; }
</style>

<div class="min-h-screen" style="background:#f8f7ff;">

    {{-- Week header --}}
    <div class="sticky top-0 z-30 bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-3xl mx-auto px-6 py-3 flex items-center gap-4">
            <a href="{{ route('mentee.journey.month', $week->month) }}"
               class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div class="flex-1 min-w-0">
                <div class="text-xs text-gray-400 font-medium">Month {{ $week->month->month_number }} · Week {{ $week->week_number }}</div>
                <div class="font-bold text-gray-900 truncate">{{ $week->title }}</div>
            </div>
            <div class="flex-shrink-0 flex items-center gap-2">
                <div class="h-2 w-32 bg-gray-100 rounded-full overflow-hidden">
                    <div id="top-progress-bar" class="h-full rounded-full transition-all duration-500"
                         style="width:{{ $progress['percent'] }}%;background:var(--c);"></div>
                </div>
                <span id="top-progress-txt" class="text-xs font-bold text-gray-600">{{ $progress['percent'] }}%</span>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-6 py-8 space-y-8">

        {{-- Week intro --}}
        @if($week->focus || $week->video_url)
        <div class="bg-white border border-gray-200 rounded-2xl p-6">
            @if($week->focus)
            <h2 class="display-font text-xl text-gray-800 mb-2">{{ $week->focus }}</h2>
            @endif
            @if($week->video_url)
            <a href="{{ $week->video_url }}" target="_blank"
               class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl text-white mt-2 hover:opacity-90 transition-opacity"
               style="background:var(--c);">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M6.271 5.055a.5.5 0 0 1 .52.038l3.5 2.5a.5.5 0 0 1 0 .814l-3.5 2.5A.5.5 0 0 1 6 10.5v-5a.5.5 0 0 1 .271-.445z"/></svg>
                Watch Intro Video
            </a>
            @endif
        </div>
        @endif

        {{-- Tasks Section --}}
        @if($week->tasks->isNotEmpty())
        <div>
            <h3 class="display-font text-xl text-gray-800 mb-4">Tasks <span class="text-sm font-normal text-gray-400 ml-2">{{ $week->tasks->count() }} items</span></h3>
            <div class="space-y-3">
                @foreach($week->tasks as $task)
                @php
                $prog = $taskStatuses[$task->id] ?? null;
                $done = $prog?->is_completed ?? false;
                $submitted = in_array($prog?->submission_status, ['submitted','approved','rejected']);
                @endphp
                <div class="bg-white border rounded-2xl transition-all {{ $done ? 'border-green-200 bg-green-50/30' : 'border-gray-200' }}"
                     id="task-card-{{ $task->id }}">
                    <div class="flex items-start gap-4 p-5">
                        <div class="flex-shrink-0 mt-0.5">
                            @if($task->submission_type && $task->submission_type !== 'none')
                            {{-- Submit button --}}
                            <button onclick="toggleSubmit({{ $task->id }})"
                                    class="task-check-btn w-9 h-9 rounded-xl border-2 flex items-center justify-center transition-all
                                    {{ $done ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 text-gray-300 hover:border-[var(--c)]' }}"
                                    style="{{ $done ? '' : 'color:var(--c);border-color:var(--c)' }}">
                                @if($done)
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                @else
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                @endif
                            </button>
                            @else
                            {{-- Simple complete toggle --}}
                            <button onclick="toggleTask({{ $task->id }}, this)"
                                    class="task-check-btn w-9 h-9 rounded-xl border-2 flex items-center justify-center transition-all {{ $done ? 'done' : '' }}"
                                    data-done="{{ $done ? '1' : '0' }}"
                                    style="{{ $done ? 'background:#16a34a;border-color:#16a34a;color:white;' : 'border-color:var(--c);color:var(--c);' }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </button>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                <span class="text-base">{{ \App\Models\CurriculumTask::TYPE_ICONS[$task->type] ?? '•' }}</span>
                                <h4 class="font-bold text-gray-900 text-sm {{ $done ? 'line-through text-gray-400' : '' }}">{{ $task->title }}</h4>
                                @if($task->is_required)<span class="text-xs bg-red-50 text-red-500 px-1.5 py-0.5 rounded font-medium">Required</span>@endif
                                @if($task->estimated_minutes)<span class="text-xs text-gray-400">⏱ {{ $task->estimated_minutes }}m</span>@endif
                            </div>
                            @if($task->description)
                            <p class="text-sm text-gray-500 leading-relaxed">{{ $task->description }}</p>
                            @endif

                            {{-- Submission status --}}
                            @if($submitted)
                            @php $ss = $prog->submission_status; @endphp
                            <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full
                                {{ $ss==='approved'?'bg-green-50 text-green-700':($ss==='rejected'?'bg-red-50 text-red-700':'bg-amber-50 text-amber-700') }}">
                                {{ ['submitted'=>'⏳ Under Review','approved'=>'✅ Approved','rejected'=>'❌ Needs Revision'][$ss] ?? $ss }}
                            </div>
                            @if($prog->mentor_feedback)
                            <div class="mt-2 p-3 bg-blue-50 border border-blue-100 rounded-xl">
                                <p class="text-xs font-semibold text-blue-800 mb-0.5">Mentor Feedback</p>
                                <p class="text-xs text-blue-700">{{ $prog->mentor_feedback }}</p>
                            </div>
                            @endif
                            @endif

                            {{-- Submission form (expandable) --}}
                            @if($task->submission_type && $task->submission_type !== 'none' && !$done)
                            <div id="submit-{{ $task->id }}" class="hidden mt-4 border-t border-gray-100 pt-4">
                                <form method="POST" action="{{ route('mentee.journey.task.complete', $task) }}"
                                      enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    @if($task->submission_type === 'text')
                                    <textarea name="submission_text" rows="4" required placeholder="Write your response here…"
                                              class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-[var(--c)] focus:ring-2 resize-none"></textarea>
                                    @elseif($task->submission_type === 'file')
                                    <input type="file" name="submission_file" required class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:text-white file:cursor-pointer hover:file:opacity-90" style="file-selector-button-background:var(--c)">
                                    @elseif($task->submission_type === 'link')
                                    <input type="url" name="submission_url" required placeholder="https://" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-[var(--c)] focus:ring-2">
                                    @endif
                                    <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold text-white px-4 py-2 rounded-xl hover:opacity-90 transition-opacity" style="background:var(--c);">
                                        Submit
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- MCQ Section --}}
        @if($week->mcqs->isNotEmpty())
        <div>
            <h3 class="display-font text-xl text-gray-800 mb-4">Knowledge Check <span class="text-sm font-normal text-gray-400 ml-2">{{ $week->mcqs->count() }} questions</span></h3>
            <div class="space-y-5">
                @foreach($week->mcqs as $qIdx => $mcq)
                @php
                $attempt = $mcqStatuses[$mcq->id] ?? null;
                $answered = $attempt !== null;
                $correct  = $attempt?->is_correct ?? false;
                @endphp
                <div class="bg-white border rounded-2xl overflow-hidden {{ $answered ? ($correct ? 'border-green-200' : 'border-red-200') : 'border-gray-200' }}"
                     id="mcq-{{ $mcq->id }}">
                    <div class="p-5">
                        <div class="flex items-start gap-3 mb-4">
                            <span class="w-7 h-7 rounded-lg text-xs font-bold flex items-center justify-center text-white flex-shrink-0 mt-0.5"
                                  style="background:{{ \App\Models\CurriculumMcq::DIFFICULTY_COLORS[$mcq->difficulty] ?? '#6b7280' }};">
                                Q{{ $qIdx + 1 }}
                            </span>
                            <p class="text-sm font-semibold text-gray-900 leading-snug">{{ $mcq->question }}</p>
                        </div>

                        <div class="space-y-2" id="options-{{ $mcq->id }}">
                            @foreach($mcq->options as $i => $option)
                            @php
                            $btnClass = '';
                            if ($answered) {
                                if ($i === $mcq->correct_index) $btnClass = 'correct';
                                elseif ($attempt?->selected_index === $i) $btnClass = 'wrong';
                                else $btnClass = 'answered';
                            }
                            @endphp
                            <button onclick="{{ $answered ? '' : "answerMcq({$mcq->id}, {$i})" }}"
                                    class="mcq-option w-full text-left flex items-start gap-3 px-4 py-3 border-2 rounded-xl {{ $answered ? 'answered' : '' }} {{ $btnClass }}"
                                    id="opt-{{ $mcq->id }}-{{ $i }}"
                                    {{ $answered ? 'disabled' : '' }}>
                                <span class="w-6 h-6 rounded-full border-2 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5
                                    {{ $btnClass === 'correct' ? 'bg-green-500 border-green-500 text-white' : ($btnClass === 'wrong' ? 'bg-red-500 border-red-500 text-white' : 'border-gray-300 text-gray-500') }}">
                                    {{ chr(65 + $i) }}
                                </span>
                                <span class="text-sm text-gray-700">{{ $option }}</span>
                                @if($btnClass === 'correct')
                                <span class="ml-auto text-green-500 font-bold">✓</span>
                                @elseif($btnClass === 'wrong')
                                <span class="ml-auto text-red-500 font-bold">✗</span>
                                @endif
                            </button>
                            @endforeach
                        </div>

                        {{-- Explanation (shown after answer) --}}
                        @if($answered && $mcq->explanation)
                        <div class="mt-4 p-3 bg-blue-50 border border-blue-100 rounded-xl">
                            <p class="text-xs font-semibold text-blue-800 mb-0.5">💡 Explanation</p>
                            <p class="text-sm text-blue-700">{{ $mcq->explanation }}</p>
                        </div>
                        @else
                        <div id="explanation-{{ $mcq->id }}" class="hidden mt-4 p-3 bg-blue-50 border border-blue-100 rounded-xl">
                            <p class="text-xs font-semibold text-blue-800 mb-0.5">💡 Explanation</p>
                            <p class="text-sm text-blue-700">{{ $mcq->explanation }}</p>
                        </div>
                        @endif

                        {{-- Result indicator (dynamic) --}}
                        <div id="result-{{ $mcq->id }}" class="{{ $answered ? '' : 'hidden' }} mt-3 flex items-center gap-2">
                            @if($answered)
                            <span class="text-sm font-semibold {{ $correct ? 'text-green-700' : 'text-red-700' }}">
                                {{ $correct ? '🎉 Correct! +' . $mcq->points . ' pts' : '❌ Incorrect' }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Weekly Check-in --}}
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-bold text-gray-900">Weekly Check-in</h3>
                <p class="text-sm text-gray-500 mt-0.5">Share your progress with your mentor</p>
            </div>
            @if($checkin && $checkin->submitted_at)
            <div class="p-5 space-y-3">
                <div class="flex items-center gap-2 text-sm text-green-700 bg-green-50 border border-green-200 px-3 py-2 rounded-xl font-medium">
                    ✅ Check-in submitted {{ $checkin->submitted_at->diffForHumans() }}
                </div>
                @if($checkin->mentor_response)
                <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl">
                    <p class="text-xs font-bold text-blue-800 mb-1">Mentor's Response</p>
                    <p class="text-sm text-blue-700">{{ $checkin->mentor_response }}</p>
                    <p class="text-xs text-blue-400 mt-1">{{ $checkin->mentor_replied_at?->format('d M, H:i') }}</p>
                </div>
                @else
                <p class="text-xs text-gray-400 italic">Waiting for mentor response…</p>
                @endif
            </div>
            @else
            <form method="POST" action="{{ route('mentee.journey.checkin', $week) }}" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">How are you feeling this week?</label>
                    <div class="flex gap-3">
                        @foreach(\App\Models\WeeklyCheckin::MOOD_LABELS as $val => $label)
                        <label class="flex-1 cursor-pointer text-center">
                            <input type="radio" name="mood_score" value="{{ $val }}" class="sr-only peer" required>
                            <div class="py-2 rounded-xl border-2 border-gray-200 peer-checked:border-[var(--c)] peer-checked:bg-[var(--cl)] transition-all text-xl leading-none">
                                {{ explode(' ', $label)[0] }}
                            </div>
                            <div class="text-xs text-gray-400 mt-1">{{ explode(' ', $label, 2)[1] ?? '' }}</div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @foreach([['wins','🏆 What went well this week?','Share your wins and progress…'],['challenges','💪 What was challenging?','Any blockers or difficulties…'],['questions','❓ Questions for your mentor','What do you want to discuss?']] as [$name,$label,$placeholder])
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ $label }}</label>
                    <textarea name="{{ $name }}" rows="2" placeholder="{{ $placeholder }}"
                              class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-[var(--c)] focus:ring-2 resize-none"></textarea>
                </div>
                @endforeach
                <button type="submit" class="w-full text-white text-sm font-semibold py-3 rounded-xl hover:opacity-90 transition-opacity"
                        style="background:var(--c);">
                    Submit Weekly Check-in
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

<script>
const PROGRESS = { completed: {{ $progress['completed'] }}, total: {{ $progress['total'] }} };

function updateProgressUI() {
    const pct = PROGRESS.total ? Math.round(PROGRESS.completed / PROGRESS.total * 100) : 0;
    document.getElementById('top-progress-bar').style.width = pct + '%';
    document.getElementById('top-progress-txt').textContent = pct + '%';
}

// Simple task complete (no submission)
async function toggleTask(taskId, btn) {
    const done = btn.dataset.done === '1';
    const newDone = !done;
    btn.dataset.done = newDone ? '1' : '0';

    // Optimistic UI
    if (newDone) {
        btn.classList.add('done', 'pop');
        btn.style.cssText = 'background:#16a34a;border-color:#16a34a;color:white;';
        document.getElementById('task-card-' + taskId).classList.add('border-green-200', 'bg-green-50/30');
        document.getElementById('task-card-' + taskId).classList.remove('border-gray-200');
        PROGRESS.completed = Math.min(PROGRESS.completed + 1, PROGRESS.total);
    } else {
        btn.classList.remove('done');
        btn.style.cssText = 'border-color:var(--c);color:var(--c);';
        document.getElementById('task-card-' + taskId).classList.remove('border-green-200', 'bg-green-50/30');
        document.getElementById('task-card-' + taskId).classList.add('border-gray-200');
        PROGRESS.completed = Math.max(0, PROGRESS.completed - 1);
    }
    updateProgressUI();
    setTimeout(() => btn.classList.remove('pop'), 300);

    try {
        await fetch("{{ route('mentee.journey.task.complete', ['task' => '__ID__']) }}".replace('__ID__', taskId), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({}),
        });
    } catch(e) { console.error(e); }
}

// Toggle submission form
function toggleSubmit(taskId) {
    const form = document.getElementById('submit-' + taskId);
    form.classList.toggle('hidden');
}

// MCQ answer
async function answerMcq(mcqId, selectedIndex) {
    // Disable all options immediately
    document.querySelectorAll(`#options-${mcqId} button`).forEach(b => {
        b.classList.add('answered');
        b.setAttribute('disabled', true);
    });

    try {
        const res = await fetch("{{ route('mentee.journey.mcq.answer', ['mcq' => '__ID__']) }}".replace('__ID__', mcqId), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ selected_index: selectedIndex }),
        });
        const data = await res.json();

        // Style the options
        const correctBtn = document.getElementById(`opt-${mcqId}-${data.correct_index}`);
        const selectedBtn = document.getElementById(`opt-${mcqId}-${selectedIndex}`);

        correctBtn.classList.add('correct');
        correctBtn.querySelector('span').classList.add('bg-green-500', 'border-green-500', 'text-white');
        correctBtn.innerHTML += '<span class="ml-auto text-green-500 font-bold">✓</span>';

        if (selectedIndex !== data.correct_index) {
            selectedBtn.classList.add('wrong');
        }

        // Show explanation
        if (data.explanation) {
            const exp = document.getElementById('explanation-' + mcqId);
            if (exp) exp.classList.remove('hidden');
        }

        // Show result
        const result = document.getElementById('result-' + mcqId);
        result.classList.remove('hidden');
        result.innerHTML = data.correct
            ? `<span class="text-sm font-semibold text-green-700">🎉 Correct! +${data.points_earned} pts</span>`
            : `<span class="text-sm font-semibold text-red-700">❌ Incorrect — the correct answer was highlighted</span>`;

        if (data.correct) {
            PROGRESS.completed++;
            updateProgressUI();
        }
    } catch(e) { console.error(e); }
}
</script>

@endsection