@php
    $isEdit        = $question->exists;
    $formAction    = $formAction ?? ($isEdit
        ? route('mentor.assessment-questions.update', $question)
        : route('mentor.assessment-questions.store'));
    $options          = old('options', $question->options ?? \App\Models\AssessmentQuestion::DEFAULT_OPTIONS);
    $selectedAssessment = (int) old('assessment_id', $question->assessment_id ?? 0);
@endphp

<form method="POST" action="{{ $formAction }}" style="max-width:720px;">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- Fixed-scale info box --}}
    <div class="alert alert-info" style="margin-bottom:20px;">
        <span class="alert-icon">ℹ️</span>
        <div style="font-size:13px;">
            <strong>Fixed answer scale for all questions:</strong>
            <ul style="margin:6px 0 0 18px;line-height:1.8;">
                <li>0 – Not at all</li>
                <li>1 – Several days</li>
                <li>2 – More than half the days</li>
                <li>3 – Nearly every day</li>
            </ul>
        </div>
    </div>

    <div class="assess-form-card" style="display:flex;flex-direction:column;gap:18px;">

        {{-- Assessment picker --}}
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Assessment Category *</label>
            <select name="assessment_id" required class="form-select">
                <option value="">Select Assessment Category</option>
                @foreach($assessments as $assessment)
                <option value="{{ $assessment->id }}" @selected($selectedAssessment === (int) $assessment->id)>
                    {{ $assessment->title }}
                </option>
                @endforeach
            </select>
            @error('assessment_id')<p class="assess-error">{{ $message }}</p>@enderror
            @if($assessments->isEmpty())
            <p class="assess-error" style="color:var(--warning);">Create an assessment category first before adding questions.</p>
            @endif
        </div>

        {{-- Question text --}}
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Question *</label>
            <textarea name="question" rows="4" required placeholder="Enter question text…" class="form-textarea">{{ old('question', $question->question ?? '') }}</textarea>
            @error('question')<p class="assess-error">{{ $message }}</p>@enderror
        </div>

        {{-- Options --}}
        <div>
            <label class="form-label">Answer Options</label>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
                @foreach([0,1,2,3] as $score)
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="font-size:11px;">Option {{ $score + 1 }} &nbsp;(score {{ $score }})</label>
                    <input type="text" name="options[{{ $score }}]" required
                           value="{{ $options[$score] ?? \App\Models\AssessmentQuestion::DEFAULT_OPTIONS[$score] }}"
                           class="form-input">
                    @error("options.$score")<p class="assess-error">{{ $message }}</p>@enderror
                </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="assess-submit">
            {{ $isEdit ? 'Update Question' : 'Save Question' }}
        </button>

    </div>
</form>
