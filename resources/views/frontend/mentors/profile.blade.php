@extends('frontend.layouts.app')
@php
    $hostLabel = $mentor->hostLabel();
    $isExpert = $mentor->isExpert();
    $rate = (float) ($mentor->rate_per_minute ?? 0);
    $rateText = rtrim(rtrim(number_format($rate, 2), '0'), '.');
    $hourly = (int) round($rate * 60);
    $prefs = is_array($mentor->preferences) ? $mentor->preferences : [];
    $expertise = array_values(array_filter($mentor->expertise ?? []));
    $strengths = array_values(array_filter($mentor->strengths ?? []));
    $modes = array_values(array_filter((array) ($prefs['session_modes'] ?? [])));
    $modeLabels = ['video' => 'Video', 'audio' => 'Audio', 'chat' => 'Chat', 'in_person' => 'In person'];
    $preferredLength = (int) ($prefs['session_length'] ?? 0);
    $preferredTime = trim((string) ($prefs['preferred_time'] ?? ''));
    $weekly = $availabilityOverview['weekly_summary'] ?? [];
    $nextOpen = collect($availabilityOverview['days'] ?? [])->firstWhere('available', true);
    $bioPlain = \App\Support\RichText::plain($mentor->bio);
    $reviewCount = $reviews->count();
    $score = $reviewCount > 0
        ? round((float) $reviews->avg('overall_rating'), 1)
        : (float) ($mentor->rating ?? 0);
    $filledStars = max(0, min(5, (int) round($score)));
    $recommendCount = $reviews->where('would_recommend', true)->count();
    $dimAvgs = [];
    foreach (['Communication' => 'communication_rating', 'Knowledge' => 'knowledge_rating', 'Punctuality' => 'punctuality_rating', 'Helpfulness' => 'helpfulness_rating'] as $label => $col) {
        $avg = $reviews->avg($col);
        if ($avg !== null) {
            $dimAvgs[$label] = round((float) $avg, 1);
        }
    }
    $linkedin = trim((string) ($mentor->linkedin ?? ''));
    if ($linkedin !== '' && ! str_starts_with($linkedin, 'http')) {
        $linkedin = 'https://'.$linkedin;
    }
    $listingUrl = route('mentors.search', $isExpert ? ['type' => 'expert'] : ['type' => 'mentor']);
    $metaBio = $bioPlain !== '' ? \Illuminate\Support\Str::limit($bioPlain, 160) : $mentor->designation.' at Vedrix';
    $firstName = explode(' ', trim((string) $mentor->name))[0];
@endphp
@section('title', ($mentor->name ?? $hostLabel).' — Vedrix')
@section('meta_description', $mentor->name.' · '.$hostLabel.($mentor->designation ? ' · '.$mentor->designation : '').'. '.$metaBio)

@section('content')
@auth
    @if(auth()->user()->role === 'mentee')
        @include('frontend.mentee.partials.bottom-nav')
    @endif
@endauth

{{-- Font Awesome (agar layout me already loaded hai to ye line hata do) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
/* ═══════════════════════════════════════════════════════════
   VEDRIX · MENTOR PROFILE — PREMIUM UI (prefix: vp-)
   Same theme as listing page · Main color: var(--vj-primary)
   ═══════════════════════════════════════════════════════════ */
.vp {
    --vp-primary: var(--vj-primary);
    --vp-soft: color-mix(in srgb, var(--vj-primary) 10%, transparent);
    --vp-mid: color-mix(in srgb, var(--vj-primary) 22%, transparent);
    --vp-dark: color-mix(in srgb, var(--vj-primary) 70%, #000);
    --vp-ink: #0f172a;
    --vp-text: #334155;
    --vp-muted: #64748b;
    --vp-border: rgba(15, 23, 42, .08);
    --vp-star: #f59e0b;
    --vp-ok: #16a34a;
    --vp-shadow-sm: 0 2px 8px rgba(15, 23, 42, .05);
    --vp-shadow-md: 0 10px 30px rgba(15, 23, 42, .08);
    --vp-shadow-lg: 0 24px 60px -12px color-mix(in srgb, var(--vj-primary) 35%, transparent);
    --vp-ease: cubic-bezier(.22, .8, .3, 1);
    min-height: 100vh;
    color: var(--vp-ink);
    background:
        radial-gradient(900px 400px at 100% 0%, var(--vp-soft), transparent 60%),
        #f8fafc;
}
.vp *, .vp *::before, .vp *::after { box-sizing: border-box; }

/* ───────── BUTTONS ───────── */
.vp .vp-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 22px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.2;
    text-decoration: none;
    border: 0;
    border-radius: 10px;
    cursor: pointer;
    transition: transform .25s var(--vp-ease), box-shadow .25s, background .25s, color .25s, border-color .25s;
}
.vp .vp-btn--sm { padding: 9px 16px; font-size: 13px; }
.vp .vp-btn--block { width: 100%; }
.vp .vp-btn--primary {
    color: #fff;
    background: linear-gradient(135deg, var(--vj-primary), var(--vp-dark));
    box-shadow: 0 8px 20px color-mix(in srgb, var(--vj-primary) 40%, transparent);
}
.vp .vp-btn--primary:hover {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 14px 30px color-mix(in srgb, var(--vj-primary) 55%, transparent);
    filter: brightness(1.06);
}
.vp .vp-btn--glass {
    color: #fff;
    background: rgba(255, 255, 255, .1);
    border: 1px solid rgba(255, 255, 255, .28);
    backdrop-filter: blur(8px);
}
.vp .vp-btn--glass:hover { color: #fff; background: rgba(255, 255, 255, .2); transform: translateY(-2px); }

/* ───────── HERO BANNER ───────── */
.vp-hero {
    position: relative;
    overflow: hidden;
    padding: 28px 0 120px;
    color: #fff;
    background-color: #0b1020;
    background-image:
        radial-gradient(600px 300px at 12% 10%, color-mix(in srgb, var(--vj-primary) 45%, transparent), transparent 70%),
        radial-gradient(500px 300px at 90% 90%, color-mix(in srgb, var(--vj-primary) 30%, transparent), transparent 70%),
        linear-gradient(135deg, #0b1020 0%, color-mix(in srgb, var(--vj-primary) 28%, #0b1020) 100%);
}
.vp-hero::before,
.vp-hero::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    background: var(--vj-primary);
    filter: blur(80px);
    opacity: .3;
    pointer-events: none;
    animation: vpFloat 12s ease-in-out infinite;
}
.vp-hero::before { width: 320px; height: 320px; top: -140px; left: -90px; }
.vp-hero::after  { width: 260px; height: 260px; bottom: -130px; right: -60px; animation-delay: -6s; }
@keyframes vpFloat {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50%      { transform: translate(30px, 20px) scale(1.1); }
}
.vp-hero .container { position: relative; z-index: 1; }

.vp-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 22px;
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 600;
    color: rgba(255, 255, 255, .85);
    text-decoration: none;
    background: rgba(255, 255, 255, .08);
    border: 1px solid rgba(255, 255, 255, .18);
    border-radius: 8px;
    backdrop-filter: blur(8px);
    transition: background .25s, transform .25s var(--vp-ease);
}
.vp-back:hover { color: #fff; background: rgba(255, 255, 255, .18); transform: translateX(-3px); }

.vp-hero__grid {
    display: grid;
    grid-template-columns: 320px minmax(0, 1fr);
    gap: 40px;
    align-items: center;
}

/* photo */
.vp-photo { position: relative; margin: 0; }
.vp-photo::before {
    content: "";
    position: absolute;
    inset: 14px -14px -14px 14px;
    border: 2px solid var(--vj-primary);
    border-radius: 18px;
    opacity: .55;
}
.vp-photo__frame {
    position: relative;
    display: grid;
    place-items: center;
    width: 100%;
    aspect-ratio: 4 / 5;
    overflow: hidden;
    font-size: 110px;
    font-weight: 800;
    color: #fff;
    border: 4px solid rgba(255, 255, 255, .92);
    border-radius: 18px;
    background:
        radial-gradient(circle at 25% 20%, rgba(255, 255, 255, .28), transparent 45%),
        radial-gradient(circle at 85% 90%, rgba(0, 0, 0, .22), transparent 50%),
        linear-gradient(135deg, var(--vj-primary), var(--vp-dark));
    box-shadow: 0 30px 60px -15px rgba(0, 0, 0, .55);
}
.vp-photo__frame img { display: block; width: 100%; height: 100%; object-fit: cover; object-position: center top; }
.vp-photo__rating {
    position: absolute;
    left: 50%;
    bottom: -16px;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 800;
    color: var(--vp-ink);
    white-space: nowrap;
    background: #fff;
    border: 1px solid var(--vp-mid);
    border-radius: 10px;
    box-shadow: 0 12px 28px rgba(0, 0, 0, .3);
    transform: translateX(-50%);
}
.vp-photo__rating i { color: var(--vp-star); }
.vp-photo__rating small { font-size: 12px; font-weight: 600; color: var(--vp-muted); }

/* info */
.vp-hero__info { min-width: 0; }
.vp-badges { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
.vp-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 13px;
    font-size: 12px;
    font-weight: 700;
    color: #fff;
    background: rgba(255, 255, 255, .1);
    border: 1px solid rgba(255, 255, 255, .22);
    border-radius: 8px;
    backdrop-filter: blur(8px);
}
.vp-pill--brand  { background: var(--vj-primary); border-color: var(--vj-primary); box-shadow: 0 6px 16px color-mix(in srgb, var(--vj-primary) 45%, transparent); }
.vp-pill--violet { background: #7c3aed; border-color: #7c3aed; box-shadow: 0 6px 16px rgba(124, 58, 237, .4); }
.vp-pill--ok     { background: rgba(22, 163, 74, .2); border-color: rgba(74, 222, 128, .5); color: #bbf7d0; }

.vp-name {
    margin: 0 0 8px;
    font-size: clamp(30px, 4.4vw, 48px);
    font-weight: 800;
    line-height: 1.1;
    letter-spacing: -.02em;
    color: #fff;
}
.vp-role { margin: 0 0 14px; font-size: clamp(15px, 1.7vw, 18px); line-height: 1.5; color: rgba(255, 255, 255, .85); }
.vp-role strong { font-weight: 700; color: #fff; }
.vp-role span { color: rgba(255, 255, 255, .7); }
.vp-line {
    width: 64px;
    height: 4px;
    margin: 0 0 18px;
    border-radius: 4px;
    background: var(--vj-primary);
    box-shadow: 0 0 18px color-mix(in srgb, var(--vj-primary) 70%, transparent);
}
.vp-meta { display: flex; flex-wrap: wrap; gap: 8px 20px; margin-bottom: 16px; font-size: 14px; color: rgba(255, 255, 255, .8); }
.vp-meta i { margin-right: 7px; color: var(--vj-primary); }

.vp-hero__chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 22px; }
.vp-hero__chips span {
    padding: 4px 12px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    background: rgb(9 28 64);
    border: 1px solid rgba(255, 255, 255, .18);
    border-radius: 8px;
}

/* glass stats */
.vp-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 24px; }
.vp-stat {
    padding: 14px 16px;
    background: rgba(255, 255, 255, .08);
    border: 1px solid rgba(255, 255, 255, .16);
    border-radius: 12px;
    backdrop-filter: blur(10px);
}
.vp-stat__icon { display: block; margin-bottom: 8px; font-size: 15px; color: var(--vj-primary); }
.vp-stat__value { display: block; font-size: 22px; font-weight: 800; line-height: 1.1; letter-spacing: -.01em; color: #fff; }
.vp-stat__value small { font-size: 12px; font-weight: 600; color: rgba(255, 255, 255, .7); }
.vp-stat__label { display: block; margin-top: 4px; font-size: 12px; color: rgba(255, 255, 255, .7); }

.vp-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
.vp-actions form { margin: 0; }

/* ───────── BODY LAYOUT ───────── */
.vp-layout {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 390px;
    gap: 24px;
    align-items: start;
    margin-top: -72px;
    padding-bottom: 80px;
}
.vp-main { min-width: 0; }

/* tabs */
.vp-tabs {
    position: sticky;
    top: 76px;
    z-index: 5;
    display: flex;
    gap: 6px;
    margin-bottom: 20px;
    padding: 6px;
    overflow-x: auto;
    background: #fff;
    border: 1px solid var(--vp-border);
    border-radius: 12px;
    box-shadow: var(--vp-shadow-md);
    scrollbar-width: none;
}
.vp-tabs::-webkit-scrollbar { display: none; }
.vp-tab {
    flex: 1 0 auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 700;
    color: var(--vp-muted);
    text-decoration: none;
    white-space: nowrap;
    border-radius: 8px;
    transition: background .25s, color .25s;
}
.vp-tab:hover { color: var(--vj-primary); background: var(--vp-soft); }
.vp-tab.is-active { color: #fff; background: var(--vj-primary); box-shadow: 0 8px 18px color-mix(in srgb, var(--vj-primary) 40%, transparent); }

/* section cards */
.vp-section { margin-bottom: 20px; scroll-margin-top: 150px; }
.vp-card {
    margin-bottom: 16px;
    padding: 24px;
    background: #fff;
    border: 1px solid rgba(15, 23, 42, .06);
    border-radius: 12px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 8px 16px -4px rgba(15, 23, 42, .08), 0 20px 40px -12px rgba(15, 23, 42, .1);
}
.vp-card__head { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
.vp-card__icon {
    display: grid;
    place-items: center;
    flex: 0 0 38px;
    width: 38px; height: 38px;
    font-size: 16px;
    color: var(--vj-primary);
    background: var(--vp-soft);
    border: 1px solid var(--vp-mid);
    border-radius: 10px;
}
.vp-card h2 { margin: 0; font-size: 19px; font-weight: 800; letter-spacing: -.01em; color: var(--vp-ink); }
.vp-hint { margin: -6px 0 16px; font-size: 13px; color: var(--vp-muted); }
.vp-empty { margin: 0; padding: 14px 16px; font-size: 14px; color: var(--vp-muted); background: #f8fafc; border: 1px dashed var(--vp-mid); border-radius: 10px; }

.vp-bio { font-size: 15px; line-height: 1.75; color: var(--vp-text); }
.vp-bio p:last-child { margin-bottom: 0; }

/* chips (expertise / strengths) */
.vp-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.vp-chip {
    padding: 5px 13px;
    font-size: 13px;
    font-weight: 700;
    color: var(--vp-dark);
    background: var(--vp-soft);
    border: 1px solid var(--vp-mid);
    border-radius: 8px;
    transition: background .25s, color .25s, transform .25s;
}
.vp-chip:hover { color: #fff; background: var(--vj-primary); transform: translateY(-1px); }
.vp-chip--line { color: var(--vp-text); background: #f1f5f9; border-color: transparent; }
.vp-chip i { margin-right: 6px; font-size: 11px; }

/* perks */
.vp-perks { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin: 0; padding: 0; list-style: none; }
.vp-perk {
    display: flex;
    gap: 14px;
    padding: 16px;
    background: linear-gradient(135deg, var(--vp-soft), transparent);
    border: 1px solid var(--vp-border);
    border-radius: 12px;
    transition: transform .3s var(--vp-ease), border-color .3s, box-shadow .3s;
}
.vp-perk:hover { transform: translateY(-4px); border-color: var(--vp-mid); box-shadow: var(--vp-shadow-lg); }
.vp-perk__icon {
    display: grid;
    place-items: center;
    flex: 0 0 42px;
    width: 42px; height: 42px;
    font-size: 17px;
    color: #fff;
    background: linear-gradient(135deg, var(--vj-primary), var(--vp-dark));
    border-radius: 10px;
    box-shadow: 0 8px 18px color-mix(in srgb, var(--vj-primary) 35%, transparent);
}
.vp-perk strong { display: block; margin-bottom: 4px; font-size: 14.5px; color: var(--vp-ink); }
.vp-perk p { margin: 0; font-size: 13px; line-height: 1.55; color: var(--vp-muted); }

/* facts */
.vp-facts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin: 0; }
.vp-fact { display: flex; align-items: center; gap: 14px; padding: 14px 16px; background: #f8fafc; border: 1px solid var(--vp-border); border-radius: 10px; }
.vp-fact__icon { flex: 0 0 18px; font-size: 16px; text-align: center; color: var(--vj-primary); }
.vp-fact dt { margin: 0 0 2px; font-size: 12px; font-weight: 600; color: var(--vp-muted); }
.vp-fact dd { margin: 0; font-size: 14.5px; font-weight: 700; color: var(--vp-ink); }

/* weekly availability */
.vp-week { display: grid; gap: 10px; }
.vp-day {
    display: grid;
    grid-template-columns: 130px minmax(0, 1fr);
    gap: 18px;
    align-items: start;
    padding: 14px 16px;
    background: #f8fafc;
    border: 1px solid var(--vp-border);
    border-radius: 12px;
    transition: border-color .25s, box-shadow .25s, transform .25s var(--vp-ease);
}
.vp-day.is-on { background: linear-gradient(135deg, rgba(22, 163, 74, .06), #fff 70%); border-color: rgba(22, 163, 74, .28); }
.vp-day.is-on:hover { transform: translateY(-2px); box-shadow: var(--vp-shadow-md); }
.vp-day.is-off { opacity: .75; align-items: center; }
.vp-day.is-today { border-color: var(--vj-primary); box-shadow: 0 0 0 3px var(--vp-soft); }

.vp-day__name { position: relative; display: flex; flex-direction: column; gap: 3px; padding-left: 14px; }
.vp-day__name::before { content: ""; position: absolute; left: 0; top: 3px; bottom: 3px; width: 4px; border-radius: 4px; background: #cbd5e1; }
.vp-day.is-on .vp-day__name::before { background: linear-gradient(var(--vp-ok), #4ade80); }
.vp-day__name strong { display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 800; color: var(--vp-ink); }
.vp-day__name em { padding: 2px 8px; font-size: 11px; font-style: normal; font-weight: 700; color: #fff; background: var(--vj-primary); border-radius: 6px; }
.vp-day__name small { font-size: 12.5px; font-weight: 600; color: var(--vp-muted); }
.vp-day.is-on .vp-day__name small { color: var(--vp-ok); }

.vp-day__slots { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
.vp-slot {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 5px 6px 5px 12px;
    font-size: 13px;
    background: #fff;
    border: 1px solid rgba(22, 163, 74, .3);
    border-radius: 8px;
}
.vp-slot b { font-weight: 700; color: var(--vp-ink); }
.vp-slot i { padding: 2px 8px; font-size: 11px; font-style: normal; font-weight: 700; color: #166534; background: rgba(22, 163, 74, .12); border-radius: 6px; }
.vp-day__slots:not(.is-open) .vp-slot.is-extra { display: none; }
.vp-slot-more {
    padding: 7px 14px;
    font-family: inherit;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--vj-primary);
    background: var(--vp-soft);
    border: 1px dashed var(--vp-mid);
    border-radius: 8px;
    cursor: pointer;
    transition: background .2s, color .2s;
}
.vp-slot-more:hover { color: #fff; background: var(--vj-primary); }
.vp-day__off { font-size: 13px; color: var(--vp-muted); }
.vp-day__off i { margin-right: 6px; }

/* reviews */
.vp-score { display: grid; grid-template-columns: 200px minmax(0, 1fr); gap: 28px; align-items: center; }
.vp-score__box { padding: 18px 12px; text-align: center; background: linear-gradient(135deg, var(--vp-soft), transparent); border: 1px solid var(--vp-mid); border-radius: 12px; }
.vp-score__num { font-size: 52px; font-weight: 800; line-height: 1; letter-spacing: -.03em; color: var(--vj-primary); }
.vp-stars { margin: 8px 0 6px; font-size: 15px; letter-spacing: 2px; color: #cbd5e1; }
.vp-stars .on { color: var(--vp-star); }
.vp-score__box p { margin: 0; font-size: 13px; color: var(--vp-muted); }
.vp-score__rec { margin-top: 4px !important; font-weight: 700; color: var(--vp-ok) !important; }
.vp-bars { display: grid; gap: 14px; }
.vp-bar__row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13.5px; font-weight: 600; color: var(--vp-text); }
.vp-bar__row strong { color: var(--vp-ink); }
.vp-bar__track { height: 8px; overflow: hidden; background: #e2e8f0; border-radius: 8px; }
.vp-bar__track i { display: block; height: 100%; border-radius: 8px; background: linear-gradient(90deg, var(--vj-primary), var(--vp-dark)); }

.vp-review {
    position: relative;
    margin-bottom: 14px;
    padding: 22px 24px 20px 28px;
    background: #fff;
    border: 1px solid rgba(15, 23, 42, .06);
    border-radius: 12px;
    box-shadow: var(--vp-shadow-sm);
}
.vp-review::before { content: ""; position: absolute; inset: 14px auto 14px 0; width: 4px; border-radius: 0 4px 4px 0; background: linear-gradient(var(--vj-primary), var(--vp-dark)); }
.vp-review__top { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
.vp-review__top .vp-stars { margin: 0; }
.vp-review time { font-size: 12.5px; color: var(--vp-muted); }
.vp-review__text { margin: 0 0 14px; font-size: 14.5px; line-height: 1.7; color: var(--vp-text); }
.vp-review__chips { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
.vp-review__chips span { padding: 3px 10px; font-size: 12px; font-weight: 700; color: var(--vp-dark); background: var(--vp-soft); border: 1px solid var(--vp-mid); border-radius: 6px; }
.vp-review__by { display: flex; align-items: center; gap: 12px; padding-top: 14px; border-top: 1px solid var(--vp-border); }
.vp-review__avatar { display: grid; place-items: center; width: 38px; height: 38px; font-weight: 800; color: #fff; border-radius: 50%; background: linear-gradient(135deg, var(--vj-primary), var(--vp-dark)); }
.vp-review__name { font-size: 14px; font-weight: 700; color: var(--vp-ink); }
.vp-review__role { font-size: 12.5px; color: var(--vp-muted); }
.vp-review__role i { margin-right: 5px; color: var(--vp-ok); }
.vp-empty-card { text-align: center; border: 2px dashed var(--vp-mid); box-shadow: none; }
.vp-empty-card i { display: block; margin-bottom: 10px; font-size: 30px; color: var(--vj-primary); opacity: .7; }
.vp-empty-card p { margin: 0; color: var(--vp-muted); }

/* ───────── BOOKING ASIDE ───────── */
.vp-aside { align-self: start; }
.vp-book {
    overflow: hidden;
    background: #fff;
    border: 1px solid rgba(15, 23, 42, .06);
    border-radius: 14px;
    box-shadow: 0 2px 4px rgba(15, 23, 42, .05), 0 16px 32px -6px color-mix(in srgb, var(--vj-primary) 28%, transparent), 0 30px 60px -14px rgba(15, 23, 42, .25);
}
.vp-book__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 20px 22px;
    color: #fff;
    background: linear-gradient(135deg, #0b1020, color-mix(in srgb, var(--vj-primary) 40%, #0b1020));
}
.vp-book__kicker { margin: 0 0 4px; font-size: 13px; font-weight: 600; color: rgba(255, 255, 255, .75); }
.vp-book__price { margin: 0; font-size: 30px; font-weight: 800; line-height: 1; letter-spacing: -.02em; }
.vp-book__price small { font-size: 14px; font-weight: 600; color: rgba(255, 255, 255, .7); }
.vp-book__hour { padding: 6px 12px; font-size: 13px; font-weight: 700; background: var(--vj-primary); border-radius: 8px; box-shadow: 0 6px 16px color-mix(in srgb, var(--vj-primary) 45%, transparent); }
.vp-book__body { padding: 20px 22px 22px; }
.vp-book__note { margin: 0 0 18px; padding: 10px 12px; font-size: 13px; line-height: 1.5; color: var(--vp-text); background: var(--vp-soft); border: 1px solid var(--vp-mid); border-radius: 8px; }
.vp-book__note i { margin-right: 7px; color: var(--vj-primary); }

.vp-book .label-caps,
.vp-label {
    margin: 18px 0 10px;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0;
    text-transform: none;
    color: var(--vp-ink);
}
.vp-book .label-caps i, .vp-label i { margin-right: 8px; color: var(--vj-primary); }
.vp-book .vp-label--first { margin-top: 0; }
.vp-book .booking-cal-head { display: flex; align-items: center; justify-content: space-between; margin: 18px 0 10px; }
.vp-book .booking-cal-head .label-caps { margin: 0; }
.vp-book .booking-cal-nav { display: flex; align-items: center; gap: 8px; }
.vp-book .booking-cal-nav strong { font-size: 13.5px; color: var(--vp-ink); }
.vp-book .booking-cal-nav .btn {
    display: grid; place-items: center;
    width: 30px; height: 30px; padding: 0;
    color: var(--vp-muted);
    background: #f1f5f9;
    border: 0;
    border-radius: 8px;
    cursor: pointer;
}
.vp-book .booking-cal-nav .btn:hover { color: #fff; background: var(--vj-primary); }

.vp-book .duration-btns { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
.vp-book .duration-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 10px 4px;
    font-family: inherit;
    color: var(--vp-ink);
    background: #f1f5f9;
    border: 1.5px solid transparent;
    border-radius: 10px;
    cursor: pointer;
    transition: transform .2s var(--vp-ease), background .2s, color .2s, box-shadow .2s;
}
.vp-book .duration-btn:hover { color: var(--vj-primary); background: var(--vp-soft); transform: translateY(-2px); }
.vp-book .duration-btn.selected { color: #fff; background: var(--vj-primary); box-shadow: 0 8px 18px color-mix(in srgb, var(--vj-primary) 45%, transparent); }
.vp-book .duration-btn__time { font-size: 14px; font-weight: 800; }
.vp-book .duration-btn__price { font-size: 11px; font-weight: 600; opacity: .8; }

.vp-book .form-group { margin-bottom: 12px; }
.vp-book .form-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 700; color: var(--vp-text); }
.vp-book .form-input {
    width: 100%;
    padding: 11px 13px;
    font-family: inherit;
    font-size: 14px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    outline: 0;
    transition: border-color .2s, box-shadow .2s, background .2s;
}
.vp-book .form-input:focus { background: #fff; border-color: var(--vj-primary); box-shadow: 0 0 0 4px var(--vp-soft); }

.vp-book .booking-summary {
    margin-top: 18px;
    padding: 14px 16px;
    background: linear-gradient(160deg, var(--vp-soft), #fff 60%);
    border: 1px solid var(--vp-mid);
    border-radius: 12px;
}
.vp-book .booking-summary-row { display: flex; justify-content: space-between; gap: 10px; padding: 8px 0; font-size: 13.5px; color: var(--vp-muted); border-bottom: 1px dashed var(--vp-border); }
.vp-book .booking-summary-row strong, .vp-book .booking-summary-row span:last-child { color: var(--vp-ink); font-weight: 700; text-align: right; }
.vp-book .booking-summary-row--discount strong { color: var(--vp-ok); }
.vp-book .booking-summary-row--total { padding-top: 12px; border-bottom: 0; font-size: 15px; font-weight: 700; }
.vp-book .booking-summary-row--total strong { font-size: 22px; font-weight: 800; color: var(--vj-primary); }

.vp-book__cta { margin-top: 16px; padding: 14px 20px; font-size: 15px; }
.vp-book__fine { margin: 12px 0 0; font-size: 12.5px; line-height: 1.5; text-align: center; color: var(--vp-muted); }
.vp-book__fine i { margin-right: 6px; color: var(--vp-ok); }

/* mobile dock */
.vp-dock { display: none; }

/* ───────── RESPONSIVE ───────── */
@media (min-width: 992px) {
    .vp-aside {
        position: sticky;
        top: 84px;
        max-height: calc(100vh - 100px);
        overflow-y: auto;
        border-radius: 14px;
        scrollbar-width: thin;
        scrollbar-color: var(--vp-mid) transparent;
    }
}
@media (max-width: 991px) {
    .vp-layout { grid-template-columns: 1fr; margin-top: -60px; padding-bottom: 110px; }
    .vp-tabs { top: 66px; }
    .vp-dock {
        position: fixed;
        left: 0; right: 0; bottom: 0;
        z-index: 40;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 18px calc(12px + env(safe-area-inset-bottom, 0px));
        background: #fff;
        border-top: 1px solid var(--vp-border);
        box-shadow: 0 -10px 30px rgba(15, 23, 42, .12);
    }
    .vp-dock__price strong { display: block; font-size: 17px; font-weight: 800; color: var(--vj-primary); }
    .vp-dock__price span { display: block; font-size: 12px; color: var(--vp-muted); }
}
@media (max-width: 900px) {
    .vp-hero { padding-bottom: 100px; }
    .vp-hero__grid { grid-template-columns: 1fr; gap: 34px; justify-items: center; text-align: center; }
    .vp-photo { width: min(260px, 70%); }
    .vp-hero__info { width: 100%; }
    .vp-badges, .vp-meta, .vp-hero__chips, .vp-actions { justify-content: center; }
    .vp-line { margin-left: auto; margin-right: auto; }
}
@media (max-width: 640px) {
    .vp-day { grid-template-columns: 1fr; gap: 10px; }
    .vp-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .vp-perks, .vp-facts { grid-template-columns: 1fr; }
    .vp-score { grid-template-columns: 1fr; }
    .vp-card { padding: 18px; }
    .vp-book .duration-btns { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (prefers-reduced-motion: reduce) {
    .vp *, .vp *::before, .vp *::after { animation: none !important; transition: none !important; }
}
</style>

<div class="vp" data-mentor-id="{{ $mentor->id }}">

    {{-- ═════════ HERO BANNER ═════════ --}}
    <header class="vp-hero">
        <div class="container">
            <a href="{{ $listingUrl }}" class="vp-back">
                <i class="fa-solid fa-arrow-left"></i> Back to {{ $isExpert ? 'experts' : 'mentors' }}
            </a>

            <div class="vp-hero__grid">
                <figure class="vp-photo">
                    <div class="vp-photo__frame">
                        @if($mentor->avatar_url)
                            <img src="{{ $mentor->avatar_url }}" alt="{{ $mentor->name }}">
                        @else
                            {{ strtoupper(substr($mentor->name ?? 'M', 0, 1)) }}
                        @endif
                    </div>
                    <span class="vp-photo__rating">
                        <i class="fa-solid fa-star"></i> {{ number_format($score, 1) }}
                        <small>({{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }})</small>
                    </span>
                </figure>

                <div class="vp-hero__info">
                    <div class="vp-badges">
                        <span class="vp-pill {{ $isExpert ? 'vp-pill--violet' : 'vp-pill--brand' }}">
                            <i class="fa-solid {{ $isExpert ? 'fa-crown' : 'fa-user-tie' }}"></i> {{ $hostLabel }}
                        </span>
                        @if(($mentor->mentor_status ?? '') === 'approved')
                        <span class="vp-pill vp-pill--ok"><i class="fa-solid fa-circle-check"></i> Verified</span>
                        @endif
                        @if((int) ($mentor->experience_years ?? 0) > 0)
                        <span class="vp-pill"><i class="fa-solid fa-briefcase"></i> {{ (int) $mentor->experience_years }}+ yrs experience</span>
                        @endif
                    </div>

                    <h1 class="vp-name">{{ $mentor->name }}</h1>
                    <p class="vp-role">
                        <strong>{{ $mentor->designation ?: $hostLabel }}</strong>
                        @if($mentor->company) <span>at {{ $mentor->company }}</span> @endif
                        @if($mentor->field) <span>· {{ $mentor->field }}</span> @endif
                    </p>
                    <div class="vp-line"></div>

                    @if($mentor->location || $nextOpen)
                    <div class="vp-meta">
                        @if($mentor->location)
                        <span><i class="fa-solid fa-location-dot"></i>{{ $mentor->location }}</span>
                        @endif
                        @if($nextOpen)
                        <span><i class="fa-solid fa-calendar-check"></i>Next opening {{ \Carbon\Carbon::parse($nextOpen['date'])->timezone('Asia/Kolkata')->format('D, j M') }}</span>
                        @endif
                    </div>
                    @endif

                    @if($expertise !== [])
                    <div class="vp-hero__chips">
                        @foreach(array_slice($expertise, 0, 5) as $skill)
                        <span>{{ $skill }}</span>
                        @endforeach
                    </div>
                    @endif

                    <div class="vp-stats" role="list">
                        <div class="vp-stat" role="listitem">
                            <i class="vp-stat__icon fa-solid fa-star"></i>
                            <span class="vp-stat__value">{{ number_format($score, 1) }}</span>
                            <span class="vp-stat__label">Rating{{ $reviewCount ? ' ('.$reviewCount.')' : '' }}</span>
                        </div>
                        <div class="vp-stat" role="listitem">
                            <i class="vp-stat__icon fa-solid fa-video"></i>
                            <span class="vp-stat__value">{{ number_format((int) ($mentor->total_sessions ?? 0)) }}</span>
                            <span class="vp-stat__label">Sessions done</span>
                        </div>
                        <div class="vp-stat" role="listitem">
                            <i class="vp-stat__icon fa-solid fa-award"></i>
                            <span class="vp-stat__value">{{ (int) ($mentor->experience_years ?? 0) }}+</span>
                            <span class="vp-stat__label">Years experience</span>
                        </div>
                        <div class="vp-stat" role="listitem">
                            <i class="vp-stat__icon fa-solid fa-indian-rupee-sign"></i>
                            <span class="vp-stat__value">₹{{ $rateText }}<small>/min</small></span>
                            <span class="vp-stat__label">₹{{ number_format($hourly) }} per hour</span>
                        </div>
                    </div>

                    <div class="vp-actions">
                        <button type="button" class="vp-btn vp-btn--primary" onclick="scrollToBook()">
                            <i class="fa-solid fa-calendar-plus"></i> Book a session
                        </button>
                        @if($linkedin)
                        <a class="vp-btn vp-btn--glass vp-btn--sm" href="{{ $linkedin }}" target="_blank" rel="noopener">
                            <i class="fa-brands fa-linkedin-in"></i> LinkedIn
                        </a>
                        @endif
                        <button type="button" class="vp-btn vp-btn--glass vp-btn--sm" id="vp-share-btn">
                            <i class="fa-solid fa-share-nodes"></i> Share
                        </button>
                        @auth
                            @if(auth()->user()->role === 'mentee' && ! $isExpert)
                                @php
                                    $isAssigned = (int) auth()->user()->assigned_mentor_id === (int) $mentor->id;
                                    $hasPending = \App\Models\MentorRequest::where('mentee_id', auth()->id())
                                        ->where('mentor_id', $mentor->id)
                                        ->where('status', 'pending')
                                        ->exists();
                                @endphp
                                @if($isAssigned)
                                    <span class="vp-pill vp-pill--ok"><i class="fa-solid fa-circle-check"></i> Your mentor</span>
                                @elseif($hasPending)
                                    <span class="vp-pill"><i class="fa-solid fa-hourglass-half"></i> Request pending</span>
                                @else
                                    <form method="POST" action="{{ route('mentee.mentor-requests.store') }}">
                                        @csrf
                                        <input type="hidden" name="mentor_id" value="{{ $mentor->id }}">
                                        <button type="submit" class="vp-btn vp-btn--glass vp-btn--sm">
                                            <i class="fa-solid fa-user-plus"></i>
                                            {{ auth()->user()->assigned_mentor_id ? 'Request as my mentor' : 'Choose as my mentor' }}
                                        </button>
                                    </form>
                                @endif
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- ═════════ BODY ═════════ --}}
    <div class="container">
        <div class="vp-layout">

            <div class="vp-main">
                <nav class="vp-tabs" role="tablist">
                    <a class="vp-tab is-active" href="#about"><i class="fa-solid fa-user"></i> About</a>
                    <a class="vp-tab" href="#profile-details"><i class="fa-solid fa-id-card"></i> Profile</a>
                    <a class="vp-tab" href="#availability-overview"><i class="fa-solid fa-clock"></i> Availability</a>
                    <a class="vp-tab" href="#reviews"><i class="fa-solid fa-comments"></i> Reviews</a>
                </nav>

                <section id="about" class="vp-section">
                    <div class="vp-card">
                        <div class="vp-card__head">
                            <span class="vp-card__icon"><i class="fa-solid fa-user"></i></span>
                            <h2>About {{ $firstName }}</h2>
                        </div>
                        @if($bioPlain !== '')
                        <div class="rich-text vp-bio">{!! $mentor->bio !!}</div>
                        @else
                        <p class="vp-empty">{{ $mentor->name }} hasn’t added a bio yet.</p>
                        @endif
                    </div>

                    @if($expertise !== [])
                    <div class="vp-card">
                        <div class="vp-card__head">
                            <span class="vp-card__icon"><i class="fa-solid fa-lightbulb"></i></span>
                            <h2>Expertise</h2>
                        </div>
                        <div class="vp-chips">
                            @foreach($expertise as $skill)
                            <span class="vp-chip">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($strengths !== [])
                    <div class="vp-card">
                        <div class="vp-card__head">
                            <span class="vp-card__icon"><i class="fa-solid fa-bolt"></i></span>
                            <h2>Strengths</h2>
                        </div>
                        <div class="vp-chips">
                            @foreach($strengths as $item)
                            <span class="vp-chip vp-chip--line"><i class="fa-solid fa-check"></i>{{ $item }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="vp-card">
                        <div class="vp-card__head">
                            <span class="vp-card__icon"><i class="fa-solid fa-gift"></i></span>
                            <h2>What you’ll get</h2>
                        </div>
                        <ul class="vp-perks">
                            <li class="vp-perk">
                                <span class="vp-perk__icon"><i class="fa-solid fa-video"></i></span>
                                <div>
                                    <strong>Focused {{ strtolower($hostLabel) }} session</strong>
                                    <p>Live video call on the slot you pick — 30, 60, 90, or 120 minutes.</p>
                                </div>
                            </li>
                            <li class="vp-perk">
                                <span class="vp-perk__icon"><i class="fa-solid fa-indian-rupee-sign"></i></span>
                                <div>
                                    <strong>Clear pricing</strong>
                                    <p>₹{{ $rateText }}/min · ₹{{ number_format($hourly) }}/hour. You only pay for the duration you book.</p>
                                </div>
                            </li>
                            <li class="vp-perk">
                                <span class="vp-perk__icon"><i class="fa-solid fa-note-sticky"></i></span>
                                <div>
                                    <strong>Session notes</strong>
                                    <p>Shared notes and next steps after the call so nothing is lost.</p>
                                </div>
                            </li>
                            <li class="vp-perk">
                                <span class="vp-perk__icon"><i class="fa-solid fa-route"></i></span>
                                <div>
                                    <strong>Practical takeaways</strong>
                                    <p>Action items and a short roadmap you can use in the next 30–90 days.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </section>

                <section id="profile-details" class="vp-section">
                    <div class="vp-card">
                        <div class="vp-card__head">
                            <span class="vp-card__icon"><i class="fa-solid fa-id-card"></i></span>
                            <h2>Professional profile</h2>
                        </div>
                        <dl class="vp-facts">
                            <div class="vp-fact">
                                <i class="vp-fact__icon fa-solid fa-user-tie"></i>
                                <div><dt>Title</dt><dd>{{ $mentor->designation ?: '—' }}</dd></div>
                            </div>
                            <div class="vp-fact">
                                <i class="vp-fact__icon fa-solid fa-building"></i>
                                <div><dt>Company</dt><dd>{{ $mentor->company ?: '—' }}</dd></div>
                            </div>
                            <div class="vp-fact">
                                <i class="vp-fact__icon fa-solid fa-layer-group"></i>
                                <div><dt>Domain</dt><dd>{{ $mentor->field ?: '—' }}</dd></div>
                            </div>
                            <div class="vp-fact">
                                <i class="vp-fact__icon fa-solid fa-briefcase"></i>
                                <div><dt>Experience</dt><dd>{{ (int) ($mentor->experience_years ?? 0) }} years</dd></div>
                            </div>
                            @if($mentor->education_stream)
                            <div class="vp-fact">
                                <i class="vp-fact__icon fa-solid fa-graduation-cap"></i>
                                <div><dt>Education stream</dt><dd>{{ $mentor->education_stream }}</dd></div>
                            </div>
                            @endif
                            @if($mentor->location)
                            <div class="vp-fact">
                                <i class="vp-fact__icon fa-solid fa-location-dot"></i>
                                <div><dt>Location</dt><dd>{{ $mentor->location }}</dd></div>
                            </div>
                            @endif
                            <div class="vp-fact">
                                <i class="vp-fact__icon fa-solid fa-video"></i>
                                <div>
                                    <dt>Session format</dt>
                                    <dd>
                                        @if($modes !== [])
                                            {{ collect($modes)->map(fn ($m) => $modeLabels[$m] ?? ucfirst($m))->join(', ') }}
                                        @else
                                            1:1 video
                                        @endif
                                    </dd>
                                </div>
                            </div>
                            @if($preferredLength)
                            <div class="vp-fact">
                                <i class="vp-fact__icon fa-solid fa-hourglass-half"></i>
                                <div><dt>Preferred length</dt><dd>{{ $preferredLength }} minutes</dd></div>
                            </div>
                            @endif
                            @if($preferredTime !== '')
                            <div class="vp-fact">
                                <i class="vp-fact__icon fa-solid fa-sun"></i>
                                <div><dt>Preferred time</dt><dd>{{ $preferredTime }}</dd></div>
                            </div>
                            @endif
                        </dl>
                    </div>
                </section>

                <section id="availability-overview" class="vp-section">
                    <div class="vp-card">
                        <div class="vp-card__head">
                            <span class="vp-card__icon"><i class="fa-solid fa-clock"></i></span>
                            <h2>Weekly availability</h2>
                        </div>
                        <p class="vp-hint">Times are in IST. Green days are when {{ $firstName }} usually takes sessions.</p>
                        @if($weekly !== [])
                        @php $todayKey = strtolower(now('Asia/Kolkata')->format('D')); @endphp
                        <div class="vp-week">
                            @foreach($weekly as $day)
                            @php
                                $on = !empty($day['enabled']);
                                $raw = $on ? ($day['windows'] ?: (($day['from'] && $day['to']) ? $day['from'].'–'.$day['to'] : 'Open')) : '';
                                $slots = $on ? array_values(array_filter(array_map('trim', preg_split('/,\s*(?=\d{1,2}:\d{2})/', (string) $raw)))) : [];
                                $isToday = strtolower(substr((string) $day['label'], 0, 3)) === $todayKey;
                                $extra = max(0, count($slots) - 4);
                            @endphp
                            <div class="vp-day {{ $on ? 'is-on' : 'is-off' }} {{ $isToday ? 'is-today' : '' }}">
                                <div class="vp-day__name">
                                    <strong>{{ $day['label'] }} @if($isToday)<em>Today</em>@endif</strong>
                                    <small>{{ $on ? count($slots).' '.\Illuminate\Support\Str::plural('slot', count($slots)) : 'Off' }}</small>
                                </div>
                                <div class="vp-day__slots">
                                    @if($on)
                                        @foreach($slots as $i => $slot)
                                            @php
                                                preg_match('/^(.*?)\s*\((.+?)\)\s*$/', $slot, $m);
                                                $time = $m[1] ?? $slot;
                                                $dur = $m[2] ?? null;
                                            @endphp
                                            <span class="vp-slot {{ $i >= 4 ? 'is-extra' : '' }}"><b>{{ $time }}</b>@if($dur)<i>{{ $dur }}</i>@endif</span>
                                        @endforeach
                                        @if($extra > 0)
                                        <button type="button" class="vp-slot-more" data-more="{{ $extra }}">+{{ $extra }} more</button>
                                        @endif
                                    @else
                                        <span class="vp-day__off"><i class="fa-regular fa-moon"></i> Not taking sessions</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="vp-empty">Weekly hours will appear here once this {{ strtolower($hostLabel) }} publishes a schedule.</p>
                        @endif
                    </div>
                </section>

                <section id="reviews" class="vp-section">
                    <div class="vp-card">
                        <div class="vp-card__head">
                            <span class="vp-card__icon"><i class="fa-solid fa-comments"></i></span>
                            <h2>Reviews</h2>
                        </div>
                        <div class="vp-score">
                            <div class="vp-score__box">
                                <div class="vp-score__num">{{ number_format($score, 1) }}</div>
                                <div class="vp-stars" aria-hidden="true">
                                    @for($i = 1; $i <= 5; $i++)<i class="fa-solid fa-star {{ $i <= $filledStars ? 'on' : '' }}"></i>@endfor
                                </div>
                                <p>{{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</p>
                                @if($reviewCount > 0)
                                <p class="vp-score__rec">{{ $recommendCount }} of {{ $reviewCount }} would recommend</p>
                                @endif
                            </div>
                            @if($dimAvgs !== [])
                            <div class="vp-bars">
                                @foreach($dimAvgs as $label => $value)
                                <div class="vp-bar">
                                    <div class="vp-bar__row"><span>{{ $label }}</span><strong>{{ number_format($value, 1) }}</strong></div>
                                    <div class="vp-bar__track"><i style="width: {{ min(100, ($value / 5) * 100) }}%"></i></div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                    @forelse($reviews as $review)
                    @php
                        $reviewerName = $review->reviewer->name ?? 'Mentee';
                        $overall = max(0, min(5, (int) $review->overall_rating));
                        $detailScores = array_filter([
                            'Communication' => $review->communication_rating,
                            'Knowledge' => $review->knowledge_rating,
                            'Punctuality' => $review->punctuality_rating,
                            'Helpfulness' => $review->helpfulness_rating,
                        ], fn ($value) => $value !== null && $value !== '');
                    @endphp
                    <article class="vp-review">
                        <div class="vp-review__top">
                            <div class="vp-stars">
                                @for($i = 1; $i <= 5; $i++)<i class="fa-solid fa-star {{ $i <= $overall ? 'on' : '' }}"></i>@endfor
                            </div>
                            <time>{{ $review->submitted_at?->diffForHumans() ?? $review->created_at?->diffForHumans() }}</time>
                        </div>
                        @if(filled($review->review_text))
                        <p class="vp-review__text">“{{ $review->review_text }}”</p>
                        @endif
                        @if($detailScores !== [])
                        <div class="vp-review__chips">
                            @foreach($detailScores as $label => $value)
                            <span>{{ $label }} {{ (int) $value }}/5</span>
                            @endforeach
                        </div>
                        @endif
                        <div class="vp-review__by">
                            <div class="vp-review__avatar">{{ strtoupper(substr($reviewerName, 0, 1)) }}</div>
                            <div>
                                <div class="vp-review__name">{{ $reviewerName }}</div>
                                <div class="vp-review__role">
                                    <i class="fa-solid {{ $review->would_recommend ? 'fa-thumbs-up' : 'fa-circle-check' }}"></i>{{ $review->would_recommend ? 'Would recommend' : 'Verified mentee' }}
                                </div>
                            </div>
                        </div>
                    </article>
                    @empty
                    <div class="vp-card vp-empty-card">
                        <i class="fa-regular fa-comment-dots"></i>
                        <p>No reviews yet. Feedback from mentees will show up here after completed sessions.</p>
                    </div>
                    @endforelse
                </section>
            </div>

            {{-- ═════════ BOOKING ═════════ --}}
            <aside id="book-section" class="vp-aside">
                <div class="vp-book">
                    <div class="vp-book__top">
                        <div>
                            <p class="vp-book__kicker">Book a session</p>
                            <p class="vp-book__price">₹{{ $rateText }}<small>/min</small></p>
                        </div>
                        <span class="vp-book__hour">₹{{ number_format($hourly) }}/hr</span>
                    </div>

                    <div class="vp-book__body">
                        <p class="vp-book__note"><i class="fa-solid fa-circle-info"></i>Pick a date, duration, and time. Payment is only taken when you confirm.</p>

                        <p class="label-caps vp-label--first"><i class="fa-solid fa-user-clock"></i>{{ $hostLabel }} availability</p>
                        <div id="availabilitySummary"></div>

                        <div class="booking-cal-head">
                            <p class="label-caps"><i class="fa-solid fa-calendar-days"></i>Select a date</p>
                            <div class="booking-cal-nav">
                                <button type="button" class="btn btn-ghost btn-sm" id="cal-prev-month" aria-label="Previous month"><i class="fa-solid fa-chevron-left"></i></button>
                                <strong id="cal-month-label"></strong>
                                <button type="button" class="btn btn-ghost btn-sm" id="cal-next-month" aria-label="Next month"><i class="fa-solid fa-chevron-right"></i></button>
                            </div>
                        </div>
                        <div id="dateGrid" class="calendar-grid calendar-grid--month"></div>

                        <p class="label-caps"><i class="fa-solid fa-hourglass-half"></i>Session duration</p>
                        <div class="duration-btns">
                            @foreach(\App\Models\ConsultationSession::BOOKING_DURATIONS as $mins)
                            <button type="button"
                                    class="duration-btn{{ $mins === 30 ? ' selected' : '' }}"
                                    data-min="{{ $mins }}"
                                    onclick="BookingWidget.setDuration({{ $mins }})">
                                <span class="duration-btn__time">{{ $mins }}m</span>
                                <span class="duration-btn__price">₹{{ number_format($rate * $mins) }}</span>
                            </button>
                            @endforeach
                        </div>

                        <p class="label-caps" id="slots-heading"><i class="fa-solid fa-clock"></i>Available slots</p>
                        <div id="timeGrid" class="time-grid">
                            <div style="grid-column:1/-1;text-align:center;padding:12px;font-size:12px;color:var(--text-3);">Select an available date</div>
                        </div>

                        <p class="label-caps"><i class="fa-solid fa-pen-to-square"></i>Session details</p>
                        <div class="form-group">
                            <label class="form-label" for="booking-topic">Topic <span style="color:var(--error)">*</span></label>
                            <input type="text" class="form-input" id="booking-topic" placeholder="What would you like to discuss?" maxlength="255">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="booking-agenda">Notes (optional)</label>
                            <textarea class="form-input" rows="2" id="booking-agenda" placeholder="Any additional context…"></textarea>
                        </div>

                        @include('frontend.partials.booking-coupon-picker')

                        <div class="booking-summary">
                            <div class="booking-summary-row"><span>Date</span><span id="bk-date">—</span></div>
                            <div class="booking-summary-row"><span>Time</span><span id="bk-time">—</span></div>
                            <div class="booking-summary-row"><span>Duration</span><span id="bk-duration">30 min</span></div>
                            <div class="booking-summary-row"><span>Subtotal</span><strong id="bk-subtotal">₹{{ number_format($rate * 30) }}</strong></div>
                            <div class="booking-summary-row booking-summary-row--discount" id="bk-discount-row" hidden>
                                <span>Coupon</span><strong id="bk-discount">—</strong>
                            </div>
                            <div class="booking-summary-row booking-summary-row--total">
                                <span>Total</span><strong id="bk-total">₹{{ number_format($rate * 30) }}</strong>
                            </div>
                        </div>

                        <input type="hidden" id="booking-mentor-id" value="{{ $mentor->id }}">
                        <input type="hidden" name="booking_date">
                        <input type="hidden" name="booking_time">
                        <input type="hidden" name="booking_duration">
                        <input type="hidden" name="booking_amount">

                        <button type="button" class="vp-btn vp-btn--primary vp-btn--block vp-book__cta" onclick="confirmBooking()">
                            <i class="fa-solid fa-circle-check"></i> Confirm booking
                        </button>
                        <p class="vp-book__fine"><i class="fa-solid fa-lock"></i>You’ll choose Wallet or Razorpay on the next step if the session is paid.</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <div class="vp-dock" aria-hidden="false">
        <div class="vp-dock__price">
            <strong>₹{{ $rateText }}/min</strong>
            <span>{{ $mentor->name }} · {{ $hostLabel }}</span>
        </div>
        <button type="button" class="vp-btn vp-btn--primary vp-btn--sm" onclick="scrollToBook()">
            <i class="fa-solid fa-calendar-plus"></i> Book
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
BookingWidget.init({{ $rate }}, {{ (int) $mentor->id }});

function scrollToBook() {
    document.getElementById('book-section')?.scrollIntoView({ behavior:'smooth', block:'start' });
}

(function () {
    const btn = document.getElementById('vp-share-btn');
    if (!btn) return;

    function toast(type, message) {
        if (typeof window.showToast === 'function') window.showToast(type, message);
    }

    function copied() {
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied';
        toast('success', 'Profile link copied');
        setTimeout(() => { btn.innerHTML = original; }, 2000);
    }

    function fallbackCopy(text) {
        const input = document.createElement('textarea');
        input.value = text;
        input.setAttribute('readonly', '');
        input.style.position = 'fixed';
        input.style.top = '0';
        input.style.left = '0';
        input.style.opacity = '0';
        document.body.appendChild(input);
        input.focus();
        input.select();
        input.setSelectionRange(0, text.length);
        let ok = false;
        try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
        document.body.removeChild(input);
        return ok;
    }

    btn.addEventListener('click', async function (e) {
        e.preventDefault();
        const url = window.location.href;
        const payload = { title: document.title, text: document.title, url: url };

        if (navigator.share) {
            try {
                await navigator.share(payload);
                return;
            } catch (err) {
                if (err && err.name === 'AbortError') return;
            }
        }

        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(url);
                copied();
                return;
            }
        } catch (e) {}

        if (fallbackCopy(url)) {
            copied();
            return;
        }

        toast('error', 'Could not copy the link. Copy it from the address bar.');
    });
})();

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.vp-slot-more');
    if (!btn) return;
    const wrap = btn.closest('.vp-day__slots');
    const open = wrap.classList.toggle('is-open');
    btn.textContent = open ? 'Show less' : '+' + btn.dataset.more + ' more';
});

(function () {
    const tabs = document.querySelectorAll('.vp-tab');
    const sections = ['about', 'profile-details', 'availability-overview', 'reviews']
        .map((id) => document.getElementById(id))
        .filter(Boolean);

    tabs.forEach((tab) => {
        tab.addEventListener('click', (e) => {
            const target = document.querySelector(tab.getAttribute('href'));
            if (!target) return;
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    if (!('IntersectionObserver' in window) || !sections.length) return;
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            tabs.forEach((tab) => tab.classList.toggle('is-active', tab.getAttribute('href') === '#' + entry.target.id));
        });
    }, { rootMargin: '-20% 0px -65% 0px', threshold: 0.1 });
    sections.forEach((el) => io.observe(el));
})();

function confirmBooking(paymentMethod) {
    const data = BookingWidget.getBookingData();
    if (!data) return;
    data.mentor_id = document.getElementById('booking-mentor-id').value;
    data.title = document.getElementById('booking-topic')?.value?.trim() || '';
    data.agenda = document.getElementById('booking-agenda')?.value || '';
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

    @guest
    showToast('info','Please sign in to book a session.');
    setTimeout(() => window.location.href = '/login?redirect={{ request()->path() }}', 1500);
    return;
    @endguest

    AjaxPost("{{ route('mentee.sessions.book') }}", data, {
        loader: true,
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
            showToast('success', res.message || '🎉 Session booked!');
            setTimeout(() => window.location.href = res.redirect || '{{ route('mentee.sessions') }}', 1500);
        },
        onError: err => {
            if (err.status === 401) window.location.href = '/login?redirect={{ request()->path() }}';
            else if (err.insufficient_wallet || err.needs_topup) openPaymentChoice(err, true);
            else if (err.topup_url) {
                showToast('error', err.message || 'Insufficient wallet balance.');
                setTimeout(() => window.location.href = err.topup_url, 1800);
            } else {
                showToast('error', err.message || 'Could not book. Please try again.');
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
    document.getElementById('payment-choice-body').innerHTML = `
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
        theme: { color: '#f59e0b' },
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