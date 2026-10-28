@extends('admin.layouts.app')
@section('title', 'Mentee Tasks')
@section('heading', 'Mentee Tasks')

@php
    $statusStyles = [
        'pending'         => 'bg-amber-50 text-amber-700',
        'in_progress'     => 'bg-blue-50 text-blue-700',
        'awaiting_review' => 'bg-violet-50 text-violet-700',
        'completed'       => 'bg-green-50 text-green-700',
        'rejected'        => 'bg-red-50 text-red-700',
    ];
@endphp

@section('content')
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500">Standalone tasks for mentees (outside curriculum). Assign a mentor to review.</p>
        <a href="{{ route('admin.mentee-tasks.create') }}"
           class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New task
        </a>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
        @foreach([
            ['Total', $stats['total'], 'bg-slate-50', 'text-slate-700', 'border-slate-200'],
            ['Pending', $stats['pending'], 'bg-amber-50', 'text-amber-700', 'border-amber-200'],
            ['Awaiting review', $stats['awaiting_review'], 'bg-violet-50', 'text-violet-700', 'border-violet-200'],
            ['Completed', $stats['completed'], 'bg-green-50', 'text-green-700', 'border-green-200'],
        ] as [$label, $count, $bg, $tc, $bc])
        <div class="{{ $bg }} border {{ $bc }} rounded-xl p-4">
            <div class="text-2xl font-bold {{ $tc }}">{{ number_format($count) }}</div>
            <div class="text-xs font-medium text-gray-500 mt-0.5">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl p-4">
        <form method="GET" class="admin-filter-form">
            <div class="admin-filter-search">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Title, mentee, mentor…"
                           class="w-full border border-gray-200 rounded-lg pl-9 pr-3 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-lg px-3 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                    <option value="">All statuses</option>
                    @foreach(['pending' => 'Pending', 'in_progress' => 'In progress', 'awaiting_review' => 'Awaiting review', 'completed' => 'Completed', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-filter-actions">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 rounded-lg">Filter</button>
                @if($search !== '' || $status !== '')
                <a href="{{ route('admin.mentee-tasks.index') }}" class="inline-flex items-center border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium px-4 rounded-lg">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Task</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Mentee</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Mentor</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Submission</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $task)
                    @php
                        $uiStatus = $task->uiStatus();
                        $statusLabel = str_replace('_', ' ', ucfirst($uiStatus));
                    @endphp
                    <tr class="hover:bg-gray-50/80">
                        <td class="px-5 py-4">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center text-base flex-shrink-0">
                                    {{ \App\Models\MenteeTask::TYPE_ICONS[$task->type] ?? '✅' }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-gray-900 truncate">{{ $task->title }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ \App\Models\MenteeTask::TYPES[$task->type] ?? ucfirst((string) $task->type) }}
                                        @if($task->is_required)
                                            · Required
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-medium text-gray-900">{{ $task->mentee->name ?? '—' }}</div>
                            @if($task->mentee?->email)
                            <div class="text-xs text-gray-400 mt-0.5">{{ $task->mentee->email }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-gray-700">{{ $task->mentor->name ?? '—' }}</td>
                        <td class="px-4 py-4 text-gray-600">{{ \App\Models\MenteeTask::SUBMISSION_TYPES[$task->submission_type] ?? $task->submission_type }}</td>
                        <td class="px-4 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusStyles[$uiStatus] ?? 'bg-gray-100 text-gray-600' }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.mentee-tasks.show', $task) }}" class="text-blue-600 hover:text-blue-800 text-xs font-semibold">View</a>
                                <a href="{{ route('admin.mentee-tasks.edit', $task) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="text-3xl mb-2">✅</div>
                            <p class="font-medium text-gray-600">No mentee tasks yet</p>
                            <p class="text-sm text-gray-400 mt-1">Create a standalone task and assign a mentor to review it.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $items->links() }}</div>
        @endif
    </div>
</div>
@endsection
