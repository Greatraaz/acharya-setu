@extends('frontend.layouts.app')
@section('title', ($task ? 'Edit' : 'New').' Task — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')
    <div class="dash-content" style="max-width:640px;">
        <a href="{{ route('mentor.tasks.index') }}" style="font-size:13px;color:var(--brand);">← Tasks</a>
        <div class="dash-title" style="margin-top:10px;">{{ $task ? 'Edit task' : 'New mentee task' }}</div>

        @if(session('error'))
        <div class="alert alert-error" style="margin:16px 0;"><span class="alert-icon">!</span><div style="font-size:13px;">{{ session('error') }}</div></div>
        @endif
        @if($errors->any())
        <div class="alert alert-error" style="margin:16px 0;"><span class="alert-icon">!</span><div style="font-size:13px;">{{ $errors->first() }}</div></div>
        @endif

        <form method="POST"
              action="{{ $task ? route('mentor.tasks.update', $task) : route('mentor.tasks.store') }}"
              enctype="multipart/form-data"
              class="card" style="padding:20px;margin-top:16px;display:grid;gap:14px;">
            @csrf
            @if($task) @method('PUT') @endif

            <div class="form-group" style="margin:0;">
                <label class="form-label">Mentee *</label>
                <select name="mentee_id" class="form-input" required @disabled($task)>
                    <option value="">— Select mentee —</option>
                    @foreach($mentees as $m)
                    <option value="{{ $m->id }}" @selected((int) old('mentee_id', $selectedMentee) === (int) $m->id)>{{ $m->name }} ({{ $m->email }})</option>
                    @endforeach
                </select>
                @if($task)<input type="hidden" name="mentee_id" value="{{ $task->mentee_id }}">@endif
            </div>

            <div class="form-group" style="margin:0;">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-input" value="{{ old('title', $task->title ?? '') }}" required maxlength="200">
            </div>

            <div class="form-group" style="margin:0;">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-input" rows="4">{{ old('description', $task->description ?? '') }}</textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-input">
                        @foreach(\App\Models\MenteeTask::TYPES as $key => $label)
                        <option value="{{ $key }}" @selected(old('type', $task->type ?? 'task') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Submission type</label>
                    <select name="submission_type" class="form-input">
                        @foreach(\App\Models\MenteeTask::SUBMISSION_TYPES as $key => $label)
                        <option value="{{ $key }}" @selected(old('submission_type', $task->submission_type ?? 'none') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin:0;">
                <label class="form-label">Attachments</label>
                <input type="file" name="attachments[]" class="form-input" multiple>
                @if($task && !empty($task->attachments))
                <label style="display:flex;gap:8px;align-items:center;margin-top:8px;font-size:13px;">
                    <input type="checkbox" name="clear_attachments" value="1"> Clear existing attachments
                </label>
                @endif
            </div>

            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <label style="display:flex;gap:8px;align-items:center;font-size:13px;">
                    <input type="checkbox" name="is_required" value="1" @checked(old('is_required', $task->is_required ?? true))> Required
                </label>
                <label style="display:flex;gap:8px;align-items:center;font-size:13px;">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $task->is_active ?? true))> Active
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-lg">{{ $task ? 'Save changes' : 'Create task' }}</button>
        </form>
    </div>
</div>
@endsection
