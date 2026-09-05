@extends('frontend.layouts.app')
@section('title', 'My Journey — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">My Journey</div>
            <div class="dash-subtitle">Your structured mentorship curriculum{{ ($canViewProgress ?? false) ? ' and progress' : '' }}.</div>
        </div>

        @unless($canViewProgress ?? false)
        <div class="alert alert-warning" style="margin-bottom:16px;">
            <span class="alert-icon">🔒</span>
            <div style="font-size:13px;">
                You can open months, weeks, and tasks. Scores, submissions, and progress reports are locked on your plan.
                <a href="{{ route('mentee.plans') }}" style="color:var(--brand);font-weight:600;">Upgrade →</a>
            </div>
        </div>
        @endunless

        @if($personalTracks->isEmpty())
            <div class="empty-state" style="padding:56px 20px;">
                <div style="font-size:48px;margin-bottom:12px;">🗺️</div>
                <div style="font-size:18px;font-weight:700;margin-bottom:8px;">No curriculum tracks yet</div>
                @if($assignedMentor)
                <p style="font-size:13px;color:var(--text-2);max-width:420px;margin:0 auto 18px;">
                    You’re connected with <strong>{{ $assignedMentor->name }}</strong>.
                    Your personalized curriculum will appear here once your mentor or admin creates a learning track for you.
                </p>
                <a href="{{ route('mentee.mentor.change') }}" class="btn btn-primary">View my mentor</a>
                @else
                <p style="font-size:13px;color:var(--text-2);max-width:420px;margin:0 auto 18px;">
                    Connect with a mentor to get a personalized curriculum — tracks are created manually by your mentor or an admin.
                </p>
                <a href="{{ route('mentors.search') }}" class="btn btn-primary">Find a Mentor</a>
                @endif
            </div>
        @else
            {{-- Track switcher: show every journey assigned to this mentee --}}
            <div class="card" style="margin-bottom:20px;padding:14px 16px;">
                <div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-3);margin-bottom:10px;">
                    Your tracks ({{ $personalTracks->count() }})
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    @foreach($personalTracks as $track)
                        @php
                            $isSelected = $selectedTrack && (int) $selectedTrack->id === (int) $track->id;
                        @endphp
                        <a href="{{ route('mentee.journey.index', ['track' => $track->id]) }}"
                           class="btn {{ $isSelected ? 'btn-primary' : 'btn-ghost' }} btn-sm"
                           style="{{ $isSelected ? '' : 'border:1px solid var(--border);' }}">
                            {{ $track->name }}
                            @if((int) $track->months_count === 0)
                                <span style="opacity:.7;font-size:11px;">· setup</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            @php
                $percent = (int) ($progress['percent'] ?? $progress['percentage'] ?? 0);
                $streamName = $selectedTrack->name ?? ($enrollment->stream->name ?? 'Your track');
            @endphp

            <div style="display:grid;grid-template-columns:{{ ($canViewProgress ?? false) ? '2fr 1fr' : '1fr' }};gap:16px;margin-bottom:24px;">
                <div class="wallet-card">
                    <div style="position:relative;z-index:1;">
                        <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Current Track</div>
                        <div style="font-size:28px;font-weight:800;font-family:var(--font-head);color:#fff;margin-bottom:6px;">{{ $streamName }}</div>
                        <div style="font-size:13px;color:rgba(255,255,255,.8);">
                            @if($enrollment)
                                Month {{ $enrollment->current_month }} · Week {{ $enrollment->current_week }}
                                · {{ ucfirst($enrollment->status) }}
                            @else
                                {{ (int) ($selectedTrack->months_count ?? 0) }} month(s)
                                · {{ ($selectedTrack->is_active ?? false) ? 'Active' : 'Inactive' }}
                            @endif
                        </div>
                        @if($canViewProgress ?? false)
                        <div style="margin-top:16px;">
                            <div style="display:flex;justify-content:space-between;font-size:12px;color:rgba(255,255,255,.8);margin-bottom:6px;">
                                <span>Overall progress</span>
                                <span>{{ $percent }}%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width:{{ $percent }}%"></div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @if($canViewProgress ?? false)
                <div class="card">
                    <div class="stat-card-icon">✅</div>
                    <div class="stat-card-label">Completed</div>
                    <div class="stat-card-value">{{ (int) ($progress['completed'] ?? 0) }}</div>
                    <div class="stat-card-delta">of {{ (int) ($progress['total'] ?? 0) }} items</div>
                </div>
                @endif
            </div>

            <div class="card">
                <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">Months</h3>
                @forelse($monthProgress as $row)
                @php
                    $m = $row['month'];
                    $mPercent = (int) ($row['percent'] ?? 0);
                @endphp
                <a href="{{ route('mentee.journey.month', $m->id) }}" class="card" style="display:block;text-decoration:none;color:inherit;margin-bottom:12px;padding:16px 18px;">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                        <div>
                            <div style="font-size:11px;color:var(--brand);font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px;">Month {{ $m->month_number }}</div>
                            <div style="font-size:15px;font-weight:700;">{{ $m->title ?: 'Month '.$m->month_number }}</div>
                            @if($m->theme)
                            <div style="font-size:12px;color:var(--text-2);margin-top:4px;">{{ $m->theme }}</div>
                            @endif
                        </div>
                        @if($canViewProgress ?? false)
                        <span style="font-size:13px;font-weight:700;color:var(--brand);">{{ $mPercent }}%</span>
                        @endif
                    </div>
                    <div style="margin-top:12px;">
                        @if($canViewProgress ?? false)
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:{{ $mPercent }}%"></div>
                        </div>
                        <div style="font-size:11px;color:var(--text-3);margin-top:6px;">
                            {{ (int) ($row['completed'] ?? 0) }} / {{ (int) ($row['total'] ?? 0) }} completed
                            · {{ $m->weeks->count() }} weeks
                        </div>
                        @else
                        <div style="font-size:11px;color:var(--text-3);">{{ $m->weeks->count() }} weeks · Open to view tasks</div>
                        @endif
                    </div>
                </a>
                @empty
                <div class="empty-state" style="padding:32px 0;">
                    <div style="font-size:14px;font-weight:700;margin-bottom:6px;">Curriculum not ready</div>
                    <div style="font-size:13px;color:var(--text-2);">Your mentor hasn’t published months for this track yet. Switch tracks above if you have others.</div>
                </div>
                @endforelse
            </div>
        @endif
    </div>
</div>
@endsection
