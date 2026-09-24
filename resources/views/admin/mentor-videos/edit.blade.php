@extends('admin.layouts.app')
@section('title', 'Edit Mentor Videos')
@section('heading', 'Edit Mentor Videos')

@section('content')
<div class="max-w-2xl space-y-4">
    <a href="{{ route('admin.mentor-videos.show', $item->id) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">← Back</a>

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

    <form method="POST" action="{{ route('admin.mentor-videos.update', $item->id) }}" enctype="multipart/form-data"
          class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Mentor *</label>
            <select name="mentor_id" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
                @foreach($mentors as $mentor)
                    <option value="{{ $mentor->id }}" @selected((string) old('mentor_id', $item->mentor_id) === (string) $mentor->id)>
                        {{ $mentor->name }} ({{ $mentor->email }})
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Collection name *</label>
            <input type="text" name="name" value="{{ old('name', $item->name) }}" required maxlength="255"
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Description</label>
            <textarea name="description" rows="3" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">{{ old('description', $item->description) }}</textarea>
        </div>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active)) class="rounded border-gray-300">
            Active (visible to mentees)
        </label>

        @if($item->files->isNotEmpty())
        <div>
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Current videos (check to remove)</div>
            <div class="space-y-2">
                @foreach($item->files as $file)
                <label class="flex items-center gap-3 border border-gray-200 rounded-xl px-3 py-2.5 text-sm">
                    <input type="checkbox" name="remove_video_ids[]" value="{{ $file->id }}" class="rounded border-gray-300">
                    <span class="flex-1 truncate">{{ $file->file_name }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endif

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Add more videos</label>
            <input type="file" name="videos[]" multiple accept="video/mp4,video/quicktime,video/x-msvideo,video/webm,video/mpeg"
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl">Save changes</button>
    </form>
</div>
@endsection
