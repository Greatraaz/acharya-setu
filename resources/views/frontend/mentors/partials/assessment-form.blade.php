@php
    $isEdit = isset($assessment) && $assessment->exists;
    $formAction = $formAction ?? ($isEdit
        ? route('mentor.assessments.update', $assessment)
        : route('mentor.assessments.store'));
    $bands = $bands ?? [];
    if ($bands === []) {
        $bands = collect(range(0, 3))->map(fn () => [
            'from' => '', 'to' => '', 'heading' => '', 'description' => '',
        ])->all();
    }
@endphp

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="assess-form-card">
        <div class="assess-form-grid">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Assessment Name *</label>
                <input type="text" name="title" value="{{ old('title', $assessment->title ?? '') }}" required
                       placeholder="Enter assessment name" class="form-input">
                @error('title')<p class="assess-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Image</label>
                <input type="file" name="image_file" accept="image/*" class="form-input">
                @if($assessment->image)
                    <img src="{{ $assessment->imageUrl() }}" alt="" class="assess-preview">
                @endif
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Assessment Icon</label>
                <input type="file" name="icon_file" accept="image/*" class="form-input">
                @if($assessment->icon)
                    <img src="{{ $assessment->iconUrl() }}" alt="" class="assess-preview is-icon">
                @endif
            </div>
        </div>

        <div class="assess-form-grid-2">
            <div>
                <label class="form-label">Assessment Description</label>
                <textarea name="description" rows="6" placeholder="Enter description" class="form-textarea">{{ old('description', $assessment->description ?? '') }}</textarea>
            </div>
            <div>
                <label class="form-label">Assessment Instructions</label>
                <textarea name="instructions" rows="6" placeholder="Enter instructions" class="form-textarea">{{ old('instructions', $assessment->instructions ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="assess-band-grid">
        @foreach($bands as $i => $band)
        <div class="assess-form-card">
            <h3 class="assess-section-title" style="margin-bottom:16px;">Score Band {{ $i }}</h3>
            <div class="assess-form-grid-3">
                <div>
                    <label class="form-label">From *</label>
                    <input type="number" min="0" name="bands[{{ $i }}][from]" value="{{ $band['from'] }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">To *</label>
                    <input type="number" min="0" name="bands[{{ $i }}][to]" value="{{ $band['to'] }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Heading *</label>
                    <input type="text" name="bands[{{ $i }}][heading]" value="{{ $band['heading'] }}" required
                           placeholder="e.g. Mild" class="form-input">
                </div>
            </div>
            @error("bands.$i.from")<p class="assess-error">{{ $message }}</p>@enderror
            @error("bands.$i.to")<p class="assess-error">{{ $message }}</p>@enderror
            @error("bands.$i.heading")<p class="assess-error">{{ $message }}</p>@enderror
            <label class="form-label">Description</label>
            <textarea name="bands[{{ $i }}][description]" rows="8" class="tinymce-band form-textarea">{{ $band['description'] }}</textarea>
        </div>
        @endforeach
    </div>
    @error('bands')<p class="assess-error">{{ $message }}</p>@enderror

    <button type="submit" class="assess-submit">
        {{ $isEdit ? 'Update Assessment' : 'Submit' }}
    </button>
</form>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
<script>
(function () {
    const light = document.documentElement.getAttribute('data-theme') === 'light';
    tinymce.init({
        selector: 'textarea.tinymce-band',
        height: 220,
        menubar: 'file edit view insert format tools table',
        plugins: 'lists link',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | link',
        branding: false,
        promotion: false,
        skin: light ? 'oxide' : 'oxide-dark',
        content_css: light ? 'default' : 'dark',
        content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 14px; }'
    });
})();
</script>
@endpush
