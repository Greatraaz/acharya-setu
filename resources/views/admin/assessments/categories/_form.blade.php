@php
    $isEdit = $category->exists;
    $formAction = $isEdit
        ? route('admin.assessment-categories.update', $category)
        : route('admin.assessment-categories.store');
@endphp

<form method="POST" action="{{ $formAction }}" class="learn-panel p-6 space-y-4">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Assessment *</label>
        <select name="assessment_id" required
                class="learn-focus w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white outline-none">
            <option value="">Select Assessment</option>
            @foreach($assessments as $assessment)
            <option value="{{ $assessment->id }}" @selected((int) old('assessment_id', $category->assessment_id) === (int) $assessment->id)>
                {{ $assessment->title }}
            </option>
            @endforeach
        </select>
        @error('assessment_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        @if($assessments->isEmpty())
        <p class="text-xs text-amber-700 mt-2">Create an assessment first.</p>
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Category Name *</label>
        <input type="text" name="name" value="{{ old('name', $category->name) }}" required
               placeholder="e.g. Mood, Sleep, Energy"
               class="learn-focus w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none">
        @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <button type="submit" class="learn-btn-primary w-full justify-center !py-3" {{ $assessments->isEmpty() ? 'disabled' : '' }}>
        {{ $isEdit ? 'Update Category' : 'Submit' }}
    </button>
</form>