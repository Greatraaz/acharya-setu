@php
    $isReply = $reply ?? false;
    $videoUrl = $message->video_url ?? null;
@endphp

@if($videoUrl)
<video class="channel-msg-video{{ $isReply ? ' channel-msg-video--reply' : '' }}" controls playsinline preload="metadata" src="{{ $videoUrl }}"></video>
@endif
