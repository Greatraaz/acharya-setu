@extends('frontend.layouts.app')
@section('title', 'Curriculum — Vedrix Mentor')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between" style="flex-wrap:wrap;gap:12px;">
            <div>
                <div class="dash-title">Mentee Curriculum</div>
                <div class="dash-subtitle">Create and manage personalized learning tracks for your mentees.</div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('mentor.journey') }}" class="btn btn-outline btn-sm">Progress tracker</a>
                <button type="button" class="btn btn-primary btn-sm" onclick="openModal('add-track-modal')">+ New Track</button>
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

        @if($filterMentee)
        <div class="card" style="margin-bottom:16px;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <span style="font-size:13px;">Showing tracks for <strong>{{ $filterMentee->name }}</strong></span>
            <a href="{{ route('mentor.curriculum.tracks') }}" class="btn btn-ghost btn-sm">Clear filter</a>
        </div>
        @endif

        @if($mentees->isNotEmpty())
        <form method="GET" action="{{ route('mentor.curriculum.tracks') }}" class="session-toolbar" style="margin-bottom:16px;">
            <div class="session-toolbar-controls" style="width:100%;flex-wrap:wrap;">
                <div class="form-group" style="margin:0;min-width:200px;">
                    <label class="form-label">Mentee</label>
                    <select name="mentee_id" class="form-select">
                        <option value="">All mentees</option>
                        @foreach($mentees as $m)
                        <option value="{{ $m->id }}" @selected((string) request('mentee_id') === (string) $m->id)>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="session-search-field" style="flex:1;min-width:200px;">
                    <span class="session-search-icon" aria-hidden="true">🔍</span>
                    <input type="search" name="search" class="form-input" value="{{ $search ?? request('search') }}"
                           placeholder="Search track name…" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-outline" style="align-self:flex-end;">Apply</button>
                @if(request()->filled('mentee_id') || request()->filled('search'))
                    <a href="{{ route('mentor.curriculum.tracks') }}" class="btn btn-ghost" style="align-self:flex-end;">Clear</a>
                @endif
            </div>
        </form>
        @endif

        @forelse($tracks as $track)
        <div class="card" style="margin-bottom:12px;padding:16px 18px;">
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                <div style="flex:1;min-width:200px;">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <div style="font-size:15px;font-weight:700;">{{ $track->name }}</div>
                        <span class="session-status {{ $track->is_active ? 'confirmed' : 'pending' }}">{{ $track->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <div style="font-size:12px;color:var(--text-2);margin-top:4px;">
                        {{ $track->mentee->name ?? 'No mentee' }}
                        · {{ $track->months_count }} month(s)
                        @if($track->description)
                        · {{ Str::limit($track->description, 80) }}
                        @endif
                    </div>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="{{ route('mentor.curriculum.months', $track) }}" class="btn btn-primary btn-sm">Manage months</a>
                    <button type="button" class="btn btn-outline btn-sm" onclick='openEditTrack(@json($track))'>Edit</button>
                    @if($track->mentee_id)
                    <a href="{{ route('mentor.journey.show', $track->mentee_id) }}" class="btn btn-ghost btn-sm">Progress</a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:48px 0;">
            <div style="font-size:48px;margin-bottom:12px;">🗺️</div>
            <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No curriculum tracks yet</div>
            <p style="font-size:13px;color:var(--text-2);max-width:400px;margin:0 auto 16px;">
                Create a track for a mentee, then add months, weeks, tasks, MCQs, and supporting materials — same flow as the app.
            </p>
            <button type="button" class="btn btn-primary" onclick="openModal('add-track-modal')">Create first track</button>
        </div>
        @endforelse

        @include('frontend.partials.pagination', ['paginator' => $tracks])
    </div>
</div>

{{-- Add Track --}}
<div class="modal-overlay" id="add-track-modal">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <h3>New Curriculum Track</h3>
            <button type="button" class="modal-close" onclick="closeModal('add-track-modal')">✕</button>
        </div>
        <form method="POST" action="{{ route('mentor.curriculum.tracks.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Mentee *</label>
                    <select name="mentee_id" class="form-select" required>
                        <option value="">— Select mentee —</option>
                        @foreach($mentees as $m)
                        <option value="{{ $m->id }}" @selected(old('mentee_id', $filterMentee->id ?? null) == $m->id)>{{ $m->name }} ({{ $m->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Track name *</label>
                    <input type="text" name="name" class="form-input" required maxlength="100" value="{{ old('name') }}" placeholder="e.g. Frontend Engineering Track">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="3" placeholder="Personalized roadmap for this mentee">{{ old('description') }}</textarea>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" checked>
                        Active
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('add-track-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create track</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Track --}}
<div class="modal-overlay" id="edit-track-modal">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <h3>Edit Track</h3>
            <button type="button" class="modal-close" onclick="closeModal('edit-track-modal')">✕</button>
        </div>
        <form id="edit-track-form" method="POST">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Mentee *</label>
                    <select name="mentee_id" class="form-select" required>
                        <option value="">— Select mentee —</option>
                        @foreach($mentees as $m)
                        <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Track name *</label>
                    <input type="text" name="name" class="form-input" required maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="3"></textarea>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1">
                        Active
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('edit-track-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update track</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEditTrack(track) {
    const form = document.getElementById('edit-track-form');
    form.action = @json(url('/mentor/curriculum/tracks')) + '/' + track.id;
    form.querySelector('[name=mentee_id]').value = track.mentee_id || '';
    form.querySelector('[name=name]').value = track.name || '';
    form.querySelector('[name=description]').value = track.description || '';
    form.querySelector('[name=is_active][type=checkbox]').checked = !!track.is_active;
    openModal('edit-track-modal');
}
</script>
@endpush
