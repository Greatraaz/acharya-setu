@extends('admin.layouts.app')
@section('title', $item->name)
@section('heading', $item->name)

@section('content')
<div class="max-w-4xl space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.mentor-videos.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">← Back to list</a>
        <div class="flex gap-2">
            <a href="{{ route('admin.mentor-videos.edit', $item->id) }}"
               class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-xl">Edit</a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl p-5 grid sm:grid-cols-3 gap-4">
        <div>
            <div class="text-[11px] font-semibold uppercase text-gray-400">Mentor</div>
            <div class="text-sm font-semibold text-gray-900 mt-1">{{ $item->mentor?->name ?? '—' }}</div>
        </div>
        <div>
            <div class="text-[11px] font-semibold uppercase text-gray-400">Status</div>
            <div class="mt-1">
                @if($item->is_active)
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                @else
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                @endif
            </div>
        </div>
        <div>
            <div class="text-[11px] font-semibold uppercase text-gray-400">Files</div>
            <div class="text-sm font-semibold text-gray-900 mt-1">{{ $item->files->count() }}</div>
        </div>
        @if($item->description)
        <div class="sm:col-span-3">
            <div class="text-[11px] font-semibold uppercase text-gray-400">Description</div>
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
