@php
    $inputId = $inputId ?? 'channel-image';
    $chipId = $chipId ?? 'channel-image-chip';
    $label = $label ?? 'Image (optional)';
    $hint = $hint ?? 'Channel cover image shown on cards and in the channel header. JPEG, PNG, WebP, GIF · max 5MB.';
    $labelClass = $labelClass ?? 'community-image-field__label';
    $hintClass = $hintClass ?? 'community-image-field__hint';
    $pickerClass = $pickerClass ?? 'community-image-field__picker';
@endphp
<div class="community-image-field form-group">
    <label class="{{ $labelClass }}" for="{{ $inputId }}">{{ $label }}</label>
    <div class="community-image-field__row">
        <label for="{{ $inputId }}" class="{{ $pickerClass }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Choose image</span>
        </label>
        <span id="{{ $chipId }}" class="channel-composer__chip community-image-field__chip"></span>
    </div>
    <input type="file" name="image" id="{{ $inputId }}" accept="image/*" data-chip="{{ $chipId }}" class="sr-only">
    <p class="{{ $hintClass }}">{{ $hint }}</p>
    @error('image')<p class="form-error">{{ $message }}</p>@enderror
</div>
