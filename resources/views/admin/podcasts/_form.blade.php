@php
    $isEdit = isset($podcast) && $podcast->exists;
    $formAction = $isEdit
        ? route('admin.podcasts.update', $podcast)
        : route('admin.podcasts.store');
    $selectedType = old('podcast_type', $podcast->podcast_type ?? 'audio');
@endphp

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-6"
      onsubmit="if (window.tinymce) { tinymce.triggerSave(); }">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Title *</label>
                <input type="text" name="title" id="podcast-title" value="{{ old('title', $podcast->title ?? '') }}" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                       placeholder="Enter podcast title">
                @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Slug *</label>
                <input type="text" name="slug" id="podcast-slug" value="{{ old('slug', $podcast->slug ?? '') }}" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                       placeholder="episode-slug">
                <p class="text-xs text-gray-500 mt-1">Lowercase letters, numbers, and hyphens only.</p>
                @error('slug')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Image {{ $isEdit ? '' : '*' }}</label>
            <input type="file" name="image" accept="image/*" {{ $isEdit ? '' : 'required' }}
                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white">
            @if($isEdit && $podcast->imageUrl())
                <img src="{{ $podcast->imageUrl() }}" alt="" class="mt-2 h-20 rounded-lg object-cover border border-gray-100">
            @endif
            @error('image')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Description *</label>
            <textarea name="description" id="podcast-description" rows="10"
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">{{ old('description', $podcast->description ?? '') }}</textarea>
            @error('description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Podcast Type *</label>
                <select name="podcast_type" id="podcast-type" required
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(\App\Models\Podcast::TYPES as $value => $label)
                        <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('podcast_type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="max-w-xs">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status *</label>
                <select name="status" required
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="active" @selected(old('status', $podcast->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $podcast->status ?? '') === 'inactive')>Inactive</option>
                </select>
                @error('status')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div id="podcast-audio-field" class="{{ $selectedType === 'youtube_url' ? 'hidden' : '' }}">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Audio File {{ ($isEdit && $podcast->audio) ? '' : '*' }}
            </label>
            <input type="file" name="audio" accept="audio/*,.mp3,.wav,.m4a,.ogg,.aac,.webm"
                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white">
            <p class="text-xs text-gray-500 mt-1">Supported: MP3, WAV, M4A, OGG, AAC (max 50MB).</p>
            @if($isEdit && $podcast->audioUrl())
                <audio controls class="mt-3 w-full max-w-md">
                    <source src="{{ $podcast->audioUrl() }}">
                </audio>
            @endif
            @error('audio')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div id="podcast-youtube-field" class="{{ $selectedType === 'audio' ? 'hidden' : '' }}">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">YouTube URL *</label>
            <input type="url" name="youtube_url" value="{{ old('youtube_url', $podcast->youtube_url ?? '') }}"
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                   placeholder="https://www.youtube.com/watch?v=... or youtu.be/... or /shorts/...">
            <p class="text-xs text-gray-500 mt-1">Paste a standard watch, youtu.be, embed, or Shorts URL.</p>
            @error('youtube_url')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.podcasts.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50">Cancel</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition">
            {{ $isEdit ? 'Update' : 'Submit' }}
        </button>
    </div>
</form>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#podcast-description',
    height: 280,
    menubar: true,
    plugins: 'lists link image table code wordcount',
    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | removeformat',
    branding: false,
    content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 14px; }'
});

(function () {
    var typeSelect = document.getElementById('podcast-type');
    var audioField = document.getElementById('podcast-audio-field');
    var youtubeField = document.getElementById('podcast-youtube-field');
    var titleInput = document.getElementById('podcast-title');
    var slugInput = document.getElementById('podcast-slug');
    var slugTouched = !!slugInput.value;

    slugInput.addEventListener('input', function () {
        slugTouched = true;
    });

    titleInput.addEventListener('input', function () {
        if (slugTouched) return;
        slugInput.value = titleInput.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    });

    function toggleTypeFields() {
        var isAudio = typeSelect.value === 'audio';
        audioField.classList.toggle('hidden', !isAudio);
        youtubeField.classList.toggle('hidden', isAudio);
    }

    typeSelect.addEventListener('change', toggleTypeFields);
    toggleTypeFields();
})();
</script>
@endpush
