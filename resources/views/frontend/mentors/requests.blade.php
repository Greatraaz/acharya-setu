@extends('frontend.layouts.app')
@section('title', 'Mentee Requests — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Mentee Requests</div>
            <div class="dash-subtitle">Accept to become a mentee’s assigned mentor (or replace their current one).</div>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>
        @endif

        <div class="card" style="margin-bottom:20px;">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:14px;">Pending ({{ $pending->count() }})</h3>
            @forelse($pending as $req)
            <div style="display:flex;align-items:flex-start;gap:14px;padding:14px 0;border-bottom:1px solid var(--border);flex-wrap:wrap;">
                <div class="mentor-avatar-lg" style="width:48px;height:48px;flex-shrink:0;">
                    @if($req->mentee?->avatar_url)
                        <img src="{{ $req->mentee->avatar_url }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
                    @else
                        {{ strtoupper(substr($req->mentee?->name ?? 'M', 0, 1)) }}
                    @endif
                </div>
                <div style="flex:1;min-width:180px;">
                    <div style="font-weight:700;">{{ $req->mentee?->name }}</div>
                    <div style="font-size:12px;color:var(--text-2);margin-top:2px;">
                        {{ $req->mentee?->college ?? $req->mentee?->field ?? 'Mentee' }}
                        · Requested {{ $req->created_at?->diffForHumans() }}
                    </div>
                    @if($req->message)
                        <p style="font-size:13px;color:var(--text-2);margin:8px 0 0;line-height:1.5;">“{{ $req->message }}”</p>
                    @endif
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <form method="POST" action="{{ route('mentor.requests.accept', $req->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">Accept</button>
                    </form>
                    <form method="POST" action="{{ route('mentor.requests.reject', $req->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm">Decline</button>
                    </form>
                </div>
            </div>
            @empty
            <p style="font-size:13px;color:var(--text-3);">No pending mentee requests.</p>
            @endforelse
        </div>

        @if($recent->isNotEmpty())
        <div class="card">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:14px;">Recent</h3>
            @foreach($recent as $req)
            <div style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);font-size:13px;flex-wrap:wrap;">
                <span><strong>{{ $req->mentee?->name }}</strong></span>
                <span class="badge {{ $req->status === 'accepted' ? 'badge-success' : 'badge-muted' }}">{{ ucfirst($req->status) }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
