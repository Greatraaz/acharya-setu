@extends('admin.layouts.app')
@section('title', 'Quiz — ' . $quiz->title)
 
@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Quiz Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-display text-xl font-bold text-gray-900">{{ $quiz->title }}</h1>
            <p class="text-sm text-gray-500">{{ $quiz->questions->count() }} questions</p>
        </div>
        @if($quiz->time_limit)
        <div class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-xl text-sm font-semibold" id="timer">
            ⏱ <span id="time-display">{{ $quiz->time_limit }}:00</span>
        </div>
        @endif
    </div>
 
    {{-- Progress Bar --}}
    <div class="bg-gray-100 rounded-full h-1.5 mb-6">
        <div class="bg-indigo-500 h-1.5 rounded-full" style="width: 0%" id="progress-bar"></div>
    </div>
 
    <form method="POST" action="{{ route('admin.quizzes.submit', [$quiz, $attempt]) }}" id="quiz-form">
        @csrf
        <div class="space-y-5">
            @foreach($quiz->questions as $qIndex => $question)
            <div class="bg-white border border-gray-100 rounded-2xl p-5 question-card" data-index="{{ $qIndex }}">
                <div class="flex items-start gap-3 mb-4">
                    <span class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                        {{ $qIndex + 1 }}
                    </span>
                    <p class="font-medium text-gray-800 leading-relaxed">{{ $question->question }}</p>
                </div>
                <div class="ml-10">
                    @if($question->type === 'mcq')
                    <div class="space-y-2">
                        @foreach($question->options as $option)
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/40 cursor-pointer transition-colors group">
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}"
                                   class="accent-indigo-600 flex-shrink-0">
                            <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $option->option_text }}</span>
                        </label>
                        @endforeach
                    </div>
 
                    @elseif($question->type === 'true_false')
                    <div class="flex gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $question->options->firstWhere('option_text', 'True')?->id }}"
                                   class="sr-only peer">
                            <span class="px-8 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 block
                                         peer-checked:bg-green-500 peer-checked:text-white peer-checked:border-green-500 hover:border-green-300 transition-colors">
                                True
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $question->options->firstWhere('option_text', 'False')?->id }}"
                                   class="sr-only peer">
                            <span class="px-8 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 block
                                         peer-checked:bg-red-400 peer-checked:text-white peer-checked:border-red-400 hover:border-red-300 transition-colors">
                                False
                            </span>
                        </label>
                    </div>
 
                    @elseif($question->type === 'short_answer')
                    <input type="text" name="answers[{{ $question->id }}]" placeholder="Type your answer..."
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @endif
 
                    @if($question->marks > 1)
                    <p class="text-xs text-gray-400 mt-2">{{ $question->marks }} marks</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
 
        <div class="mt-6 flex gap-3">
            <button type="submit"
                    onclick="return confirm('Submit quiz? You cannot change answers after submitting.')"
                    class="flex-1 bg-indigo-600 text-white py-3 rounded-xl font-medium hover:bg-indigo-700 transition-colors">
                Submit Quiz
            </button>
        </div>
    </form>
</div>
 
@push('scripts')
<script>
// Progress tracking
const cards = document.querySelectorAll('.question-card');
const bar   = document.getElementById('progress-bar');
 
document.querySelectorAll('input[type="radio"], input[type="text"]').forEach(input => {
    input.addEventListener('change', updateProgress);
    input.addEventListener('input', updateProgress);
});
 
function updateProgress() {
    const total    = cards.length;
    let answered   = 0;
    cards.forEach((card, i) => {
        const radios = card.querySelectorAll('input[type="radio"]:checked');
        const texts  = card.querySelectorAll('input[type="text"]');
        const hasText = Array.from(texts).some(t => t.value.trim());
        if (radios.length > 0 || hasText) answered++;
    });
    bar.style.width = Math.round((answered / total) * 100) + '%';
}
 
// Timer
@if($quiz->time_limit)
let remaining = {{ $quiz->time_limit * 60 }};
const display = document.getElementById('time-display');
const timer = setInterval(() => {
    remaining--;
    const m = Math.floor(remaining / 60);
    const s = remaining % 60;
    display.textContent = m + ':' + String(s).padStart(2, '0');
    if (remaining <= 60) display.parentElement.classList.add('bg-red-50', 'text-red-700');
    if (remaining <= 0) { clearInterval(timer); document.getElementById('quiz-form').submit(); }
}, 1000);
@endif
</script>
@endpush
@endsection