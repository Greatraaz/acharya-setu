<div class="media-lightbox" id="media-lightbox" hidden aria-hidden="true">
    <div class="media-lightbox__backdrop" data-media-close></div>
    <div class="media-lightbox__dialog" role="dialog" aria-modal="true" aria-labelledby="media-lightbox-title">
        <button type="button" class="media-lightbox__close" data-media-close aria-label="Close player">&times;</button>
        <h3 class="media-lightbox__title" id="media-lightbox-title"></h3>
        <div class="media-lightbox__player" id="media-lightbox-player"></div>
        <a class="media-lightbox__external is-hidden" id="media-lightbox-external" href="#" target="_blank" rel="noopener noreferrer">
            Watch on YouTube
        </a>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var cards = document.querySelectorAll('[data-media-card]');
    var lightbox = document.getElementById('media-lightbox');
    var player = document.getElementById('media-lightbox-player');
    var titleEl = document.getElementById('media-lightbox-title');
    var externalLink = document.getElementById('media-lightbox-external');

    if (!lightbox || !player || !titleEl) return;

    function closeLightbox() {
        player.innerHTML = '';
        lightbox.hidden = true;
        lightbox.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('media-lightbox-open');
        externalLink.classList.add('is-hidden');
        externalLink.href = '#';
    }

    function openLightbox(card) {
        var type = card.getAttribute('data-media-type');
        var title = card.getAttribute('data-media-title') || 'Media';
        titleEl.textContent = title;
        player.innerHTML = '';
        externalLink.classList.add('is-hidden');

        if (type === 'audio') {
            var audioSrc = card.getAttribute('data-audio-src');
            if (!audioSrc) return;

            player.classList.add('is-audio');
            var audio = document.createElement('audio');
            audio.controls = true;
            audio.autoplay = true;
            audio.className = 'media-lightbox__audio';
            audio.setAttribute('controlsList', 'nodownload');
            audio.innerHTML = '<source src="' + audioSrc + '">';
            player.appendChild(audio);
            audio.play().catch(function () {});
        } else {
            var youtubeSrc = card.getAttribute('data-youtube-src');
            var watchUrl = card.getAttribute('data-youtube-watch');
            if (!youtubeSrc) return;

            player.classList.remove('is-audio');
            var iframe = document.createElement('iframe');
            var join = youtubeSrc.indexOf('?') >= 0 ? '&' : '?';
            iframe.src = youtubeSrc + join + 'origin=' + encodeURIComponent(window.location.origin);
            iframe.title = title;
            iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
            iframe.allowFullscreen = true;
            iframe.referrerPolicy = 'strict-origin-when-cross-origin';
            player.appendChild(iframe);

            if (watchUrl) {
                externalLink.href = watchUrl;
                externalLink.classList.remove('is-hidden');
            }
        }

        lightbox.hidden = false;
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.classList.add('media-lightbox-open');
    }

    cards.forEach(function (card) {
        card.addEventListener('click', function () {
            openLightbox(card);
        });
    });

    lightbox.querySelectorAll('[data-media-close]').forEach(function (el) {
        el.addEventListener('click', closeLightbox);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !lightbox.hidden) {
            closeLightbox();
        }
    });
})();
</script>
@endpush
