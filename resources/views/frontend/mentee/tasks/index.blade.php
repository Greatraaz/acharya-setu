@extends('frontend.layouts.app')
@section('title', 'My Tasks — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')
    <div class="dash-content">
        <div class="dash-title">My Tasks</div>
        <div class="dash-subtitle">Tasks assigned by your mentor or Vedrix admin (outside curriculum).</div>

        @if(session('success'))
        <div class="alert alert-success" style="margin:16px 0;"><span class="alert-icon">✓</span><div style="font-size:13px;">{{ session('success') }}</div></div>
        @endif

        <div class="card" style="padding:0;overflow:hidden;margin-top:16px;">
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Mentor</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $task)
                        @php $status = $task->uiStatus(); @endphp
                        <tr>
                            <td>
                                <div style="font-weight:700;">{{ \App\Models\MenteeTask::TYPE_ICONS[$task->type] ?? '✅' }} {{ $task->title }}</div>
                                @if($task->due_at)
                                <div style="font-size:12px;color:var(--text-3);">Due {{ $task->due_at->format('d M Y') }}</div>
                                @endif
                            </td>
                            <td>{{ $task->mentor->name ?? '—' }}</td>
                            <td>{{ \App\Models\MenteeTask::SUBMISSION_TYPES[$task->submission_type] ?? $task->submission_type }}</td>
                            <td>
                                <span class="cs-status cs-status--{{ $status === 'completed' ? 'ok' : ($status === 'awaiting_review' ? 'wait' : ($status === 'rejected' ? 'error' : 'neutral')) }}">
                                    {{ str_replace('_', ' ', ucfirst($status)) }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('mentee.tasks.show', $task) }}" class="btn btn-ghost btn-sm">Open</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--text-3);padding:28px;">No tasks assigned yet.</td></tr>
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
