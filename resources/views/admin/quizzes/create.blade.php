@extends('admin.layouts.app')
@section('title', 'Create Quiz')
 
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.quizzes.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back</a>
        <h1 class="font-display text-2xl font-bold text-gray-900 mt-2">Create Quiz</h1>
    </div>
 
    <form method="POST" action="{{ route('admin.quizzes.store') }}" id="quiz-form">
        @csrf
 
        <div class="bg-white border border-gray-100 rounded-2xl p-6 mb-4">
            <h2 class="font-semibold text-gray-800 mb-4">Quiz Details</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           placeholder="e.g. JavaScript Fundamentals"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="2"
                              class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('description') }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Time Limit (minutes)</label>
                        <input type="number" name="time_limit" value="{{ old('time_limit') }}" min="1" placeholder="No limit"
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Pass Score (%)</label>
                        <input type="number" name="pass_score" value="{{ old('pass_score', 60) }}" required min="1" max="100"
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" checked class="accent-indigo-600">
                    <span class="text-sm text-gray-700">Publish immediately</span>
                </label>
            </div>
        </div>
 
        <div class="bg-white border border-gray-100 rounded-2xl p-6 mb-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-800">Questions</h2>
                <button type="button" onclick="addQuestion()"
                        class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">+ Add Question</button>
            </div>
            <div id="questions-container" class="space-y-5"></div>
        </div>
 
        <button type="submit"
                class="w-full bg-indigo-600 text-white py-3 rounded-xl font-medium hover:bg-indigo-700 transition-colors">
            Save Quiz
        </button>
    </form>
</div>
 
@push('scripts')
<script>
let qCount = 0;
 
function addQuestion() {
    const c = document.getElementById('questions-container');
    const i = qCount++;
    const div = document.createElement('div');
    div.className = 'border border-gray-100 rounded-xl p-4 bg-gray-50 relative';
    div.id = 'qq-' + i;
    div.innerHTML = `
        <button type="button" onclick="document.getElementById('qq-${i}').remove()"
                class="absolute top-3 right-3 text-gray-300 hover:text-red-400 text-xl leading-none">×</button>
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Question ${i + 1}</label>
                <input type="text" name="questions[${i}][question]" required placeholder="Enter question..."
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                    <select name="questions[${i}][type]" onchange="handleQType(this, ${i})"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="mcq">Multiple Choice (MCQ)</option>
                        <option value="true_false">True / False</option>
                        <option value="short_answer">Short Answer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Marks</label>
                    <input type="number" name="questions[${i}][marks]" value="1" min="1" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>
            <div id="opts-${i}" class="space-y-2">
                <label class="block text-xs font-medium text-gray-600">Options <span class="text-gray-400">(check correct)</span></label>
                <div id="opts-list-${i}" class="space-y-1.5"></div>
                <button type="button" onclick="addOption(${i})"
                        class="text-xs text-indigo-500 hover:text-indigo-700">+ Add Option</button>
            </div>
            <div id="tf-${i}" class="hidden">
                <label class="block text-xs font-medium text-gray-600 mb-1">Correct Answer</label>
                <div class="flex gap-3">
                    <label class="flex items-center gap-1.5 text-sm text-gray-700 cursor-pointer">
                        <input type="radio" name="questions[${i}][tf_answer]" value="True" class="accent-indigo-600"> True
                    </label>
                    <label class="flex items-center gap-1.5 text-sm text-gray-700 cursor-pointer">
                        <input type="radio" name="questions[${i}][tf_answer]" value="False" class="accent-indigo-600"> False
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Explanation (optional)</label>
                <input type="text" name="questions[${i}][explanation]" placeholder="Explain the correct answer..."
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
        </div>
    `;
    c.appendChild(div);
    addOption(i); addOption(i);
}
 
let optCounts = {};
function addOption(qi) {
    if (!optCounts[qi]) optCounts[qi] = 0;
    const oi = optCounts[qi]++;
    const list = document.getElementById('opts-list-' + qi);
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2';
    row.innerHTML = `
        <input type="checkbox" name="questions[${qi}][options][${oi}][is_correct]" value="1"
               class="accent-indigo-600 flex-shrink-0">
        <input type="text" name="questions[${qi}][options][${oi}][text]" required placeholder="Option ${oi + 1}"
               class="flex-1 border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-400">
        <button type="button" onclick="this.parentElement.remove()" class="text-gray-300 hover:text-red-400">×</button>
    `;
    list.appendChild(row);
}
 
function handleQType(sel, i) {
    const opts = document.getElementById('opts-' + i);
    const tf   = document.getElementById('tf-' + i);
    if (sel.value === 'true_false') { opts.classList.add('hidden'); tf.classList.remove('hidden'); }
    else if (sel.value === 'short_answer') { opts.classList.add('hidden'); tf.classList.add('hidden'); }
    else { opts.classList.remove('hidden'); tf.classList.add('hidden'); }
}
 
addQuestion();
</script>
@endpush
@endsection