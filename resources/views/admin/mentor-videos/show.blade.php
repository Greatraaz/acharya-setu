@extends('admin.layouts.app')
@section('title', $item->name)
@section('heading', $item->name)

@section('content')
<div class="max-w-4xl space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.mentor-videos.index') }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to videos
        </a>
        <a href="{{ route('admin.mentor-videos.edit', $item->id) }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium px-3.5 py-2 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">Edit</a>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl p-5 grid sm:grid-cols-3 gap-4">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Mentor</div>
            <div class="text-sm font-semibold text-gray-900 mt-1">{{ $item->mentor?->name ?? '—' }}</div>
            @if($item->mentor?->email)
            <div class="text-xs text-gray-400 mt-0.5">{{ $item->mentor->email }}</div>
            @endif
        </div>
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Status</div>
            <div class="mt-1">
                @if($item->is_active)
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">Active</span>
                @else
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Inactive</span>
                @endif
            </div>
        </div>
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Files</div>
            <div class="text-sm font-semibold text-gray-900 mt-1">{{ $item->files->count() }}</div>
        </div>
        @if($item->description)
        <div class="sm:col-span-3">
            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Description</div>
            <p class="text-sm text-gray-700 mt-1">{{ $item->description }}</p>
        </div>
        @endif
    </div>

    <div class="space-y-4">
        @forelse($item->files as $file)
        <div class="bg-white border border-gray-200 rounded-2xl p-4">
            <div class="text-sm font-semibold text-gray-900 mb-3">{{ $file->file_name }}</div>
            <video controls preload="metadata" class="w-full max-h-[420px] rounded-xl bg-black">
                <source src="{{ $file->video_url }}" type="video/mp4">
            </video>
        </div>
        @empty
        <div class="bg-white border border-gray-200 rounded-2xl py-12 text-center text-gray-400 text-sm">No files.</div>
        @endforelse
    </div>
</div>
@endsection
