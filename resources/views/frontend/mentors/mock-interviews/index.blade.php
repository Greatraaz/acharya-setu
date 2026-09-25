@extends('frontend.layouts.app')
@section('title', 'Mock Interviews — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-title">Mock Interviews</div>
        <div class="dash-subtitle">Sessions assigned to you by Vedrix admin. Join via Agora when confirmed.</div>

        <div class="card" style="padding:0;overflow:hidden;margin-top:16px;">
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mentee</th>
                            <th>When</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td style="font-weight:600;">{{ $item->user->name ?? '—' }}</td>
                            <td>{{ $item->preferred_at?->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</td>
                            <td>{{ $item->duration_minutes }} min</td>
                            <td>{{ $item->statusLabel() }}</td>
                            <td style="display:flex;gap:8px;justify-content:flex-end;">
                                @if($item->canJoinCall())
                                    <a href="{{ route('mock-interviews.call', $item->id) }}" class="btn btn-primary btn-sm">🎥 Join</a>
                                @endif
                                <a href="{{ route('mentor.mock-interviews.show', $item) }}" class="btn btn-ghost btn-sm">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--text-3);padding:28px;">No mock interviews assigned yet.</td></tr>
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
