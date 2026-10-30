@extends('frontend.layouts.app')
@section('title', 'Curriculum — Vedrix Mentor')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header dash-header--actions flex-between">
            <div class="dash-header__main">
                <div class="dash-title">Mentee Curriculum</div>
                <div class="dash-subtitle">Start from a template matched to your expertise, tweak it, then assign it to a mentee — or build a track from scratch.</div>
            </div>
            <div class="dash-header__actions">
                <a href="{{ route('mentor.journey') }}" class="btn btn-outline btn-sm">Progress tracker</a>
                <button type="button" class="btn btn-outline btn-sm" onclick="openModal('import-templates-modal')">Import Excel</button>
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

        @if(session('import_errors'))
        <div class="alert alert-error" style="margin-bottom:16px;">
            <ul style="margin:0;padding-left:16px;font-size:13px;">
                @foreach(session('import_errors') as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <section class="curriculum-templates">
            <div class="curriculum-templates__header">
                <div>
                    <div class="curriculum-templates__title">Starter templates for your industry</div>
                    <div class="curriculum-templates__subtitle">
                        @if(auth()->user()->field)
                            Showing plans for <strong>{{ \App\Support\MentorIndustry::canonicalize(auth()->user()->field) ?: auth()->user()->field }}</strong>. Assign one, then tweak weeks and tasks for the mentee.
                        @else
                            Set your industry on your profile to see starter templates for that domain.
                        @endif
                    </div>
                </div>
                <a href="{{ route('mentor.curriculum.templates.sample') }}" class="btn btn-ghost btn-sm">Sample Excel</a>
            </div>

            @if(($templates ?? collect())->isNotEmpty())
            <div class="curriculum-templates__grid">
                @foreach($templates as $template)
                    @include('frontend.mentors.curriculum.partials.template-card', ['template' => $template, 'mentees' => $mentees])
                @endforeach
            </div>
            @else
            <div class="card" style="padding:20px;">
                <p style="margin:0;font-size:13px;color:var(--text-2);">
                    @if(! auth()->user()->field)
                        Add your primary domain in profile / onboarding to unlock industry templates.
                    @else
                        No starter template is published for {{ \App\Support\MentorIndustry::canonicalize(auth()->user()->field) ?: auth()->user()->field }} yet. You can still create a track from scratch.
                    @endif
                </p>
            </div>
            @endif
        </section>

        @if($filterMentee)
        <div class="card" style="margin-bottom:16px;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <span style="font-size:13px;">Showing tracks for <strong>{{ $filterMentee->name }}</strong></span>
            <a href="{{ route('mentor.curriculum.tracks') }}" class="btn btn-ghost btn-sm">Clear filter</a>
        </div>
        @endif

        @if($mentees->isNotEmpty())
        <form method="GET" action="{{ route('mentor.curriculum.tracks') }}" class="curriculum-tracks-toolbar">
            <div class="curriculum-tracks-toolbar__grid">
                <div class="curriculum-tracks-toolbar__field">
                    <label class="curriculum-tracks-toolbar__label" for="curriculum-mentee-filter">Mentee</label>
                    <select id="curriculum-mentee-filter" name="mentee_id" class="form-input form-select curriculum-tracks-toolbar__select" aria-label="Filter by mentee">
                        <option value="">All mentees</option>
                        @foreach($mentees as $m)
                        <option value="{{ $m->id }}" @selected((string) request('mentee_id') === (string) $m->id)>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="curriculum-tracks-toolbar__search-row">
                    <div class="session-search-field curriculum-tracks-toolbar__search">
                        <span class="session-search-icon" aria-hidden="true">🔍</span>
                        <input id="curriculum-track-search" type="search" name="search" class="form-input" value="{{ $search ?? request('search') }}"
                               placeholder="Search track name…" autocomplete="off" aria-label="Search track name">
                    </div>
                    <button type="submit" class="btn btn-outline curriculum-tracks-toolbar__submit">Search</button>
                    @if(request()->filled('mentee_id') || request()->filled('search'))
                        <a href="{{ route('mentor.curriculum.tracks') }}" class="btn btn-ghost curriculum-tracks-toolbar__clear">Clear</a>
                    @endif
                </div>
            </div>
        </form>
        @endif

        @forelse($tracks as $track)
        <div class="card curriculum-track-card">
            <div class="curriculum-track-card__inner">
                <div class="curriculum-track-card__main">
                    <div class="curriculum-track-card__title-row">
                        <div class="curriculum-track-card__title">{{ $track->name }}</div>
                        <span class="session-status {{ $track->is_active ? 'confirmed' : 'pending' }}">{{ $track->is_active ? 'Active' : 'Inactive' }}</span>
                        @if($track->cloned_from_id)
                        <span class="curriculum-template-pill">From template</span>
                        @endif
                    </div>
                    <div class="curriculum-track-card__meta">
                        {{ $track->mentee->name ?? 'No mentee' }}
                        · {{ $track->months_count }} month(s)
                        @if($track->description && $track->description !== $track->name)
                        · {{ Str::limit($track->description, 80) }}
                        @endif
                    </div>
                </div>
                <div class="curriculum-track-card__actions">
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
                Use a starter template above to skip the blank page, or create a track from scratch and add months, weeks, and tasks.
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

{{-- Import templates --}}
<div class="modal-overlay" id="import-templates-modal">
    <div class="modal" style="max-width:520px;">
        <div class="modal-header">
            <h3>Import templates from Excel</h3>
            <button type="button" class="modal-close" onclick="closeModal('import-templates-modal')">✕</button>
        </div>
        <form method="POST" action="{{ route('mentor.curriculum.templates.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <p style="font-size:13px;color:var(--text-2);margin:0 0 14px;">
                    Upload one spreadsheet with multiple templates. Each row is a task or MCQ. Repeat the template name on every row that belongs to it. Imports are saved under your industry
                    @if(auth()->user()->field)
                        (<strong>{{ \App\Support\MentorIndustry::canonicalize(auth()->user()->field) ?: auth()->user()->field }}</strong>)
                    @endif
                    and stay private to you.
                </p>
                <div class="form-group">
                    <label class="form-label">Excel file *</label>
                    <input type="file" name="file" class="form-input" accept=".xlsx,.xls,.csv" required>
                </div>
                <p style="font-size:12px;color:var(--text-3);margin:0;">
                    Required columns: template_name, industry, month_number, week_number, plus task and/or MCQ fields.
                    <a href="{{ route('mentor.curriculum.templates.sample') }}" style="color:var(--brand);">Download the sample</a>
                    (includes a Guide sheet with every column).
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('import-templates-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Import</button>
            </div>
        </form>
    </div>
</div>

{{-- Assign template --}}
<div class="modal-overlay" id="assign-template-modal">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <h3 id="assign-template-title">Assign template</h3>
            <button type="button" class="modal-close" onclick="closeModal('assign-template-modal')">✕</button>
        </div>
        <form id="assign-template-form" method="POST">
            @csrf
            <div class="modal-body">
                <p style="font-size:13px;color:var(--text-2);margin:0 0 14px;">Copies the full 6-month plan to this mentee. You can edit months and tasks right after.</p>
                @if($mentees->isEmpty())
                <div class="alert alert-info" style="margin-bottom:0;">Link a mentee first, then you can assign a template.</div>
                @else
                <div class="form-group">
                    <label class="form-label">Mentee *</label>
                    <select name="mentee_id" class="form-select" required>
                        <option value="">— Select mentee —</option>
                        @foreach($mentees as $m)
                        <option value="{{ $m->id }}" @selected(old('mentee_id', $filterMentee->id ?? null) == $m->id)>{{ $m->name }} ({{ $m->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Track name</label>
                    <input type="text" name="name" id="assign-template-name" class="form-input" maxlength="100">
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('assign-template-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary" @disabled($mentees->isEmpty())>Copy &amp; assign</button>
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
function openAssignTemplate(id, name, action) {
    const form = document.getElementById('assign-template-form');
    form.action = action || (@json(url('/mentor/curriculum/templates')) + '/' + id + '/assign');
    document.getElementById('assign-template-title').textContent = name ? ('Assign “' + name + '”') : 'Assign template';
    const nameInput = document.getElementById('assign-template-name');
    if (nameInput) nameInput.value = name || '';
    openModal('assign-template-modal');
}
document.addEventListener('click', function (event) {
    const btn = event.target.closest('.js-assign-template');
    if (!btn) return;
    event.preventDefault();
    openAssignTemplate(btn.getAttribute('data-id'), btn.getAttribute('data-name') || '', btn.getAttribute('data-action'));
});
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
