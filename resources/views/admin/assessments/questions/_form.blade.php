@php

    $isEdit = $question->exists;

    $formAction = $isEdit

        ? route('admin.assessment-questions.update', $question)

        : route('admin.assessment-questions.store');

    $options = old('options', $question->options ?? \App\Models\AssessmentQuestion::DEFAULT_OPTIONS);

    $selectedAssessment = (int) old('assessment_id', $question->assessment_id);

@endphp



<form method="POST" action="{{ $formAction }}" class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5 max-w-3xl">

    @csrf

    @if($isEdit) @method('PUT') @endif



    <div class="bg-blue-50 border border-blue-100 text-blue-900 text-sm rounded-xl px-4 py-3">

        <strong>Note:</strong> This assessment uses fixed answer options for all questions:

        <ul class="list-disc ml-5 mt-1 space-y-0.5">

            <li>0 – Not at all</li>

            <li>1 – Several days</li>

            <li>2 – More than half the days</li>

            <li>3 – Nearly every day</li>

        </ul>

    </div>



    <div>

        <label class="block text-sm font-medium text-gray-700 mb-1.5">Assessment Category *</label>

        <select name="assessment_id" required

                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">

            <option value="">Select Assessment Category</option>

            @foreach($assessments as $assessment)

            <option value="{{ $assessment->id }}" @selected($selectedAssessment === (int) $assessment->id)>

                {{ $assessment->title }}

            </option>

            @endforeach

        </select>

        @error('assessment_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror

        @if($assessments->isEmpty())

        <p class="text-xs text-amber-700 mt-2">Create an assessment category first before adding questions.</p>

        @endif

    </div>



    <div>

        <label class="block text-sm font-medium text-gray-700 mb-1.5">Question *</label>

        <textarea name="question" rows="4" required placeholder="Enter Question"

                  class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">{{ old('question', $question->question) }}</textarea>

        @error('question')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror

    </div>



    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        @foreach([0,1,2,3] as $score)

        <div>

            <label class="block text-sm font-medium text-gray-700 mb-1.5">Option {{ $score + 1 }} * (score {{ $score }})</label>

            <input type="text" name="options[{{ $score }}]" required

                   value="{{ $options[$score] ?? \App\Models\AssessmentQuestion::DEFAULT_OPTIONS[$score] }}"

                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">

            @error("options.$score")<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror

        </div>

        @endforeach

    </div>



    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-medium hover:bg-blue-700">

        Submit

    </button>

</form>

