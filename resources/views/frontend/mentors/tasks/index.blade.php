@extends('frontend.layouts.app')
@section('title', 'Mentee Tasks — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')
    <div class="dash-content">
        <div class="flex-between" style="gap:12px;flex-wrap:wrap;">
            <div>
                <div class="dash-title">Mentee Tasks</div>
                <div class="dash-subtitle">Create standalone tasks for your mentees and review submissions.</div>
            </div>
            <a href="{{ route('mentor.tasks.create') }}" class="btn btn-primary">+ New task</a>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin:16px 0;"><span class="alert-icon">✓</span><div style="font-size:13px;">{{ session('success') }}</div></div>
        @endif
        @if(session('error'))
        <div class="alert alert-error" style="margin:16px 0;"><span class="alert-icon">!</span><div style="font-size:13px;">{{ session('error') }}</div></div>
        @endif

        @if($pending->count())
        <div class="card" style="padding:16px;margin-top:16px;">
            <div style="font-weight:800;margin-bottom:12px;">Pending reviews</div>
            <div style="display:grid;gap:12px;">
                @foreach($pending as $row)
                @php $p = $row['progress']; $t = $row['task']; @endphp
                <div style="border:1px solid var(--border);border-radius:12px;padding:12px;">
                    <div style="font-weight:700;">{{ $t->title ?? 'Task' }} · {{ $p->user->name ?? 'Mentee' }}</div>
                    @if($p->submission_text)<p style="font-size:13px;margin:6px 0;">{{ $p->submission_text }}</p>@endif
                    @if($p->submissionLink())<p style="font-size:13px;"><a href="{{ $p->submissionLink() }}" target="_blank">Open submission</a></p>@endif
                    <form method="POST" action="{{ route('mentor.tasks.review', $p->id) }}" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;">
                        @csrf
                        <input type="text" name="mentor_feedback" class="form-input" placeholder="Feedback (optional)" style="flex:1;min-width:180px;">
                        <button type="submit" name="submission_status" value="approved" class="btn btn-primary btn-sm">Approve</button>
                        <button type="submit" name="submission_status" value="rejected" class="btn btn-ghost btn-sm">Reject</button>
                    </form>
                </div>
                @endforeach
            </div>
            @if($pending->hasPages())
            <div style="margin-top:12px;">{{ $pending->links() }}</div>
            @endif
        </div>
        @endif

        <form method="GET" style="margin:16px 0;display:flex;gap:8px;flex-wrap:wrap;">
            <select name="mentee_id" class="form-input" style="max-width:260px;" onchange="this.form.submit()">
                <option value="">All mentees</option>
                @foreach($mentees as $m)
                <option value="{{ $m->id }}" @selected((int)$menteeId === (int)$m->id)>{{ $m->name }}</option>
                @endforeach
            </select>
        </form>

        <div class="card" style="padding:0;overflow:hidden;">
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Mentee</th>
                            <th>Submission</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $task)
                        <tr>
                            <td style="font-weight:700;">{{ $task->title }}</td>
                            <td>{{ $task->mentee->name ?? '—' }}</td>
                            <td>{{ \App\Models\MenteeTask::SUBMISSION_TYPES[$task->submission_type] ?? $task->submission_type }}</td>
                            <td>{{ str_replace('_', ' ', ucfirst($task->uiStatus())) }}</td>
                            <td style="text-align:right;white-space:nowrap;">
                                <a href="{{ route('mentor.tasks.show', $task) }}" class="btn btn-ghost btn-sm">View</a>
                                <a href="{{ route('mentor.tasks.edit', $task) }}" class="btn btn-ghost btn-sm">Edit</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;padding:28px;color:var(--text-3);">No standalone tasks yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
            <div style="padding:12px 18px;">@include('frontend.partials.pagination', ['paginator' => $items])</div>
            @endif
        </div>
    </div>
</div>
@endsection
