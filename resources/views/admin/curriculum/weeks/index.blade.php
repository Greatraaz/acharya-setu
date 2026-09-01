@extends('admin.layouts.app')
@section('title','Week Builder — Month ' . $month->month_number)
@section('heading','Week & Content Builder')
@section('content')

<div class="space-y-4 sm:space-y-6 min-w-0">

    {{-- Breadcrumb --}}
    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500 min-w-0">
        <a href="{{ route('admin.curriculum.streams') }}" class="hover:text-violet-600 whitespace-nowrap">Streams</a>
        <span>/</span>
        <a href="{{ route('admin.curriculum.months', $month->stream) }}" class="hover:text-violet-600 truncate max-w-[40vw] sm:max-w-none">{{ $month->stream->name }}</a>
        <span>/</span>
        <span class="font-semibold text-gray-800 break-words">Month {{ $month->month_number }}: {{ $month->title }}</span>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">✓ {{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-xl">{{ session('error') }}</div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(!($month->mentee_id || $month->stream?->mentee_id))
    <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-xl">
        This stream/month has no mentee assigned. Content can still be added, but assign a mentee on the stream for mentee-specific curriculum.
    </div>
    @endif

    {{-- Month overview --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:gap-4 min-w-0">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-bold text-lg flex-shrink-0"
                 style="background: {{ $month->stream->color ?: '#7c3aed' }};">{{ $month->month_number }}</div>
            <div class="min-w-0 flex-1">
                <h2 class="font-bold text-gray-900 break-words">{{ $month->title }}</h2>
                @if($month->theme)<p class="text-sm text-gray-500 break-words">Theme: {{ $month->theme }}</p>@endif
            </div>
        </div>
        <div class="grid grid-cols-3 gap-2 sm:flex sm:gap-3 text-center text-xs w-full lg:w-auto lg:flex-shrink-0">
            <div class="bg-gray-50 rounded-xl px-3 sm:px-4 py-2"><div class="text-lg sm:text-xl font-bold text-gray-800">{{ $month->weeks->count() }}</div><div class="text-gray-400">Weeks</div></div>
            <div class="bg-gray-50 rounded-xl px-3 sm:px-4 py-2"><div class="text-lg sm:text-xl font-bold text-gray-800">{{ $month->tasks->count() }}</div><div class="text-gray-400">Tasks</div></div>
            <div class="bg-gray-50 rounded-xl px-3 sm:px-4 py-2"><div class="text-lg sm:text-xl font-bold text-gray-800">{{ $month->mcqs->count() }}</div><div class="text-gray-400">MCQs</div></div>
        </div>
        <button type="button" onclick="document.getElementById('add-week-modal').classList.remove('hidden')"
                class="inline-flex items-center justify-center gap-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors whitespace-nowrap w-full lg:w-auto lg:flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Week
        </button>
    </div>

    {{-- Weeks accordion --}}
    @forelse($month->weeks as $week)
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden" id="week-{{ $week->id }}">

        {{-- Week header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center p-4 sm:p-5 cursor-pointer hover:bg-gray-50 transition-colors min-w-0"
             onclick="toggleWeek({{ $week->id }})">
            <div class="flex items-start gap-3 min-w-0 flex-1">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                     style="background: {{ $month->stream->color ?: '#7c3aed' }};">W{{ $week->week_number }}</div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="font-bold text-gray-900 text-sm break-words">{{ $week->title }}</h3>
                        @if(!$week->is_active)<span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full whitespace-nowrap">Inactive</span>@endif
                    </div>
                    @if($week->focus)<p class="text-xs text-gray-500 mt-0.5 break-words">{{ $week->focus }}</p>@endif
                </div>
            </div>
            <div class="flex items-center justify-between sm:justify-end gap-3 flex-shrink-0 pl-12 sm:pl-0">
                <div class="flex gap-3 text-xs text-gray-500 whitespace-nowrap">
                    <span>{{ $week->tasks->count() }} tasks</span>
                    <span>{{ $week->mcqs->count() }} MCQs</span>
                </div>
                <svg id="chevron-{{ $week->id }}" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>

        {{-- Week body (collapsible) --}}
        <div id="week-body-{{ $week->id }}" class="border-t border-gray-100">

            {{-- Tabs --}}
            <div class="flex border-b border-gray-100 px-3 sm:px-5 bg-gray-50 overflow-x-auto">
                <button type="button" onclick="switchTab({{ $week->id }}, 'tasks')" id="tab-tasks-{{ $week->id }}"
                        class="week-tab active-tab px-3 sm:px-4 py-2.5 text-xs font-semibold text-violet-700 border-b-2 border-violet-600 -mb-px transition-colors whitespace-nowrap flex-shrink-0">
                    📋 Tasks ({{ $week->tasks->count() }})
                </button>
                <button type="button" onclick="switchTab({{ $week->id }}, 'mcqs')" id="tab-mcqs-{{ $week->id }}"
                        class="week-tab px-3 sm:px-4 py-2.5 text-xs font-semibold text-gray-500 border-b-2 border-transparent -mb-px hover:text-gray-700 transition-colors whitespace-nowrap flex-shrink-0">
                    🧠 MCQs ({{ $week->mcqs->count() }})
                </button>
                <button type="button" onclick="switchTab({{ $week->id }}, 'settings')" id="tab-settings-{{ $week->id }}"
                        class="week-tab px-3 sm:px-4 py-2.5 text-xs font-semibold text-gray-500 border-b-2 border-transparent -mb-px hover:text-gray-700 transition-colors whitespace-nowrap flex-shrink-0">
                    ⚙️ Settings
                </button>
            </div>

            {{-- Tasks panel --}}
            <div id="panel-tasks-{{ $week->id }}" class="p-4 sm:p-5">
                <div class="space-y-2 mb-4">
                    @forelse($week->tasks as $task)
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start p-3.5 bg-gray-50 rounded-xl border border-gray-100 hover:border-gray-200 transition-colors min-w-0">
                        <div class="flex items-start gap-3 min-w-0 flex-1">
                            <span class="text-lg leading-none flex-shrink-0 mt-0.5">{{ \App\Models\CurriculumTask::TYPE_ICONS[$task->type] ?? '•' }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-800 break-words min-w-0">{{ $task->title }}</span>
                                    <span class="text-xs bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded font-medium whitespace-nowrap">{{ ucfirst($task->type) }}</span>
                                    @if($task->is_required)<span class="text-xs bg-red-50 text-red-600 px-1.5 py-0.5 rounded font-medium whitespace-nowrap">Required</span>@endif
                                    @if(!$task->is_active)<span class="text-xs bg-gray-100 text-gray-400 px-1.5 py-0.5 rounded whitespace-nowrap">Inactive</span>@endif
                                </div>
                                @if($task->description)<p class="text-xs text-gray-500 mt-0.5 line-clamp-2 break-words">{{ $task->description }}</p>@endif
                                @if($task->estimated_minutes)<p class="text-xs text-gray-400 mt-0.5">⏱ {{ $task->estimated_minutes }} min</p>@endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0 self-end sm:self-start">
                            <button type="button" onclick='openEditTask(@json($task))' class="text-xs font-medium text-blue-600 bg-blue-50 px-2.5 py-1.5 rounded-lg hover:bg-blue-100 transition-colors whitespace-nowrap">Edit</button>
                            <form method="POST" action="{{ route('admin.curriculum.tasks.destroy', $task) }}" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-red-500 bg-red-50 px-2.5 py-1.5 rounded-lg hover:bg-red-100 transition-colors whitespace-nowrap">Del</button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 text-center py-6">No tasks yet. Add the first task for this week.</p>
                    @endforelse
                </div>
                <button onclick="openAddTask({{ $week->id }})"
                        class="w-full border-2 border-dashed border-gray-200 hover:border-violet-300 hover:bg-violet-50 text-sm font-medium text-gray-400 hover:text-violet-600 py-3 rounded-xl transition-all">
                    + Add Task
                </button>
            </div>

            {{-- MCQs panel --}}
            <div id="panel-mcqs-{{ $week->id }}" class="p-4 sm:p-5 hidden">
                <div class="space-y-3 mb-4">
                    @forelse($week->mcqs as $mcq)
                    <div class="p-4 bg-gray-50 border border-gray-100 rounded-xl min-w-0">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full text-white flex-shrink-0 mt-0.5 whitespace-nowrap"
                                      style="background: {{ \App\Models\CurriculumMcq::DIFFICULTY_COLORS[$mcq->difficulty] }};">
                                    {{ ucfirst($mcq->difficulty) }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-800 leading-snug break-words">{{ Str::limit($mcq->question, 100) }}</p>
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach($mcq->options as $i => $opt)
                                        <span class="text-xs px-2.5 py-1 rounded-lg border break-words {{ $i == $mcq->correct_index ? 'bg-green-50 border-green-300 text-green-700 font-semibold' : 'bg-white border-gray-200 text-gray-600' }}">
                                            {{ chr(65+$i) }}. {{ Str::limit($opt, 30) }}
                                            @if($i == $mcq->correct_index) ✓@endif
                                        </span>
                                        @endforeach
                                    </div>
                                    @if($mcq->explanation)<p class="text-xs text-gray-400 mt-1.5 italic break-words">💡 {{ Str::limit($mcq->explanation, 80) }}</p>@endif
                                </div>
                            </div>
                            <div class="flex gap-2 flex-shrink-0 self-end sm:self-start">
                                <button type="button" onclick="openEditMcq({{ $mcq->toJson() }})" class="text-xs font-medium text-blue-600 bg-blue-50 px-2.5 py-1.5 rounded-lg hover:bg-blue-100 transition-colors whitespace-nowrap">Edit</button>
                                <form method="POST" action="{{ route('admin.curriculum.mcqs.destroy', $mcq) }}" onsubmit="return confirm('Delete MCQ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-red-500 bg-red-50 px-2.5 py-1.5 rounded-lg hover:bg-red-100 transition-colors whitespace-nowrap">Del</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 text-center py-6">No MCQs yet. Add quiz questions for this week.</p>
                    @endforelse
                </div>
                <button onclick="openAddMcq({{ $week->id }})"
                        class="w-full border-2 border-dashed border-gray-200 hover:border-violet-300 hover:bg-violet-50 text-sm font-medium text-gray-400 hover:text-violet-600 py-3 rounded-xl transition-all">
                    + Add MCQ
                </button>
            </div>

            {{-- Settings panel --}}
            <div id="panel-settings-{{ $week->id }}" class="p-4 sm:p-5 hidden">
                <form method="POST" action="{{ route('admin.curriculum.weeks.update', $week) }}" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Week Number</label>
                            <input type="number" name="week_number" value="{{ $week->week_number }}" min="1" max="5" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Title</label>
                            <input type="text" name="title" value="{{ $week->title }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Focus Statement</label>
                        <input type="text" name="focus" value="{{ $week->focus }}" placeholder="What is the core focus of this week?" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Mentor Guide (private)</label>
                        <textarea name="mentor_guide" rows="3" placeholder="Guidance for the mentor on running this week…" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 resize-none">{{ $week->mentor_guide }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Intro Video URL</label>
                        <input type="url" name="video_url" value="{{ $week->video_url }}" placeholder="https://youtube.com/…" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ $week->is_active ? 'checked' : '' }} class="rounded">
                            <span class="text-sm text-gray-700 font-medium">Active</span>
                        </label>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
                        <button type="submit" class="w-full sm:w-auto bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors whitespace-nowrap">Update Week</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.curriculum.weeks.destroy', $week) }}" onsubmit="return confirm('Delete this week and all its content?')" class="mt-3">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full sm:w-auto text-sm font-medium text-red-600 border border-red-200 px-4 py-2.5 rounded-lg hover:bg-red-50 transition-colors whitespace-nowrap">Delete Week</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-12 text-center">
        <p class="text-gray-500 font-medium mb-2">No weeks yet for Month {{ $month->month_number }}</p>
        <p class="text-gray-400 text-sm">Add weeks to structure this month's content.</p>
    </div>
    @endforelse
</div>

{{-- Add Week Modal --}}
<div id="add-week-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto p-4 sm:p-6">
        <h3 class="text-base font-bold text-gray-900 mb-5">Add Week to Month {{ $month->month_number }}</h3>
        <form method="POST" action="{{ route('admin.curriculum.weeks.store', $month) }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Week Number <span class="text-red-500">*</span></label>
                    @php $usedWeeks = $month->weeks->pluck('week_number')->map(fn ($n) => (int) $n)->all(); @endphp
                    <select name="week_number" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 bg-white">
                        <option value="">— Select —</option>
                        @for($i = 1; $i <= 5; $i++)
                            @unless(in_array($i, $usedWeeks, true))
                            <option value="{{ $i }}" @selected((string) old('week_number') === (string) $i)>Week {{ $i }}</option>
                            @endunless
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required value="{{ old('title') }}" placeholder="Week title…" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Focus Statement</label>
                <input type="text" name="focus" value="{{ old('focus') }}" placeholder="Core focus of this week…" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" checked class="rounded">
                <span class="text-sm text-gray-700 font-medium">Active</span>
            </label>
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit" class="w-full sm:flex-1 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors whitespace-nowrap">Add Week</button>
                <button type="button" onclick="document.getElementById('add-week-modal').classList.add('hidden')" class="w-full sm:w-auto px-4 py-2.5 border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Add/Edit Task Modal --}}
<div id="task-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <h3 class="text-base font-bold text-gray-900 mb-5" id="task-modal-title">Add Task</h3>
        <form id="task-form" method="POST" class="space-y-4">
            @csrf
            <span id="task-method-field"></span>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="task-title" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="description" id="task-description" rows="3" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 resize-none"></textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Type</label>
                    <select name="type" id="task-type" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 bg-white">
                        @foreach(\App\Models\CurriculumTask::TYPES as $val => $label)
                        <option value="{{ $val }}">{{ \App\Models\CurriculumTask::TYPE_ICONS[$val] }} {{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Plan</label>
                    <select name="plan_id" id="task-plan" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 bg-white">
                        <option value="">— Optional —</option>
                        @foreach(($plans ?? collect()) as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->plan_name ?? $plan->name ?? ('Plan #'.$plan->id) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Submission Type</label>
                    <select name="submission_type" id="task-submission-type" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 bg-white">
                        @foreach(\App\Models\CurriculumTask::SUBMISSION_TYPES as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Est. Minutes</label>
                    <input type="number" name="estimated_minutes" id="task-minutes" min="0" value="30" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Order Index</label>
                    <input type="number" name="order_index" id="task-order" value="0" min="0" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                </div>
            </div>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_required" value="0">
                    <input type="checkbox" id="task-required" name="is_required" value="1" checked class="rounded">
                    <span class="text-sm font-medium text-gray-700">Required</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="task-active" name="is_active" value="1" checked class="rounded">
                    <span class="text-sm font-medium text-gray-700">Active</span>
                </label>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit" class="w-full sm:flex-1 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors whitespace-nowrap">Save Task</button>
                <button type="button" onclick="document.getElementById('task-modal').classList.add('hidden')" class="w-full sm:w-auto px-4 py-2.5 border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Add/Edit MCQ Modal --}}
<div id="mcq-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <h3 class="text-base font-bold text-gray-900 mb-5" id="mcq-modal-title">Add MCQ</h3>
        <form id="mcq-form" method="POST" class="space-y-4">
            @csrf
            <span id="mcq-method-field"></span>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Question <span class="text-red-500">*</span></label>
                <textarea name="question" id="mcq-question" required rows="3" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 resize-none"></textarea>
            </div>
            <div id="mcq-options-container" class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Options</label>
                @for($i = 0; $i < 4; $i++)
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-gray-100 text-xs font-bold text-gray-600 flex items-center justify-center flex-shrink-0">{{ chr(65+$i) }}</span>
                    <input type="text" name="options[]" class="flex-1 border border-gray-200 rounded-xl px-3.5 py-2 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100" placeholder="Option {{ chr(65+$i) }}">
                    <input type="radio" name="correct_index" value="{{ $i }}" class="w-4 h-4 text-violet-600" {{ $i===0?'checked':'' }}>
                    <span class="text-xs text-gray-400">Correct</span>
                </div>
                @endfor
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Explanation (shown after answer)</label>
                <textarea name="explanation" id="mcq-explanation" rows="2" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 resize-none"></textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Difficulty</label>
                    <select name="difficulty" id="mcq-difficulty" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 bg-white">
                        <option value="easy">🟢 Easy</option>
                        <option value="medium" selected>🟡 Medium</option>
                        <option value="hard">🔴 Hard</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Points</label>
                    <input type="number" name="points" id="mcq-points" value="1" min="1" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Order</label>
                    <input type="number" name="order_index" id="mcq-order" value="0" min="0" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit" class="w-full sm:flex-1 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors whitespace-nowrap">Save MCQ</button>
                <button type="button" onclick="document.getElementById('mcq-modal').classList.add('hidden')" class="w-full sm:w-auto px-4 py-2.5 border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Week accordion
const openWeeks = new Set();
function toggleWeek(id) {
    const body = document.getElementById('week-body-' + id);
    const chevron = document.getElementById('chevron-' + id);
    if (openWeeks.has(id)) {
        body.style.display = 'none';
        chevron.style.transform = '';
        openWeeks.delete(id);
    } else {
        body.style.display = 'block';
        chevron.style.transform = 'rotate(180deg)';
        openWeeks.add(id);
    }
}
// Open first week by default
document.addEventListener('DOMContentLoaded', () => {
    const first = document.querySelector('[id^="week-body-"]');
    if (first) {
        const id = first.id.replace('week-body-','');
        first.style.display = 'block';
        const chevron = document.getElementById('chevron-'+id);
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        openWeeks.add(parseInt(id));
    }

    @if($errors->any())
    @if($errors->has('week_number') || $errors->has('title') || $errors->has('focus'))
    document.getElementById('add-week-modal')?.classList.remove('hidden');
    @elseif($errors->has('type') || $errors->has('plan_id') || $errors->has('submission_type'))
    document.getElementById('task-modal')?.classList.remove('hidden');
    @endif
    @endif
});

// Tab switching
function switchTab(weekId, tab) {
    ['tasks','mcqs','settings'].forEach(t => {
        document.getElementById('panel-'+t+'-'+weekId).classList.add('hidden');
        document.getElementById('tab-'+t+'-'+weekId).classList.remove('text-violet-700','border-violet-600');
        document.getElementById('tab-'+t+'-'+weekId).classList.add('text-gray-500','border-transparent');
    });
    document.getElementById('panel-'+tab+'-'+weekId).classList.remove('hidden');
    document.getElementById('tab-'+tab+'-'+weekId).classList.remove('text-gray-500','border-transparent');
    document.getElementById('tab-'+tab+'-'+weekId).classList.add('text-violet-700','border-violet-600');
}

// Task modal
const TASK_STORE_URLS = {
    @foreach($month->weeks as $w)
    {{ $w->id }}: "{{ route('admin.curriculum.tasks.store', $w) }}",
    @endforeach
};

function openAddTask(weekId) {
    document.getElementById('task-modal-title').textContent = 'Add Task';
    document.getElementById('task-form').action = TASK_STORE_URLS[weekId] || (`{{ url('/admin/curriculum/weeks') }}/` + weekId + '/tasks');
    document.getElementById('task-method-field').innerHTML = '';
    document.getElementById('task-title').value = '';
    document.getElementById('task-description').value = '';
    document.getElementById('task-type').value = 'task';
    const plan = document.getElementById('task-plan');
    if (plan) plan.value = '';
    document.getElementById('task-submission-type').value = 'none';
    document.getElementById('task-minutes').value = 30;
    document.getElementById('task-order').value = 0;
    document.getElementById('task-required').checked = true;
    document.getElementById('task-active').checked = true;
    document.getElementById('task-modal').classList.remove('hidden');
}

function openEditTask(task) {
    document.getElementById('task-modal-title').textContent = 'Edit Task';
    document.getElementById('task-form').action = `/admin/curriculum/tasks/${task.id}`;
    document.getElementById('task-method-field').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('task-title').value = task.title || '';
    document.getElementById('task-description').value = task.description || '';
    document.getElementById('task-type').value = task.type || 'task';
    const plan = document.getElementById('task-plan');
    if (plan) plan.value = task.plan_id || '';
    document.getElementById('task-submission-type').value = task.submission_type || 'none';
    document.getElementById('task-minutes').value = task.estimated_minutes || 0;
    document.getElementById('task-order').value = task.order_index || 0;
    document.getElementById('task-required').checked = !!task.is_required;
    document.getElementById('task-active').checked = !!task.is_active;
    document.getElementById('task-modal').classList.remove('hidden');
}

// MCQ modal
const MCQ_STORE_URLS = {
    @foreach($month->weeks as $w)
    {{ $w->id }}: "{{ route('admin.curriculum.mcqs.store', $w) }}",
    @endforeach
};

function openAddMcq(weekId) {
    document.getElementById('mcq-modal-title').textContent = 'Add MCQ';
    document.getElementById('mcq-form').action = MCQ_STORE_URLS[weekId];
    document.getElementById('mcq-method-field').innerHTML = '';
    document.getElementById('mcq-question').value = '';
    document.getElementById('mcq-explanation').value = '';
    document.querySelectorAll('[name="options[]"]').forEach(i => i.value = '');
    document.querySelector('[name="correct_index"][value="0"]').checked = true;
    document.getElementById('mcq-modal').classList.remove('hidden');
}

function openEditMcq(mcq) {
    document.getElementById('mcq-modal-title').textContent = 'Edit MCQ';
    document.getElementById('mcq-form').action = `/admin/curriculum/mcqs/${mcq.id}`;
    document.getElementById('mcq-method-field').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('mcq-question').value = mcq.question || '';
    document.getElementById('mcq-explanation').value = mcq.explanation || '';
    document.getElementById('mcq-difficulty').value = mcq.difficulty || 'medium';
    document.getElementById('mcq-points').value = mcq.points || 1;
    document.getElementById('mcq-order').value = mcq.order_index || 0;
    const opts = document.querySelectorAll('[name="options[]"]');
    (mcq.options || []).forEach((o, i) => { if(opts[i]) opts[i].value = o; });
    const correct = document.querySelector(`[name="correct_index"][value="${mcq.correct_index}"]`);
    if (correct) correct.checked = true;
    document.getElementById('mcq-modal').classList.remove('hidden');
}
</script>

@endsection