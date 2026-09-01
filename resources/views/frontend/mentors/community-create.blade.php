@extends('frontend.layouts.app')
@section('title', 'Create Channel — Vedrix Mentor')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Create Channel</div>
            <div class="dash-subtitle">Create a community channel for your mentees.</div>
        </div>

        <div class="card community-channel-form-card">
            @php $backUrl = route('mentor.community'); @endphp
            <div class="community-channel-form-card__back">
                <a href="{{ $backUrl }}" class="btn btn-ghost btn-sm">← Back</a>
            </div>

            @include('partials.community-content-warning')

            <form method="POST" action="{{ route('mentor.community.store') }}" enctype="multipart/form-data" class="community-channel-form community-content-guarded">
                @csrf

                <div class="community-channel-form__grid community-channel-form__grid--top">
                    <div class="form-group community-channel-form__name">
                        <label class="form-label" for="channel-name">Channel Name</label>
                        <input type="text" name="name" id="channel-name" value="{{ old('name') }}" placeholder="e.g. Career Tips"
                               class="form-input" required>
                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group community-channel-form__icon">
                        <label class="form-label" for="channel-icon">Icon (emoji)</label>
                        <input type="text" name="icon" id="channel-icon" value="{{ old('icon', '💬') }}" maxlength="4" class="form-input">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="channel-slug">Slug</label>
                    <input type="text" name="slug" id="channel-slug" value="{{ old('slug') }}" placeholder="ask-a-mentor"
                           pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                           class="form-input">
                    <p class="form-hint">URL-friendly id. Leave blank to auto-generate from name.</p>
                    @error('slug')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="channel-description">Description</label>
                    <textarea name="description" id="channel-description" rows="4" placeholder="What is this channel about?" class="form-textarea">{{ old('description') }}</textarea>
                    @error('description')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <span class="form-label" id="channel-category-label">Category</span>
                    <div class="community-category-options" role="radiogroup" aria-labelledby="channel-category-label">
                        <label class="community-category-option">
                            <input type="radio" name="category" value="" {{ old('category', '') === '' ? 'checked' : '' }}>
                            <span>No category</span>
                        </label>
                        @foreach(($categories ?? \App\Models\Channel::CATEGORIES) as $key => $label)
                            <label class="community-category-option">
                                <input type="radio" name="category" value="{{ $key }}" {{ old('category') === $key ? 'checked' : '' }}>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-group">
                    <span class="form-label">Visibility</span>
                    <div class="community-visibility-options">
                        <label class="community-visibility-option">
                            <input type="radio" name="type" value="public" {{ old('type','public')==='public' ? 'checked' : '' }}>
                            <span>Public — mentors &amp; mentees can join</span>
                        </label>
                        <label class="community-visibility-option">
                            <input type="radio" name="type" value="private" {{ old('type')==='private' ? 'checked' : '' }}>
                            <span>Private — invite only</span>
                        </label>
                    </div>
                </div>

                @include('partials.community-channel-image-input', [
                    'label' => 'Cover image (optional)',
                    'hint' => 'Channel cover image shown on cards and in the channel header. JPEG, PNG, WebP, GIF · max 5MB.',
                    'labelClass' => 'form-label',
                    'hintClass' => 'form-hint',
                ])

                <div class="community-channel-form__actions">
                    <button type="submit" class="btn btn-primary w-full">Create Channel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
@include('partials.community-composer-scripts')
@include('partials.community-content-guard-scripts')
<script>
(function () {
    const nameInput = document.getElementById('channel-name');
    const slugInput = document.getElementById('channel-slug');
    if (!nameInput || !slugInput) return;

    let slugTouched = slugInput.value.trim() !== '';
    slugInput.addEventListener('input', () => { slugTouched = true; });

    nameInput.addEventListener('input', function () {
        if (slugTouched) return;
        slugInput.value = this.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s_]+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    });
})();
</script>
@endpush

@endsection
