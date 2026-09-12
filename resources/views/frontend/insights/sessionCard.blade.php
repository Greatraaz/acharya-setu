<article class="blog-card session-card">
    <div class="blog-card__media">
        @if($item->imageUrl())
            <img src="{{ $item->imageUrl() }}" alt="{{ $item->title }}" loading="lazy">
        @else
            <div class="blog-card__placeholder">
                <i class="bi {{ $item->isWebinar() ? 'bi-broadcast' : 'bi-calendar-event' }} text-muted"></i>
            </div>
        @endif
        <span class="session-card__status session-card__status--{{ $item->isUpcoming() ? 'upcoming' : 'past' }}">
            {{ $item->scheduleBadge() }}
        </span>
        <span class="session-card__date"><i class="bi bi-calendar-event me-1"></i> {{ $item->dateLabel() }}</span>
    </div>
    <div class="blog-card__body session-card__body">
        <span class="session-card__type"><i class="bi {{ $item->isWebinar() ? 'bi-broadcast' : 'bi-calendar-event' }} me-1"></i> {{ $item->isWebinar() ? 'WEBINAR' : 'EVENT' }}</span>
        <h2 class="blog-card__title">{{ $item->title }}</h2>
        <p class="blog-card__excerpt">{{ $item->excerpt(24) }}</p>
        <div class="session-card__speaker"><i class="bi bi-person me-1"></i> Speaker: {{ $item->speaker }}</div>
        <a href="{{ route($showRoute, $item->slug) }}" class="session-view-btn">
            View {{ $item->isWebinar() ? 'Webinar' : 'Event' }} Details <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
</article>
