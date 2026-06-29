@extends('admin.layouts.app')
@section('title','Months — ' . $stream->name)
@section('heading','6-Month Journey')
@section('content')

<div class="space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.curriculum.streams') }}" class="hover:text-violet-600 transition-colors">Streams</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="font-semibold text-gray-800">{{ $stream->name }}</span>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">✓ {{ session('success') }}</div>
    @endif

    {{-- Stream header --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-6 flex items-center gap-5">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl flex-shrink-0"
             style="background: {{ $stream->color ?: '#7c3aed' }}18;">{{ $stream->icon ?: '📚' }}</div>
        <div class="flex-1">
            <h2 class="text-lg font-bold text-gray-900">{{ $stream->name }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ $stream->description }}</p>
            <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                <span>{{ $months->count() }}/6 months configured</span>
                <span>{{ $months->sum(fn($m) => $m->weeks->count()) }} weeks total</span>
            </div>
        </div>
        <button onclick="document.getElementById('add-month-modal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Month
        </button>
    </div>

    {{-- Journey Timeline --}}
    <div class="relative">
        {{-- Vertical line --}}
        <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gradient-to-b from-violet-300 via-violet-200 to-transparent"></div>

        <div class="space-y-5 pl-16">
            @php
            $configured = $months->keyBy('month_number');
            @endphp
            @for($m = 1; $m <= 6; $m++)
            @php $month = $configured->get($m); @endphp
            <div class="relative">
                {{-- Timeline dot --}}
                <div class="absolute -left-10 top-5 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shadow-md z-10
                    {{ $month ? 'text-white' : 'bg-white border-2 border-dashed border-gray-300 text-gray-400' }}"
                     style="{{ $month ? 'background:'.($stream->color ?: '#7c3aed') : '' }}">
                    {{ $m }}
                </div>

                @if($month)
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden hover:shadow-md transition-shadow">
                    <div class="flex items-start gap-4 p-5">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-gray-900">Month {{ $m }}: {{ $month->title }}</h3>
                                @if($month->theme)
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full text-violet-700" style="background:{{ $stream->color ?: '#7c3aed' }}18;">{{ $month->theme }}</span>
                                @endif
                                @if($month->milestone_badge)
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">🏅 {{ $month->milestone_badge }}</span>
                                @endif
                                @if(!$month->is_active)
                                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Inactive</span>
                                @endif
                            </div>
                            @if($month->description)
                            <p class="text-sm text-gray-500 leading-relaxed mb-3">{{ $month->description }}</p>
                            @endif

                            @if($month->learning_outcomes && count($month->learning_outcomes))
                            <div class="flex flex-wrap gap-1.5 mb-3">
                                @foreach(array_slice($month->learning_outcomes, 0, 4) as $outcome)
                                <span class="text-xs text-gray-600 bg-gray-50 border border-gray-200 px-2.5 py-0.5 rounded-full">✓ {{ $outcome }}</span>
                                @endforeach
                                @if(count($month->learning_outcomes) > 4)
                                <span class="text-xs text-gray-400 px-2.5 py-0.5">+{{ count($month->learning_outcomes) - 4 }} more</span>
                                @endif
                            </div>
                            @endif

                            {{-- Week pills --}}
                            <div class="flex flex-wrap gap-2">
                                @foreach($month->weeks as $week)
                                <a href="{{ route('admin.curriculum.weeks', $month) }}"
                                   class="inline-flex items-center gap-1.5 text-xs font-medium bg-gray-50 border border-gray-200 text-gray-700 px-2.5 py-1 rounded-lg hover:border-violet-300 hover:bg-violet-50 hover:text-violet-700 transition-colors">
                                    <span class="w-4 h-4 rounded-full text-center leading-4 text-white text-xs flex-shrink-0" style="background:{{ $stream->color ?: '#7c3aed' }}; font-size:9px;">{{ $week->week_number }}</span>
                                    {{ Str::limit($week->title, 20) }}
                                    <span class="text-gray-400">{{ $week->tasks->count() + $week->mcqs->count() }} items</span>
                                </a>
                                @endforeach
                                @if($month->weeks->count() < 4)
                                <span class="text-xs text-dashed text-gray-300 italic">
                                    {{ 4 - $month->weeks->count() }} week(s) not yet added
                                </span>
                                @endif
                            </div>
                        </div>

                        {{-- Stats column --}}
                        <div class="flex-shrink-0 text-right space-y-1 text-xs text-gray-500">
                            <div><span class="font-semibold text-gray-800">{{ $month->weeks->count() }}</span> weeks</div>
                            <div><span class="font-semibold text-gray-800">{{ $month->tasks->count() }}</span> tasks</div>
                            <div><span class="font-semibold text-gray-800">{{ $month->mcqs->count() }}</span> MCQs</div>
                        </div>
                    </div>

                    {{-- Actions bar --}}
                    <div class="flex items-center gap-2 px-5 py-3 border-t border-gray-100 bg-gray-50">
                        <a href="{{ route('admin.curriculum.weeks', $month) }}"
                           class="text-xs font-semibold text-violet-700 bg-violet-50 hover:bg-violet-100 px-3 py-1.5 rounded-lg transition-colors">
                            Manage Weeks →
                        </a>
                        <button onclick="openEditMonth({{ $month->toJson() }})"
                                class="text-xs font-medium text-gray-600 border border-gray-200 bg-white px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                            Edit Month
                        </button>
                        <form method="POST" action="{{ route('admin.curriculum.months.destroy', $month) }}" onsubmit="return confirm('Delete Month {{ $m }}?')" class="ml-auto">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700 transition-colors">Delete</button>
                        </form>
                    </div>
                </div>
                @else
                {{-- Empty slot --}}
                <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-5 flex items-center justify-between group hover:border-violet-300 hover:bg-violet-50/30 transition-all cursor-pointer"
                     onclick="openAddMonth({{ $m }})">
                    <div>
                        <p class="text-sm font-semibold text-gray-400 group-hover:text-violet-600">Month {{ $m }} — Not configured</p>
                        <p class="text-xs text-gray-300 mt-0.5">Click to add content for this month</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300 group-hover:text-violet-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                </div>
                @endif
            </div>
            @endfor
        </div>
    </div>
</div>

{{-- Add Month Modal --}}
<div id="add-month-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-bold text-gray-900" id="modal-month-title">Add Month</h3>
            <button onclick="document.getElementById('add-month-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.curriculum.months.store', $stream) }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Month Number <span class="text-red-500">*</span></label>
                    <select name="month_number" id="modal-month-number" required
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 bg-white">
                        @for($i=1;$i<=6;$i++)
                        <option value="{{ $i }}" {{ $configured->has($i) ? 'disabled' : '' }}>Month {{ $i }}{{ $configured->has($i) ? ' (exists)' : '' }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Theme</label>
                    <input type="text" name="theme" placeholder="Foundation, Deep Dive…"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" required placeholder="e.g. Foundations of Product Thinking"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="description" rows="2" placeholder="What will the mentee accomplish this month?"
                          class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 resize-none"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Learning Outcomes</label>
                <textarea name="learning_outcomes" rows="4" placeholder="One outcome per line:&#10;Understand REST APIs&#10;Build a CRUD app&#10;Deploy to production"
                          class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 resize-none font-mono"></textarea>
                <p class="text-xs text-gray-400 mt-1">One per line — shown as bullet points to mentees.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Milestone Badge Name</label>
                <input type="text" name="milestone_badge" placeholder="e.g. Foundation Builder, Code Craftsman"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                <p class="text-xs text-gray-400 mt-1">Earned on 100% completion of this month.</p>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">Save Month</button>
                <button type="button" onclick="document.getElementById('add-month-modal').classList.add('hidden')"
                        class="px-4 py-2.5 border border-gray-200 text-gray-600 text-sm rounded-xl hover:bg-gray-50 transition-colors">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddMonth(num) {
    document.getElementById('modal-month-number').value = num;
    document.getElementById('modal-month-title').textContent = 'Add Month ' + num;
    document.getElementById('add-month-modal').classList.remove('hidden');
}
</script>

@endsection