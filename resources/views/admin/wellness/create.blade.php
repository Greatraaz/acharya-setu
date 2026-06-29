@extends('admin.layouts.app')
@section('title', 'Create Wellness Survey')
 
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.wellness.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back</a>
        <h1 class="font-display text-2xl font-bold text-gray-900 mt-2">Create Wellness Survey</h1>
    </div>
 
    <form method="POST" action="{{ route('admin.wellness.store') }}" id="survey-form">
        @csrf
 
        {{-- Survey Details --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-6 mb-4">
            <h2 class="font-semibold text-gray-800 mb-4">Survey Details</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           placeholder="e.g. Weekly Wellness Check-in"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="2"
                              class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Expiry Date (optional)</label>
                    <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                           class="border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
            </div>
        </div>
 
        {{-- Questions --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-6 mb-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-800">Questions</h2>
                <button type="button" onclick="addQuestion()"
                        class="text-sm text-amber-600 hover:text-amber-700 font-medium">+ Add Question</button>
            </div>
 
            <div id="questions-container" class="space-y-4">
                {{-- Template rendered by JS --}}
            </div>
        </div>
 
        <button type="submit"
                class="w-full bg-amber-500 text-white py-3 rounded-xl font-medium hover:bg-amber-600 transition-colors">
            Create Survey
        </button>
    </form>
</div>
 
@push('scripts')
<script>
let questionCount = 0;
 
function addQuestion() {
    const container = document.getElementById('questions-container');
    const i = questionCount++;
    const div = document.createElement('div');
    div.className = 'border border-gray-100 rounded-xl p-4 space-y-3 bg-gray-50 relative';
    div.id = 'q-' + i;
    div.innerHTML = `
        <button type="button" onclick="document.getElementById('q-${i}').remove()"
                class="absolute top-3 right-3 text-gray-300 hover:text-red-400 text-lg leading-none">×</button>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Question ${i + 1}</label>
            <input type="text" name="questions[${i}][text]" required placeholder="Enter question..."
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
            <select name="questions[${i}][type]" onchange="handleTypeChange(this, ${i})"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
                <option value="scale">Scale (1–10)</option>
                <option value="text">Free Text</option>
                <option value="yes_no">Yes / No</option>
                <option value="multiple_choice">Multiple Choice</option>
            </select>
        </div>
        <div id="options-${i}" class="hidden">
            <label class="block text-xs font-medium text-gray-600 mb-1">Options (one per line)</label>
            <textarea name="questions[${i}][options_raw]" rows="3" placeholder="Option 1&#10;Option 2&#10;Option 3"
                      class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white resize-none"></textarea>
        </div>
    `;
    container.appendChild(div);
}
 
function handleTypeChange(select, i) {
    const optionsDiv = document.getElementById('options-' + i);
    optionsDiv.classList.toggle('hidden', select.value !== 'multiple_choice');
}
 
// Add first question by default
addQuestion();
</script>
@endpush
@endsection