@extends('admin.layouts.app')
@section('title', ($task ? 'Edit' : 'New').' Mentee Task')
@section('heading', $task ? 'Edit mentee task' : 'New mentee task')

@section('content')
<div class="max-w-3xl space-y-5">
    <a href="{{ $task ? route('admin.mentee-tasks.show', $task) : route('admin.mentee-tasks.index') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-800">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        {{ $task ? 'Back to task' : 'Back to tasks' }}
    </a>

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">{{ session('error') }}</div>
    @endif
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">{{ $errors->first() }}</div>
    @endif

    <form method="POST"
          action="{{ $task ? route('admin.mentee-tasks.update', $task) : route('admin.mentee-tasks.store') }}"
          enctype="multipart/form-data"
          class="space-y-5">
        @csrf
        @if($task) @method('PUT') @endif

        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/60">
                <h3 class="text-sm font-semibold text-gray-800">Assignment</h3>
            </div>
            <div class="p-5 grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Mentee <span class="text-red-400">*</span></label>
                    <select id="mentee-select" name="mentee_id" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                        <option value="">— Select —</option>
                        @foreach($mentees as $m)
                        <option value="{{ $m->id }}" @selected((int) old('mentee_id', $task->mentee_id ?? null) === (int) $m->id)>{{ $m->name }} ({{ $m->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Assign mentor <span class="text-red-400">*</span></label>
                    <select id="mentor-select" name="mentor_id" required disabled class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-50 disabled:text-gray-400">
                        <option value="">Select a mentee first</option>
                    </select>
                    <p id="mentor-hint" class="text-xs text-gray-400 mt-1">Only mentors linked to this mentee are listed.</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/60">
                <h3 class="text-sm font-semibold text-gray-800">Task details</h3>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-400">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $task->title ?? '') }}" required maxlength="200"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="4" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">{{ old('description', $task->description ?? '') }}</textarea>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Type</label>
                        <select name="type" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            @foreach(\App\Models\MenteeTask::TYPES as $key => $label)
                            <option value="{{ $key }}" @selected(old('type', $task->type ?? 'task') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Submission type</label>
                        <select name="submission_type" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            @foreach(\App\Models\MenteeTask::SUBMISSION_TYPES as $key => $label)
                            <option value="{{ $key }}" @selected(old('submission_type', $task->submission_type ?? 'none') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Attachments</label>
                    <input type="file" name="attachments[]" multiple class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white">
                    @if($task && !empty($task->attachments))
                    <label class="inline-flex items-center gap-2 text-sm text-gray-600 mt-2">
                        <input type="checkbox" name="clear_attachments" value="1"> Clear existing attachments
                    </label>
                    @endif
                </div>

                <div class="flex flex-wrap gap-5 text-sm text-gray-700">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="is_required" value="1" class="rounded border-gray-300" @checked(old('is_required', $task->is_required ?? true))>
                        Required
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300" @checked(old('is_active', $task->is_active ?? true))>
                        Active
                    </label>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl">
                {{ $task ? 'Save changes' : 'Create task' }}
            </button>
            <a href="{{ $task ? route('admin.mentee-tasks.show', $task) : route('admin.mentee-tasks.index') }}"
               class="inline-flex items-center border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium px-5 py-2.5 rounded-xl">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const menteeSelect = document.getElementById('mentee-select');
    const mentorSelect = document.getElementById('mentor-select');
    const hint = document.getElementById('mentor-hint');
    const url = @json(route('admin.mentee-tasks.linked-mentors'));
    const selectedMentorId = @json((string) old('mentor_id', $task?->mentor_id ?? ''));

    function setHint(text, isError) {
        hint.textContent = text;
        hint.className = isError ? 'text-xs text-amber-600 mt-1' : 'text-xs text-gray-400 mt-1';
    }

    function resetMentors(placeholder) {
        mentorSelect.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholder;
        mentorSelect.appendChild(opt);
        mentorSelect.value = '';
        mentorSelect.disabled = true;
    }

    async function loadMentors(menteeId, preferredMentorId) {
        if (!menteeId) {
            resetMentors('Select a mentee first');
            setHint('Only mentors linked to this mentee are listed.', false);
            return;
        }

        resetMentors('Loading…');
        try {
            const response = await fetch(url + '?mentee_id=' + encodeURIComponent(menteeId), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            const mentors = data.mentors || [];

            mentorSelect.innerHTML = '';
            const blank = document.createElement('option');
            blank.value = '';
            blank.textContent = mentors.length ? '— Select —' : 'No linked mentor';
            mentorSelect.appendChild(blank);

            mentors.forEach(function (mentor) {
                const opt = document.createElement('option');
                opt.value = String(mentor.id);
                opt.textContent = mentor.name + ' (' + mentor.email + ')';
                if (preferredMentorId && String(mentor.id) === String(preferredMentorId)) {
                    opt.selected = true;
                }
                mentorSelect.appendChild(opt);
            });

            mentorSelect.disabled = mentors.length === 0;
            if (mentors.length === 0) {
                setHint('This mentee has no linked mentor. Assign one on the mentee profile first.', true);
            } else {
                setHint('Only mentors linked to this mentee are listed.', false);
            }
        } catch (e) {
            resetMentors('Could not load mentors');
            setHint('Could not load linked mentors. Refresh and try again.', true);
        }
    }

    menteeSelect.addEventListener('change', function () {
        loadMentors(menteeSelect.value, '');
    });

    if (menteeSelect.value) {
        loadMentors(menteeSelect.value, selectedMentorId);
    }
})();
</script>
@endpush
