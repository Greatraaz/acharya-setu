@php
    $isEdit = isset($caseStudy) && $caseStudy->exists;
    $formAction = $isEdit
        ? route('admin.case-studies.update', $caseStudy)
        : route('admin.case-studies.store');
@endphp

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-6"
      onsubmit="if (window.tinymce) { tinymce.triggerSave(); }">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Case Study Title *</label>
            <input type="text" name="title" value="{{ old('title', $caseStudy->title ?? '') }}" required
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                   placeholder="Enter case study title">
            @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Industry *</label>
                <input type="text" name="industry" value="{{ old('industry', $caseStudy->industry ?? '') }}" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                       placeholder="e.g. Higher Education, EdTech, Career Services">
                @error('industry')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Image {{ $isEdit ? '' : '*' }}</label>
                <input type="file" name="image" accept="image/*" {{ $isEdit ? '' : 'required' }}
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white">
                @if($isEdit && $caseStudy->imageUrl())
                    <img src="{{ $caseStudy->imageUrl() }}" alt="" class="mt-2 h-20 rounded-lg object-cover border border-gray-100">
                @endif
                @error('image')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Description *</label>
            <textarea name="description" id="case-study-description" rows="12"
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">{{ old('description', $caseStudy->description ?? '') }}</textarea>
            @error('description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Result</label>
            <textarea name="result" id="case-study-result" rows="8"
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">{{ old('result', $caseStudy->result ?? '') }}</textarea>
            <p class="text-xs text-gray-500 mt-1">Optional measured outcome shown on the case study detail page.</p>
            @error('result')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="max-w-xs">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status *</label>
            <select name="status" required
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="active" @selected(old('status', $caseStudy->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $caseStudy->status ?? '') === 'inactive')>Inactive</option>
            </select>
            @error('status')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.case-studies.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50">Cancel</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition">
            {{ $isEdit ? 'Update' : 'Submit' }}
        </button>
    </div>
</form>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#case-study-description, #case-study-result',
    height: 320,
    menubar: true,
    plugins: 'lists link image table code wordcount',
    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | removeformat',
    branding: false,
    content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 14px; }'
});
</script>
@endpush
