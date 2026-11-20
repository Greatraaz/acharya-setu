@extends('admin.layouts.app')
@section('title', 'Quizzes & MCQs')
@section('heading', 'Quizzes & MCQs')

@section('content')
@include('admin.partials.learning-ui')

<div class="learn-page space-y-5">
    <div class="learn-intro">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="learn-eyebrow">Learning · Quizzes</span>
                <h2>Quizzes &amp; MCQs</h2>
                <p>Create practice quizzes for mentees, publish when ready, and track attempts from one place.</p>
            </div>
            <a href="{{ route('admin.quizzes.create') }}" class="learn-btn-primary self-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Create Quiz
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
        @foreach([
            ['Total', $stats['total'], '🎯'],
            ['Published', $stats['published'], '✅'],
            ['Drafts', $stats['draft'], '📝'],
            ['Attempts', $stats['attempts'], '📈'],
        ] as [$label, $count, $icon])
        <div class="learn-stat flex items-start justify-between gap-3">
            <div>
                <div class="learn-stat__value">{{ number_format($count) }}</div>
                <div class="learn-stat__label">{{ $label }}</div>
            </div>
            <span class="learn-stat__icon text-base">{{ $icon }}</span>
        </div>
        @endforeach
    </div>

    <div class="learn-panel p-4">
        <form method="GET" class="admin-filter-form">
            <div class="admin-filter-search">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search title or description…"
                           class="learn-focus w-full border border-gray-200 rounded-lg pl-9 pr-3 text-sm outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="learn-focus w-full border border-gray-200 rounded-lg px-3 text-sm bg-white outline-none">
                    <option value="">All quizzes</option>
                    <option value="published" @selected($status === 'published')>Published</option>
                    <option value="draft" @selected($status === 'draft')>Drafts</option>
                </select>
            </div>
            <div class="admin-filter-actions">
                <button type="submit" class="learn-btn-primary !py-0 !px-4 h-10">Filter</button>
                @if($search !== '' || $status !== '')
                <a href="{{ route('admin.quizzes.index') }}" class="learn-btn-ghost h-10">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($quizzes as $quiz)
        <article class="learn-card p-5 flex flex-col">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-xl">🎯</div>
                <div class="flex flex-wrap gap-1.5 justify-end">
                    @if($quiz->is_published)
                    <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">Published</span>
                    @else
                    <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">Draft</span>
                    @endif
                    @if($quiz->time_limit)
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-50 text-gray-600 border border-gray-100">⏱ {{ $quiz->time_limit }}m</span>
                    @endif
                </div>
            </div>
            <h3 class="font-bold text-slate-900 leading-snug mb-1">{{ $quiz->title }}</h3>
            <p class="text-sm text-slate-500 line-clamp-2 mb-3 flex-1">{{ $quiz->description ?: 'No description.' }}</p>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-400 mb-4">
                <span>{{ $quiz->questions_count }} questions</span>
                <span>Pass {{ $quiz->pass_score }}%</span>
                <span>{{ $quiz->attempts_count }} attempts</span>
                <span>by {{ $quiz->creator->name ?? 'Admin' }}</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.quizzes.show', $quiz) }}" class="learn-btn-primary flex-1 !py-2 text-center text-sm">Manage</a>
                <form method="POST" action="{{ route('admin.quizzes.toggle-publish', $quiz) }}">
                    @csrf
                    <button type="submit" class="learn-btn-ghost !py-2 text-sm" title="{{ $quiz->is_published ? 'Unpublish' : 'Publish' }}">
                        {{ $quiz->is_published ? 'Unpublish' : 'Publish' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.quizzes.destroy', $quiz) }}" onsubmit="return confirm('Delete this quiz?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="learn-btn-ghost !py-2 !px-3 text-red-500 hover:border-red-200 hover:bg-red-50" title="Delete">🗑</button>
                </form>
            </div>
        </article>
        @empty
        <div class="col-span-full learn-panel py-16 text-center text-slate-400">
            <div class="text-4xl mb-3">🎯</div>
            <p class="font-semibold text-slate-600">No quizzes found</p>
            <p class="text-sm mt-1 mb-4">Create the first quiz or adjust your filters.</p>
            <a href="{{ route('admin.quizzes.create') }}" class="learn-btn-primary">Create Quiz</a>
        </div>
        @endforelse
    </div>

    @include('admin.partials.pagination', ['paginator' => $quizzes])
</div>
@endsection
