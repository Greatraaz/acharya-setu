@php
    $inputId = $inputId ?? 'channel-image';
    $chipId = $chipId ?? 'channel-image-chip';
    $label = $label ?? 'Image (optional)';
    $hint = $hint ?? 'Channel cover image shown on cards and in the channel header. JPEG, PNG, WebP, GIF · max 5MB.';
    $labelClass = $labelClass ?? 'block text-sm font-semibold text-gray-700 mb-1.5';
    $hintClass = $hintClass ?? 'text-xs text-gray-500 mt-1';
@endphp
<div>
    <label class="{{ $labelClass }}" for="{{ $inputId }}">{{ $label }}</label>
    <div class="flex items-center gap-3">
        <label for="{{ $inputId }}" class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-600 hover:text-blue-600 border border-gray-200 rounded-xl px-3.5 py-2.5 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Choose image</span>
        </label>
        <span id="{{ $chipId }}" class="channel-composer__chip text-xs text-gray-500"></span>
    </div>
    <input type="file" name="image" id="{{ $inputId }}" accept="image/*" data-chip="{{ $chipId }}" class="sr-only">
    <p class="{{ $hintClass }}">{{ $hint }}</p>
    @error('image')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
</div>
