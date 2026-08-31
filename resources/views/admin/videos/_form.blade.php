@php
    $isEdit = isset($video) && $video->exists;
    $formAction = $isEdit
        ? route('admin.videos.update', $video)
        : route('admin.videos.store');
@endphp

<form method="POST" action="{{ $formAction }}" class="space-y-6"
      onsubmit="if (window.tinymce) { tinymce.triggerSave(); }">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Title *</label>
                <input type="text" name="title" id="video-title" value="{{ old('title', $video->title ?? '') }}" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                       placeholder="Enter video title">
                @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Slug *</label>
                <input type="text" name="slug" id="video-slug" value="{{ old('slug', $video->slug ?? '') }}" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                       placeholder="video-slug">
                <p class="text-xs text-gray-500 mt-1">Lowercase letters, numbers, and hyphens only.</p>
                @error('slug')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">YouTube URL *</label>
            <input type="url" name="youtube_url" value="{{ old('youtube_url', $video->youtube_url ?? '') }}" required
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                   placeholder="https://www.youtube.com/watch?v=... or youtu.be/...">
            <p class="text-xs text-gray-500 mt-1">Thumbnail is pulled automatically from YouTube.</p>
            @error('youtube_url')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Description *</label>
            <textarea name="description" id="video-description" rows="10"
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">{{ old('description', $video->description ?? '') }}</textarea>
            @error('description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="max-w-xs">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status *</label>
            <select name="status" required
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="active" @selected(old('status', $video->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $video->status ?? '') === 'inactive')>Inactive</option>
            </select>
            @error('status')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.videos.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50">Cancel</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition">
            {{ $isEdit ? 'Update' : 'Submit' }}
        </button>
    </div>
</form>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#video-description',
    height: 280,
    menubar: true,
    plugins: 'lists link image table code wordcount',
    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | removeformat',
    branding: false,
    content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 14px; }'
});

(function () {
    var titleInput = document.getElementById('video-title');
    var slugInput = document.getElementById('video-slug');
    if (!titleInput || !slugInput) return;

    var slugTouched = !!slugInput.value;
    slugInput.addEventListener('input', function () { slugTouched = true; });
    titleInput.addEventListener('input', function () {
        if (slugTouched) return;
        slugInput.value = titleInput.value.toLowerCase().trim()
            .replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
    });
})();
</script>
@endpush
