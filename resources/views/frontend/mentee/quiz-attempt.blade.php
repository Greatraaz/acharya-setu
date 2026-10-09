@extends('frontend.layouts.app')
@section('title', 'Quiz — '.$quiz->title)

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content mq">
        <a href="{{ route('mentee.quizzes.show', $quiz) }}" class="mq-back">← Back</a>

        <form method="POST" action="{{ route('mentee.quizzes.submit', [$quiz, $attempt]) }}" id="quiz-form" class="mq-attempt">
            @csrf
            <div class="mq-attempt__bar">
                <div class="mq-attempt__heading">
                    <h1 class="mq-attempt__title">{{ $quiz->title }}</h1>
                    <p class="mq-attempt__sub">Attempt {{ $attemptNumber }} · {{ $quiz->questions->count() }} {{ \Illuminate\Support\Str::plural('question', $quiz->questions->count()) }}</p>
                </div>
                <div class="mq-attempt__actions">
                    @if($quiz->time_limit)
                    <div class="mq-timer" id="timer">⏱ <span id="time-display">{{ $quiz->time_limit }}:00</span></div>
                    @endif
                    <div class="mq-attempt__count"><span id="answered-count">0</span>/{{ $quiz->questions->count() }} answered</div>
                    <button type="button" class="btn btn-primary" id="quiz-submit-open">Submit quiz</button>
                </div>
            </div>

            <div class="progress-bar mq-progress" aria-hidden="true">
                <div class="progress-fill" id="progress-bar" style="width:0%"></div>
            </div>

            <div class="mq-attempt__main">
                @foreach($quiz->questions as $qIndex => $question)
                <section class="card question-card mq-question" data-index="{{ $qIndex }}">
                    <div class="mq-question__head">
                        <span class="mq-question__num">{{ $qIndex + 1 }}</span>
                        <div>
                            <h2 class="mq-question__text">{{ $question->question }}</h2>
                            @if($question->marks > 1)
                            <div class="mq-question__marks">{{ $question->marks }} marks</div>
                            @endif
                        </div>
                    </div>

                    @if($question->type === 'mcq' || $question->type === 'true_false')
                    <div class="mq-options" role="radiogroup" aria-label="{{ $question->question }}">
                        @foreach($question->options as $option)
                        <label class="mq-option">
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}">
                            <span class="mq-option__mark" aria-hidden="true"></span>
                            <span class="mq-option__text">{{ $option->option_text }}</span>
                        </label>
                        @endforeach
                    </div>
                    @elseif($question->type === 'short_answer')
                    <input type="text" name="answers[{{ $question->id }}]" class="form-input" placeholder="Type your answer…">
                    @endif
                </section>
                @endforeach
            </div>

            <div class="mq-modal" id="quiz-confirm" hidden>
                <div class="mq-modal__backdrop" data-close></div>
                <div class="mq-modal__card" role="dialog" aria-modal="true" aria-labelledby="quiz-confirm-title">
                    <h3 id="quiz-confirm-title">Submit this attempt?</h3>
                    <p id="quiz-confirm-copy">Your score is saved for this attempt. You can retake the quiz anytime.</p>
                    <div class="mq-modal__actions">
                        <button type="button" class="btn btn-outline" data-close>Keep editing</button>
                        <button type="submit" class="btn btn-primary" id="quiz-submit-final">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('quiz-form');
    const cards = document.querySelectorAll('.question-card');
    const bar = document.getElementById('progress-bar');
    const countEl = document.getElementById('answered-count');
    const modal = document.getElementById('quiz-confirm');
    const copy = document.getElementById('quiz-confirm-copy');
    const openBtn = document.getElementById('quiz-submit-open');
    const finalBtn = document.getElementById('quiz-submit-final');

    function answeredCount() {
        let answered = 0;
        cards.forEach((card) => {
            const radio = card.querySelector('input[type="radio"]:checked');
            const text = card.querySelector('input[type="text"]');
            if (radio || (text && text.value.trim())) answered++;
        });
        return answered;
    }

    function updateProgress() {
        const answered = answeredCount();
        if (countEl) countEl.textContent = String(answered);
        if (bar && cards.length) bar.style.width = ((answered / cards.length) * 100) + '%';
    }

    document.querySelectorAll('#quiz-form input[type="radio"], #quiz-form input[type="text"]').forEach((input) => {
        input.addEventListener('change', updateProgress);
        input.addEventListener('input', updateProgress);
    });
    updateProgress();

    function openModal() {
        const answered = answeredCount();
        const missing = cards.length - answered;
        copy.textContent = missing > 0
            ? missing + ' question' + (missing === 1 ? ' is' : 's are') + ' still unanswered and will be marked wrong. You can retake this quiz later.'
            : 'Your score is saved for this attempt. You can retake the quiz anytime.';
        modal.hidden = false;
    }

    function closeModal() {
        modal.hidden = true;
    }

    openBtn?.addEventListener('click', openModal);
    modal?.querySelectorAll('[data-close]').forEach((el) => el.addEventListener('click', closeModal));

    form?.addEventListener('submit', () => {
        if (finalBtn) finalBtn.disabled = true;
    });

    @if($quiz->time_limit)
    let remaining = {{ (int) $quiz->time_limit }} * 60;
    const display = document.getElementById('time-display');
    const tick = setInterval(() => {
        remaining--;
        if (remaining <= 0) {
            clearInterval(tick);
            closeModal();
            form.submit();
            return;
        }
        const m = Math.floor(remaining / 60);
        const s = String(remaining % 60).padStart(2, '0');
        if (display) display.textContent = m + ':' + s;
    }, 1000);
    @endif
})();
</script>
@endpush
