@extends('frontend.layouts.app')
@section('title', ($month->title ?: 'Month '.$month->month_number).' — Journey')

@section('content')
<div class="dash-layout journey-page">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header journey-page__header">
            <nav class="journey-page__breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('mentee.journey.index') }}">My Journey</a>
                <span class="journey-page__breadcrumb-sep">/</span>
                <span class="journey-page__breadcrumb-current">Month {{ $month->month_number }}</span>
            </nav>
            <div class="dash-title journey-page__title">{{ $month->title ?: 'Month '.$month->month_number }}</div>
            <div class="dash-subtitle journey-page__subtitle">{{ $month->theme ?: ($month->description ? Str::limit($month->description, 120) : 'Weekly plan for this month') }}</div>
        </div>

        @if($canViewProgress ?? false)
        <div class="card journey-page__progress-card">
            <div class="journey-page__progress-head">
                <span>Month progress</span>
                <span>{{ (int) ($progress['percent'] ?? 0) }}%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width:{{ (int) ($progress['percent'] ?? 0) }}%"></div>
            </div>
            <div class="journey-page__week-meta" style="margin-top:8px;">
                {{ (int) ($progress['completed'] ?? 0) }} of {{ (int) ($progress['total'] ?? 0) }} items completed
            </div>
        </div>
        @else
        <div class="alert alert-warning journey-page__alert">
            <span class="alert-icon">🔒</span>
            <div class="journey-page__alert-body">Progress % and completion counts are hidden on your plan. You can still open weeks and work on tasks.</div>
        </div>
        @endif

        <div class="card journey-page__section journey-page__section--last">
            <h3 class="journey-page__section-title">Weeks</h3>
            @forelse($weekProgress as $row)
            @php
                $w = $row['week'];
                $wPercent = (int) ($row['percent'] ?? 0);
            @endphp
            <a href="{{ route('mentee.journey.week', $w->id) }}" class="card journey-page__week-card journey-page__week-link">
                <div class="journey-page__week-row">
                    <div class="journey-page__week-main">
                        <div class="journey-page__week-label">Week {{ $w->week_number }}</div>
                        <div class="journey-page__week-title">{{ $w->title ?: 'Week '.$w->week_number }}</div>
                        @if($w->focus)
                        <div class="journey-page__week-focus">{{ $w->focus }}</div>
                        @endif
                    </div>
                    @if($canViewProgress ?? false)
                    <span class="journey-page__week-pct">{{ $wPercent }}%</span>
                    @endif
                </div>
                <div style="margin-top:12px;">
                    @if($canViewProgress ?? false)
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:{{ $wPercent }}%"></div>
                    </div>
                    <div class="journey-page__week-meta">
                        {{ (int) ($row['completed'] ?? 0) }} / {{ (int) ($row['total'] ?? 0) }} completed
                        · {{ $w->tasks->count() }} tasks · {{ $w->mcqs->count() }} MCQs
                    </div>
                    @else
                    <div class="journey-page__week-meta">
                        {{ $w->tasks->count() }} tasks · {{ $w->mcqs->count() }} MCQs
                    </div>
                    @endif
                </div>
            </a>
            @empty
            <div class="empty-state" style="padding:28px 0;">
                <div style="font-size:14px;font-weight:700;">No weeks yet</div>
                <div class="journey-page__empty" style="margin-top:4px;">This month doesn’t have weekly content published.</div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
