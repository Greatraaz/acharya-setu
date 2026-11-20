@extends('admin.layouts.app')
@section('title', 'Curriculum Enrollments')
@section('heading', 'Curriculum Enrollments')

@section('content')
@include('admin.partials.learning-ui')

<div class="learn-page space-y-5">
    <div class="learn-intro">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="learn-eyebrow">Learning · Enrollments</span>
                <h2>Curriculum Enrollments</h2>
                <p>Track which mentees are enrolled on which journey streams.</p>
            </div>
            <a href="{{ route('admin.curriculum.streams') }}" class="learn-btn-ghost self-start">← Journey Manager</a>
        </div>
    </div>

    <div class="learn-panel p-4">
        <form method="GET" class="admin-filter-form">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Stream</label>
                <select name="stream_id" class="learn-focus w-full border border-gray-200 rounded-lg px-3 text-sm bg-white outline-none">
                    <option value="">All streams</option>
                    @foreach($streams as $stream)
                    <option value="{{ $stream->id }}" @selected((string) request('stream_id') === (string) $stream->id)>{{ $stream->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="learn-focus w-full border border-gray-200 rounded-lg px-3 text-sm bg-white outline-none">
                    <option value="">All statuses</option>
                    @foreach(['active' => 'Active', 'paused' => 'Paused', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-filter-actions">
                <button type="submit" class="learn-btn-primary !py-0 !px-4 h-10">Filter</button>
                @if(request()->filled('stream_id') || request()->filled('status'))
                <a href="{{ route('admin.curriculum.enrollments.index') }}" class="learn-btn-ghost h-10">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="learn-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Mentee</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Mentor</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Stream</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Progress</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($enrollments as $enrollment)
                    <tr class="hover:bg-amber-50/40">
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900">{{ $enrollment->mentee->name ?? '—' }}</div>
                            <div class="text-xs text-slate-400">{{ $enrollment->mentee->email ?? '' }}</div>
                        </td>
                        <td class="px-4 py-4 text-slate-700">{{ $enrollment->mentor->name ?? '—' }}</td>
                        <td class="px-4 py-4 text-slate-700">{{ $enrollment->stream->name ?? '—' }}</td>
                        <td class="px-4 py-4 text-slate-600 text-xs">
                            M{{ $enrollment->current_month ?? 1 }} · W{{ $enrollment->current_week ?? 1 }}
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ ($enrollment->status ?? '') === 'active' ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                {{ ucfirst($enrollment->status ?? 'unknown') }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.curriculum.enrollments.show', $enrollment) }}" class="text-amber-700 hover:text-amber-900 text-xs font-semibold">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center text-slate-400">
                            <div class="text-3xl mb-2">🗺️</div>
                            <p class="font-medium text-slate-600">No enrollments yet</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $enrollments])
    </div>
</div>
@endsection
