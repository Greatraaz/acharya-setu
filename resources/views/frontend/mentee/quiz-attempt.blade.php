@extends('frontend.layouts.app')
@section('title', 'Quiz — '.$quiz->title)

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between" style="flex-wrap:wrap;gap:12px;">
            <div>
                <div class="dash-title">{{ $quiz->title }}</div>
                <div class="dash-subtitle">{{ $quiz->questions->count() }} questions</div>
            </div>
            @if($quiz->time_limit)
            <div class="tag" id="timer" style="font-size:13px;padding:8px 12px;">⏱ <span id="time-display">{{ $quiz->time_limit }}:00</span></div>
            @endif
        </div>

        <div class="progress-bar" style="margin-bottom:20px;">
            <div class="progress-fill" id="progress-bar" style="width:0%"></div>
        </div>

        <form method="POST" action="{{ route('mentee.quizzes.submit', [$quiz, $attempt]) }}" id="quiz-form">
            @csrf
            @foreach($quiz->questions as $qIndex => $question)
            <div class="card question-card" style="margin-bottom:14px;padding:16px 18px;" data-index="{{ $qIndex }}">
                <div style="display:flex;gap:10px;align-items:flex-start;margin-bottom:12px;">
                    <span style="width:28px;height:28px;border-radius:999px;background:var(--brand-muted);color:var(--brand);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;">{{ $qIndex + 1 }}</span>
                    <div style="font-size:14px;font-weight:700;line-height:1.5;">{{ $question->question }}</div>
                </div>

                @if($question->type === 'mcq' || $question->type === 'true_false')
                <div style="display:grid;gap:8px;padding-left:38px;">
                    @foreach($question->options as $option)
                    <label style="display:flex;gap:10px;align-items:flex-start;padding:10px 12px;border:1px solid var(--border);border-radius:var(--radius);cursor:pointer;">
                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" style="margin-top:3px;accent-color:var(--brand);">
                        <span style="font-size:13px;color:var(--text-2);">{{ $option->option_text }}</span>
                    </label>
                    @endforeach
                </div>
                @elseif($question->type === 'short_answer')
                <div style="padding-left:38px;">
                    <input type="text" name="answers[{{ $question->id }}]" class="form-input" placeholder="Type your answer...">
                </div>
                @endif

                @if($question->marks > 1)
                <div style="font-size:11px;color:var(--text-3);margin-top:8px;padding-left:38px;">{{ $question->marks }} marks</div>
                @endif
            </div>
            @endforeach

            <button type="submit" class="btn btn-primary btn-lg"
                    onclick="return confirm('Submit quiz? You cannot change answers after submitting.')">
                Submit Quiz
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const cards = document.querySelectorAll('.question-card');
const bar = document.getElementById('progress-bar');

function updateProgress() {
    let answered = 0;
    cards.forEach(card => {
        const radio = card.querySelector('input[type="radio"]:checked');
        const text = card.querySelector('input[type="text"]');
        if (radio || (text && text.value.trim())) answered++;
    });
    if (bar && cards.length) bar.style.width = ((answered / cards.length) * 100) + '%';
}

document.querySelectorAll('input[type="radio"], input[type="text"]').forEach(input => {
    input.addEventListener('change', updateProgress);
    input.addEventListener('input', updateProgress);
});

@if($quiz->time_limit)
let remaining = {{ (int) $quiz->time_limit }} * 60;
const display = document.getElementById('time-display');
const tick = setInterval(() => {
    remaining--;
    if (remaining <= 0) {
        clearInterval(tick);
        document.getElementById('quiz-form').submit();
        return;
    }
    const m = Math.floor(remaining / 60);
    const s = String(remaining % 60).padStart(2, '0');
    if (display) display.textContent = `${m}:${s}`;
}, 1000);
@endif
</script>
@endpush
