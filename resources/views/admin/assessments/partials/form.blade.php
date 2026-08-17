@php
    $isEdit = isset($assessment) && $assessment->exists;
    $formAction = $formAction ?? ($isEdit
        ? route('admin.assessments.update', $assessment)
        : route('admin.assessments.store'));
    $bands = $bands ?? [];
    if ($bands === []) {
        $bands = collect(range(0, 3))->map(fn () => [
            'from' => '', 'to' => '', 'heading' => '', 'description' => '',
        ])->all();
    }
@endphp

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Assessment Name *</label>
                <input type="text" name="title" value="{{ old('title', $assessment->title ?? '') }}" required
                       placeholder="Enter assessment name"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Image</label>
                <input type="file" name="image_file" accept="image/*"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white">
                @if($assessment->image)
                    <img src="{{ $assessment->imageUrl() }}" alt="" class="mt-2 h-16 rounded-lg object-cover">
                @endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Assessment Icon</label>
                <input type="file" name="icon_file" accept="image/*"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white">
                @if($assessment->icon)
                    <img src="{{ $assessment->iconUrl() }}" alt="" class="mt-2 h-12 w-12 rounded-lg object-cover">
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Assessment Description</label>
                <textarea name="description" rows="6" placeholder="Enter description"
                          class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 resize-y">{{ old('description', $assessment->description ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Assessment Instructions</label>
                <textarea name="instructions" rows="6" placeholder="Enter instructions"
                          class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 resize-y">{{ old('instructions', $assessment->instructions ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($bands as $i => $band)
        <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Score Band {{ $i }}</h3>
            <div class="grid grid-cols-3 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">From *</label>
                    <input type="number" min="0" name="bands[{{ $i }}][from]" value="{{ $band['from'] }}" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">To *</label>
                    <input type="number" min="0" name="bands[{{ $i }}][to]" value="{{ $band['to'] }}" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Heading *</label>
                    <input type="text" name="bands[{{ $i }}][heading]" value="{{ $band['heading'] }}" required
                           placeholder="e.g. Mild"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                </div>
            </div>
            @error("bands.$i.from")<p class="text-xs text-red-600 mb-2">{{ $message }}</p>@enderror
            @error("bands.$i.to")<p class="text-xs text-red-600 mb-2">{{ $message }}</p>@enderror
            @error("bands.$i.heading")<p class="text-xs text-red-600 mb-2">{{ $message }}</p>@enderror
            <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
            <textarea name="bands[{{ $i }}][description]" rows="8" class="tinymce-band w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">{{ $band['description'] }}</textarea>
        </div>
        @endforeach
    </div>
    @error('bands')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

    <button type="submit"
            class="w-full bg-blue-600 text-white py-3 rounded-xl font-medium hover:bg-blue-700 transition-colors">
        {{ $isEdit ? 'Update Assessment' : 'Submit' }}
    </button>
</form>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: 'textarea.tinymce-band',
    height: 220,
    menubar: 'file edit view insert format tools table',
    plugins: 'lists link',
    toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | link',
    branding: false,
    promotion: false,
    content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 14px; }'
});
</script>
@endpush
