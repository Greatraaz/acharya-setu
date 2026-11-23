{{-- Shared premium Mentor / Expert / Mentee dashboard tokens (brand amber) --}}
<style>
.dash-page {
    --ln: var(--brand, #F59E0B);
    --ln-dark: var(--brand-dark, #D97706);
    --ln-soft: var(--brand-muted, rgba(245, 158, 11, .12));
    --ln-ring: rgba(245, 158, 11, .28);
    --ln-ink: var(--text);
    --ln-muted: var(--text-2);
    --ln-line: var(--card-border);
    --ln-surface: var(--card-bg);
    --ln-surface-2: var(--bg-2);
    font-family: var(--font-body);
}
.dash-page .learn-intro {
    position: relative;
    overflow: hidden;
    border: 1px solid var(--ln-line);
    border-radius: 1rem;
    background:
        radial-gradient(520px 180px at 100% 0%, rgba(245,158,11,.16), transparent 70%),
        radial-gradient(380px 160px at 0% 100%, rgba(245,158,11,.07), transparent 65%),
        var(--ln-surface);
    box-shadow: var(--shadow);
    padding: 1.25rem 1.35rem;
}
.dash-page .learn-intro::before {
    content: "";
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, var(--ln), var(--ln-dark));
}
.dash-page .learn-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--ln-dark);
    background: var(--ln-soft);
    padding: .35rem .7rem;
    border-radius: 8px;
    font-family: var(--font-head);
}
.dash-page .learn-intro h2 {
    margin: .7rem 0 .35rem;
    font-family: var(--font-head);
    font-size: clamp(1.25rem, 2.4vw, 1.7rem);
    font-weight: 800;
    letter-spacing: -.02em;
    color: var(--ln-ink);
    line-height: 1.2;
}
.dash-page .learn-intro p {
    margin: 0;
    max-width: 38rem;
    font-size: .875rem;
    line-height: 1.55;
    color: var(--ln-muted);
}
.dash-page .learn-stat {
    border: 1px solid var(--ln-line);
    border-radius: 1rem;
    background: var(--ln-surface);
    padding: 1rem 1.1rem;
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.dash-page .learn-stat:hover {
    border-color: rgba(245,158,11,.4);
    box-shadow: 0 10px 24px rgba(245,158,11,.1);
    transform: translateY(-1px);
}
.dash-page .learn-stat__value {
    font-family: var(--font-head);
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -.02em;
    color: var(--ln-ink);
    line-height: 1.1;
}
.dash-page .learn-stat__label {
    margin-top: .25rem;
    font-size: .75rem;
    font-weight: 600;
    color: var(--ln-muted);
}
.dash-page .learn-stat__icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--ln-soft);
    color: var(--ln-dark);
    flex-shrink: 0;
    font-size: .95rem;
}
.dash-page .learn-panel {
    background: var(--ln-surface);
    border: 1px solid var(--ln-line);
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(0,0,0,.04);
}
.dash-page .learn-btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .4rem;
    background: linear-gradient(135deg, var(--ln), var(--ln-dark));
    color: #fff !important;
    font-size: .8rem;
    font-weight: 700;
    padding: .6rem 1rem;
    border-radius: 8px;
    border: none;
    text-decoration: none;
    box-shadow: 0 10px 22px -8px var(--ln-ring);
    transition: filter .15s ease, transform .15s ease;
}
.dash-page .learn-btn-primary:hover { filter: brightness(1.05); transform: translateY(-1px); color: #fff !important; }
.dash-page .learn-btn-ghost {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    background: transparent;
    color: var(--text-2);
    font-size: .8rem;
    font-weight: 600;
    padding: .55rem .9rem;
    border-radius: 8px;
    border: 1px solid var(--ln-line);
    text-decoration: none;
    transition: border-color .15s ease, background .15s ease;
}
.dash-page .learn-btn-ghost:hover {
    border-color: rgba(245,158,11,.4);
    background: var(--ln-soft);
    color: var(--text);
}
.dash-page .dash-grid { display: grid; gap: .85rem; }
.dash-page .dash-grid-4 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
.dash-page .dash-grid-2 { grid-template-columns: 1fr; }
.dash-page .dash-grid-3 { grid-template-columns: 1fr; }
.dash-page .dash-grid-6 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
@media (min-width: 640px) {
    .dash-page .dash-grid-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .dash-page .dash-grid-6 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (min-width: 1100px) {
    .dash-page .dash-grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .dash-page .dash-grid-2 { grid-template-columns: 1.6fr 1fr; }
    .dash-page .dash-grid-3 { grid-template-columns: 1.7fr 1fr; }
    .dash-page .dash-grid-6 { grid-template-columns: repeat(6, minmax(0, 1fr)); }
}
.dash-page .fu { animation: dashFu .45s ease both; }
.dash-page .fu:nth-child(2){animation-delay:.05s}
.dash-page .fu:nth-child(3){animation-delay:.1s}
.dash-page .fu:nth-child(4){animation-delay:.15s}
@keyframes dashFu{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
.dash-page .chart-wrap { position: relative; width: 100%; min-height: 220px; }
.dash-page .chart-wrap canvas { max-width: 100%; }
.dash-page .growth-up { background: rgba(34,197,94,.12); color: #16a34a; }
.dash-page .growth-down { background: rgba(239,68,68,.12); color: #ef4444; }
.dash-page .growth-flat { background: var(--bg-2); color: var(--text-3); }
.dash-page .growth-pill {
    font-size: 10px;
    font-weight: 700;
    padding: .2rem .5rem;
    border-radius: 999px;
}
.dash-page .dash-section-title {
    font-family: var(--font-head);
    font-size: .875rem;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}
.dash-page .dash-section-sub {
    font-size: .75rem;
    color: var(--text-3);
    margin: .15rem 0 0;
}
.dash-page .dash-link {
    font-size: .75rem;
    font-weight: 700;
    color: var(--ln-dark);
    text-decoration: none;
}
.dash-page .dash-link:hover { text-decoration: underline; }
.dash-page .dash-row {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .85rem 1rem;
    border-bottom: 1px solid var(--ln-line);
}
.dash-page .dash-row:last-child { border-bottom: 0; }
.dash-page .dash-avatar {
    width: 2.15rem;
    height: 2.15rem;
    border-radius: .65rem;
    background: var(--ln-soft);
    border: 1px solid rgba(245,158,11,.2);
    color: var(--ln-dark);
    font-size: .7rem;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}
.dash-page .dash-avatar img { width: 100%; height: 100%; object-fit: cover; }
.dash-page .dash-status {
    display: inline-flex;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: .2rem .55rem;
    border-radius: 999px;
    border: 1px solid transparent;
}
.dash-page .dash-status.completed { background: rgba(34,197,94,.12); color: #16a34a; border-color: rgba(34,197,94,.2); }
.dash-page .dash-status.upcoming,
.dash-page .dash-status.confirmed { background: rgba(56,189,248,.12); color: #0ea5e9; border-color: rgba(56,189,248,.2); }
.dash-page .dash-status.pending { background: rgba(245,158,11,.12); color: var(--ln-dark); border-color: rgba(245,158,11,.25); }
.dash-page .dash-status.cancelled { background: rgba(239,68,68,.12); color: #ef4444; border-color: rgba(239,68,68,.2); }
.dash-page .qa-link {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .7rem .85rem;
    border-radius: .75rem;
    border: 1px solid var(--ln-line);
    background: var(--ln-surface);
    text-decoration: none;
    color: var(--text);
    transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
}
.dash-page .qa-link:hover {
    border-color: rgba(245,158,11,.4);
    box-shadow: 0 10px 22px rgba(245,158,11,.08);
    transform: translateY(-1px);
    color: var(--text);
}
.dash-page .revenue-card {
    position: relative;
    overflow: hidden;
    border-radius: 1rem;
    border: 1px solid rgba(245,158,11,.35);
    padding: 1rem 1.1rem;
    color: #fff;
    background: linear-gradient(135deg, #F59E0B, #D97706);
    box-shadow: 0 14px 28px rgba(245,158,11,.22);
}
.dash-page .revenue-card::after {
    content: "";
    position: absolute;
    right: -1.5rem;
    top: -1.5rem;
    width: 6rem;
    height: 6rem;
    border-radius: 999px;
    background: rgba(255,255,255,.12);
}
.dash-page .empty-block {
    text-align: center;
    padding: 1.75rem 1rem;
    color: var(--text-3);
    font-size: .85rem;
}
.dash-page .empty-block .icon { font-size: 1.6rem; margin-bottom: .4rem; }
.dash-page .progress-track {
    height: .4rem;
    border-radius: 999px;
    background: var(--bg-2);
    overflow: hidden;
}
.dash-page .progress-track > span {
    display: block;
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--ln), var(--ln-dark));
}
.dash-page .panel-pad { padding: 1.1rem 1.15rem; }
.dash-page .panel-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: 1rem;
}
.dash-page .mini-metric {
    border: 1px solid var(--ln-line);
    border-radius: .85rem;
    background: var(--ln-surface);
    padding: .85rem .95rem;
}
.dash-page .mini-metric__value {
    font-family: var(--font-head);
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--text);
}
.dash-page .mini-metric__label {
    font-size: .7rem;
    font-weight: 600;
    color: var(--text-3);
    margin-top: .15rem;
}
.dash-page .alert-premium {
    display: flex;
    gap: .75rem;
    align-items: flex-start;
    border: 1px solid rgba(245,158,11,.3);
    background: var(--ln-soft);
    border-radius: 1rem;
    padding: 1rem 1.1rem;
    color: var(--text);
}
.dash-page .alert-premium strong { display: block; margin-bottom: .2rem; }
.dash-page .alert-premium p { margin: 0; font-size: .85rem; color: var(--text-2); }
.dash-content:has(.dash-page) { max-width: 100%; }
</style>
