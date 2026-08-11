@extends('frontend.layouts.app')
@section('title', ($assignedMentor ? 'Change Mentor' : 'Choose Mentor') . ' — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between" style="gap:16px;flex-wrap:wrap;">
            <div>
                <div class="dash-title">{{ $assignedMentor ? 'Change Mentor' : 'Choose Your Mentor' }}</div>
                <div class="dash-subtitle">
                    @if($assignedMentor)
                        Request a new mentor. Your current mentor stays until the new one accepts.
                    @else
                        Send a request to a mentor. They’ll be assigned once they accept.
                    @endif
                </div>
            </div>
            <a href="{{ route('mentee.dashboard') }}" class="btn btn-ghost">← Dashboard</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>
        @endif

        @if($assignedMentor)
        <div class="card" style="margin-bottom:20px;">
            <div style="font-size:12px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">Current mentor</div>
            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                <div class="mentor-avatar-lg" style="width:52px;height:52px;border-radius:14px;overflow:hidden;flex-shrink:0;">
                    @if($assignedMentor->avatar_url)
                        <img src="{{ $assignedMentor->avatar_url }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        {{ strtoupper(substr($assignedMentor->name, 0, 1)) }}
                    @endif
                </div>
                <div style="flex:1;min-width:160px;">
                    <div style="font-weight:700;">{{ $assignedMentor->name }}</div>
                    <div style="font-size:13px;color:var(--text-2);">{{ $assignedMentor->designation }}{{ $assignedMentor->company ? ' · '.$assignedMentor->company : '' }}</div>
                </div>
                <a href="{{ $assignedMentor->profile_url }}" class="btn btn-outline btn-sm">View profile</a>
            </div>
        </div>
        @endif

        @if($pendingRequests->isNotEmpty())
        <div class="card" style="margin-bottom:20px;">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:12px;">Pending requests</h3>
            @foreach($pendingRequests as $req)
            <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);flex-wrap:wrap;">
                <div style="flex:1;min-width:160px;">
                    <div style="font-weight:600;font-size:14px;">{{ $req->mentor?->name }}</div>
                    <div style="font-size:12px;color:var(--text-3);">Sent {{ $req->created_at?->diffForHumans() }}</div>
                </div>
                <span class="badge badge-muted">Pending</span>
                <form method="POST" action="{{ route('mentee.mentor-requests.destroy', $req->id) }}" onsubmit="return confirm('Cancel this request?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-ghost btn-sm">Cancel</button>
                </form>
            </div>
            @endforeach
        </div>
        @endif

        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;gap:12px;flex-wrap:wrap;">
                <h3 style="font-size:15px;font-weight:700;margin:0;">Available mentors</h3>
                <a href="{{ route('mentors.search') }}" style="font-size:12px;color:var(--brand);">Browse all →</a>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;">
                @forelse($mentors as $mentor)
                <div class="mentor-card" style="display:flex;flex-direction:column;gap:12px;">
                    <div class="mentor-card-head" style="cursor:pointer;" onclick="window.location='{{ $mentor->profile_url }}'">
                        <div class="mentor-avatar-lg">
                            @if($mentor->avatar_url)
                                <img src="{{ $mentor->avatar_url }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
                            @else
                                {{ strtoupper(substr($mentor->name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="mentor-card-info">
                            <div class="mentor-card-name">{{ $mentor->name }}</div>
                            <div class="mentor-card-role">{{ $mentor->designation }}</div>
                        </div>
                    </div>
                    <div class="mentor-card-meta">
                        <span class="mentor-rate">₹{{ $mentor->rate_per_minute }}/min</span>
                        <span class="mentor-rating">⭐ {{ number_format((float) $mentor->rating, 1) }}</span>
                    </div>
                    @if(in_array($mentor->id, $pendingMentorIds, true))
                        <button class="btn btn-ghost btn-sm" disabled style="width:100%;">Request pending</button>
                    @else
                        <form method="POST" action="{{ route('mentee.mentor-requests.store') }}">
                            @csrf
                            <input type="hidden" name="mentor_id" value="{{ $mentor->id }}">
                            <button type="submit" class="btn btn-primary btn-sm" style="width:100%;">
                                {{ $assignedMentor ? 'Request switch' : 'Request mentor' }}
                            </button>
                        </form>
                    @endif
                </div>
                @empty
                <p style="font-size:13px;color:var(--text-3);">No other mentors available right now.</p>
                @endforelse
            </div>

            <div style="margin-top:20px;">{{ $mentors->links() }}</div>
        </div>
    </div>
</div>
@endsection
