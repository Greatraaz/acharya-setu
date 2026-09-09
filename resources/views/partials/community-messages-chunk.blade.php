@php
    $prevDate = null;
@endphp
@foreach($messages as $message)
@php
    $msgDate = $message->created_at->toDateString();
@endphp

@if($msgDate !== $prevDate)
<div class="community-thread__date-divider" data-date="{{ $msgDate }}">
    <span>
        @if($message->created_at->isToday()) Today
        @elseif($message->created_at->isYesterday()) Yesterday
        @else {{ $message->created_at->format('d M Y') }}
        @endif
    </span>
</div>
@php $prevDate = $msgDate; @endphp
@endif

@include('partials.community-message-bubble', [
    'message' => $message,
    'channel' => $channel,
    'routePrefix' => $routePrefix,
])
@endforeach
