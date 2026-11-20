@extends('admin.layouts.app')
@section('title', 'Upload Mentor Videos')
@section('heading', 'Upload Mentor Videos')

@section('content')
@include('admin.partials.learning-ui')

<div class="learn-page max-w-2xl space-y-5">
    <div class="learn-intro">
        <a href="{{ route('admin.mentor-videos.index') }}" class="text-sm text-slate-400 hover:text-slate-700">← Back to list</a>
        <span class="learn-eyebrow mt-3 inline-flex">Learning · Upload</span>
        <h2>Upload Mentor Videos</h2>
        <p>Create a video collection on behalf of a mentor for mentees to watch.</p>
    </div>

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">{{ session('error') }}</div>
    @endif
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">
        <ul class="list-disc pl-4 space-y-1">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.mentor-videos.store') }}" enctype="multipart/form-data"
          class="learn-panel p-6 space-y-5">
        @csrf
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Mentor *</label>
            <select name="mentor_id" required class="learn-focus w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white outline-none">
                <option value="">Select mentor…</option>
                @foreach($mentors as $mentor)
                    <option value="{{ $mentor->id }}" @selected((string) old('mentor_id') === (string) $mentor->id)>
                        {{ $mentor->name }} ({{ $mentor->email }})
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Collection name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required maxlength="255"
                   class="learn-focus w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none">
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Description</label>
            <textarea name="description" rows="3" class="learn-focus w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none resize-y">{{ old('description') }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Video files * (max 10MB each)</label>
            <input type="file" name="videos[]" multiple required accept="video/mp4,video/quicktime,video/x-msvideo,video/webm,video/mpeg"
                   class="learn-focus w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white outline-none">
        </div>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-gray-300">
            Active (visible to mentees)
        </label>
        <button type="submit" class="learn-btn-primary">Upload</button>
    </form>
</div>
@endsection