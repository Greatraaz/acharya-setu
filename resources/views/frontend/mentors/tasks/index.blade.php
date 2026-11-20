@extends('frontend.layouts.app')
@section('title', 'Mentee Tasks — Vedrix')

@push('styles')
<style>
.mt-hub{
    --mt: var(--brand, #f59e0b);
    --mt-dark: var(--brand-dark, #d97706);
    --mt-soft: var(--brand-muted, rgba(245,158,11,.12));
    font-family: var(--font-body, "DM Sans", sans-serif);
}
.mt-hub .dash-title{ font-family: var(--font-head, "Sora", sans-serif); letter-spacing:-.02em; }
.mt-hub__intro{
    position:relative; overflow:hidden;
    margin:4px 0 18px; padding:20px 22px;
    border:1px solid var(--border, rgba(0,0,0,.07));
    border-radius: var(--radius-lg, 16px);
    background:
        radial-gradient(480px 160px at 100% 0%, color-mix(in srgb, var(--mt) 16%, transparent), transparent 70%),
        var(--card-bg, #fff);
    box-shadow: var(--shadow, 0 4px 24px rgba(0,0,0,.06));
}
.mt-hub__intro::before{
    content:""; position:absolute; left:0; top:0; bottom:0; width:4px;
    background:linear-gradient(180deg, var(--mt), var(--mt-dark));
}
.mt-hub__eyebrow{
    display:inline-flex; font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; font-weight:700; letter-spacing:.12em; text-transform:uppercase;
    color:var(--mt-dark); background:var(--mt-soft); padding:6px 11px; border-radius:8px;
}
.mt-hub__intro h2{
    margin:12px 0 6px; font-family: var(--font-head, "Sora", sans-serif);
    font-size:clamp(22px, 3vw, 28px); font-weight:800; letter-spacing:-.03em; color:var(--text, #09090b);
}
.mt-hub__intro p{ margin:0; max-width:520px; font-size:14px; line-height:1.55; color:var(--text-2, #52525b); }
.mt-hub__review{
    border:1px solid color-mix(in srgb, var(--mt) 28%, transparent);
    background: color-mix(in srgb, var(--mt-soft) 70%, #fff);
    border-radius: 14px; padding:16px; margin-top:16px;
}
.mt-hub__review-item{
    border:1px solid var(--border); border-radius:12px; padding:14px;
    background: var(--card-bg, #fff);
}
</style>
@endpush

@section('content')
<div class="dash-layout mt-hub">
    @include('frontend.mentors.partials.sidebar')
    <div class="dash-content">
        <div class="flex-between" style="gap:12px;flex-wrap:wrap;">
            <div>
                <div class="dash-title">Mentee Tasks</div>
                <div class="dash-subtitle">Create standalone tasks for your mentees and review submissions.</div>
            </div>
            <a href="{{ route('mentor.tasks.create') }}" class="btn btn-primary">+ New task</a>
        </div>

        <div class="mt-hub__intro">
            <span class="mt-hub__eyebrow">Teaching</span>
            <h2>Assign. Review. Guide.</h2>
            <p>Standalone tasks sit outside the curriculum track — perfect for one-off homework and feedback loops.</p>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin:16px 0;"><span class="alert-icon">✓</span><div style="font-size:13px;">{{ session('success') }}</div></div>
        @endif
        @if(session('error'))
        <div class="alert alert-error" style="margin:16px 0;"><span class="alert-icon">!</span><div style="font-size:13px;">{{ session('error') }}</div></div>
        @endif

        @if($pending->count())
        <div class="mt-hub__review">
            <div style="font-family:var(--font-head,Sora,sans-serif);font-weight:800;margin-bottom:12px;letter-spacing:-.02em;">Pending reviews</div>
            <div style="display:grid;gap:12px;">
                @foreach($pending as $row)
                @php $p = $row['progress']; $t = $row['task']; @endphp
                <div class="mt-hub__review-item">
                    <div style="font-weight:700;">{{ $t->title ?? 'Task' }} · {{ $p->user->name ?? 'Mentee' }}</div>
                    @if($p->submission_text)<p style="font-size:13px;margin:6px 0;color:var(--text-2);">{{ $p->submission_text }}</p>@endif
                    @if($p->submissionLink())<p style="font-size:13px;"><a href="{{ $p->submissionLink() }}" target="_blank" style="color:var(--brand);">Open submission</a></p>@endif
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
                <option value="{{ encrypt_id($m->id) }}" @selected((int)$menteeId === (int)$m->id)>{{ $m->name }}</option>
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
