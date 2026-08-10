@extends('frontend.layouts.app')
@section('title', ($month->title ?: 'Month '.$month->month_number).' — Journey')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div style="font-size:12px;margin-bottom:8px;">
                <a href="{{ route('mentee.journey.index') }}" style="color:var(--brand);">My Journey</a>
                <span style="color:var(--text-3);"> / </span>
                <span style="color:var(--text-2);">Month {{ $month->month_number }}</span>
            </div>
            <div class="dash-title">{{ $month->title ?: 'Month '.$month->month_number }}</div>
            <div class="dash-subtitle">{{ $month->theme ?: ($month->description ? Str::limit($month->description, 120) : 'Weekly plan for this month') }}</div>
        </div>

        @if($canViewProgress ?? false)
        <div class="card" style="margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-2);margin-bottom:8px;">
                <span>Month progress</span>
                <span>{{ (int) ($progress['percent'] ?? 0) }}%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width:{{ (int) ($progress['percent'] ?? 0) }}%"></div>
            </div>
            <div style="font-size:12px;color:var(--text-3);margin-top:8px;">
                {{ (int) ($progress['completed'] ?? 0) }} of {{ (int) ($progress['total'] ?? 0) }} items completed
            </div>
        </div>
        @else
        <div class="alert alert-warning" style="margin-bottom:16px;">
            <span class="alert-icon">🔒</span>
            <div style="font-size:13px;">Progress % and completion counts are hidden on your plan. You can still open weeks and work on tasks.</div>
        </div>
        @endif

        <div class="card">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">Weeks</h3>
            @forelse($weekProgress as $row)
            @php
                $w = $row['week'];
                $wPercent = (int) ($row['percent'] ?? 0);
            @endphp
            <a href="{{ route('mentee.journey.week', $w->id) }}" class="card" style="display:block;text-decoration:none;color:inherit;margin-bottom:12px;padding:16px 18px;">
                <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                    <div>
                        <div style="font-size:11px;color:var(--brand);font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px;">Week {{ $w->week_number }}</div>
                        <div style="font-size:15px;font-weight:700;">{{ $w->title ?: 'Week '.$w->week_number }}</div>
                        @if($w->focus)
                        <div style="font-size:12px;color:var(--text-2);margin-top:4px;">{{ $w->focus }}</div>
                        @endif
                    </div>
                    @if($canViewProgress ?? false)
                    <span style="font-size:13px;font-weight:700;color:var(--brand);">{{ $wPercent }}%</span>
                    @endif
                </div>
                <div style="margin-top:12px;">
                    @if($canViewProgress ?? false)
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:{{ $wPercent }}%"></div>
                    </div>
                    <div style="font-size:11px;color:var(--text-3);margin-top:6px;">
                        {{ (int) ($row['completed'] ?? 0) }} / {{ (int) ($row['total'] ?? 0) }} completed
                        · {{ $w->tasks->count() }} tasks · {{ $w->mcqs->count() }} MCQs
                    </div>
                    @else
                    <div style="font-size:11px;color:var(--text-3);">
                        {{ $w->tasks->count() }} tasks · {{ $w->mcqs->count() }} MCQs
                    </div>
                    @endif
                </div>
            </a>
            @empty
            <div class="empty-state" style="padding:28px 0;">
                <div style="font-size:14px;font-weight:700;">No weeks yet</div>
                <div style="font-size:13px;color:var(--text-2);margin-top:4px;">This month doesn’t have weekly content published.</div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
