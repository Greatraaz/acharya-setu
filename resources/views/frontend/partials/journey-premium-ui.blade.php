{{-- My Journey module — rich amber premium chrome --}}
@include('frontend.partials.dash-premium-ui')
<style>
.journey-premium {
    --jp: var(--brand, #F59E0B);
    --jp-dark: var(--brand-dark, #D97706);
    --jp-soft: var(--brand-muted, rgba(245,158,11,.12));
    --jp-ring: rgba(245,158,11,.28);
}
.journey-premium .jp-crumb {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .4rem;
    font-size: .72rem;
    font-weight: 650;
    color: var(--text-3);
    margin-bottom: .65rem;
}
.journey-premium .jp-crumb a {
    color: var(--jp-dark);
    text-decoration: none;
}
.journey-premium .jp-crumb a:hover { text-decoration: underline; }
.journey-premium .jp-crumb__sep { opacity: .5; }
.journey-premium .jp-crumb__now { color: var(--text-2); }

.journey-premium .jp-hero {
    position: relative;
    overflow: hidden;
    border-radius: 1.15rem;
    border: 1px solid rgba(245,158,11,.35);
    padding: 1.35rem 1.4rem;
    color: #fff;
    background:
        radial-gradient(420px 180px at 100% 0%, rgba(255,255,255,.18), transparent 60%),
        linear-gradient(135deg, #F59E0B 0%, #D97706 55%, #B45309 100%);
    box-shadow: 0 18px 36px rgba(245,158,11,.22);
}
.journey-premium .jp-hero::after {
    content: "";
    position: absolute;
    right: -2rem;
    bottom: -2.5rem;
    width: 9rem;
    height: 9rem;
    border-radius: 999px;
    background: rgba(255,255,255,.1);
}
.journey-premium .jp-hero__kicker {
    position: relative;
    z-index: 1;
    font-size: 11px;
    font-weight: 750;
    letter-spacing: .12em;
    text-transform: uppercase;
    opacity: .88;
}
.journey-premium .jp-hero__title {
    position: relative;
    z-index: 1;
    margin: .4rem 0 .3rem;
    font-family: var(--font-head);
    font-size: clamp(1.35rem, 2.6vw, 1.85rem);
    font-weight: 800;
    letter-spacing: -.03em;
    line-height: 1.15;
}
.journey-premium .jp-hero__sub {
    position: relative;
    z-index: 1;
    margin: 0;
    font-size: .88rem;
    opacity: .92;
    max-width: 36rem;
    line-height: 1.5;
}
.journey-premium .jp-hero__progress {
    position: relative;
    z-index: 1;
    margin-top: 1.1rem;
}
.journey-premium .jp-hero__progress-row {
    display: flex;
    justify-content: space-between;
    font-size: .75rem;
    font-weight: 700;
    margin-bottom: .4rem;
    opacity: .95;
}
.journey-premium .jp-hero__bar {
    height: .45rem;
    border-radius: 999px;
    background: rgba(255,255,255,.28);
    overflow: hidden;
}
.journey-premium .jp-hero__bar > span {
    display: block;
    height: 100%;
    border-radius: 999px;
    background: #fff;
    box-shadow: 0 0 12px rgba(255,255,255,.45);
}

.journey-premium .jp-timeline {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: .85rem;
    padding-left: .35rem;
}
.journey-premium .jp-timeline::before {
    content: "";
    position: absolute;
    left: 1.05rem;
    top: .75rem;
    bottom: .75rem;
    width: 2px;
    background: linear-gradient(180deg, rgba(245,158,11,.55), rgba(245,158,11,.08));
}
.journey-premium .jp-node {
    position: relative;
    display: grid;
    grid-template-columns: 2.1rem 1fr;
    gap: .85rem;
    align-items: stretch;
}
.journey-premium .jp-node__dot {
    position: relative;
    z-index: 1;
    width: 2.1rem;
    height: 2.1rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .7rem;
    font-weight: 800;
    font-family: var(--font-head);
    color: var(--jp-dark);
    background: #fff;
    border: 2px solid rgba(245,158,11,.55);
    box-shadow: 0 0 0 4px rgba(245,158,11,.12);
    margin-top: 1rem;
}
.journey-premium .jp-node__card {
    display: block;
    padding: 1.05rem 1.15rem;
    border: 1px solid var(--card-border, rgba(0,0,0,.07));
    border-radius: 1rem;
    background: var(--card-bg, #fff);
    text-decoration: none;
    color: inherit;
    box-shadow: 0 4px 16px rgba(0,0,0,.03);
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.journey-premium .jp-node__card:hover {
    border-color: rgba(245,158,11,.45);
    box-shadow: 0 14px 28px rgba(245,158,11,.12);
    transform: translateY(-2px);
    color: inherit;
}
.journey-premium .jp-node__label {
    font-size: 11px;
    font-weight: 750;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--jp-dark);
}
.journey-premium .jp-node__title {
    margin: .25rem 0 0;
    font-family: var(--font-head);
    font-size: 1rem;
    font-weight: 750;
    letter-spacing: -.015em;
    color: var(--text);
}
.journey-premium .jp-node__meta {
    margin-top: .55rem;
    font-size: .75rem;
    color: var(--text-3);
    font-weight: 600;
}
.journey-premium .jp-node__pct {
    font-family: var(--font-head);
    font-size: .95rem;
    font-weight: 800;
    color: var(--jp-dark);
}

.journey-premium .jp-item {
    border: 1px solid var(--card-border, rgba(0,0,0,.07));
    border-radius: 1rem;
    background: var(--card-bg, #fff);
    padding: 1.1rem 1.15rem;
    margin-bottom: .75rem;
    transition: border-color .2s ease, box-shadow .2s ease;
}
.journey-premium .jp-item:last-child { margin-bottom: 0; }
.journey-premium .jp-item:hover {
    border-color: rgba(245,158,11,.35);
    box-shadow: 0 10px 22px rgba(245,158,11,.08);
}
.journey-premium .jp-item__head {
    display: flex;
    align-items: flex-start;
    gap: .65rem;
    flex-wrap: wrap;
}
.journey-premium .jp-item__icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: .7rem;
    background: var(--jp-soft);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
}
.journey-premium .jp-item__title {
    margin: 0;
    font-family: var(--font-head);
    font-size: .95rem;
    font-weight: 750;
    color: var(--text);
    letter-spacing: -.01em;
}
.journey-premium .jp-item__desc {
    margin: .45rem 0 0;
    font-size: .82rem;
    line-height: 1.55;
    color: var(--text-2);
}
.journey-premium .jp-item__meta {
    margin-top: .55rem;
    font-size: .72rem;
    font-weight: 650;
    color: var(--text-3);
}
.journey-premium .jp-mcq-option {
    display: flex;
    align-items: center;
    gap: .65rem;
    width: 100%;
    text-align: left;
    padding: .75rem .9rem;
    margin-bottom: .45rem;
    border-radius: .75rem;
    border: 1px solid var(--card-border, rgba(0,0,0,.07));
    background: var(--bg-2, #fafafa);
    color: var(--text);
    font-size: .85rem;
    font-weight: 600;
    cursor: pointer;
    transition: border-color .15s ease, background .15s ease, transform .15s ease;
}
.journey-premium .jp-mcq-option:hover:not(:disabled) {
    border-color: rgba(245,158,11,.45);
    background: var(--jp-soft);
    transform: translateY(-1px);
}
.journey-premium .jp-mcq-option:disabled { cursor: default; opacity: .95; }
.journey-premium .jp-mcq-option.is-correct {
    border-color: rgba(34,197,94,.45);
    background: rgba(34,197,94,.1);
}
.journey-premium .jp-mcq-option.is-selected {
    border-color: rgba(245,158,11,.5);
    background: rgba(245,158,11,.12);
}
.journey-premium .jp-mcq-letter {
    width: 1.6rem;
    height: 1.6rem;
    border-radius: .45rem;
    background: #fff;
    border: 1px solid rgba(245,158,11,.25);
    color: var(--jp-dark);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .72rem;
    font-weight: 800;
    font-family: var(--font-head);
    flex-shrink: 0;
}
.journey-premium .jp-mcq-tag {
    margin-left: auto;
    font-size: 10px;
    font-weight: 750;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--jp-dark);
}
.journey-premium .jp-result {
    margin-top: .65rem;
    font-size: .8rem;
    font-weight: 650;
}
.journey-premium .jp-result--ok { color: #16a34a; }
.journey-premium .jp-result--bad { color: #ef4444; }
.journey-premium .jp-result--wait { color: var(--jp-dark); }
.journey-premium .jp-feedback {
    margin-top: .75rem;
    padding: .75rem .9rem;
    border-radius: .75rem;
    border: 1px solid rgba(245,158,11,.25);
    background: var(--jp-soft);
    font-size: .8rem;
    color: var(--text-2);
}
.journey-premium .jp-feedback strong {
    display: block;
    margin-bottom: .25rem;
    color: var(--text);
    font-size: .75rem;
}
.journey-premium .jp-submit-box {
    margin-top: .85rem;
    display: grid;
    gap: .65rem;
}
.journey-premium .jp-submit-box .form-input {
    border: 1px solid var(--card-border, rgba(0,0,0,.08));
    border-radius: .75rem;
    padding: .7rem .85rem;
    font-size: .85rem;
    background: var(--bg-2, #fafafa);
    color: var(--text);
}
.journey-premium .jp-submit-box .form-input:focus {
    outline: none;
    border-color: rgba(245,158,11,.55);
    box-shadow: 0 0 0 3px var(--jp-ring);
}
.journey-premium .jp-track-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: .75rem;
}
.journey-premium .jp-track-card {
    display: flex;
    flex-direction: column;
    gap: .45rem;
    padding: 1rem;
    border-radius: 1rem;
    border: 1px solid var(--card-border, rgba(0,0,0,.07));
    background: var(--card-bg, #fff);
    text-decoration: none;
    color: inherit;
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.journey-premium .jp-track-card:hover {
    border-color: rgba(245,158,11,.4);
    box-shadow: 0 12px 24px rgba(245,158,11,.1);
    transform: translateY(-2px);
    color: inherit;
}
.journey-premium .jp-track-card.is-active {
    border-color: rgba(245,158,11,.55);
    background: linear-gradient(160deg, rgba(245,158,11,.14), rgba(245,158,11,.04));
    box-shadow: 0 10px 22px rgba(245,158,11,.1);
}
</style>
