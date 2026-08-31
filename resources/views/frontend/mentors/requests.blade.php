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

        @php
            $activeStatus = $status ?? request('status', 'pending');
            $tabParams = fn (string $key) => array_filter([
                'status' => $key === 'all' ? null : $key,
                'search' => ($search ?? request('search')) ?: null,
            ]);
        @endphp

        <form method="GET" action="{{ route('mentor.requests') }}" class="session-toolbar">
            <div class="session-filter-tabs">
                @foreach([
                    'pending' => 'Pending ('.((int) ($counts[\App\Models\MentorRequest::STATUS_PENDING] ?? 0)).')',
                    'accepted' => 'Accepted',
                    'rejected' => 'Declined',
                    'all' => 'All',
                ] as $key => $label)
                    <a href="{{ route('mentor.requests', $tabParams($key)) }}"
                       class="session-filter-tab {{ $activeStatus === $key ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="session-toolbar-controls">
                @if($activeStatus !== 'all')
                    <input type="hidden" name="status" value="{{ $activeStatus }}">
                @endif
                <div class="session-search-field">
                    <span class="session-search-icon" aria-hidden="true">🔍</span>
                    <input type="search" name="search" class="form-input" value="{{ $search ?? request('search') }}"
                           placeholder="Search mentee name or email…" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-outline">Search</button>
                @if(request()->filled('search'))
                    <a href="{{ route('mentor.requests', array_filter(['status' => $activeStatus === 'all' ? null : $activeStatus])) }}" class="btn btn-ghost">Clear</a>
                @endif
            </div>
        </form>

        <div class="card">
            @forelse($requests as $req)
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
                        @if($req->status !== \App\Models\MentorRequest::STATUS_PENDING && $req->responded_at)
                            · {{ ucfirst($req->status) }} {{ $req->responded_at->diffForHumans() }}
                        @endif
                    </div>
                    @if($req->message)
                        <p style="font-size:13px;color:var(--text-2);margin:8px 0 0;line-height:1.5;">“{{ $req->message }}”</p>
                    @endif
                </div>
                @if($req->status === \App\Models\MentorRequest::STATUS_PENDING)
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
                @else
                <span class="badge {{ $req->status === 'accepted' ? 'badge-success' : 'badge-muted' }}">{{ ucfirst($req->status) }}</span>
                @endif
            </div>
            @empty
            <p style="font-size:13px;color:var(--text-3);padding:12px 0;">No mentee requests match your filters.</p>
            @endforelse
        </div>

        @include('frontend.partials.pagination', ['paginator' => $requests])
    </div>
</div>
@endsection
