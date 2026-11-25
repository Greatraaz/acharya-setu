@extends('frontend.layouts.app')
@section('title', ($month->title ?: 'Month '.$month->month_number).' — Journey')

@section('content')
@include('frontend.partials.journey-premium-ui')

<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-page journey-premium" style="display:flex;flex-direction:column;gap:1.15rem;">

            <div class="learn-intro fu">
                <nav class="jp-crumb" aria-label="Breadcrumb">
                    <a href="{{ route('mentee.journey.index') }}">My Journey</a>
                    <span class="jp-crumb__sep">/</span>
                    <span class="jp-crumb__now">Month {{ $month->month_number }}</span>
                </nav>
                <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-start;justify-content:space-between;">
                    <div>
                        <span class="learn-eyebrow">Month {{ $month->month_number }}</span>
                        <h2>{{ $month->title ?: 'Month '.$month->month_number }}</h2>
                        <p>{{ $month->theme ?: ($month->description ? Str::limit(strip_tags($month->description), 140) : 'Weekly plan for this month — open a week to keep moving.') }}</p>
                    </div>
                    <a href="{{ route('mentee.journey.index') }}" class="learn-btn-ghost">← All months</a>
                </div>
            </div>

            @if($canViewProgress ?? false)
            <div class="jp-hero fu">
                <div class="jp-hero__kicker">Month progress</div>
                <h3 class="jp-hero__title">{{ (int) ($progress['percent'] ?? 0) }}% complete</h3>
                <p class="jp-hero__sub">{{ (int) ($progress['completed'] ?? 0) }} of {{ (int) ($progress['total'] ?? 0) }} items completed this month</p>
                <div class="jp-hero__progress">
                    <div class="jp-hero__bar"><span style="width:{{ (int) ($progress['percent'] ?? 0) }}%"></span></div>
                </div>
            </div>
            @else
            <div class="alert-premium fu">
                <span>🔒</span>
                <div>
                    <strong>Progress % is hidden on your plan</strong>
                    <p>You can still open weeks and work on tasks. <a href="{{ route('mentee.plans') }}" class="dash-link">Upgrade →</a></p>
                </div>
            </div>
            @endif

            <div class="learn-panel panel-pad fu">
                <div class="panel-head">
                    <div>
                        <h3 class="dash-section-title">Weeks</h3>
                        <p class="dash-section-sub">Pick a week to work on tasks and practice MCQs</p>
                    </div>
                </div>

                <div class="jp-timeline">
                    @forelse($weekProgress as $row)
                        @php
                            $w = $row['week'];
                            $wPercent = (int) ($row['percent'] ?? 0);
                        @endphp
                        <div class="jp-node">
                            <div class="jp-node__dot">W{{ $w->week_number }}</div>
                            <a href="{{ route('mentee.journey.week', $w->id) }}" class="jp-node__card">
                                <div style="display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;">
                                    <div style="min-width:0;">
                                        <div class="jp-node__label">Week {{ $w->week_number }}</div>
                                        <h4 class="jp-node__title">{{ $w->title ?: 'Week '.$w->week_number }}</h4>
                                        @if($w->focus)
                                            <div style="font-size:.8rem;color:var(--text-2);margin-top:.3rem;">{{ $w->focus }}</div>
                                        @endif
                                    </div>
                                    @if($canViewProgress ?? false)
                                        <span class="jp-node__pct">{{ $wPercent }}%</span>
                                    @endif
                                </div>
                                @if($canViewProgress ?? false)
                                    <div class="progress-track" style="margin-top:.85rem;"><span style="width:{{ $wPercent }}%"></span></div>
                                    <div class="jp-node__meta">
                                        {{ (int) ($row['completed'] ?? 0) }} / {{ (int) ($row['total'] ?? 0) }} completed
                                        · {{ $w->tasks->count() }} tasks · {{ $w->mcqs->count() }} MCQs
                                    </div>
                                @else
                                    <div class="jp-node__meta">{{ $w->tasks->count() }} tasks · {{ $w->mcqs->count() }} MCQs</div>
                                @endif
                            </a>
                        </div>
                    @empty
                        <div class="empty-block">
                            <div class="icon">📅</div>
                            <div style="font-weight:750;color:var(--text);">No weeks yet</div>
                            <p>This month doesn’t have weekly content published.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
