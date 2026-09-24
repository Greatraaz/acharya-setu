@extends('admin.layouts.app')
@section('title', 'Upload Mentor Videos')
@section('heading', 'Upload Mentor Videos')

@section('content')
<div class="max-w-2xl space-y-4">
    <a href="{{ route('admin.mentor-videos.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">← Back to list</a>

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
          class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5">
        @csrf
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Mentor *</label>
            <select name="mentor_id" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
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
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Description</label>
            <textarea name="description" rows="3" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">{{ old('description') }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Video files * (max 10MB each)</label>
            <input type="file" name="videos[]" multiple required accept="video/mp4,video/quicktime,video/x-msvideo,video/webm,video/mpeg"
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">
        </div>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-gray-300">
            Active (visible to mentees)
        </label>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl">Upload</button>
    </form>
</div>
@endsection
