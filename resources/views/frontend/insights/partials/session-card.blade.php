<article class="blog-card session-card">
    <div class="blog-card__media">
        @if($item->imageUrl())
            <img src="{{ $item->imageUrl() }}" alt="{{ $item->title }}">
        @else
            <div class="blog-card__placeholder">{{ $item->isWebinar() ? '🎥' : '📅' }}</div>
        @endif
        <span class="session-card__status session-card__status--{{ $item->isUpcoming() ? 'upcoming' : 'past' }}">
            {{ $item->scheduleBadge() }}
        </span>
        <span class="session-card__date">📅 {{ $item->dateLabel() }}</span>
    </div>
    <div class="blog-card__body session-card__body">
        <span class="session-card__type">{{ $item->isWebinar() ? '🎥 WEBINAR' : '📅 EVENT' }}</span>
        <h2 class="blog-card__title">{{ $item->title }}</h2>
        <p class="blog-card__excerpt">{{ $item->excerpt(24) }}</p>
        <div class="session-card__speaker">👤 Speaker: {{ $item->speaker }}</div>
        <a href="{{ route($showRoute, $item->slug) }}" class="session-view-btn">
            View {{ $item->isWebinar() ? 'Webinar' : 'Event' }} Details →
        </a>
    </div>
</article>
