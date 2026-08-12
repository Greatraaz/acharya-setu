@php
    $inputId = $inputId ?? 'youtube-url';
    $wrapperClass = $wrapperClass ?? 'channel-composer__youtube';
    $inputClass = $inputClass ?? 'channel-composer__youtube-input';
    $labelClass = $labelClass ?? 'channel-composer__youtube-label';
    $label = $label ?? 'YouTube link (optional)';
@endphp

<div class="{{ $wrapperClass }}">
    @if($label)
    <label class="{{ $labelClass }}" for="{{ $inputId }}">{{ $label }}</label>
    @endif
    <input
        type="url"
        name="youtube_url"
        id="{{ $inputId }}"
        class="{{ $inputClass }}"
        placeholder="https://youtube.com/watch?v=… or youtu.be/…"
        autocomplete="off">
</div>
