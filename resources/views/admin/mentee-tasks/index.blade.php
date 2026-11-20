@extends('admin.layouts.app')
@section('title', 'Mentee Tasks')
@section('heading', 'Mentee Tasks')

@php
    $statusStyles = [
        'pending'         => 'bg-amber-50 text-amber-700 border-amber-100',
        'in_progress'     => 'bg-blue-50 text-blue-700 border-blue-100',
        'awaiting_review' => 'bg-violet-50 text-violet-700 border-violet-100',
        'completed'       => 'bg-green-50 text-green-700 border-green-100',
        'rejected'        => 'bg-red-50 text-red-700 border-red-100',
    ];
@endphp

@section('content')
@include('admin.partials.learning-ui')

<div class="learn-page space-y-5">
    <div class="learn-intro">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="learn-eyebrow">Learning · Tasks</span>
                <h2>Mentee Tasks</h2>
                <p>Standalone assignments outside curriculum. Assign a linked mentor to review submissions.</p>
            </div>
            <a href="{{ route('admin.mentee-tasks.create') }}" class="learn-btn-primary self-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New task
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
            ['Total', $stats['total'], '✅'],
            ['Pending', $stats['pending'], '⏳'],
            ['Awaiting review', $stats['awaiting_review'], '👀'],
            ['Completed', $stats['completed'], '🏁'],
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
                    <input type="text" name="search" value="{{ $search }}" placeholder="Title, mentee, mentor…"
                           class="learn-focus w-full border border-gray-200 rounded-lg pl-9 pr-3 text-sm outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="learn-focus w-full border border-gray-200 rounded-lg px-3 text-sm bg-white outline-none">
                    <option value="">All statuses</option>
                    @foreach(['pending' => 'Pending', 'in_progress' => 'In progress', 'awaiting_review' => 'Awaiting review', 'completed' => 'Completed', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-filter-actions">
                <button type="submit" class="learn-btn-primary !py-0 !px-4 h-10">Filter</button>
                @if($search !== '' || $status !== '')
                <a href="{{ route('admin.mentee-tasks.index') }}" class="learn-btn-ghost h-10">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="learn-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Task</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Mentee</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Mentor</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Submission</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $task)
                    @php
                        $uiStatus = $task->uiStatus();
                        $statusLabel = str_replace('_', ' ', ucfirst($uiStatus));
                    @endphp
                    <tr class="hover:bg-amber-50/40 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 border border-amber-100 flex items-center justify-center text-base flex-shrink-0">
                                    {{ \App\Models\MenteeTask::TYPE_ICONS[$task->type] ?? '✅' }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-900 truncate">{{ $task->title }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        {{ \App\Models\MenteeTask::TYPES[$task->type] ?? ucfirst((string) $task->type) }}
                                        @if($task->is_required) · Required @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-medium text-slate-900">{{ $task->mentee->name ?? '—' }}</div>
                            @if($task->mentee?->email)
                            <div class="text-xs text-slate-400 mt-0.5">{{ $task->mentee->email }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-slate-700">{{ $task->mentor->name ?? '—' }}</td>
                        <td class="px-4 py-4 text-slate-600">{{ \App\Models\MenteeTask::SUBMISSION_TYPES[$task->submission_type] ?? $task->submission_type }}</td>
                        <td class="px-4 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusStyles[$uiStatus] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.mentee-tasks.show', $task) }}" class="text-amber-700 hover:text-amber-900 text-xs font-semibold">View</a>
                                <a href="{{ route('admin.mentee-tasks.edit', $task) }}" class="text-slate-600 hover:text-slate-900 text-xs font-semibold">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="text-3xl mb-2">✅</div>
                            <p class="font-medium text-slate-600">No mentee tasks yet</p>
                            <p class="text-sm text-slate-400 mt-1">Create a standalone task and assign a mentor to review it.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">{{ $items->links() }}</div>
        @endif
    </div>
</div>
@endsection
