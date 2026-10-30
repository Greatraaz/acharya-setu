@extends('frontend.layouts.app')
@section('title', $template->name.' — Starter template')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div style="font-size:12px;color:var(--text-2);margin-bottom:12px;">
            <a href="{{ route('mentor.curriculum.tracks') }}" style="color:var(--brand);">Curriculum</a>
            <span> / </span>
            <span>Templates</span>
            <span> / </span>
            <span>{{ $template->name }}</span>
        </div>

        <div class="dash-header dash-header--actions flex-between">
            <div class="dash-header__main">
                <div class="dash-title">{{ $template->icon ? $template->icon.' ' : '' }}{{ $template->name }}</div>
                <div class="dash-subtitle">
                    {{ $summary['expertise_stream'] ?: 'All streams' }}
                    @if($summary['expertise_field']) · {{ $summary['expertise_field'] }} @endif
                    · {{ $summary['months_count'] }} months
                    · {{ $summary['weeks_count'] }} weeks
                    · {{ $summary['tasks_count'] }} tasks
                    @if(!empty($template->matched)) · Matched to your expertise @endif
                </div>
            </div>
            <div class="dash-header__actions">
                <button type="button" class="btn btn-primary btn-sm" onclick="openModal('assign-template-modal')" @disabled($mentees->isEmpty())>
                    Assign to mentee
                </button>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
        @endif

        @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:16px;">
            <ul style="margin:0;padding-left:16px;font-size:13px;">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        @if($template->description)
        <div class="card" style="margin-bottom:16px;">
            <p style="margin:0;font-size:14px;color:var(--text-2);line-height:1.6;">{{ $template->description }}</p>
        </div>
        @endif

        @if($mentees->isEmpty())
        <div class="alert alert-info" style="margin-bottom:16px;">
            You can preview this plan now. Once a mentee is linked, assign it, then tweak weeks and tasks for them.
        </div>
        @endif

        @forelse($outline as $month)
        <div class="card" style="margin-bottom:12px;padding:16px 18px;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:8px;">
                <span style="font-size:12px;font-weight:700;color:var(--brand);">Month {{ $month['month_number'] }}</span>
                <strong>{{ $month['title'] }}</strong>
                @if($month['theme'])
                <span class="curriculum-template-pill">{{ $month['theme'] }}</span>
                @endif
            </div>
            @if($month['description'])
            <p style="margin:0 0 10px;font-size:13px;color:var(--text-2);">{{ $month['description'] }}</p>
            @endif
            <div class="curriculum-template-weeks">
                @foreach($month['weeks'] as $week)
                <div class="curriculum-template-week">
                    <div class="curriculum-template-week__title">Week {{ $week['week_number'] }} · {{ $week['title'] }}</div>
                    @if($week['focus'])
                    <div class="curriculum-template-week__focus">{{ $week['focus'] }}</div>
                    @endif
                    <ul class="curriculum-template-week__tasks">
                        @foreach($week['tasks'] as $task)
                        <li>{{ $task['title'] }} <span>{{ $task['type'] }}</span></li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:40px 0;">
            <div style="font-size:16px;font-weight:700;margin-bottom:8px;">This template has no months yet</div>
            <p style="font-size:13px;color:var(--text-2);">Ask admin to add the 6-month plan, or create a track from scratch.</p>
        </div>
        @endforelse
    </div>
</div>

<div class="modal-overlay" id="assign-template-modal">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <h3>Assign “{{ $template->name }}”</h3>
            <button type="button" class="modal-close" onclick="closeModal('assign-template-modal')">✕</button>
        </div>
        <form method="POST" action="{{ route('mentor.curriculum.templates.assign', $template) }}">
            @csrf
            <div class="modal-body">
                <p style="font-size:13px;color:var(--text-2);margin:0 0 14px;">Creates a copy for the mentee. You can rename it and edit months afterwards.</p>
                <div class="form-group">
                    <label class="form-label">Mentee *</label>
                    <select name="mentee_id" class="form-select" required>
                        <option value="">— Select mentee —</option>
                        @foreach($mentees as $m)
                        <option value="{{ $m->id }}" @selected(old('mentee_id') == $m->id)>{{ $m->name }} ({{ $m->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Track name</label>
                    <input type="text" name="name" class="form-input" maxlength="100" value="{{ old('name', $template->name) }}">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Note for this mentee</label>
                    <textarea name="description" class="form-textarea" rows="3">{{ old('description', $template->description) }}</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('assign-template-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary" @disabled($mentees->isEmpty())>Copy &amp; assign</button>
            </div>
        </form>
    </div>
</div>
@endsection
