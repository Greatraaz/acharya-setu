@extends('frontend.layouts.app')
@section('title', 'Find Mentors — Vedrix')

@section('content')
@auth
    @if(auth()->user()->role === 'mentee')
        @include('frontend.mentee.partials.bottom-nav')
    @endif
@endauth

<style>
/* ═══════════════════════════════════════════════════════════
   VEDRIX · MENTORS LISTING — PREMIUM UI (merged)
   Main color: var(--vj-primary)
   ═══════════════════════════════════════════════════════════ */
.mentors-browse {
    --mb-primary: var(--vj-primary);
    --mb-primary-soft: color-mix(in srgb, var(--vj-primary) 10%, transparent);
    --mb-primary-mid: color-mix(in srgb, var(--vj-primary) 22%, transparent);
    --mb-primary-dark: color-mix(in srgb, var(--vj-primary) 70%, #000);
    --mb-ink: #0f172a;
    --mb-muted: #64748b;
    --mb-border: rgba(15, 23, 42, .08);
    --mb-card: #ffffff;
    --mb-radius: 20px;
    --mb-shadow-sm: 0 2px 8px rgba(15, 23, 42, .05);
    --mb-shadow-md: 0 10px 30px rgba(15, 23, 42, .08);
    --mb-shadow-lg: 0 24px 60px -12px color-mix(in srgb, var(--vj-primary) 35%, transparent);
    --mb-ease: cubic-bezier(.22, .8, .3, 1);
    background:
        radial-gradient(900px 400px at 100% 0%, var(--mb-primary-soft), transparent 60%),
        #f8fafc;
    min-height: 100vh;
}

/* ───────── HERO ───────── */
.mentors-browse__hero {
    position: relative;
    overflow: hidden;
    padding: 60px 0;
    background-color: #0b1020;
    background-image:
        /* glow (top) */ radial-gradient(600px 300px at 12% 10%, color-mix(in srgb, var(--vj-primary) 45%, transparent), transparent 70%), radial-gradient(500px 300px at 90% 90%, color-mix(in srgb, var(--vj-primary) 30%, transparent), transparent 70%), /* dark + brand overlay */ linear-gradient(135deg, rgb(11 16 32 / 55%) 0%, color-mix(in srgb, color-mix(in srgb, var(--vj-primary) 28%, #0b1020e3) 80%, transparent) 100%), /* image (bottom) */ var(--hero-img, none);
        background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    color: #fff;
    text-align: center;
}
.mentors-browse__hero::before,
.mentors-browse__hero::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    background: var(--vj-primary);
    filter: blur(70px);
    opacity: .35;
    pointer-events: none;
    animation: mbFloat 12s ease-in-out infinite;
}
.mentors-browse__hero::before { width: 320px; height: 320px; top: -120px; left: -80px; }
.mentors-browse__hero::after  { width: 260px; height: 260px; bottom: -120px; right: -60px; animation-delay: -6s; }

@keyframes mbFloat {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50%      { transform: translate(30px, 20px) scale(1.1); }
}

.mentors-browse__hero .container {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}
.mentors-browse__hero .search-hero,
.mentors-browse__hero .chip-wrap { width: 100%; }

.mentors-browse__eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 18px;
    padding: 7px 16px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: #fff;
    background: rgba(255, 255, 255, .1);
    border: 1px solid rgba(255, 255, 255, .22);
    border-radius: 999px;
    backdrop-filter: blur(10px);
}
.mentors-browse__eyebrow::before {
    content: "";
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--vj-primary);
    box-shadow: 0 0 0 4px color-mix(in srgb, var(--vj-primary) 35%, transparent);
    animation: mbPulse 2s infinite;
}
@keyframes mbPulse {
    0%, 100% { box-shadow: 0 0 0 3px color-mix(in srgb, var(--vj-primary) 35%, transparent); }
    50%      { box-shadow: 0 0 0 8px transparent; }
}

.mentors-browse__title {
    width: 100%;
    max-width: 900px;
    margin: 0 auto 14px;
    font-size: clamp(30px, 5vw, 52px);
    font-weight: 800;
    line-height: 1.1;
    letter-spacing: -.02em;
    color: #fff;
    text-align: center !important;
}
.mentors-browse__lede {
    width: 100%;
    max-width: 620px;
    margin: 0 auto 28px;
    font-size: clamp(14px, 1.6vw, 17px);
    line-height: 1.6;
    color: #fff;
    text-align: center !important;
}
.mentors-browse__hero .lain {
    width: 64px;
    height: 4px;
    margin: 0 auto 28px !important;
    border-radius: 4px;
    background: var(--vj-primary);
    box-shadow: 0 0 18px color-mix(in srgb, var(--vj-primary) 70%, transparent);
}

/* ───────── TABS / CHIPS ───────── */
.mentors-browse .chip-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.mentors-browse__tabs { margin-bottom: 10px; }

.mentors-browse .chip {
        display: inline-flex;
    align-items: center;
    padding: 4px 18px;
    font-size: 13px;
    font-weight: 600;
    color: #fff;
    background: rgb(9 28 64);
    border: 1px solid rgba(255, 255, 255, .18);
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    backdrop-filter: blur(8px);
    transition: transform .25s var(--mb-ease), background .25s, box-shadow .25s, color .25s, border-color .25s;
}
.mentors-browse .chip:hover {
    transform: translateY(-3px);
    color: #fff;
    background: var(--vj-primary);
    border-color: rgba(255, 255, 255, .4);
    box-shadow: 0 10px 24px rgba(0, 0, 0, .25);
}
.mentors-browse .chip.selected {
    color: #fff;
    background: var(--vj-primary);
    border-color: var(--vj-primary);
    box-shadow: 0 10px 28px color-mix(in srgb, var(--vj-primary) 55%, transparent);
}

/* ───────── SEARCH BAR ───────── */
.mentors-browse__search, .mentors-browse .search-hero {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 auto 15px;
    padding: 7px 10px 7px 22px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 30px 60px -15px rgba(0, 0, 0, .45), 0 0 0 1px rgba(255, 255, 255, .6);
    transition: box-shadow .3s, transform .3s var(--mb-ease);
}
.mentors-browse .search-hero:focus-within {
    transform: translateY(-2px);
    box-shadow: 0 34px 70px -15px rgba(0, 0, 0, .5), 0 0 0 4px color-mix(in srgb, var(--vj-primary) 45%, transparent);
}
.mentors-browse .search-hero__icon {
    flex: 0 0 22px;
    width: 22px; height: 22px;
    color: var(--vj-primary);
}
.mentors-browse .search-hero input[type="text"] {
    flex: 1;
    min-width: 0;
    height: 38px;
    font-size: 15px;
    color: var(--mb-ink);
    background: transparent;
    border: 0;
    outline: 0;
    box-shadow: none;
}
.mentors-browse .search-hero input::placeholder { color: #94a3b8; }

.mentors-browse .search-filters {
    display: flex;
    align-items: center;
    gap: 8px;
}
.mentors-browse .search-filter-btn {
    height: 38px;
    padding: 0 14px;
    font-size: 13px;
    font-weight: 600;
    color: var(--mb-ink);
    background: #f1f5f9;
    border: 1px solid #ffa5026e;
    border-radius: 8px;
    cursor: pointer;
    outline: 0;
    transition: background .2s, border-color .2s, box-shadow .2s;
}
.mentors-browse .search-filter-btn:hover { background: var(--mb-primary-soft); }
.mentors-browse .search-filter-btn:focus {
    border-color: var(--vj-primary);
    box-shadow: 0 0 0 3px var(--mb-primary-mid);
}

/* ───────── BUTTONS ───────── */
.mentors-browse .btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-weight: 700;
    border-radius: 12px;
    cursor: pointer;
    text-decoration: none;
    transition: transform .25s var(--mb-ease), box-shadow .25s, background .25s, color .25s, border-color .25s;
}
.mentors-browse .btn-primary {
    color: #fff;
    background: linear-gradient(135deg, var(--vj-primary), var(--mb-primary-dark));
    border: 0;
    box-shadow: 0 8px 20px color-mix(in srgb, var(--vj-primary) 40%, transparent);
}
.mentors-browse .btn-primary:hover,
.modal-overlay .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 30px color-mix(in srgb, var(--vj-primary) 55%, transparent);
    filter: brightness(1.06);
}
.mentors-browse .btn-primary:active { transform: translateY(0); }
.mentors-browse .search-filters .btn-primary { height: 38px; padding: 0 26px; }

.mentors-browse .btn-outline {
    color: var(--vj-primary);
    background: transparent;
    border: 1.5px solid var(--vj-primary);
}
.mentors-browse .btn-outline:hover {
    color: #fff;
    background: var(--vj-primary);
    transform: translateY(-2px);
    box-shadow: 0 10px 22px color-mix(in srgb, var(--vj-primary) 35%, transparent);
}
.mentors-browse .btn-ghost {
    color: var(--mb-muted);
    background: transparent;
    border: 0;
}
.mentors-browse .btn-ghost:hover { color: var(--vj-primary); background: var(--mb-primary-soft); }

/* ───────── BODY LAYOUT ───────── */
.mentors-browse__body { margin-top: -48px; position: relative; z-index: 2; padding-bottom: 80px; }
.mentors-browse .search-layout {
    display: grid;
    grid-template-columns: 290px 1fr;
    gap: 20px;
    align-items: start;
}
.mentors-browse .search-filter-toggle {
    display: none;
    grid-column: 1 / -1;
    padding: 12px 18px;
    font-weight: 700;
    color: var(--vj-primary);
    background: #fff;
    border: 1.5px solid var(--vj-primary);
    border-radius: 14px;
    box-shadow: var(--mb-shadow-sm);
    cursor: pointer;
}

/* ───────── FILTER SIDEBAR ───────── */
.mentors-browse .filter-sidebar {
    position: sticky;
    top: 90px;
    max-height: calc(100vh - 110px);
    overflow-y: auto;
    padding: 8px 20px 20px;
    background: var(--mb-card);
    border: 1px solid var(--mb-border);
    border-radius: var(--mb-radius);
    box-shadow: var(--mb-shadow-md);
    scrollbar-width: thin;
    scrollbar-color: var(--mb-primary-mid) transparent;
}
.mentors-browse__filter-head {
    position: sticky;
    top: 0;
    z-index: 3;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 0 -20px 6px;
    padding: 18px 20px 14px;
    font-size: 17px;
    font-weight: 800;
    color: var(--mb-ink);
    background: linear-gradient(#fff 80%, rgba(255, 255, 255, 0));
}
.mentors-browse__filter-head span { display: inline-flex; align-items: center; gap: 8px; }
.mentors-browse__filter-head span::before {
    content: "";
    width: 4px; height: 18px;
    border-radius: 4px;
    background: var(--vj-primary);
}
.mentors-browse .filter-section {
    padding: 16px 0;
    border-top: 1px dashed var(--mb-border);
}
.mentors-browse .filter-section-title {
    margin-bottom: 10px;
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--mb-muted);
}
.mentors-browse .filter-option {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 2px -8px;
    padding: 8px 10px;
    font-size: 14px;
    font-weight: 500;
    color: #334155;
    border-radius: 10px;
    cursor: pointer;
    transition: background .2s, color .2s, transform .2s var(--mb-ease);
}
.mentors-browse .filter-option:hover {
    color: var(--vj-primary);
    background: var(--mb-primary-soft);
    transform: translateX(3px);
}
.mentors-browse .filter-option input {
    width: 17px; height: 17px;
    accent-color: var(--vj-primary);
    cursor: pointer;
}

/* ───────── RESULTS HEADER ───────── */
.mentors-browse .search-results-header {
        display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin: 18px 0;
    padding: 5px 20px;
    background: var(--mb-card);
    border: 1px solid var(--mb-border);
    border-radius: 16px;
    box-shadow: var(--mb-shadow-sm);
}
.mentors-browse__count { font-size: 14px; color: var(--mb-muted); }
.mentors-browse__count strong { color: var(--vj-primary); font-weight: 800; }
.mentors-browse .search-sort-select {
    height: 36px;
    padding: 0 14px;
    font-size: 13px;
    font-weight: 600;
    color: var(--mb-ink);
    background: #f1f5f9;
    border: 1px solid #f7d28f;
    border-radius: 8px;
    outline: 0;
    cursor: pointer;
    transition: border-color .2s, box-shadow .2s;
}
.mentors-browse .search-sort-select:focus {
    border-color: var(--vj-primary);
    box-shadow: 0 0 0 3px var(--mb-primary-mid);
}

/* ───────── MENTOR GRID ───────── */
.mentors-browse__grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 15px;
}

/* ───────── MENTOR CARD ───────── */
.mentors-browse .mentor-card {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 0;
    padding: 0;
    overflow: hidden;
    background: #fff;
    border: 1px solid rgba(15, 23, 42, .06);
    border-radius: 12px;
    box-shadow:
        0 1px 2px rgba(15, 23, 42, .04),
        0 8px 16px -4px rgba(15, 23, 42, .08),
        0 20px 40px -12px rgba(15, 23, 42, .12);
    opacity: 0;
    animation: mbRise .6s var(--mb-ease) forwards;
    transition: transform .4s var(--mb-ease), box-shadow .4s, border-color .4s;
}
.mentors-browse .mentor-card:nth-child(1) { animation-delay: .03s; }
.mentors-browse .mentor-card:nth-child(2) { animation-delay: .08s; }
.mentors-browse .mentor-card:nth-child(3) { animation-delay: .13s; }
.mentors-browse .mentor-card:nth-child(4) { animation-delay: .18s; }
.mentors-browse .mentor-card:nth-child(5) { animation-delay: .23s; }
.mentors-browse .mentor-card:nth-child(6) { animation-delay: .28s; }
.mentors-browse .mentor-card:nth-child(n+7) { animation-delay: .32s; }
@keyframes mbRise {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
}
.mentors-browse .mentor-card:hover {
    transform: translateY(-8px);
    border-color: var(--mb-primary-mid);
    box-shadow:
        0 2px 4px rgba(15, 23, 42, .05),
        0 16px 32px -6px color-mix(in srgb, var(--vj-primary) 28%, transparent),
        0 30px 60px -14px color-mix(in srgb, var(--vj-primary) 38%, transparent);
}

/* head: image + name */
.mentors-browse .mentor-card-head {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 0;
}
.mentors-browse .mentor-avatar-lg {
    position: relative;
    width: 100%;
    height: 221px;
    overflow: visible;
    font-size: 64px;
    font-weight: 800;
    letter-spacing: -.02em;
    color: #fff;
    text-shadow: 0 6px 24px rgba(0, 0, 0, .25);
    border: 0;
    border-radius: 0;
    box-shadow: none;
}
.mentors-browse .mentor-avatar-lg img {
    display: block;
    width: 100%;border-radius: 0 !important;
    height: 100%;
    object-fit: cover;
    transition: transform .7s var(--mb-ease);
}

/* role badge on image (bottom center) */
.mentors-browse .mentor-role-badge {
    position: absolute;
    left: 50%;
    bottom: -12px;
    z-index: 2;
    transform: translateX(-50%);
    display: block;
    max-width: calc(100% - 24px);
    padding: 6px 14px;
    overflow: hidden;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.3;
    letter-spacing: .01em;
    text-align: center;
    text-shadow: none;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #fff;
    background: var(--vj-primary);
    border: 1px solid rgb(254 171 28);
    border-radius: 8px;
    transition: background .3s, transform .4s var(--mb-ease), border-color .3s;
}
.mentors-browse .mentor-card:hover .mentor-role-badge {
    transform: translateX(-50%) translateY(-3px);
}

/* name / role / bio */
.mentors-browse .mentor-card-info {
    min-width: 0;
    padding: 15px 0 12px;
}
.mentors-browse .mentor-card-name {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    font-size: 18px;
    font-weight: 800;
    line-height: 1.3;
    letter-spacing: -.01em;
    color: var(--mb-ink);
}
.mentors-browse .mentor-card-role {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-top: 4px;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.45;
    color: var(--mb-primary-dark);
}
.mentors-browse .mentor-card-bio {
    position: relative;
    z-index: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: calc(13.5px * 1.6 * 3 + 12px);
    margin: 12px 20px 0;
    padding: 12px 0 0;
    border-top: 1px solid rgba(15, 23, 42, .07);
    font-size: 13.5px;
    line-height: 1.6;
    color: #334155;
}

/* tags */
.mentors-browse .mentor-tags {
    position: relative;
    z-index: 1;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;    border-image: linear-gradient(to right, #ffa55e, #ffa55ea6, transparent) 1;
    border-bottom: 1px solid;
    padding-bottom: 10px;
}
.mentors-browse .tag {
    padding: 1px 10px;
    font-size: 11.5px;
    font-weight: 700;
    line-height: 23px;
    color: var(--mb-primary-dark);
    background: var(--mb-primary-soft);
    border: 1px solid var(--mb-primary-mid);
    border-radius: 6px;
    transition: background .25s, color .25s, border-color .25s, transform .25s;
}
.mentors-browse .tag:hover { color: #fff; background: var(--vj-primary); transform: translateY(-1px); }
/* NOTE: colour/background/size of "Expert" tag were already overridden by .tag in the
   original cascade; only these 3 properties were actually taking effect. */
.mentors-browse .tag-brand {
    letter-spacing: .08em;
    text-transform: uppercase;
    box-shadow: 0 4px 10px color-mix(in srgb, var(--vj-primary) 40%, transparent);
}

/* price + rating strip */
.mentors-browse .mentor-card-meta {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 4px 9px;
    background: #fff;
    border: 1px solid var(--mb-primary-mid);
    border-radius: 6px;
    box-shadow: 0 4px 12px -4px color-mix(in srgb, var(--vj-primary) 25%, transparent);
}
.mentors-browse .mentor-rate {
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -.01em;
    color: var(--vj-primary);
}
.mentors-browse .mentor-rating {
    padding: 4px 9px;
    font-size: 12.5px;
    font-weight: 700;
    color: #0f172a;
    background: #f1f5f9;
    border-radius: 6px;
    white-space: nowrap;
}

/* actions */
.mentors-browse .mentor-card-actions {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    padding: 5px 0 15px;
}
.mentors-browse .mentor-card-actions .btn {
    padding: 11px 6px;
    font-size: 13px;
    white-space: nowrap;
    border-radius: 10px;
}

/* ───────── EMPTY STATE ───────── */
.mentors-browse__empty {
    grid-column: 1 / -1;
    padding: 70px 24px;
    text-align: center;
    background: var(--mb-card);
    border: 2px dashed var(--mb-primary-mid);
    border-radius: var(--mb-radius);
}
.mentors-browse__empty .empty-state-icon {
    width: 84px; height: 84px;
    display: grid;
    place-items: center;
    margin: 0 auto 16px;
    font-size: 38px;
    background: var(--mb-primary-soft);
    border-radius: 50%;
}
.mentors-browse__empty h3 { margin: 0 0 6px; font-size: 20px; color: var(--mb-ink); }
.mentors-browse__empty p  { margin: 0; color: var(--mb-muted); }

/* ───────── PAGINATION ───────── */
.mentors-browse__pagination {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
    margin-top: 36px;
}
.mentors-browse__pagination a,
.mentors-browse__pagination button,
.mentors-browse__pagination .page-btn {
    min-width: 42px; height: 42px;
    padding: 0 14px;
    font-weight: 700;
    color: var(--mb-ink);
    background: #fff;
    border: 1px solid var(--mb-border);
    border-radius: 12px;
    cursor: pointer;
    box-shadow: var(--mb-shadow-sm);
    transition: transform .2s var(--mb-ease), background .2s, color .2s, box-shadow .2s;
}
.mentors-browse__pagination a:hover,
.mentors-browse__pagination button:hover,
.mentors-browse__pagination .page-btn:hover {
    color: var(--vj-primary);
    transform: translateY(-2px);
    box-shadow: var(--mb-shadow-md);
}
.mentors-browse__pagination .active,
.mentors-browse__pagination [aria-current="page"] {
    color: #fff;
    background: var(--vj-primary);
    border-color: var(--vj-primary);
    box-shadow: 0 8px 20px color-mix(in srgb, var(--vj-primary) 45%, transparent);
}

/* ───────── BOOKING MODAL ───────── */
#booking-modal.modal-overlay { backdrop-filter: blur(6px); }
#booking-modal .booking-modal {
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 32px);   /* screen se bahar nahi jayega */
    overflow: hidden;                 /* rounded corners ke liye rakha */
    border-radius: 24px;
    box-shadow: 0 40px 100px rgba(15, 23, 42, .4);
}
#booking-modal .modal-header { flex: 0 0 auto; }   /* header fixed rahega */
#booking-modal .booking-modal__body {
    flex: 1 1 auto;
    min-height: 0;                    /* flex child ko scroll karne ke liye zaroori */
    overflow-y: auto;                 /* scroll yahan hoga */
    overscroll-behavior: contain;
    padding: 20px 24px 24px;          /* agar tumhare global .modal-body mein padding hai to ye line hata do */
    scrollbar-width: thin;
    scrollbar-color: var(--mb-primary-mid) transparent;
}
#booking-modal .modal-header {
    padding: 18px 24px;
    color: #fff;
    background: linear-gradient(135deg, #0b1020, color-mix(in srgb, var(--vj-primary) 40%, #0b1020));
    border-bottom: 0;
}
#booking-modal .modal-title { font-size: 18px; font-weight: 800; color: #fff; }
#booking-modal .modal-close {
    width: 36px; height: 36px;
    color: #fff;
    background: rgba(255, 255, 255, .12);
    border: 0;
    border-radius: 50%;
    cursor: pointer;
    transition: background .2s, transform .3s var(--mb-ease);
}
#booking-modal .modal-close:hover { background: var(--vj-primary); transform: rotate(90deg); }

#booking-modal .booking-modal__layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 22px;
}
#booking-modal .booking-flow { display: grid; gap: 14px; }
#booking-modal .booking-card {
    padding: 18px;
    background: #fff;
    border: 1px solid var(--mb-border);
    border-radius: 16px;
    box-shadow: var(--mb-shadow-sm);
    transition: box-shadow .3s, border-color .3s;
}
#booking-modal .booking-card:hover { border-color: var(--mb-primary-mid); box-shadow: var(--mb-shadow-md); }
#booking-modal .label-caps {
    margin: 0 0 12px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--vj-primary);
}
#booking-modal .booking-cal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}
#booking-modal .booking-cal-head__label { margin: 0; }
#booking-modal .booking-cal-nav { display: flex; align-items: center; gap: 8px; }

#booking-modal .duration-btns { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
#booking-modal .duration-btn {
    padding: 12px 0;
    text-align: center;
    font-weight: 700;
    color: var(--mb-ink);
    background: #f1f5f9;
    border: 1.5px solid transparent;
    border-radius: 12px;
    cursor: pointer;
    transition: transform .2s var(--mb-ease), background .2s, color .2s, box-shadow .2s;
}
#booking-modal .duration-btn:hover { color: var(--vj-primary); background: var(--mb-primary-soft); transform: translateY(-2px); }
#booking-modal .duration-btn.selected {
    color: #fff;
    background: var(--vj-primary);
    box-shadow: 0 8px 18px color-mix(in srgb, var(--vj-primary) 45%, transparent);
}

#booking-modal .form-input {
    width: 100%;
    padding: 12px 14px;
    font-size: 14px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    outline: 0;
    transition: border-color .2s, box-shadow .2s, background .2s;
}
#booking-modal .form-input:focus {
    background: #fff;
    border-color: var(--vj-primary);
    box-shadow: 0 0 0 4px var(--mb-primary-soft);
}

#booking-modal .booking-modal__summary {
    position: sticky;
    top: 0;
    align-self: start;
    padding: 20px;
    background: linear-gradient(160deg, var(--mb-primary-soft), #fff 60%);
    border: 1px solid var(--mb-primary-mid);
    border-radius: 18px;
    box-shadow: var(--mb-shadow-md);
}
#booking-modal .booking-summary-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 10px 0;
    font-size: 13.5px;
    color: var(--mb-muted);
    border-bottom: 1px dashed var(--mb-border);
}
#booking-modal .booking-summary-row strong { color: var(--mb-ink); text-align: right; }
#booking-modal .booking-summary-row--discount strong { color: #16a34a; }
#booking-modal .booking-summary-row--total {
    margin-top: 6px;
    padding-top: 14px;
    border-bottom: 0;
    font-size: 15px;
    font-weight: 700;
}
#booking-modal .booking-summary-row--total strong { font-size: 22px; font-weight: 800; color: var(--vj-primary); }

#booking-modal .booking-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 20px;
    padding-top: 18px;
    border-top: 1px solid var(--mb-border);
}
#booking-modal .btn-lg { padding: 14px 28px; font-size: 15px;color: #fff; }

/* ───────── RESPONSIVE ───────── */
@media (max-width: 1199px) {
    .mentors-browse__grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 991px) {
    .mentors-browse .search-layout { grid-template-columns: 1fr; }
    .mentors-browse .search-filter-toggle { display: block; }
    .mentors-browse .filter-sidebar { position: static; max-height: none; }
    #booking-modal .booking-modal__layout { grid-template-columns: 1fr; }
    #booking-modal .booking-modal__summary { position: static; }
}
@media (max-width: 767px) {
    .mentors-browse__hero { padding: 52px 0; }
    .mentors-browse .search-hero { flex-wrap: wrap; padding: 14px; border-radius: 20px; }
    .mentors-browse .search-hero__icon { display: none; }
    .mentors-browse .search-hero input[type="text"] { flex: 1 1 100%; background: #f1f5f9; padding: 0 16px; border-radius: 12px; }
    .mentors-browse .search-filters { flex: 1 1 100%; flex-wrap: wrap; }
    .mentors-browse .search-filter-btn { flex: 1; }
    .mentors-browse .search-filters .btn-primary { flex: 1 1 100%; }
    .mentors-browse .search-results-header { flex-direction: column; align-items: stretch; }
    .mentors-browse__grid { grid-template-columns: 1fr; }
    #booking-modal .booking-modal__footer { flex-direction: column-reverse; }
    #booking-modal .booking-modal__footer .btn { width: 100%; }
}
@media (prefers-reduced-motion: reduce) {
    .mentors-browse *, .mentors-browse *::before, .mentors-browse *::after { animation: none !important; transition: none !important; }
    .mentors-browse .mentor-card { opacity: 1; }
}
.px-20{padding-left: 20px; padding-right: 20px;}
</style>

<div class="mentors-browse">

    {{-- Search Header --}}
    <section class="mentors-browse__hero" style="--hero-img: url('https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=2000&q=80')">
        <div class="container">
            <p class="mentors-browse__eyebrow">Vedrix Mentor Network</p>
            <h1 class="mentors-browse__title">{{ \App\Support\MentorBrowseQuery::listingTitle(request()) }}</h1>
            <div class="lain"></div>
            <p class="mentors-browse__lede">Browse verified mentors and experts, then refine by domain, format, and availability</p>
        </div>
    </section>

    {{-- Main Content --}}
    <div class="mentors-browse__body">
        <div class="container">
            <div class="search-layout">

                <button type="button" class="search-filter-toggle" id="searchFilterToggle">Show filters</button>

                {{-- FILTERS SIDEBAR --}}
                <aside class="filter-sidebar">
                    <div class="mentors-browse__filter-head">
                        <span>Filters</span>
                        <button type="button" class="btn btn-ghost btn-sm" onclick="clearAllFilters()">Clear all</button>
                    </div>

                    {{-- Domain --}}
                    <div class="filter-section">
                        <div class="filter-section-title">Domain</div>
                        @foreach(\App\Support\MentorBrowseQuery::domainOptions() as [$val, $label])
                        <label class="filter-option">
                            <input type="checkbox" data-filter="domain" value="{{ $val }}" @checked(in_array($val, (array) request('domain', []), true))>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>

                    {{-- Price --}}
                    <div class="filter-section">
                        <div class="filter-section-title">Price (₹/min)</div>
                        <label class="filter-option"><input type="radio" data-filter="rate_range" name="rate_range" value="" @checked(!request()->filled('rate_range'))> Any</label>
                        <label class="filter-option"><input type="radio" data-filter="rate_range" name="rate_range" value="0-10" @checked(request('rate_range') === '0-10')> Under ₹10</label>
                        <label class="filter-option"><input type="radio" data-filter="rate_range" name="rate_range" value="10-20" @checked(request('rate_range') === '10-20')> ₹10–₹20</label>
                        <label class="filter-option"><input type="radio" data-filter="rate_range" name="rate_range" value="20-50" @checked(request('rate_range') === '20-50')> ₹20–₹50</label>
                        <label class="filter-option"><input type="radio" data-filter="rate_range" name="rate_range" value="50+" @checked(request('rate_range') === '50+')> ₹50+</label>
                    </div>

                    {{-- Rating --}}
                    <div class="filter-section">
                        <div class="filter-section-title">Minimum Rating</div>
                        <label class="filter-option"><input type="radio" data-filter="min_rating" name="min_rating" value="" @checked(!request()->filled('min_rating'))> Any</label>
                        <label class="filter-option"><input type="radio" data-filter="min_rating" name="min_rating" value="4.5" @checked((string) request('min_rating') === '4.5')> ⭐ 4.5+</label>
                        <label class="filter-option"><input type="radio" data-filter="min_rating" name="min_rating" value="4" @checked((string) request('min_rating') === '4')> ⭐ 4.0+</label>
                    </div>

                    {{-- Experience --}}
                    <div class="filter-section">
                        <div class="filter-section-title">Experience</div>
                        @php $expSelected = (array) request('exp', []); @endphp
                        <label class="filter-option"><input type="checkbox" data-filter="exp" value="1-3" @checked(in_array('1-3', $expSelected, true))> 1–3 years</label>
                        <label class="filter-option"><input type="checkbox" data-filter="exp" value="3-7" @checked(in_array('3-7', $expSelected, true))> 3–7 years</label>
                        <label class="filter-option"><input type="checkbox" data-filter="exp" value="7-15" @checked(in_array('7-15', $expSelected, true))> 7–15 years</label>
                        <label class="filter-option"><input type="checkbox" data-filter="exp" value="15+" @checked(in_array('15+', $expSelected, true))> 15+ years</label>
                    </div>

                    {{-- Meeting duration --}}
                    <div class="filter-section">
                        <div class="filter-section-title">Meeting duration</div>
                        <label class="filter-option"><input type="radio" data-filter="duration" name="duration" value="" @checked(!request()->filled('duration'))> Any</label>
                        @foreach(\App\Models\ConsultationSession::BOOKING_DURATIONS as $minutes)
                        <label class="filter-option"><input type="radio" data-filter="duration" name="duration" value="{{ $minutes }}" @checked((string) request('duration') === (string) $minutes)> {{ $minutes }} minutes</label>
                        @endforeach
                    </div>

                    {{-- Mentorship model / session type hidden — session modes are unused --}}

                    {{-- Availability --}}
                    <div class="filter-section">
                        <div class="filter-section-title">Availability</div>
                        @php $availabilitySelected = (array) request('availability', []); @endphp
                        <label class="filter-option"><input type="checkbox" data-filter="availability" value="today" @checked(in_array('today', $availabilitySelected, true))> Available Today</label>
                        <label class="filter-option"><input type="checkbox" data-filter="availability" value="weekend" @checked(in_array('weekend', $availabilitySelected, true))> Weekends</label>
                        <label class="filter-option"><input type="checkbox" data-filter="availability" value="evening" @checked(in_array('evening', $availabilitySelected, true))> Evenings (6–10 PM)</label>
                    </div>
                </aside>

                {{-- RESULTS --}}
                <div class="mentors-browse__results">
                    <div class="chip-wrap mentors-browse__tabs">
                        @foreach(['' => 'All', 'mentor' => 'Mentors', 'expert' => 'Experts'] as $val => $label)
                        <a href="{{ route('mentors.search', array_filter(array_merge(request()->except(['page']), ['type' => $val ?: null]))) }}"
                           class="chip {{ request('type', '') === $val ? 'selected' : '' }}">{{ $label }}</a>
                        @endforeach
                    </div>

                    {{-- Search bar --}}
                    <div class="search-hero mentors-browse__search">
                        <svg class="search-hero__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                            <path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <input type="text" id="mentor-search-input" value="{{ request('q') }}" placeholder="Search by name, skill, company…">
                        <div class="search-filters">
                            <select data-filter="experience" class="search-filter-btn">
                                <option value="">Any Experience</option>
                                <option value="1-3" @selected(request('exp') === '1-3' || request('experience') === '1-3')>1–3 yrs</option>
                                <option value="3-7" @selected(request('exp') === '3-7' || request('experience') === '3-7')>3–7 yrs</option>
                                <option value="7+" @selected(request('exp') === '7+' || request('experience') === '7+')>7+ yrs</option>
                            </select>
                            <select data-filter="rate_max" class="search-filter-btn">
                                <option value="">Any Price</option>
                                <option value="10" @selected((string) request('rate_max') === '10')>Under ₹10/min</option>
                                <option value="20" @selected((string) request('rate_max') === '20')>Under ₹20/min</option>
                                <option value="50" @selected((string) request('rate_max') === '50')>Under ₹50/min</option>
                            </select>
                            <button class="btn btn-primary" onclick="MentorSearch.submit()">Search</button>
                        </div>
                    </div>

                    {{-- Quick chips from mentor fields in DB --}}
                    <div class="chip-wrap mentors-browse__fields" id="mentor-field-chips">
                        @foreach($mentorFields ?? [] as $chip)
                        <button type="button"
                                class="chip {{ request('field') === $chip ? 'selected' : '' }}"
                                data-field-chip="{{ $chip }}"
                                aria-pressed="{{ request('field') === $chip ? 'true' : 'false' }}">{{ $chip }}</button>
                        @endforeach
                    </div>
                    {{-- Results header --}}
                    <div class="search-results-header">
                        <div class="mentors-browse__count" id="mentor-count-wrap">
                            @if(($mentors->total() ?? 0) > 0)
                                Showing <strong id="mentor-count-range">{{ $mentors->firstItem() }}–{{ $mentors->lastItem() }}</strong>
                                of <strong id="mentor-count">{{ $mentors->total() }}</strong> mentors
                            @else
                                Showing <strong id="mentor-count">0</strong> mentors
                            @endif
                        </div>
                        <select data-sort-select class="search-sort-select">
                            <option value="best" @selected(request('sort', 'best') === 'best')>Best Match</option>
                            <option value="rating" @selected(request('sort') === 'rating')>Highest Rated</option>
                            <option value="rate_asc" @selected(request('sort') === 'rate_asc')>Lowest Price</option>
                            <option value="rate_desc" @selected(request('sort') === 'rate_desc')>Highest Price</option>
                            <option value="sessions" @selected(request('sort') === 'sessions')>Most Sessions</option>
                        </select>
                    </div>

                    {{-- MENTOR GRID --}}
                    <div id="mentors-grid" class="mentors-browse__grid">
                        {{-- Initial load from server --}}
                        @forelse($mentors ?? [] as $mentor)
                        <article class="mentor-card">
                            <div class="mentor-card-head">
                                <div class="mentor-avatar-lg">
                                    @if($mentor->avatar_url)<img src="{{ $mentor->avatar_url }}" alt="{{ $mentor->name }}">
                                    @else{{ strtoupper(substr($mentor->name,0,1)) }}@endif
                                    @if($mentor->designation || $mentor->company)
                                    <span class="mentor-role-badge">{{ $mentor->designation }}{{ $mentor->company ? ' · '.$mentor->company : '' }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="px-20">
                                <div class="mentor-card-info">
                                    <div class="mentor-card-name">
                                        {{ $mentor->name }}
                                        @if($mentor->isExpert())
                                        <span class="tag tag-brand">Expert</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mentor-tags">
                                    @foreach(array_slice($mentor->expertise ?? [],0,4) as $tag)
                                    <span class="tag">{{ $tag }}</span>
                                    @endforeach
                                </div>
                                <div class="mentor-card-meta">
                                    <span class="mentor-rate">₹{{ $mentor->rate_per_minute }}/min</span>
                                    <span class="mentor-rating">⭐ {{ number_format($mentor->rating,1) }} ({{ $mentor->total_sessions }})</span>
                                </div>
                                <div class="mentor-card-actions">
                                    <a href="{{ $mentor->profile_url }}" class="btn btn-outline btn-sm">View Profile</a>
                                    <button class="btn btn-primary btn-sm" onclick="openBookingModal({{ $mentor->id }},'{{ addslashes($mentor->name) }}',{{ $mentor->rate_per_minute }})">Book Session</button>
                                </div>
                            </div>
                        </article>
                        @empty
                        <div class="empty-state mentors-browse__empty">
                            <div class="empty-state-icon">🔍</div>
                            <h3>No mentors found</h3>
                            <p>Try adjusting your filters or search term.</p>
                        </div>
                        @endforelse
                    </div>

                    {{-- Pagination --}}
                    <div id="pagination-wrap" class="pagination mentors-browse__pagination"
                         data-page="{{ $mentors->currentPage() }}"
                         data-current-page="{{ $mentors->currentPage() }}"
                         data-last-page="{{ $mentors->lastPage() }}"
                         data-total="{{ $mentors->total() }}"
                         data-from="{{ $mentors->firstItem() ?? 0 }}"
                         data-to="{{ $mentors->lastItem() ?? 0 }}"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════ BOOKING MODAL ═══════════════════ --}}
<div id="booking-modal" class="modal-overlay">
    <div class="modal modal-lg booking-modal">
        <div class="modal-header">
            <span class="modal-title" id="booking-mentor-name">Book a Session</span>
            <button class="modal-close" onclick="closeModal('booking-modal')">✕</button>
        </div>
        <div class="modal-body booking-modal__body">
            <div class="booking-modal__layout">
                <div class="booking-flow">
                    <div class="booking-card">
                        <p class="label-caps booking-card__label">Mentor Availability</p>
                        <div id="availabilitySummary"></div>
                    </div>

                    <div class="booking-card">
                        <div class="booking-cal-head">
                            <p class="label-caps booking-cal-head__label">Select a Date</p>
                            <div class="booking-cal-nav">
                                <button type="button" class="btn btn-ghost btn-sm" id="cal-prev-month" aria-label="Previous month">‹</button>
                                <strong id="cal-month-label"></strong>
                                <button type="button" class="btn btn-ghost btn-sm" id="cal-next-month" aria-label="Next month">›</button>
                            </div>
                        </div>
                        <div id="dateGrid" class="calendar-grid calendar-grid--month"></div>
                    </div>

                    <div class="booking-card">
                        <p class="label-caps booking-card__label">Session Duration</p>
                        <div class="duration-btns">
                            <div class="duration-btn selected" data-min="30" onclick="BookingWidget.setDuration(30)">30m</div>
                            <div class="duration-btn" data-min="60" onclick="BookingWidget.setDuration(60)">60m</div>
                            <div class="duration-btn" data-min="90" onclick="BookingWidget.setDuration(90)">90m</div>
                            <div class="duration-btn" data-min="120" onclick="BookingWidget.setDuration(120)">120m</div>
                        </div>
                    </div>

                    <div class="booking-card">
                        <p class="label-caps booking-card__label" id="slots-heading">Available Slots</p>
                        <div id="timeGrid" class="time-grid">
                            <div class="text-sm text-muted booking-slot-placeholder">Pick an available date</div>
                        </div>
                    </div>

                    <div class="booking-card">
                        <p class="label-caps booking-card__label">Session Details</p>
                        <div class="form-group">
                            <label class="form-label">Topic <span style="color:var(--error)">*</span></label>
                            <input type="text" name="title" class="form-input" id="booking-topic"
                                   placeholder="What would you like to discuss?" maxlength="255" required>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Notes (optional)</label>
                            <textarea name="agenda" class="form-input" rows="3" id="booking-agenda"
                                      placeholder="Any additional context..."></textarea>
                        </div>
                        @include('frontend.partials.booking-coupon-picker')
                    </div>
                </div>

                <aside class="booking-modal__summary">
                    <p class="label-caps booking-card__label">Booking Summary</p>
                    <div class="booking-summary">
                        <div class="booking-summary-row"><span>Mentor</span><strong id="bk-mentor">—</strong></div>
                        <div class="booking-summary-row"><span>Date</span><strong id="bk-date">—</strong></div>
                        <div class="booking-summary-row"><span>Time</span><strong id="bk-time">—</strong></div>
                        <div class="booking-summary-row"><span>Duration</span><strong id="bk-duration">30 min</strong></div>
                        <div class="booking-summary-row"><span>Rate</span><strong id="bk-rate">—</strong></div>
                        <div class="booking-summary-row"><span>Subtotal</span><strong id="bk-subtotal">—</strong></div>
                        <div class="booking-summary-row booking-summary-row--discount" id="bk-discount-row" hidden>
                            <span>Coupon</span><strong id="bk-discount">—</strong>
                        </div>
                        <div class="booking-summary-row booking-summary-row--total"><span>Total</span><strong id="bk-total">—</strong></div>
                    </div>

                    <input type="hidden" name="booking_mentor_id" id="booking-mentor-id">
                    <input type="hidden" name="booking_date">
                    <input type="hidden" name="booking_time">
                    <input type="hidden" name="booking_duration">
                    <input type="hidden" name="booking_amount">
                </aside>
            </div>

            <div class="booking-modal__footer">
                <button class="btn btn-ghost" onclick="closeModal('booking-modal')">Cancel</button>
                <button class="btn btn-primary btn-lg" id="confirm-booking-btn" onclick="confirmBooking()">
                    ✓ Confirm & Book Session
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Init search on page load
MentorSearch.init();

// Mobile: filter sidebar toggle (UI only — safe if MentorSearch already binds this)
(function () {
    const toggle = document.getElementById('searchFilterToggle');
    const sidebar = document.querySelector('.filter-sidebar');
    if (!toggle || !sidebar || toggle.dataset.mbBound) return;
    toggle.dataset.mbBound = '1';
    if (window.matchMedia('(max-width: 991px)').matches) sidebar.style.display = 'none';
    toggle.addEventListener('click', () => {
        const hidden = sidebar.style.display === 'none';
        sidebar.style.display = hidden ? 'block' : 'none';
        toggle.textContent = hidden ? 'Hide filters' : 'Show filters';
    });
})();

function clearAllFilters() {
    document.querySelectorAll('[data-filter]').forEach(el => {
        if (el.type === 'checkbox' || el.type === 'radio') el.checked = false;
        else if (el.tagName === 'SELECT') el.selectedIndex = 0;
    });
    document.getElementById('mentor-search-input').value = '';
    document.querySelectorAll('[data-field-chip]').forEach((chip) => {
        chip.classList.remove('selected');
        chip.setAttribute('aria-pressed', 'false');
    });
    MentorSearch.submit();
}

function openBookingModal(mentorId, mentorName, ratePerMin) {
    document.getElementById('booking-mentor-id').value = mentorId;
    document.getElementById('booking-mentor-name').textContent = `Book: ${mentorName}`;
    document.getElementById('bk-mentor').textContent = mentorName;
    document.getElementById('bk-rate').textContent = `₹${ratePerMin}/min`;
    if (typeof BookingCoupon !== 'undefined' && BookingCoupon.clear) BookingCoupon.clear();
    openModal('booking-modal');
    // Init after modal is open so date/time grids are interactive
    setTimeout(() => BookingWidget.init(ratePerMin, mentorId), 0);
}

function confirmBooking(paymentMethod) {
    @guest
    showToast('info','Please sign in to book a session.');
    setTimeout(() => window.location.href = '/login?redirect=/mentors', 1500);
    return;
    @endguest

    const data = BookingWidget.getBookingData();
    if (!data) return;

    data.mentor_id = document.getElementById('booking-mentor-id').value;
    data.title     = document.getElementById('booking-topic')?.value?.trim() || '';
    data.agenda    = document.getElementById('booking-agenda')?.value || '';
    const couponCode = (typeof BookingCoupon !== 'undefined' && BookingCoupon.getCode)
        ? BookingCoupon.getCode()
        : (document.getElementById('booking-coupon')?.value || '').trim();
    if (couponCode) {
        data.coupon_code = couponCode;
    }
    if (!data.title) {
        showToast('error', 'Please enter a topic for the session.');
        return;
    }
    if (paymentMethod) data.payment_method = paymentMethod;

    AjaxPost('{{ route('mentee.sessions.book') }}', data, {
        btn: document.getElementById('confirm-booking-btn'), loader: true,
        onSuccess: res => {
            if (res.requires_payment_choice) {
                openPaymentChoice(res);
                return;
            }
            if (res.requires_payment) {
                closePaymentChoice();
                openSessionPayment(res);
                return;
            }
            closePaymentChoice();
            closeModal('booking-modal');
            showToast('success', res.message || '🎉 Session booked!');
            setTimeout(() => window.location.href = res.redirect || '{{ route('mentee.sessions') }}', 1500);
        },
        onError: err => {
            if (err.status === 401) {
                window.location.href = '/login?redirect=/mentors';
            } else if (err.insufficient_wallet || err.needs_topup) {
                openPaymentChoice(err, true);
            } else if (err.topup_url) {
                showToast('error', err.message || 'Insufficient wallet balance.');
                setTimeout(() => window.location.href = err.topup_url, 1800);
            } else {
                showToast('error', err.message || 'Could not book session. Please try again.');
            }
        }
    });
}

function openPaymentChoice(info, fromError) {
    let box = document.getElementById('payment-choice-modal');
    if (!box) {
        box = document.createElement('div');
        box.id = 'payment-choice-modal';
        box.className = 'modal-overlay open';
        box.innerHTML = `
          <div class="modal" style="max-width:420px;">
            <div class="modal-header"><h3>Choose payment method</h3><button type="button" class="modal-close" onclick="closePaymentChoice()">×</button></div>
            <div class="modal-body" id="payment-choice-body"></div>
          </div>`;
        document.body.appendChild(box);
    }
    box.classList.add('open');
    const amount = info.amount ?? info.required_amount ?? 0;
    const base = info.base_amount ?? amount;
    const discount = info.coupon_discount ?? 0;
    const bal = info.wallet_balance ?? 0;
    const shortfall = info.shortfall ?? Math.max(0, amount - bal);
    const opts = info.payment_options || ['wallet','razorpay'];
    const body = document.getElementById('payment-choice-body');
    body.innerHTML = `
      <p style="font-size:13px;color:var(--text-2);margin-bottom:12px;">
        ${discount > 0 ? `Original fee: <strong>₹${Number(base).toLocaleString()}</strong><br>Coupon discount: <strong>-₹${Number(discount).toLocaleString()}</strong><br>` : ''}
        Session fee: <strong>₹${Number(amount).toLocaleString()}</strong><br>
        Wallet balance: <strong>₹${Number(bal).toLocaleString()}</strong>
        ${shortfall > 0 ? `<br>Shortfall: <strong>₹${Number(shortfall).toLocaleString()}</strong>` : ''}
      </p>
      <div style="display:grid;gap:8px;">
        ${opts.includes('wallet') ? `<button type="button" class="btn btn-primary" onclick="confirmBooking('wallet')">Pay with Wallet</button>` : ''}
        ${opts.includes('razorpay') ? `<button type="button" class="btn btn-ghost" onclick="confirmBooking('razorpay')">Pay with Razorpay</button>` : ''}
        ${opts.includes('hybrid') ? `<button type="button" class="btn btn-ghost" onclick="confirmBooking('hybrid')">Use Wallet + Razorpay (₹${Number(shortfall).toLocaleString()} online)</button>` : ''}
        ${(opts.includes('topup') || (fromError && shortfall > 0)) ? `<a class="btn btn-ghost" href="{{ route('mentee.wallet') }}">Top up wallet</a>` : ''}
      </div>`;
}

function closePaymentChoice() {
    const box = document.getElementById('payment-choice-modal');
    if (box) box.classList.remove('open');
}

function openSessionPayment(order) {
    if (typeof Razorpay === 'undefined') {
        const s = document.createElement('script');
        s.src = 'https://checkout.razorpay.com/v1/checkout.js';
        s.onload = () => launchSessionRzp(order);
        s.onerror = () => showToast('error', 'Could not load payment gateway.');
        document.head.appendChild(s);
        return;
    }
    launchSessionRzp(order);
}

function launchSessionRzp(order) {
    const draft = order.booking_draft || {};
    if (typeof armUnpaidSessionHold === 'function') {
        armUnpaidSessionHold({
            mentor_id: draft.mentor_id || document.getElementById('booking-mentor-id')?.value,
            date: draft.date,
            time: draft.time,
        });
    }
    const options = {
        key: order.key,
        amount: order.amount,
        currency: order.currency || 'INR',
        name: order.name || 'Vedrix',
        description: order.description || 'Session booking',
        order_id: order.order_id,
        prefill: order.prefill || {},
        theme: { color: getComputedStyle(document.documentElement).getPropertyValue('--vj-primary').trim() || '#f59e0b' },
        modal: {
            ondismiss: function () {
                if (typeof releaseUnpaidSessionHold === 'function') releaseUnpaidSessionHold(false);
                showToast('info', 'Payment was not completed. That time slot is available again.');
            },
        },
        handler: function (response) {
            if (typeof markSessionPaymentSettled === 'function') markSessionPaymentSettled();
            AjaxPost('{{ route('mentee.sessions.verify') }}', {
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_order_id: response.razorpay_order_id,
                razorpay_signature: response.razorpay_signature,
            }, {
                loader: true,
                onSuccess: (data) => {
                    closeModal('booking-modal');
                    showToast('success', data.message || 'Payment successful!');
                    setTimeout(() => window.location.href = data.redirect || '{{ route('mentee.sessions') }}', 1200);
                },
                onError: (err) => showToast('error', err.message || 'Payment verification failed.'),
            });
        },
    };
    const rzp = new Razorpay(options);
    rzp.on('payment.failed', function () {
        if (typeof releaseUnpaidSessionHold === 'function') releaseUnpaidSessionHold(false);
        showToast('error', 'Payment failed. That time slot is available again.');
    });
    rzp.open();
}
</script>
@endpush