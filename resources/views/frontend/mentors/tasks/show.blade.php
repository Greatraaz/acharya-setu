@extends('frontend.layouts.app')
@section('title', $task->title.' — Task')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')
    <div class="dash-content" style="max-width:720px;">
        <a href="{{ route('mentor.tasks.index') }}" style="font-size:13px;color:var(--brand);">← Tasks</a>
        <div class="flex-between" style="gap:12px;flex-wrap:wrap;margin-top:10px;">
            <div>
                <div class="dash-title">{{ $task->title }}</div>
                <div class="dash-subtitle">{{ $task->mentee->name ?? 'Mentee' }} · {{ \App\Models\MenteeTask::SUBMISSION_TYPES[$task->submission_type] ?? '' }}</div>
            </div>
            <a href="{{ route('mentor.tasks.edit', $task) }}" class="btn btn-ghost">Edit</a>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin:16px 0;"><span class="alert-icon">✓</span><div style="font-size:13px;">{{ session('success') }}</div></div>
        @endif

        <div class="card" style="padding:18px;margin-top:16px;display:grid;gap:12px;">
            @if($task->description)
            <p style="white-space:pre-wrap;line-height:1.5;">{{ $task->description }}</p>
            @endif
            <div style="font-size:13px;color:var(--text-2);">Status: <strong>{{ str_replace('_', ' ', ucfirst($task->uiStatus($progress))) }}</strong></div>

            @if($progress && $progress->submission_status === 'submitted')
            <div style="border-top:1px solid var(--border);padding-top:12px;">
                <div style="font-weight:700;margin-bottom:8px;">Review submission</div>
                @if($progress->submission_text)<p style="font-size:13px;">{{ $progress->submission_text }}</p>@endif
                @if($progress->submissionLink())<p style="font-size:13px;"><a href="{{ $progress->submissionLink() }}" target="_blank">Open file/link</a></p>@endif
                <form method="POST" action="{{ route('mentor.tasks.review', $progress->id) }}" style="display:grid;gap:10px;margin-top:10px;">
                    @csrf
                    <textarea name="mentor_feedback" class="form-input" rows="3" placeholder="Feedback for mentee">{{ old('mentor_feedback') }}</textarea>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" name="submission_status" value="approved" class="btn btn-primary">Approve</button>
                        <button type="submit" name="submission_status" value="rejected" class="btn btn-ghost">Reject</button>
                    </div>
                </form>
            </div>
            @elseif($progress?->mentor_feedback)
            <div style="padding:10px;border-radius:10px;background:var(--bg-3);font-size:13px;white-space:pre-wrap;">{{ $progress->mentor_feedback }}</div>
            @endif

            <form method="POST" action="{{ route('mentor.tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-ghost" style="color:var(--error);">Delete task</button>
            </form>
        </div>
    </div>
</div>
@endsection
