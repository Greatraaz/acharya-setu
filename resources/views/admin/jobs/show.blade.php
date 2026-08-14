@extends('admin.layouts.app')
@section('title', $job->title)
@section('heading', $job->title)
@section('content')

@php
    $statusColors = ['active'=>'bg-green-50 text-green-700','draft'=>'bg-yellow-50 text-yellow-700','paused'=>'bg-orange-50 text-orange-700','closed'=>'bg-gray-100 text-gray-500'];
    $statusDots   = ['active'=>'bg-green-500','draft'=>'bg-yellow-400','paused'=>'bg-orange-400','closed'=>'bg-gray-400'];
@endphp

<div class="space-y-6">
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <a href="{{ route('admin.jobs.index') }}" class="text-sm font-medium text-blue-600 hover:underline">← Job Listings</a>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.jobs.edit', $job) }}" class="text-sm font-medium px-3 py-2 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition">Edit job</a>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ $job->title }}</h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $job->department ?: 'General' }} · {{ $job->location }} · {{ $job->location_type_label }}
                    · {{ $job->job_type_label }} · {{ $job->experience_label }}
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusColors[$job->status] ?? 'bg-gray-100 text-gray-500' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $statusDots[$job->status] ?? 'bg-gray-400' }}"></span>
                {{ ucfirst($job->status) }}
            </span>
        </div>

        <div class="flex flex-wrap gap-2 mt-4">
            <span class="text-xs font-semibold bg-gray-100 text-gray-700 px-2.5 py-1 rounded-full">{{ $job->salary_range }}</span>
            @if($job->deadline)
            <span class="text-xs font-medium bg-amber-50 text-amber-700 px-2.5 py-1 rounded-full">Deadline {{ $job->deadline->format('d M Y') }}</span>
            @endif
            <span class="text-xs font-medium bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full">{{ $applications->total() }} application{{ $applications->total() === 1 ? '' : 's' }}</span>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Applications</h3>
            <span class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full">{{ $applications->total() }}</span>
        </div>

        @if($applications->isEmpty())
        <div class="py-16 text-center">
            <p class="text-gray-600 font-medium">No applications yet</p>
            <p class="text-gray-400 text-sm mt-1">Mentees who apply from the dashboard will show up here.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Applicant</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Qualification</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Skills</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Experience</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Applied</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($applications as $application)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900">{{ $application->fullname }}</div>
                            @if($application->user)
                            <div class="text-xs text-gray-400 mt-0.5">{{ $application->user->email }}</div>
                            @if($application->user->phone)
                            <div class="text-xs text-gray-400">{{ $application->user->phone }}</div>
                            @endif
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-700">{{ $application->jobRole }}</td>
                        <td class="px-4 py-4">
                            <div class="text-sm text-gray-700">{{ $application->qualification }}</div>
                            @if($application->specification)
                            <div class="text-xs text-gray-400 mt-0.5">{{ $application->specification }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-600 max-w-xs">{{ $application->skills ?: '—' }}</td>
                        <td class="px-4 py-4">
                            <div class="text-sm text-gray-700">{{ $application->experience ?: '—' }}</div>
                            @if($application->lastJob)
                            <div class="text-xs text-gray-400 mt-0.5">{{ $application->lastJob }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-xs text-gray-500 whitespace-nowrap">
                            {{ $application->created_at?->format('d M Y, h:i A') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100">
            <div class="text-xs text-gray-500">
                @if($applications->total())
                Showing {{ $applications->firstItem() }}–{{ $applications->lastItem() }} of {{ $applications->total() }}
                @endif
            </div>
            {{ $applications->links() }}
        </div>
        @endif
    </div>
</div>

@endsection
