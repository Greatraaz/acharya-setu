@php
    $size = $size ?? 'md';
    $channel = $channel ?? null;
@endphp
@if($channel?->image_url)
    <img src="{{ $channel->image_url }}" alt="{{ $channel->name }}" class="community-thumb community-thumb--{{ $size }}">
@else
    <div class="community-thumb community-thumb--{{ $size }} community-thumb--emoji" aria-hidden="true">{{ $channel->icon ?? '💬' }}</div>
@endif
