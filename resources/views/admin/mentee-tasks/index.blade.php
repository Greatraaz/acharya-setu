@extends('admin.layouts.app')
@section('title', 'Mentee Tasks')
@section('heading', 'Mentee Tasks')

@section('content')
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500">Standalone tasks for mentees (outside curriculum). Assign a mentor to review.</p>
        <a href="{{ route('admin.mentee-tasks.create') }}" class="inline-flex bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl">+ New task</a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search title, mentee, mentor…"
               class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm w-full max-w-md">
        <button class="bg-gray-900 text-white text-sm px-4 py-2 rounded-xl">Search</button>
    </form>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-left">
                <tr>
                    <th class="px-5 py-3 font-semibold">Task</th>
                    <th class="px-5 py-3 font-semibold">Mentee</th>
                    <th class="px-5 py-3 font-semibold">Mentor</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($items as $task)
                <tr>
                    <td class="px-5 py-3 font-medium text-gray-900">{{ $task->title }}</td>
                    <td class="px-5 py-3">{{ $task->mentee->name ?? '—' }}</td>
                    <td class="px-5 py-3">{{ $task->mentor->name ?? '—' }}</td>
                    <td class="px-5 py-3">{{ str_replace('_', ' ', ucfirst($task->uiStatus())) }}</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.mentee-tasks.show', $task) }}" class="text-blue-600 font-medium">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">No mentee tasks yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($items->hasPages())
        <div class="px-5 py-3">{{ $items->links() }}</div>
        @endif
    </div>
</div>
@endsection
