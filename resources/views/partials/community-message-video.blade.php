@php
    $isReply = $reply ?? false;
    $embedUrl = $message->youtube_embed_url ?? null;
    $legacyVideoUrl = (! $embedUrl && ($message->video_url ?? $message->video_path))
        ? ($message->video_url ?? asset('storage/'.$message->video_path))
        : null;
@endphp

@if($embedUrl)
<div class="channel-msg-youtube{{ $isReply ? ' channel-msg-youtube--reply' : '' }}">
    <iframe
        src="{{ $embedUrl }}"
        title="YouTube video"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        allowfullscreen
        loading="lazy"></iframe>
</div>
@elseif($legacyVideoUrl)
<video class="channel-msg-video{{ $isReply ? ' channel-msg-video--reply' : '' }}" controls playsinline preload="metadata" src="{{ $legacyVideoUrl }}"></video>
@endif
