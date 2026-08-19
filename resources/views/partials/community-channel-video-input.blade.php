@php
    $inputId = $inputId ?? 'channel-video';
    $chipId = $chipId ?? 'channel-video-chip';
    $label = $label ?? 'Video (optional)';
    $hint = $hint ?? 'If provided, this video is posted as the first channel message. MP4, MOV, AVI, WebM · max 10MB.';
    $labelClass = $labelClass ?? 'block text-sm font-semibold text-gray-700 mb-1.5';
    $hintClass = $hintClass ?? 'text-xs text-gray-500 mt-1';
@endphp
<div>
    <label class="{{ $labelClass }}" for="{{ $inputId }}">{{ $label }}</label>
    <div class="flex items-center gap-3">
        <label for="{{ $inputId }}" class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-600 hover:text-blue-600 border border-gray-200 rounded-xl px-3.5 py-2.5 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <span>Choose video</span>
        </label>
        <span id="{{ $chipId }}" class="channel-composer__chip text-xs text-gray-500"></span>
    </div>
    <input type="file" name="video" id="{{ $inputId }}" accept="video/mp4,video/webm,video/quicktime,video/x-msvideo,video/mpeg,.mp4,.mov,.avi,.webm" data-chip="{{ $chipId }}" class="sr-only">
    <p class="{{ $hintClass }}">{{ $hint }}</p>
    @error('video')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
</div>
