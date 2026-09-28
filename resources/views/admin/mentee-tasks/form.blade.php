@extends('admin.layouts.app')
@section('title', ($task ? 'Edit' : 'New').' Mentee Task')
@section('heading', $task ? 'Edit mentee task' : 'New mentee task')

@section('content')
<div class="max-w-2xl space-y-5">
    <a href="{{ route('admin.mentee-tasks.index') }}" class="text-sm text-blue-600">← Back</a>

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">{{ session('error') }}</div>
    @endif
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">{{ $errors->first() }}</div>
    @endif

    <form method="POST"
          action="{{ $task ? route('admin.mentee-tasks.update', $task) : route('admin.mentee-tasks.store') }}"
          enctype="multipart/form-data"
          class="bg-white border border-gray-200 rounded-2xl p-5 space-y-4">
        @csrf
        @if($task) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Mentee *</label>
            <select name="mentee_id" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
                <option value="">— Select —</option>
                @foreach($mentees as $m)
                <option value="{{ $m->id }}" @selected((int) old('mentee_id', $task->mentee_id ?? null) === (int) $m->id)>{{ $m->name }} ({{ $m->email }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Assign mentor *</label>
            <select name="mentor_id" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
                <option value="">— Select —</option>
                @foreach($mentors as $m)
                <option value="{{ $m->id }}" @selected((int) old('mentor_id', $task->mentor_id ?? null) === (int) $m->id)>{{ $m->name }} ({{ $m->email }})</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">Mentor must already be linked to the mentee.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Title *</label>
            <input type="text" name="title" value="{{ old('title', $task->title ?? '') }}" required maxlength="200"
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
            <textarea name="description" rows="4" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">{{ old('description', $task->description ?? '') }}</textarea>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Type</label>
                <select name="type" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
                    @foreach(\App\Models\MenteeTask::TYPES as $key => $label)
                    <option value="{{ $key }}" @selected(old('type', $task->type ?? 'task') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Submission type</label>
                <select name="submission_type" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
                    @foreach(\App\Models\MenteeTask::SUBMISSION_TYPES as $key => $label)
                    <option value="{{ $key }}" @selected(old('submission_type', $task->submission_type ?? 'none') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Attachments</label>
            <input type="file" name="attachments[]" multiple class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
            @if($task && !empty($task->attachments))
            <label class="inline-flex items-center gap-2 text-sm text-gray-600 mt-2">
                <input type="checkbox" name="clear_attachments" value="1"> Clear existing attachments
            </label>
            @endif
        </div>

        <div class="flex gap-4 text-sm">
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_required" value="1" @checked(old('is_required', $task->is_required ?? true))> Required</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $task->is_active ?? true))> Active</label>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl">
            {{ $task ? 'Save changes' : 'Create task' }}
        </button>
    </form>
</div>
@endsection
