{{-- Standard PWA install sheet (mobile + desktop when installable) --}}
<div id="a2hs-banner" class="a2hs-banner" hidden role="dialog" aria-labelledby="a2hs-title" aria-modal="true">
    <div class="a2hs-banner__card">
        <button type="button" class="a2hs-banner__close" data-a2hs-dismiss aria-label="Close">✕</button>
        <div class="a2hs-banner__row">
            <img class="a2hs-banner__icon" src="{{ asset('icons/icon-192.png') }}" width="48" height="48" alt="">
            <div class="a2hs-banner__copy">
                <strong id="a2hs-title">Install Vedrix</strong>
                <p data-a2hs-subtitle>Add a home-screen shortcut for quicker access.</p>
            </div>
        </div>

        {{-- Native install (Chrome/Edge/Samsung Android & desktop Chromium) --}}
        <div data-a2hs-panel="native" hidden>
            <div class="a2hs-banner__actions">
                <button type="button" class="a2hs-banner__cta" data-a2hs-install>Install</button>
                <button type="button" class="a2hs-banner__link" data-a2hs-dismiss>Not now</button>
            </div>
        </div>

        {{-- Manual steps (iOS / Firefox / etc.) --}}
        <div data-a2hs-panel="guide" hidden>
            <ol class="a2hs-banner__steps" data-a2hs-steps></ol>
            <p class="a2hs-banner__hint" data-a2hs-hint hidden></p>
            <div class="a2hs-banner__actions">
                <button type="button" class="a2hs-banner__cta" data-a2hs-dismiss>Got it</button>
            </div>
        </div>
    </div>
</div>

{{-- Compact floating affordance (standard PWA pattern; not in nav menu) --}}
<button type="button" id="a2hs-chip" class="a2hs-chip" hidden aria-label="Install Vedrix">
    <span class="a2hs-chip__ico" aria-hidden="true">↓</span>
    <span>Install</span>
</button>
