@extends('admin.layouts.app')
@section('title', 'Create Quiz')
@section('heading', 'Create Quiz')

@section('content')
@include('admin.partials.learning-ui')

<div class="learn-page max-w-2xl mx-auto space-y-5">
    <div class="learn-intro">
        <a href="{{ route('admin.quizzes.index') }}" class="text-sm text-slate-400 hover:text-slate-700">← Back to Quizzes</a>
        <span class="learn-eyebrow mt-3 inline-flex">Learning · New quiz</span>
        <h2>Create Quiz</h2>
        <p>Add questions, set a pass score, and publish when mentees should see it.</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.quizzes.store') }}" id="quiz-form" class="space-y-4">
        @csrf

        <div class="learn-panel p-6">
            <h2 class="font-bold text-slate-900 mb-4">Quiz details</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           placeholder="e.g. JavaScript Fundamentals"
                           class="learn-focus w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
                    <textarea name="description" rows="2"
                              class="learn-focus w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none resize-none">{{ old('description') }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Time limit (minutes)</label>
                        <input type="number" name="time_limit" value="{{ old('time_limit') }}" min="1" placeholder="No limit"
                               class="learn-focus w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Pass score (%)</label>
                        <input type="number" name="pass_score" value="{{ old('pass_score', 60) }}" required min="1" max="100"
                               class="learn-focus w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none">
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" checked class="accent-amber-600">
                    <span class="text-sm text-slate-700">Publish immediately for mentees</span>
                </label>
            </div>
        </div>

        <div class="learn-panel p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-slate-900">Questions</h2>
                <button type="button" onclick="addQuestion()" class="text-sm text-amber-700 hover:text-amber-900 font-semibold">+ Add Question</button>
            </div>
            <div id="questions-container" class="space-y-5"></div>
        </div>

        <button type="submit" class="learn-btn-primary w-full !py-3">Save Quiz</button>
    </form>
</div>

@push('scripts')
<script>
let qCount = 0;
let optCounts = {};

function addQuestion() {
    const c = document.getElementById('questions-container');
    const i = qCount++;
    const div = document.createElement('div');
    div.className = 'border border-slate-200 rounded-xl p-4 bg-slate-50 relative';
    div.id = 'qq-' + i;
    div.innerHTML = `
        <button type="button" onclick="document.getElementById('qq-${i}').remove()"
                class="absolute top-3 right-3 text-slate-300 hover:text-red-400 text-xl leading-none">×</button>
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Question ${i + 1}</label>
                <input type="text" name="questions[${i}][question]" required placeholder="Enter question..."
                       class="learn-focus w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none bg-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Type</label>
                    <select name="questions[${i}][type]" onchange="handleQType(this, ${i})"
                            class="learn-focus w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white outline-none">
                        <option value="mcq">Multiple Choice (MCQ)</option>
                        <option value="true_false">True / False</option>
                        <option value="short_answer">Short Answer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Marks</label>
                    <input type="number" name="questions[${i}][marks]" value="1" min="1" required
                           class="learn-focus w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white outline-none">
                </div>
            </div>
            <div id="opts-${i}" class="space-y-2">
                <label class="block text-xs font-medium text-slate-600">Options <span class="text-slate-400">(check correct)</span></label>
                <div id="opts-list-${i}" class="space-y-1.5"></div>
                <button type="button" onclick="addOption(${i})" class="text-xs text-amber-700 hover:text-amber-900 font-semibold">+ Add Option</button>
            </div>
            <div id="tf-${i}" class="hidden space-y-2">
                <label class="block text-xs font-medium text-slate-600 mb-1">Correct answer</label>
                <div class="flex gap-3">
                    <label class="flex items-center gap-1.5 text-sm text-slate-700 cursor-pointer">
                        <input type="radio" name="questions[${i}][tf_answer]" value="True" checked class="accent-amber-600" onchange="syncTrueFalse(${i})"> True
                    </label>
                    <label class="flex items-center gap-1.5 text-sm text-slate-700 cursor-pointer">
                        <input type="radio" name="questions[${i}][tf_answer]" value="False" class="accent-amber-600" onchange="syncTrueFalse(${i})"> False
                    </label>
                </div>
                <div id="tf-hidden-${i}"></div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Explanation (optional)</label>
                <input type="text" name="questions[${i}][explanation]" placeholder="Explain the correct answer..."
                       class="learn-focus w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white outline-none">
            </div>
        </div>
    `;
    c.appendChild(div);
    addOption(i);
    addOption(i);
}

function addOption(qi) {
    if (!optCounts[qi]) optCounts[qi] = 0;
    const oi = optCounts[qi]++;
    const list = document.getElementById('opts-list-' + qi);
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2';
    row.innerHTML = `
        <input type="checkbox" name="questions[${qi}][options][${oi}][is_correct]" value="1"
               class="accent-amber-600 flex-shrink-0">
        <input type="text" name="questions[${qi}][options][${oi}][text]" required placeholder="Option ${oi + 1}"
               class="learn-focus flex-1 border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm bg-white outline-none">
        <button type="button" onclick="this.parentElement.remove()" class="text-slate-300 hover:text-red-400">×</button>
    `;
    list.appendChild(row);
}

function syncTrueFalse(i) {
    const selected = document.querySelector(`input[name="questions[${i}][tf_answer]"]:checked`);
    const correct = selected ? selected.value : 'True';
    const wrap = document.getElementById('tf-hidden-' + i);
    if (!wrap) return;
    wrap.innerHTML = `
        <input type="hidden" name="questions[${i}][options][0][text]" value="True">
        <input type="hidden" name="questions[${i}][options][0][is_correct]" value="${correct === 'True' ? '1' : '0'}">
        <input type="hidden" name="questions[${i}][options][1][text]" value="False">
        <input type="hidden" name="questions[${i}][options][1][is_correct]" value="${correct === 'False' ? '1' : '0'}">
    `;
}

function handleQType(sel, i) {
    const opts = document.getElementById('opts-' + i);
    const tf = document.getElementById('tf-' + i);
    const list = document.getElementById('opts-list-' + i);
    if (sel.value === 'true_false') {
        opts.classList.add('hidden');
        tf.classList.remove('hidden');
        // Disable MCQ option required fields while hidden
        list.querySelectorAll('input').forEach(el => { el.disabled = true; el.required = false; });
        syncTrueFalse(i);
    } else if (sel.value === 'short_answer') {
        opts.classList.add('hidden');
        tf.classList.add('hidden');
        list.querySelectorAll('input').forEach(el => { el.disabled = true; el.required = false; });
        document.getElementById('tf-hidden-' + i).innerHTML = '';
    } else {
        opts.classList.remove('hidden');
        tf.classList.add('hidden');
        list.querySelectorAll('input[type="text"]').forEach(el => { el.disabled = false; el.required = true; });
        list.querySelectorAll('input[type="checkbox"]').forEach(el => { el.disabled = false; });
        document.getElementById('tf-hidden-' + i).innerHTML = '';
    }
}

document.getElementById('quiz-form').addEventListener('submit', function () {
    document.querySelectorAll('[id^="tf-"]:not(.hidden)').forEach(tf => {
        const id = tf.id.replace('tf-', '');
        syncTrueFalse(id);
    });
});

addQuestion();
</script>
@endpush
@endsection
