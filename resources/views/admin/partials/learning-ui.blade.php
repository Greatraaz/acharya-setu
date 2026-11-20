{{-- Shared premium Admin / Learning UI tokens — primary amber (#F59E0B) --}}
<style>
.learn-page,
.admin-page {
    --ln: #F59E0B;
    --ln-dark: #D97706;
    --ln-soft: rgba(245, 158, 11, .12);
    --ln-ring: rgba(245, 158, 11, .28);
    --ln-ink: #0f172a;
    --ln-muted: #64748b;
    --ln-line: #e2e8f0;
    --ln-surface: #ffffff;
    --ln-surface-2: #f8fafc;
}
.learn-page .learn-intro,
.admin-page .learn-intro {
    position: relative;
    overflow: hidden;
    border: 1px solid var(--ln-line);
    border-radius: 1rem;
    background:
        radial-gradient(520px 180px at 100% 0%, rgba(245,158,11,.14), transparent 70%),
        radial-gradient(380px 160px at 0% 100%, rgba(245,158,11,.07), transparent 65%),
        var(--ln-surface);
    box-shadow: 0 10px 35px rgba(0,0,0,.05);
    padding: 1.25rem 1.35rem;
}
.learn-page .learn-intro::before,
.admin-page .learn-intro::before {
    content: "";
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, var(--ln), var(--ln-dark));
}
.learn-page .learn-eyebrow,
.admin-page .learn-eyebrow {
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
}
.learn-page .learn-intro h2,
.admin-page .learn-intro h2 {
    margin: .7rem 0 .35rem;
    font-size: clamp(1.25rem, 2.4vw, 1.65rem);
    font-weight: 800;
    letter-spacing: -.02em;
    color: var(--ln-ink);
    line-height: 1.2;
}
.learn-page .learn-intro p,
.admin-page .learn-intro p {
    margin: 0;
    max-width: 36rem;
    font-size: .875rem;
    line-height: 1.55;
    color: var(--ln-muted);
}
.learn-page .learn-stat,
.admin-page .learn-stat {
    border: 1px solid var(--ln-line);
    border-radius: 1rem;
    background: var(--ln-surface);
    padding: 1rem 1.1rem;
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.learn-page .learn-stat:hover,
.admin-page .learn-stat:hover {
    border-color: rgba(245,158,11,.35);
    box-shadow: 0 10px 24px rgba(245,158,11,.08);
    transform: translateY(-1px);
}
.learn-page .learn-stat__value,
.admin-page .learn-stat__value {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -.02em;
    color: var(--ln-ink);
    line-height: 1.1;
}
.learn-page .learn-stat__label,
.admin-page .learn-stat__label {
    margin-top: .2rem;
    font-size: .75rem;
    font-weight: 600;
    color: var(--ln-muted);
}
.learn-page .learn-stat__icon,
.admin-page .learn-stat__icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--ln-soft);
    color: var(--ln-dark);
    flex-shrink: 0;
}
.learn-page .learn-btn-primary,
.admin-page .learn-btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .4rem;
    background: linear-gradient(135deg, var(--ln), var(--ln-dark));
    color: #fff !important;
    font-size: .875rem;
    font-weight: 700;
    padding: .65rem 1.1rem;
    border-radius: 8px;
    border: none;
    box-shadow: 0 10px 22px -8px var(--ln-ring);
    transition: filter .15s ease, transform .15s ease;
}
.learn-page .learn-btn-primary:hover,
.admin-page .learn-btn-primary:hover {
    filter: brightness(1.05);
    transform: translateY(-1px);
}
.learn-page .learn-btn-ghost,
.admin-page .learn-btn-ghost {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    background: #fff;
    color: #334155;
    font-size: .8rem;
    font-weight: 600;
    padding: .6rem .9rem;
    border-radius: 8px;
    border: 1px solid var(--ln-line);
    transition: background .15s ease, border-color .15s ease;
}
.learn-page .learn-btn-ghost:hover,
.admin-page .learn-btn-ghost:hover {
    background: var(--ln-surface-2);
    border-color: rgba(245,158,11,.35);
}
.learn-page .learn-panel,
.admin-page .learn-panel {
    background: var(--ln-surface);
    border: 1px solid var(--ln-line);
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(15,23,42,.03);
}
.learn-page .learn-card,
.admin-page .learn-card {
    background: var(--ln-surface);
    border: 1px solid var(--ln-line);
    border-radius: 1rem;
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.learn-page .learn-card:hover,
.admin-page .learn-card:hover {
    border-color: rgba(245,158,11,.4);
    box-shadow: 0 14px 28px rgba(245,158,11,.1);
    transform: translateY(-2px);
}
.learn-page .learn-focus:focus,
.admin-page .learn-focus:focus {
    outline: none;
    border-color: var(--ln) !important;
    box-shadow: 0 0 0 3px var(--ln-soft) !important;
}
</style>
