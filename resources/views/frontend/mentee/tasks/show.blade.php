@extends('frontend.layouts.app')
@section('title', $task->title.' — Task')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')
    <div class="dash-content" style="max-width:720px;">
        <a href="{{ route('mentee.tasks.index') }}" style="font-size:13px;color:var(--brand);">← My Tasks</a>
        <div class="dash-title" style="margin-top:10px;">{{ $task->title }}</div>
        <div class="dash-subtitle">
            {{ \App\Models\MenteeTask::TYPES[$task->type] ?? 'Task' }}
            · {{ \App\Models\MenteeTask::SUBMISSION_TYPES[$task->submission_type] ?? $task->submission_type }}
            @if($task->mentor) · Mentor: {{ $task->mentor->name }} @endif
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin:16px 0;"><span class="alert-icon">✓</span><div style="font-size:13px;">{{ session('success') }}</div></div>
        @endif
        @if(session('error'))
        <div class="alert alert-error" style="margin:16px 0;"><span class="alert-icon">!</span><div style="font-size:13px;">{{ session('error') }}</div></div>
        @endif
        @if($errors->any())
        <div class="alert alert-error" style="margin:16px 0;"><span class="alert-icon">!</span><div style="font-size:13px;">{{ $errors->first() }}</div></div>
        @endif

        <div class="card" style="padding:18px;margin-top:16px;display:grid;gap:14px;">
            @if($task->description)
            <div>
                <div style="font-size:12px;font-weight:700;color:var(--text-3);text-transform:uppercase;">Description</div>
                <p style="margin:6px 0 0;white-space:pre-wrap;line-height:1.5;">{{ $task->description }}</p>
            </div>
            @endif

            @if(!empty($task->attachments))
            <div>
                <div style="font-size:12px;font-weight:700;color:var(--text-3);text-transform:uppercase;">Attachments</div>
                <ul style="margin:8px 0 0;padding-left:18px;">
                    @foreach($task->attachments as $file)
                    <li><a href="{{ $file['url'] ?? '#' }}" target="_blank" rel="noopener">{{ $file['name'] ?? 'File' }}</a></li>
                    @endforeach
                </ul>
            </div>
            @endif

            @php $status = $task->uiStatus($progress); @endphp
            <div>
                <div style="font-size:12px;font-weight:700;color:var(--text-3);text-transform:uppercase;">Status</div>
                <p style="margin:6px 0 0;">{{ str_replace('_', ' ', ucfirst($status)) }}</p>
                @if($progress?->mentor_feedback)
                <p style="margin:8px 0 0;padding:10px;border-radius:10px;background:var(--bg-3);font-size:13px;white-space:pre-wrap;">{{ $progress->mentor_feedback }}</p>
                @endif
                @if($progress?->submission_text)
                <p style="margin:8px 0 0;font-size:13px;"><strong>Your text:</strong> {{ $progress->submission_text }}</p>
                @endif
                @if($progress?->submissionLink())
                <p style="margin:8px 0 0;font-size:13px;"><a href="{{ $progress->submissionLink() }}" target="_blank">View your submission</a></p>
                @endif
            </div>

            @if(! in_array($status, ['completed', 'awaiting_review'], true))
            <form method="POST" action="{{ route('mentee.tasks.submit', $task) }}" enctype="multipart/form-data" style="display:grid;gap:12px;border-top:1px solid var(--border);padding-top:14px;">
                @csrf
                @if($task->submission_type === 'none' || ! $task->submission_type)
                <p style="font-size:13px;color:var(--text-2);">Mark this task as done when you’ve completed it.</p>
                <button type="submit" class="btn btn-primary">Mark complete</button>
                @else
                @if(in_array($task->submission_type, ['text'], true))
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Your answer</label>
                    <textarea name="submission_text" class="form-input" rows="4" required>{{ old('submission_text', $progress->submission_text ?? '') }}</textarea>
                </div>
                @endif
                @if(in_array($task->submission_type, ['link'], true))
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Submission URL</label>
                    <input type="url" name="submission_url" class="form-input" value="{{ old('submission_url') }}" required>
                </div>
                @endif
                @if(in_array($task->submission_type, ['file', 'pdf', 'video'], true))
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Upload file</label>
                    <input type="file" name="submission_file" class="form-input" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="submission_text" class="form-input" rows="3">{{ old('submission_text') }}</textarea>
                </div>
                @endif
                <button type="submit" class="btn btn-primary">Submit for review</button>
                @endif
            </form>
            @elseif($status === 'awaiting_review')
            <p style="font-size:13px;color:var(--text-2);">Your submission is waiting for mentor review.</p>
            @endif
        </div>
    </div>
</div>
@endsection
