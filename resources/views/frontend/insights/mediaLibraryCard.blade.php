@php
    $isAudio = ($mediaType ?? 'youtube') === 'audio';
    $isYoutube = ! $isAudio;
    $thumb = $thumbnail ?? null;
    $cardTitle = $title ?? '';
    $cardExcerpt = $excerpt ?? '';
    $ctaLabel = $isAudio ? 'Listen to Episode' : 'Watch Video';
    $badgeLabel = $isAudio ? 'Audio' : 'YouTube';
    $badgeClass = $isAudio ? 'media-library-card__badge--audio' : 'media-library-card__badge--youtube';
    $playClass = $isAudio ? 'media-library-card__play--audio' : 'media-library-card__play--youtube';
    $ctaClass = $isAudio ? 'media-library-card__cta--audio' : 'media-library-card__cta--video';
@endphp

<article class="media-library-card"
         data-media-card
         data-media-title="{{ $cardTitle }}"
         data-media-type="{{ $mediaType ?? 'youtube' }}"
         @if(! empty($audioSrc)) data-audio-src="{{ $audioSrc }}" @endif
         @if(! empty($youtubeSrc)) data-youtube-src="{{ $youtubeSrc }}" @endif
         @if(! empty($youtubeWatch)) data-youtube-watch="{{ $youtubeWatch }}" @endif>
    <button type="button" class="media-library-card__thumb" data-media-open>
        @if($thumb)
            <img src="{{ $thumb }}" alt="{{ $cardTitle }}" loading="lazy">
        @else
            <div class="media-library-card__placeholder">
                <i class="bi {{ $isAudio ? 'bi-headphones' : 'bi-play-circle-fill' }} text-muted"></i>
            </div>
        @endif
        <span class="media-library-card__badge {{ $badgeClass }}">
            @if($isAudio)
                <i class="bi bi-headphones me-1"></i> {{ $badgeLabel }}
            @else
                <i class="bi bi-youtube me-1"></i> {{ $badgeLabel }}
            @endif
        </span>
        <span class="media-library-card__play {{ $playClass }}" aria-hidden="true">
            <i class="bi {{ $isAudio ? 'bi-headphones' : 'bi-play-fill' }}"></i>
        </span>
    </button>
    <div class="media-library-card__body">
        <h2 class="media-library-card__title">{{ $cardTitle }}</h2>
        @if($cardExcerpt !== '')
            <p class="media-library-card__excerpt">{{ $cardExcerpt }}</p>
        @endif
        <span class="media-library-card__cta {{ $ctaClass }}">{{ $ctaLabel }} <i class="bi bi-arrow-right ms-1"></i></span>
    </div>
</article>
