{{-- My Tasks module — rich amber premium chrome --}}
@include('frontend.partials.dash-premium-ui')
<style>
.tasks-premium {
    --tp: var(--brand, #F59E0B);
    --tp-dark: var(--brand-dark, #D97706);
    --tp-soft: var(--brand-muted, rgba(245,158,11,.12));
    --tp-ring: rgba(245,158,11,.28);
}
.tasks-premium .learn-search {
    flex-wrap: nowrap !important;
    align-items: center;
}
.tasks-premium .learn-search .session-search-field {
    flex: 1 1 auto;
    min-width: 180px;
    max-width: 280px;
}
.tasks-premium .learn-search .learn-btn-ghost,
.tasks-premium .learn-search button {
    flex: 0 0 auto;
    height: 40px;
    align-self: center;
}
.tasks-premium .tp-hero {
    position: relative;
    overflow: hidden;
    border-radius: 1.15rem;
    border: 1px solid rgba(245,158,11,.35);
    padding: 1.3rem 1.4rem;
    color: #fff;
    background:
        radial-gradient(420px 180px at 100% 0%, rgba(255,255,255,.18), transparent 60%),
        linear-gradient(135deg, #F59E0B 0%, #D97706 55%, #B45309 100%);
    box-shadow: 0 18px 36px rgba(245,158,11,.22);
}
.tasks-premium .tp-hero::after {
    content: "";
    position: absolute;
    right: -2rem;
    bottom: -2.5rem;
    width: 8.5rem;
    height: 8.5rem;
    border-radius: 999px;
    background: rgba(255,255,255,.1);
}
.tasks-premium .tp-hero__kicker {
    position: relative;
    z-index: 1;
    font-size: 11px;
    font-weight: 750;
    letter-spacing: .12em;
    text-transform: uppercase;
    opacity: .88;
}
.tasks-premium .tp-hero__title {
    position: relative;
    z-index: 1;
    margin: .35rem 0 .25rem;
    font-family: var(--font-head);
    font-size: clamp(1.3rem, 2.5vw, 1.75rem);
    font-weight: 800;
    letter-spacing: -.03em;
}
.tasks-premium .tp-hero__sub {
    position: relative;
    z-index: 1;
    margin: 0;
    font-size: .88rem;
    opacity: .92;
    max-width: 34rem;
    line-height: 1.5;
}
.tasks-premium .tp-card {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 1rem;
    align-items: start;
    padding: 1.15rem 1.2rem;
    border: 1px solid var(--card-border, rgba(0,0,0,.07));
    border-radius: 1.05rem;
    background: var(--card-bg, #fff);
    box-shadow: 0 4px 16px rgba(0,0,0,.03);
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.tasks-premium .tp-card:hover {
    border-color: rgba(245,158,11,.45);
    box-shadow: 0 14px 28px rgba(245,158,11,.12);
    transform: translateY(-2px);
}
.tasks-premium .tp-card__icon {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: .85rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(145deg, rgba(245,158,11,.18), rgba(245,158,11,.06));
    border: 1px solid rgba(245,158,11,.22);
    font-size: 1.2rem;
    flex-shrink: 0;
}
.tasks-premium .tp-card__title {
    margin: 0;
    font-family: var(--font-head);
    font-size: 1rem;
    font-weight: 750;
    letter-spacing: -.015em;
    color: var(--text);
}
.tasks-premium .tp-card__desc {
    margin: .4rem 0 0;
    font-size: .82rem;
    line-height: 1.55;
    color: var(--text-2);
}
.tasks-premium .tp-card__meta {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem .7rem;
    margin-top: .7rem;
    font-size: .72rem;
    font-weight: 650;
    color: var(--text-3);
}
.tasks-premium .tp-card__meta span {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
}
.tasks-premium .tp-due {
    color: var(--tp-dark) !important;
    font-weight: 750 !important;
}
.tasks-premium .tp-due.is-overdue {
    color: #ef4444 !important;
}
.tasks-premium .tp-detail-grid {
    display: grid;
    gap: 1rem;
}
@media (min-width: 900px) {
    .tasks-premium .tp-detail-grid {
        grid-template-columns: 1.55fr .95fr;
        align-items: start;
    }
}
.tasks-premium .tp-section-label {
    font-size: 11px;
    font-weight: 750;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--text-3);
    margin-bottom: .45rem;
}
.tasks-premium .tp-body-text {
    margin: 0;
    font-size: .9rem;
    line-height: 1.65;
    color: var(--text-2);
    white-space: pre-wrap;
}
.tasks-premium .tp-file {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem .75rem;
    border-radius: .7rem;
    border: 1px solid rgba(245,158,11,.25);
    background: var(--tp-soft);
    color: var(--tp-dark);
    font-size: .78rem;
    font-weight: 700;
    text-decoration: none;
}
.tasks-premium .tp-file:hover {
    background: rgba(245,158,11,.2);
    color: var(--tp-dark);
}
.tasks-premium .tp-submit .form-label {
    display: block;
    font-size: .78rem;
    font-weight: 700;
    color: var(--text-2);
    margin-bottom: .35rem;
}
.tasks-premium .tp-submit .form-input {
    width: 100%;
    border: 1px solid var(--card-border, rgba(0,0,0,.08));
    border-radius: .75rem;
    padding: .7rem .85rem;
    font-size: .85rem;
    background: var(--bg-2, #fafafa);
    color: var(--text);
}
.tasks-premium .tp-submit .form-input:focus {
    outline: none;
    border-color: rgba(245,158,11,.55);
    box-shadow: 0 0 0 3px var(--tp-ring);
}
.tasks-premium .tp-side-stat {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .75rem 0;
    border-bottom: 1px solid var(--card-border, rgba(0,0,0,.06));
    font-size: .82rem;
}
.tasks-premium .tp-side-stat:last-child { border-bottom: 0; }
.tasks-premium .tp-side-stat__label { color: var(--text-3); font-weight: 650; }
.tasks-premium .tp-side-stat__value { color: var(--text); font-weight: 750; text-align: right; }
@media (max-width: 720px) {
    .tasks-premium .tp-card {
        grid-template-columns: auto 1fr;
    }
    .tasks-premium .tp-card__cta {
        grid-column: 1 / -1;
    }
    .tasks-premium .tp-card__cta .learn-btn-primary,
    .tasks-premium .tp-card__cta .learn-btn-ghost {
        width: 100%;
    }
}
</style>
