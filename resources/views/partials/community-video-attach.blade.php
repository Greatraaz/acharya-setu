@php
    $chipId = $chipId ?? null;
    $inputId = $inputId ?? null;
    $wrapperClass = $wrapperClass ?? 'channel-composer__attach';
    $svgClass = $svgClass ?? null;
    $inputClass = $inputClass ?? '';
    $accept = $accept ?? 'video/mp4,video/webm,video/quicktime,video/x-msvideo,video/mpeg,.mp4,.mov,.avi,.webm';
    $title = $title ?? 'Add video';
@endphp
<label class="{{ $wrapperClass }}" title="{{ $title }}">
    <input
        type="file"
        name="video"
        accept="{{ $accept }}"
        @if($chipId) data-chip="{{ $chipId }}" @endif
        @if($inputId) id="{{ $inputId }}" @endif
        @if($inputClass) class="{{ $inputClass }}" @endif>
    <svg xmlns="http://www.w3.org/2000/svg" @if($svgClass) class="{{ $svgClass }}" @endif fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
    </svg>
</label>
