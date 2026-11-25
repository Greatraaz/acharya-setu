@extends('frontend.layouts.app')
@section('title', 'My Journey — Vedrix')

@section('content')
@include('frontend.partials.journey-premium-ui')

<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-page journey-premium" style="display:flex;flex-direction:column;gap:1.15rem;">

            <div class="learn-intro fu">
                <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-start;justify-content:space-between;">
                    <div>
                        <span class="learn-eyebrow">Learning · Curriculum</span>
                        <h2>My Journey</h2>
                        <p>A premium path through months and weeks — guided by your mentor, paced for real progress.</p>
                    </div>
                    @if($assignedMentor ?? null)
                        <a href="{{ route('mentee.mentor.change') }}" class="learn-btn-ghost">View mentor</a>
                    @else
                        <a href="{{ route('mentors.search') }}" class="learn-btn-primary">Find a mentor</a>
                    @endif
                </div>
            </div>

            @unless($canViewProgress ?? false)
            <div class="alert-premium fu">
                <span style="font-size:1.2rem;">🔒</span>
                <div>
                    <strong>Progress reports are locked on your plan</strong>
                    <p>You can still open months, weeks, and tasks. Upgrade to unlock scores and completion tracking.
                        <a href="{{ route('mentee.plans') }}" class="dash-link">Upgrade →</a>
                    </p>
                </div>
            </div>
            @endunless

            @if($personalTracks->isEmpty())
                <div class="learn-panel empty-block fu">
                    <div class="icon">🗺️</div>
                    <div style="font-family:var(--font-head);font-size:1.1rem;font-weight:800;color:var(--text);margin-bottom:.4rem;">Your journey hasn’t started yet</div>
                    @if($assignedMentor)
                        <p>You’re connected with <strong>{{ $assignedMentor->name }}</strong>. Your personalized curriculum will appear once a learning track is created for you.</p>
                        <a href="{{ route('mentee.mentor.change') }}" class="learn-btn-primary" style="margin-top:.9rem;">View my mentor</a>
                    @else
                        <p>Connect with a mentor to unlock a structured curriculum built around your goals.</p>
                        <a href="{{ route('mentors.search') }}" class="learn-btn-primary" style="margin-top:.9rem;">Find a Mentor</a>
                    @endif
                </div>
            @else
                @php
                    $percent = (int) ($progress['percent'] ?? $progress['percentage'] ?? 0);
                    $streamName = $selectedTrack->name ?? ($enrollment->stream->name ?? 'Your track');
                    $completed = (int) ($progress['completed'] ?? 0);
                    $total = (int) ($progress['total'] ?? 0);
                    $monthCount = (int) ($selectedTrack->months_count ?? 0);
                @endphp

                <div class="jp-hero fu">
                    <div class="jp-hero__kicker">Current track</div>
                    <h3 class="jp-hero__title">{{ $streamName }}</h3>
                    <p class="jp-hero__sub">
                        @if($enrollment)
                            Month {{ $enrollment->current_month }} · Week {{ $enrollment->current_week }} · {{ ucfirst($enrollment->status) }}
                        @else
                            {{ $monthCount }} month{{ $monthCount === 1 ? '' : 's' }} · {{ ($selectedTrack->is_active ?? false) ? 'Active' : 'Inactive' }}
                        @endif
                    </p>
                    @if($canViewProgress ?? false)
                    <div class="jp-hero__progress">
                        <div class="jp-hero__progress-row">
                            <span>Overall progress</span>
                            <span>{{ $percent }}%</span>
                        </div>
                        <div class="jp-hero__bar"><span style="width:{{ $percent }}%"></span></div>
                    </div>
                    @endif
                </div>

                <div class="dash-grid dash-grid-4 fu">
                    <div class="learn-stat">
                        <div style="display:flex;justify-content:space-between;gap:.75rem;">
                            <div>
                                <div class="learn-stat__value">{{ $personalTracks->count() }}</div>
                                <div class="learn-stat__label">Tracks assigned</div>
                            </div>
                            <span class="learn-stat__icon">📘</span>
                        </div>
                    </div>
                    <div class="learn-stat">
                        <div style="display:flex;justify-content:space-between;gap:.75rem;">
                            <div>
                                <div class="learn-stat__value">{{ $monthCount }}</div>
                                <div class="learn-stat__label">Months in track</div>
                            </div>
                            <span class="learn-stat__icon">🗓️</span>
                        </div>
                    </div>
                    @if($canViewProgress ?? false)
                    <div class="learn-stat">
                        <div style="display:flex;justify-content:space-between;gap:.75rem;">
                            <div>
                                <div class="learn-stat__value" style="color:var(--ln-dark);">{{ $percent }}%</div>
                                <div class="learn-stat__label">Completion</div>
                            </div>
                            <span class="learn-stat__icon">📈</span>
                        </div>
                    </div>
                    <div class="learn-stat">
                        <div style="display:flex;justify-content:space-between;gap:.75rem;">
                            <div>
                                <div class="learn-stat__value">{{ $completed }}<span style="font-size:.85rem;color:var(--text-3);font-weight:650;">/{{ $total }}</span></div>
                                <div class="learn-stat__label">Items done</div>
                            </div>
                            <span class="learn-stat__icon">✅</span>
                        </div>
                    </div>
                    @else
                    <div class="learn-stat" style="grid-column:span 2;">
                        <div style="display:flex;justify-content:space-between;gap:.75rem;">
                            <div>
                                <div class="learn-stat__value" style="font-size:1.05rem;">Open months freely</div>
                                <div class="learn-stat__label">Progress % unlocks with plan upgrade</div>
                            </div>
                            <span class="learn-stat__icon">🔓</span>
                        </div>
                    </div>
                    @endif
                </div>

                @if($personalTracks->count() > 1)
                <div class="learn-panel panel-pad fu">
                    <div class="panel-head">
                        <div>
                            <h3 class="dash-section-title">Your tracks</h3>
                            <p class="dash-section-sub">Switch curriculum anytime</p>
                        </div>
                    </div>
                    <div class="jp-track-grid">
                        @foreach($personalTracks as $track)
                            @php
                                $isSelected = $selectedTrack && (int) $selectedTrack->id === (int) $track->id;
                                $isSetup = (int) $track->months_count === 0;
                            @endphp
                            <a href="{{ route('mentee.journey.index', ['track' => $track->id]) }}"
                               class="jp-track-card {{ $isSelected ? 'is-active' : '' }}"
                               aria-current="{{ $isSelected ? 'true' : 'false' }}">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                                    <span class="learn-stat__icon">{{ $isSelected ? '✓' : '📘' }}</span>
                                    @if($isSelected)
                                        <span class="learn-chip learn-chip--todo">Current</span>
                                    @elseif($isSetup)
                                        <span class="learn-chip learn-chip--wait">Setup</span>
                                    @endif
                                </div>
                                <div style="font-family:var(--font-head);font-weight:750;font-size:.92rem;">{{ $track->name }}</div>
                                <div style="font-size:.72rem;color:var(--text-3);font-weight:650;">
                                    @if($isSetup) Setup in progress @else {{ (int) $track->months_count }} month{{ (int) $track->months_count === 1 ? '' : 's' }} @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="learn-panel panel-pad fu">
                    <div class="panel-head">
                        <div>
                            <h3 class="dash-section-title">Month roadmap</h3>
                            <p class="dash-section-sub">Follow the path — open a month to continue</p>
                        </div>
                    </div>

                    <div class="jp-timeline">
                        @forelse($monthProgress as $row)
                            @php
                                $m = $row['month'];
                                $mPercent = (int) ($row['percent'] ?? 0);
                            @endphp
                            <div class="jp-node">
                                <div class="jp-node__dot">{{ $m->month_number }}</div>
                                <a href="{{ route('mentee.journey.month', $m->id) }}" class="jp-node__card">
                                    <div style="display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;">
                                        <div style="min-width:0;">
                                            <div class="jp-node__label">Month {{ $m->month_number }}</div>
                                            <h4 class="jp-node__title">{{ $m->title ?: 'Month '.$m->month_number }}</h4>
                                            @if($m->theme)
                                                <div style="font-size:.8rem;color:var(--text-2);margin-top:.3rem;">{{ $m->theme }}</div>
                                            @endif
                                        </div>
                                        @if($canViewProgress ?? false)
                                            <span class="jp-node__pct">{{ $mPercent }}%</span>
                                        @endif
                                    </div>
                                    @if($canViewProgress ?? false)
                                        <div class="progress-track" style="margin-top:.85rem;"><span style="width:{{ $mPercent }}%"></span></div>
                                        <div class="jp-node__meta">
                                            {{ (int) ($row['completed'] ?? 0) }} / {{ (int) ($row['total'] ?? 0) }} completed · {{ $m->weeks->count() }} weeks
                                        </div>
                                    @else
                                        <div class="jp-node__meta">{{ $m->weeks->count() }} weeks · Open to view tasks</div>
                                    @endif
                                </a>
                            </div>
                        @empty
                            <div class="empty-block">
                                <div class="icon">🗓️</div>
                                <div style="font-weight:750;color:var(--text);margin-bottom:.25rem;">Curriculum not ready</div>
                                <p>Your mentor hasn’t published months for this track yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
