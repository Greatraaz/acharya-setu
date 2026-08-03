@extends('frontend.layouts.app')
@section('title', 'Months — '.$track->name)

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div style="font-size:12px;color:var(--text-2);margin-bottom:12px;">
            <a href="{{ route('mentor.curriculum.tracks') }}" style="color:var(--brand);">Curriculum</a>
            <span> / </span>
            <span>{{ $track->name }}</span>
        </div>

        <div class="dash-header flex-between" style="flex-wrap:wrap;gap:12px;">
            <div>
                <div class="dash-title">{{ $track->name }}</div>
                <div class="dash-subtitle">
                    Mentee: {{ $track->mentee->name ?? '—' }}
                    · {{ $months->count() }} month(s)
                    · {{ $months->sum(fn ($m) => $m->weeks->count()) }} week(s)
                </div>
            </div>
            <button type="button" class="btn btn-primary btn-sm" onclick="openAddMonth()" @disabled(! $track->mentee_id)>
                + Add month
            </button>
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

        @unless($track->mentee_id)
        <div class="alert alert-error" style="margin-bottom:16px;">Assign a mentee to this track before adding months.</div>
        @endunless

        @forelse($months as $month)
        <div class="card" style="margin-bottom:12px;padding:0;overflow:hidden;">
            <div style="padding:16px 18px;">
                <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-start;">
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span style="font-size:13px;font-weight:700;color:var(--brand);">Month {{ $month->month_number }}</span>
                            <div style="font-size:15px;font-weight:700;">{{ $month->title }}</div>
                            @if($month->theme)
                            <span style="font-size:11px;background:var(--brand-muted);color:var(--brand-dark);padding:2px 8px;border-radius:999px;">{{ $month->theme }}</span>
                            @endif
                            @unless($month->is_active)
                            <span class="session-status pending">Inactive</span>
                            @endunless
                        </div>
                        @if($month->description)
                        <p style="font-size:13px;color:var(--text-2);margin:8px 0 0;">{{ $month->description }}</p>
                        @endif
                        @if(is_array($month->learning_outcomes) && count($month->learning_outcomes))
                        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;">
                            @foreach(array_slice($month->learning_outcomes, 0, 4) as $outcome)
                            <span style="font-size:11px;background:var(--bg);border:1px solid var(--border);padding:3px 8px;border-radius:999px;">✓ {{ $outcome }}</span>
                            @endforeach
                        </div>
                        @endif
                        <div style="font-size:12px;color:var(--text-3);margin-top:10px;">
                            {{ $month->weeks->count() }} weeks
                            · {{ $month->weeks->sum(fn ($w) => $w->tasks->count()) }} tasks
                            · {{ $month->weeks->sum(fn ($w) => $w->mcqs->count()) }} MCQs
                        </div>
                    </div>
                </div>
            </div>
            @php
                $monthEditPayload = [
                    'id' => $month->id,
                    'month_number' => $month->month_number,
                    'title' => $month->title,
                    'theme' => $month->theme,
                    'description' => $month->description,
                    'learning_outcomes' => is_array($month->learning_outcomes) ? implode("\n", $month->learning_outcomes) : '',
                    'is_active' => (bool) $month->is_active,
                ];
            @endphp
            <div style="display:flex;gap:8px;padding:12px 18px;border-top:1px solid var(--border);background:var(--bg);flex-wrap:wrap;">
                <a href="{{ route('mentor.curriculum.weeks', $month) }}" class="btn btn-primary btn-sm">Manage weeks →</a>
                <button type="button" class="btn btn-outline btn-sm" onclick='openEditMonth(@json($monthEditPayload))'>Edit</button>
                <form method="POST" action="{{ route('mentor.curriculum.months.destroy', $month) }}" onsubmit="return confirm('Delete this month and all its weeks/content?')" style="margin-left:auto;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--error);">Delete</button>
                </form>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:48px 0;">
            <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No months yet</div>
            <p style="font-size:13px;color:var(--text-2);margin-bottom:16px;">Add Month 1–12 to build this mentee’s curriculum.</p>
            <button type="button" class="btn btn-primary" onclick="openAddMonth()" @disabled(! $track->mentee_id)>Add month</button>
        </div>
        @endforelse
    </div>
</div>

{{-- Add / Edit Month Modal --}}
<div class="modal-overlay" id="month-modal">
    <div class="modal" style="max-width:520px;">
        <div class="modal-header">
            <h3 id="month-modal-title">Add Month</h3>
            <button type="button" class="modal-close" onclick="closeModal('month-modal')">✕</button>
        </div>
        <form id="month-form" method="POST">
            @csrf
            <input type="hidden" name="_method" id="month-method" value="POST">
            <input type="hidden" name="mentee_id" value="{{ $track->mentee_id }}">
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:120px 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Month # *</label>
                        <input type="number" name="month_number" class="form-input" min="1" max="12" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-input" maxlength="200" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Theme</label>
                    <input type="text" name="theme" class="form-input" maxlength="100" placeholder="e.g. Foundations">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Learning outcomes</label>
                    <textarea name="learning_outcomes" class="form-textarea" rows="3" placeholder="One outcome per line"></textarea>
                    <div class="form-hint">One outcome per line (saved as array, same as app API).</div>
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
                <button type="button" class="btn btn-outline" onclick="closeModal('month-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="month-submit">Save month</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openAddMonth() {
    document.getElementById('month-modal-title').textContent = 'Add Month';
    document.getElementById('month-form').action = @json(route('mentor.curriculum.months.store', $track));
    document.getElementById('month-method').value = 'POST';
    document.getElementById('month-form').reset();
    document.querySelector('#month-form [name=is_active][type=checkbox]').checked = true;
    openModal('month-modal');
}

function openEditMonth(month) {
    document.getElementById('month-modal-title').textContent = 'Edit Month';
    document.getElementById('month-form').action = @json(url('/mentor/curriculum/months')) + '/' + month.id;
    document.getElementById('month-method').value = 'PUT';
    const form = document.getElementById('month-form');
    form.month_number.value = month.month_number || '';
    form.title.value = month.title || '';
    form.theme.value = month.theme || '';
    form.description.value = month.description || '';
    form.learning_outcomes.value = month.learning_outcomes || '';
    form.querySelector('[name=is_active][type=checkbox]').checked = !!month.is_active;
    openModal('month-modal');
}
</script>
@endpush
