@php
    $isEdit = isset($download) && $download->exists;
    $formAction = $isEdit
        ? route('admin.download-centres.update', $download)
        : route('admin.download-centres.store');
@endphp

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-6"
      onsubmit="if (window.tinymce) { tinymce.triggerSave(); }">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Title *</label>
            <input type="text" name="title" value="{{ old('title', $download->title ?? '') }}" required
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                   placeholder="Enter download title">
            @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $download->slug ?? '') }}"
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                   placeholder="auto-generated-from-title">
            <p class="text-xs text-gray-500 mt-1">Leave blank to auto-generate from the title. Use lowercase letters, numbers, and hyphens only.</p>
            @error('slug')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Cover Image {{ $isEdit ? '' : '*' }}</label>
                <input type="file" name="image" accept="image/*" {{ $isEdit ? '' : 'required' }}
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white">
                @if($isEdit && $download->imageUrl())
                    <img src="{{ $download->imageUrl() }}" alt="" class="mt-2 h-20 rounded-lg object-cover border border-gray-100">
                @endif
                @error('image')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Document {{ $isEdit ? '' : '*' }}</label>
                <input type="file" name="document" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip" {{ $isEdit ? '' : 'required' }}
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white">
                <p class="text-xs text-gray-500 mt-1">PDF, Word, Excel, PowerPoint, or ZIP — max 20 MB.</p>
                @if($isEdit && $download->document)
                    <p class="text-xs text-gray-600 mt-2">Current file: <strong>{{ strtoupper($download->documentExtension()) }}</strong></p>
                @endif
                @error('document')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Description *</label>
            <textarea name="description" id="download-centre-description" rows="10"
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">{{ old('description', $download->description ?? '') }}</textarea>
            <p class="text-xs text-gray-500 mt-1">Shown on the website download card.</p>
            @error('description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="max-w-xs">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status *</label>
            <select name="status" required
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="active" @selected(old('status', $download->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $download->status ?? '') === 'inactive')>Inactive</option>
            </select>
            @error('status')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.download-centres.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50">Cancel</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition">
            {{ $isEdit ? 'Update' : 'Submit' }}
        </button>
    </div>
</form>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#download-centre-description',
    height: 320,
    menubar: true,
    plugins: 'lists link table code wordcount',
    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | removeformat',
    branding: false,
    content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 14px; }'
});
</script>
@endpush
