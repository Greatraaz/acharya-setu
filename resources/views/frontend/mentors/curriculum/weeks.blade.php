@extends('frontend.layouts.app')
@section('title', 'Weeks — Month '.$month->month_number)

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div style="font-size:12px;color:var(--text-2);margin-bottom:12px;">
            <a href="{{ route('mentor.curriculum.tracks') }}" style="color:var(--brand);">Curriculum</a>
            <span> / </span>
            <a href="{{ route('mentor.curriculum.months', $month->stream) }}" style="color:var(--brand);">{{ $month->stream->name }}</a>
            <span> / </span>
            <span>Month {{ $month->month_number }}</span>
        </div>

        <div class="dash-header flex-between" style="flex-wrap:wrap;gap:12px;">
            <div>
                <div class="dash-title">Month {{ $month->month_number }}: {{ $month->title }}</div>
                <div class="dash-subtitle">
                    {{ $month->stream->mentee->name ?? 'Mentee' }}
                    · {{ $month->weeks->count() }} weeks
                    @if($month->theme)· Theme: {{ $month->theme }}@endif
                </div>
            </div>
            <button type="button" class="btn btn-primary btn-sm" onclick="openAddWeek()">+ Add week</button>
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

        @forelse($month->weeks as $week)
        <div class="card" style="margin-bottom:16px;padding:0;overflow:hidden;" id="week-{{ $week->id }}">
            <div style="padding:16px 18px;display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;cursor:pointer;" onclick="toggleWeek({{ $week->id }})">
                <div>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:var(--brand);color:#fff;font-size:11px;font-weight:700;">W{{ $week->week_number }}</span>
                        <strong style="font-size:14px;">{{ $week->title }}</strong>
                        @unless($week->is_active)<span class="session-status pending">Inactive</span>@endunless
                    </div>
                    @if($week->focus)
                    <div style="font-size:12px;color:var(--text-2);margin-top:4px;margin-left:36px;">{{ $week->focus }}</div>
                    @endif
                </div>
                <div style="font-size:12px;color:var(--text-3);">
                    {{ $week->tasks->count() }} tasks · {{ $week->mcqTopics->count() }} topics · {{ $week->supportingMaterials->count() }} materials
                </div>
            </div>

            <div id="week-body-{{ $week->id }}" style="border-top:1px solid var(--border);display:none;">
                <div style="display:flex;gap:0;border-bottom:1px solid var(--border);background:var(--bg);overflow-x:auto;">
                    <button type="button" class="curr-tab active" data-week="{{ $week->id }}" data-tab="tasks" onclick="switchTab({{ $week->id }}, 'tasks')">Tasks ({{ $week->tasks->count() }})</button>
                    <button type="button" class="curr-tab" data-week="{{ $week->id }}" data-tab="mcqs" onclick="switchTab({{ $week->id }}, 'mcqs')">MCQ Topics ({{ $week->mcqTopics->count() }})</button>
                    <button type="button" class="curr-tab" data-week="{{ $week->id }}" data-tab="materials" onclick="switchTab({{ $week->id }}, 'materials')">Materials ({{ $week->supportingMaterials->count() }})</button>
                    <button type="button" class="curr-tab" data-week="{{ $week->id }}" data-tab="settings" onclick="switchTab({{ $week->id }}, 'settings')">Settings</button>
                </div>

                {{-- Tasks --}}
                <div id="panel-tasks-{{ $week->id }}" class="week-panel" style="padding:16px;">
                    <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:12px;">
                        @forelse($week->tasks as $task)
                        <div class="curr-task-row">
                            <span class="curr-task-icon">{{ \App\Models\CurriculumTask::TYPE_ICONS[$task->type] ?? '•' }}</span>
                            <div class="curr-task-body">
                                <div class="curr-task-title-row">
                                    <span class="curr-task-title">{{ $task->title }}</span>
                                    <span class="curr-badge">{{ ucfirst($task->type) }}</span>
                                    @if($task->is_required)<span class="curr-badge curr-badge-required">Required</span>@endif
                                    @unless($task->is_active)<span class="curr-badge">Inactive</span>@endunless
                                </div>
                                @if($task->description)
                                <p class="curr-task-desc">{{ Str::limit($task->description, 120) }}</p>
                                @endif
                                <div class="curr-task-meta">
                                    <span>{{ \App\Models\CurriculumTask::SUBMISSION_TYPES[$task->submission_type] ?? $task->submission_type }}</span>
                                    @if($task->plan)<span>· Plan: {{ $task->plan->name ?? $task->plan->plan_name }}</span>@endif
                                    @if($task->estimated_minutes)<span>· ⏱ {{ $task->estimated_minutes }} min</span>@endif
                                </div>
                            </div>
                            <div class="curr-task-actions">
                                <button type="button" class="curr-btn-edit" onclick='openEditTask(@json($task))'>Edit</button>
                                <form method="POST" action="{{ route('mentor.curriculum.tasks.destroy', $task) }}" onsubmit="return confirm('Delete task?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="curr-btn-del">Del</button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <p style="font-size:13px;color:var(--text-3);text-align:center;padding:24px 0;">No tasks yet. Add the first task for this week.</p>
                        @endforelse
                    </div>
                    <button type="button" class="curr-add-dashed" onclick="openAddTask({{ $week->id }}, {{ $week->mentee_id }})">+ Add Task</button>
                </div>

                {{-- MCQ Topics --}}
                <div id="panel-mcqs-{{ $week->id }}" class="week-panel" style="padding:16px;display:none;">
                    @forelse($week->mcqTopics as $topic)
                    <div style="border:1px solid var(--border);border-radius:var(--radius-sm);margin-bottom:10px;overflow:hidden;">
                        <div style="padding:12px 14px;background:var(--bg);display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;align-items:center;">
                            <div>
                                <strong style="font-size:13px;">{{ $topic->name }}</strong>
                                <span style="font-size:11px;color:var(--text-3);margin-left:6px;">{{ $topic->mcqs->count() }} question(s)</span>
                                @if($topic->description)<div style="font-size:12px;color:var(--text-2);margin-top:2px;">{{ $topic->description }}</div>@endif
                            </div>
                            <div style="display:flex;gap:6px;">
                                <button type="button" class="btn btn-outline btn-sm" onclick='openEditMcqTopic({{ $week->id }}, @json($topic))'>Edit</button>
                                <form method="POST" action="{{ route('mentor.curriculum.mcqs.destroy', [$week, $topic]) }}" onsubmit="return confirm('Delete topic and all MCQs?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--error);">Del</button>
                                </form>
                            </div>
                        </div>
                        <div style="padding:10px 14px;">
                            @foreach($topic->mcqs as $mcq)
                            <div style="padding:8px 0;border-bottom:1px solid var(--border);font-size:12px;">
                                <div style="display:flex;justify-content:space-between;gap:8px;">
                                    <div>
                                        <span style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--text-3);">{{ $mcq->difficulty }}</span>
                                        · {{ Str::limit($mcq->question, 100) }}
                                        <div style="margin-top:4px;display:flex;flex-wrap:wrap;gap:4px;">
                                            @foreach($mcq->options ?? [] as $i => $opt)
                                            <span style="padding:2px 6px;border-radius:4px;border:1px solid var(--border);{{ $i == $mcq->correct_index ? 'background:#ecfdf5;border-color:#86efac;color:#166534;font-weight:600;' : '' }}">
                                                {{ chr(65+$i) }}. {{ Str::limit($opt, 28) }}
                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('mentor.curriculum.mcqs.item.destroy', [$week, $topic, $mcq]) }}" onsubmit="return confirm('Delete this MCQ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--error);">×</button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <p style="font-size:13px;color:var(--text-3);text-align:center;padding:20px 0;">No MCQ topics yet.</p>
                    @endforelse
                    <button type="button" class="btn btn-outline" style="width:100%;margin-top:8px;" onclick="openAddMcqTopic({{ $week->id }}, {{ $week->mentee_id }})">+ Add MCQ topic</button>
                </div>

                {{-- Materials --}}
                <div id="panel-materials-{{ $week->id }}" class="week-panel" style="padding:16px;display:none;">
                    @forelse($week->supportingMaterials as $material)
                    <div style="display:flex;justify-content:space-between;gap:12px;padding:12px;border:1px solid var(--border);border-radius:var(--radius-sm);margin-bottom:8px;">
                        <div>
                            <strong style="font-size:13px;">{{ $material->title ?: ($material->file_name ?: 'Material') }}</strong>
                            <div style="font-size:11px;color:var(--text-3);margin-top:2px;">
                                {{ \App\Models\TaskSupportingMaterial::TYPES[$material->type] ?? $material->type }}
                                @if($material->link) · <a href="{{ $material->link }}" target="_blank" style="color:var(--brand);">Open link</a>@endif
                                @if($material->file_url) · <a href="{{ $material->file_url }}" target="_blank" style="color:var(--brand);">Download</a>@endif
                            </div>
                            @if($material->description)<p style="font-size:12px;color:var(--text-2);margin:4px 0 0;">{{ Str::limit($material->description, 100) }}</p>@endif
                        </div>
                        <div style="display:flex;gap:6px;">
                            <button type="button" class="btn btn-outline btn-sm" onclick='openEditMaterial(@json($material))'>Edit</button>
                            <form method="POST" action="{{ route('mentor.curriculum.materials.destroy', $material) }}" onsubmit="return confirm('Delete material?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--error);">Del</button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <p style="font-size:13px;color:var(--text-3);text-align:center;padding:20px 0;">No supporting materials yet.</p>
                    @endforelse
                    <button type="button" class="btn btn-outline" style="width:100%;margin-top:8px;" onclick="openAddMaterial({{ $week->id }}, {{ $week->mentee_id }})">+ Add material</button>
                </div>

                {{-- Settings --}}
                <div id="panel-settings-{{ $week->id }}" class="week-panel" style="padding:16px;display:none;">
                    <form method="POST" action="{{ route('mentor.curriculum.weeks.update', $week) }}">
                        @csrf @method('PUT')
                        <div style="display:grid;grid-template-columns:120px 1fr;gap:12px;">
                            <div class="form-group">
                                <label class="form-label">Week #</label>
                                <input type="number" name="week_number" class="form-input" min="1" max="52" value="{{ $week->week_number }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-input" value="{{ $week->title }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Focus / description</label>
                            <textarea name="focus" class="form-textarea" rows="2">{{ $week->focus }}</textarea>
                        </div>
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:14px;cursor:pointer;">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked($week->is_active)>
                            Active
                        </label>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <button type="submit" class="btn btn-primary btn-sm">Update week</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('mentor.curriculum.weeks.destroy', $week) }}" onsubmit="return confirm('Delete this week and all content?')" style="margin-top:12px;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline btn-sm" style="color:var(--error);border-color:var(--error);">Delete week</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:48px 0;">
            <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No weeks yet</div>
            <p style="font-size:13px;color:var(--text-2);margin-bottom:16px;">Add weeks, then attach tasks, MCQ topics, and supporting materials.</p>
            <button type="button" class="btn btn-primary" onclick="openAddWeek()">Add week</button>
        </div>
        @endforelse
    </div>
</div>

{{-- Add Week Modal --}}
<div class="modal-overlay" id="add-week-modal">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <h3>Add Week</h3>
            <button type="button" class="modal-close" onclick="closeModal('add-week-modal')">✕</button>
        </div>
        <form method="POST" action="{{ route('mentor.curriculum.weeks.store', $month) }}">
            @csrf
            <input type="hidden" name="mentee_id" value="{{ $month->mentee_id }}">
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:120px 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Week # *</label>
                        <input type="number" name="week_number" class="form-input" min="1" max="52" required value="{{ ($month->weeks->max('week_number') ?? 0) + 1 }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-input" maxlength="200" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Focus</label>
                    <textarea name="focus" class="form-textarea" rows="2" placeholder="Core focus for this week"></textarea>
                </div>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked>
                    Active
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('add-week-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create week</button>
            </div>
        </form>
    </div>
</div>

{{-- Task Modal --}}
<div class="modal-overlay" id="task-modal">
    <div class="modal" style="max-width:560px;">
        <div class="modal-header">
            <h3 id="task-modal-title">Add Task</h3>
            <button type="button" class="modal-close" onclick="closeModal('task-modal')">✕</button>
        </div>
        <form id="task-form" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="task-method" value="POST">
            <input type="hidden" name="mentee_id" id="task-mentee-id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-input" maxlength="200" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="2"></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            @foreach(\App\Models\CurriculumTask::TYPES as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Plan *</label>
                        <select name="plan_id" class="form-select" required>
                            <option value="">— Select plan —</option>
                            @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name ?? $plan->plan_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Submission type</label>
                    <select name="submission_type" class="form-select">
                        @foreach(\App\Models\CurriculumTask::SUBMISSION_TYPES as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Attachments</label>
                    <input type="file" name="attachments[]" class="form-input" multiple>
                    <div class="form-hint">Images, docs, or video · max 10MB each</div>
                </div>
                <div style="display:flex;gap:16px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                        <input type="hidden" name="is_required" value="0">
                        <input type="checkbox" name="is_required" value="1" checked>
                        Required
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" checked>
                        Active
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('task-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save task</button>
            </div>
        </form>
    </div>
</div>

{{-- MCQ Topic Modal --}}
<div class="modal-overlay" id="mcq-modal">
    <div class="modal" style="max-width:640px;">
        <div class="modal-header">
            <h3 id="mcq-modal-title">Add MCQ Topic</h3>
            <button type="button" class="modal-close" onclick="closeModal('mcq-modal')">✕</button>
        </div>
        <form id="mcq-form" method="POST">
            @csrf
            <input type="hidden" name="_method" id="mcq-method" value="POST">
            <input type="hidden" name="mentee_id" id="mcq-mentee-id">
            <div class="modal-body" style="max-height:70vh;overflow:auto;">
                <div class="form-group">
                    <label class="form-label">Topic name *</label>
                    <input type="text" name="name" class="form-input" maxlength="255" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="2"></textarea>
                </div>
                <div id="mcq-questions"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addMcqRow()" style="margin-bottom:12px;">+ Add question</button>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked>
                    Active
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('mcq-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save topic</button>
            </div>
        </form>
    </div>
</div>

{{-- Material Modal --}}
<div class="modal-overlay" id="material-modal">
    <div class="modal" style="max-width:520px;">
        <div class="modal-header">
            <h3 id="material-modal-title">Add Supporting Material</h3>
            <button type="button" class="modal-close" onclick="closeModal('material-modal')">✕</button>
        </div>
        <form id="material-form" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="material-method" value="POST">
            <input type="hidden" name="mentee_id" id="material-mentee-id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Type *</label>
                    <select name="type" class="form-select" id="material-type" required onchange="toggleMaterialFields()">
                        @foreach(\App\Models\TaskSupportingMaterial::TYPES as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-input" maxlength="200">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="2"></textarea>
                </div>
                <div class="form-group" id="material-link-group" style="display:none;">
                    <label class="form-label">Video link *</label>
                    <input type="url" name="link" class="form-input" placeholder="https://…">
                </div>
                <div class="form-group" id="material-file-group">
                    <label class="form-label">File</label>
                    <input type="file" name="file" class="form-input">
                </div>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked>
                    Active
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('material-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save material</button>
            </div>
        </form>
    </div>
</div>

<style>
.curr-tab {
    background: none; border: none; border-bottom: 2px solid transparent;
    padding: 10px 14px; font-size: 12px; font-weight: 600; color: var(--text-2);
    cursor: pointer; white-space: nowrap;
}
.curr-tab.active { color: var(--brand); border-bottom-color: var(--brand); }
.mcq-row { border: 1px solid var(--border); border-radius: 10px; padding: 12px; margin-bottom: 10px; background: var(--bg); }

.curr-task-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    transition: border-color .15s ease;
}
.curr-task-row:hover { border-color: color-mix(in srgb, var(--brand) 35%, var(--border)); }
.curr-task-icon {
    font-size: 18px;
    line-height: 1;
    flex-shrink: 0;
    margin-top: 2px;
}
.curr-task-body { flex: 1; min-width: 0; }
.curr-task-title-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
}
.curr-task-title {
    font-size: 13px;
    font-weight: 650;
    color: var(--text);
}
.curr-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 6px;
    background: color-mix(in srgb, var(--text) 8%, transparent);
    color: var(--text-2);
}
.curr-badge-required {
    background: color-mix(in srgb, var(--error) 12%, transparent);
    color: var(--error);
}
.curr-task-desc {
    margin: 4px 0 0;
    font-size: 12px;
    color: var(--text-2);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.curr-task-meta {
    margin-top: 4px;
    font-size: 11px;
    color: var(--text-3);
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}
.curr-task-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}
.curr-btn-edit,
.curr-btn-del {
    border: none;
    border-radius: 8px;
    padding: 5px 10px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
}
.curr-btn-edit {
    background: color-mix(in srgb, #3b82f6 14%, transparent);
    color: #60a5fa;
}
.curr-btn-edit:hover { background: color-mix(in srgb, #3b82f6 24%, transparent); }
.curr-btn-del {
    background: color-mix(in srgb, var(--error) 14%, transparent);
    color: var(--error);
}
.curr-btn-del:hover { background: color-mix(in srgb, var(--error) 24%, transparent); }
.curr-add-dashed {
    width: 100%;
    border: 2px dashed var(--border);
    background: transparent;
    color: var(--text-3);
    font-size: 13px;
    font-weight: 600;
    padding: 12px;
    border-radius: 12px;
    cursor: pointer;
    transition: border-color .15s ease, color .15s ease, background .15s ease;
}
.curr-add-dashed:hover {
    border-color: var(--brand);
    color: var(--brand);
    background: color-mix(in srgb, var(--brand) 8%, transparent);
}
</style>
@endsection

@push('scripts')
<script>
const STORE_TASK = @json(url('/mentor/curriculum/weeks'));
const STORE_MCQ = @json(url('/mentor/curriculum/weeks'));
const STORE_MAT = @json(url('/mentor/curriculum/weeks'));
const UPDATE_TASK = @json(url('/mentor/curriculum/tasks'));
const UPDATE_MAT = @json(url('/mentor/curriculum/supporting-materials'));

function openAddWeek() { openModal('add-week-modal'); }

function toggleWeek(id) {
    const body = document.getElementById('week-body-' + id);
    body.style.display = body.style.display === 'none' ? 'block' : 'none';
}

function switchTab(weekId, tab) {
    ['tasks', 'mcqs', 'materials', 'settings'].forEach(t => {
        const panel = document.getElementById('panel-' + t + '-' + weekId);
        if (panel) panel.style.display = t === tab ? 'block' : 'none';
    });
    document.querySelectorAll('.curr-tab[data-week="' + weekId + '"]').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tab);
    });
}

function openAddTask(weekId, menteeId) {
    document.getElementById('task-modal-title').textContent = 'Add Task';
    document.getElementById('task-form').action = STORE_TASK + '/' + weekId + '/tasks';
    document.getElementById('task-method').value = 'POST';
    document.getElementById('task-form').reset();
    document.getElementById('task-mentee-id').value = menteeId;
    document.querySelector('#task-form [name=is_required][type=checkbox]').checked = true;
    document.querySelector('#task-form [name=is_active][type=checkbox]').checked = true;
    openModal('task-modal');
}

function openEditTask(task) {
    document.getElementById('task-modal-title').textContent = 'Edit Task';
    document.getElementById('task-form').action = UPDATE_TASK + '/' + task.id;
    document.getElementById('task-method').value = 'POST';
    const form = document.getElementById('task-form');
    form.title.value = task.title || '';
    form.description.value = task.description || '';
    form.type.value = task.type || 'task';
    form.plan_id.value = task.plan_id || '';
    form.submission_type.value = task.submission_type || 'none';
    document.getElementById('task-mentee-id').value = task.mentee_id || '';
    form.querySelector('[name=is_required][type=checkbox]').checked = !!task.is_required;
    form.querySelector('[name=is_active][type=checkbox]').checked = !!task.is_active;
    openModal('task-modal');
}

let mcqIndex = 0;
function mcqRowHtml(i, data = {}) {
    const opts = data.options || ['', '', '', ''];
    const correct = data.correct_option || ((data.correct_index ?? 0) + 1);
    return `<div class="mcq-row" data-i="${i}">
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <strong style="font-size:12px;">Question ${i + 1}</strong>
            <button type="button" class="btn btn-ghost btn-sm" style="color:var(--error);" onclick="this.closest('.mcq-row').remove()">Remove</button>
        </div>
        <div class="form-group"><label class="form-label">Question *</label>
            <textarea name="mcqs[${i}][question]" class="form-textarea" rows="2" required>${data.question || ''}</textarea></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            ${[0,1,2,3].map(o => `<div class="form-group" style="margin:0;"><label class="form-label">Option ${o+1} *</label>
                <input type="text" name="mcqs[${i}][options][${o}]" class="form-input" required value="${(opts[o]||'').replace(/"/g,'&quot;')}"></div>`).join('')}
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-top:8px;">
            <div class="form-group" style="margin:0;"><label class="form-label">Correct *</label>
                <select name="mcqs[${i}][correct_option]" class="form-select" required>
                    ${[1,2,3,4].map(n => `<option value="${n}" ${correct==n?'selected':''}>Option ${n}</option>`).join('')}
                </select></div>
            <div class="form-group" style="margin:0;"><label class="form-label">Difficulty</label>
                <select name="mcqs[${i}][difficulty]" class="form-select">
                    ${['easy','medium','hard'].map(d => `<option value="${d}" ${(data.difficulty||'medium')===d?'selected':''}>${d}</option>`).join('')}
                </select></div>
            <div class="form-group" style="margin:0;"><label class="form-label">Points</label>
                <input type="number" name="mcqs[${i}][points]" class="form-input" min="1" max="100" value="${data.points || 1}"></div>
        </div>
        <div class="form-group" style="margin-top:8px;margin-bottom:0;"><label class="form-label">Explanation</label>
            <input type="text" name="mcqs[${i}][explanation]" class="form-input" value="${(data.explanation||'').replace(/"/g,'&quot;')}"></div>
    </div>`;
}

function addMcqRow(data) {
    const wrap = document.getElementById('mcq-questions');
    wrap.insertAdjacentHTML('beforeend', mcqRowHtml(mcqIndex++, data || {}));
}

function openAddMcqTopic(weekId, menteeId) {
    document.getElementById('mcq-modal-title').textContent = 'Add MCQ Topic';
    document.getElementById('mcq-form').action = STORE_MCQ + '/' + weekId + '/mcqs';
    document.getElementById('mcq-method').value = 'POST';
    document.getElementById('mcq-form').reset();
    document.getElementById('mcq-mentee-id').value = menteeId;
    document.getElementById('mcq-questions').innerHTML = '';
    mcqIndex = 0;
    addMcqRow();
    openModal('mcq-modal');
}

function openEditMcqTopic(weekId, topic) {
    document.getElementById('mcq-modal-title').textContent = 'Edit MCQ Topic';
    document.getElementById('mcq-form').action = STORE_MCQ + '/' + weekId + '/mcqs/' + topic.id;
    document.getElementById('mcq-method').value = 'PUT';
    document.getElementById('mcq-form').name.value = topic.name || '';
    document.getElementById('mcq-form').description.value = topic.description || '';
    document.getElementById('mcq-mentee-id').value = topic.mentee_id || '';
    document.querySelector('#mcq-form [name=is_active][type=checkbox]').checked = !!topic.is_active;
    document.getElementById('mcq-questions').innerHTML = '';
    mcqIndex = 0;
    (topic.mcqs || []).forEach(q => addMcqRow({
        question: q.question,
        options: q.options,
        correct_index: q.correct_index,
        difficulty: q.difficulty,
        points: q.points,
        explanation: q.explanation,
    }));
    if (!(topic.mcqs || []).length) addMcqRow();
    openModal('mcq-modal');
}

function toggleMaterialFields() {
    const type = document.getElementById('material-type').value;
    const isLink = type === 'videolink';
    document.getElementById('material-link-group').style.display = isLink ? 'block' : 'none';
    document.getElementById('material-file-group').style.display = isLink ? 'none' : 'block';
}

function openAddMaterial(weekId, menteeId) {
    document.getElementById('material-modal-title').textContent = 'Add Supporting Material';
    document.getElementById('material-form').action = STORE_MAT + '/' + weekId + '/supporting-materials';
    document.getElementById('material-method').value = 'POST';
    document.getElementById('material-form').reset();
    document.getElementById('material-mentee-id').value = menteeId;
    document.querySelector('#material-form [name=is_active][type=checkbox]').checked = true;
    toggleMaterialFields();
    openModal('material-modal');
}

function openEditMaterial(material) {
    document.getElementById('material-modal-title').textContent = 'Edit Supporting Material';
    document.getElementById('material-form').action = UPDATE_MAT + '/' + material.id;
    document.getElementById('material-method').value = 'POST';
    const form = document.getElementById('material-form');
    form.type.value = material.type || 'pdf';
    form.title.value = material.title || '';
    form.description.value = material.description || '';
    form.link.value = material.link || '';
    document.getElementById('material-mentee-id').value = material.mentee_id || '';
    form.querySelector('[name=is_active][type=checkbox]').checked = !!material.is_active;
    toggleMaterialFields();
    openModal('material-modal');
}

@if($month->weeks->isNotEmpty())
document.addEventListener('DOMContentLoaded', () => {
    const first = {{ $month->weeks->first()->id }};
    document.getElementById('week-body-' + first).style.display = 'block';
});
@endif
</script>
@endpush
