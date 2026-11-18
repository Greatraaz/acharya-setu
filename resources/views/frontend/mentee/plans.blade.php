@extends('frontend.layouts.app')
@section('title', 'Plans — Vedrix')

@section('content')
@php
    $currentPlanId = $current?->plan_id;
@endphp

{{-- Font Awesome 6.5.1 (agar layout me pehle se load hai to ye line hata do) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
button.btn.btn-primary.plan-period-card__cta {
    color: #fff !important;
}
/* =========================================================
   Vedrix — Plans (light + dark)  |  scoped to .mentee-plans
   ========================================================= */
.mentee-plans{
    --pp: var(--vj-primary, #f59e0b);
    --pp-soft: color-mix(in srgb, var(--pp) 9%, #fff);
    --pp-soft-2: color-mix(in srgb, var(--pp) 16%, #fff);
    --pp-ring: color-mix(in srgb, var(--pp) 32%, transparent);
    --ink: #0f172a;
    --ink-2: #475569;
    --ink-3: #94a3b8;
    --line: #e8ecf3;
    --surface: #ffffff;
    --surface-2: #fafbfd;
    --surface-3: #f4f6fa;
    --surface-4: #eef1f6;
    --ok: #059669;
    --ok-soft: #ecfdf5;
    --ok-border: #a7f3d0;
    --bad: #dc2626;
    --bad-soft: #fef2f2;
    --warn-soft: #fffbeb;
    --warn-border: #fde68a;
    --warn-ink: #92400e;
    --sh-sm: 0 1px 2px rgba(15,23,42,.04), 0 2px 6px rgba(15,23,42,.05);
    --sh-md: 0 14px 34px -16px rgba(15,23,42,.20);
    --sh-lg: 0 28px 56px -22px rgba(15,23,42,.30);
    --r: 8px;
}
html[data-theme="dark"] .mentee-plans{
    --pp-soft: color-mix(in srgb, var(--pp) 16%, transparent);
    --pp-soft-2: color-mix(in srgb, var(--pp) 22%, transparent);
    --pp-ring: color-mix(in srgb, var(--pp) 38%, transparent);
    --ink: #fafafa;
    --ink-2: #a1a1aa;
    --ink-3: #71717a;
    --line: rgba(255,255,255,.10);
    --surface: #18181b;
    --surface-2: #111113;
    --surface-3: #27272a;
    --surface-4: #27272a;
    --ok: #22c55e;
    --ok-soft: rgba(34,197,94,.14);
    --ok-border: rgba(34,197,94,.32);
    --bad: #ef4444;
    --bad-soft: rgba(239,68,68,.14);
    --warn-soft: rgba(245,158,11,.12);
    --warn-border: rgba(245,158,11,.35);
    --warn-ink: #fcd34d;
    --sh-sm: 0 1px 2px rgba(0,0,0,.35);
    --sh-md: 0 14px 34px -16px rgba(0,0,0,.55);
    --sh-lg: 0 28px 56px -22px rgba(0,0,0,.65);
}
.mentee-plans i.fa-solid,
.mentee-plans i.fa-regular{ line-height:1; }
.mentee-plans .card{
    background:var(--surface) !important;
    color:var(--ink);
    border-color:var(--line) !important;
}
.mentee-plans .btn{ border-radius:var(--r) !important; }

/* ---------- header ---------- */
.mentee-plans .dash-title{
    display:flex; align-items:center; gap:12px;
    color:var(--ink); font-weight:800; letter-spacing:-.02em;
}
.mentee-plans .dash-title__icon{
    width:42px; height:42px; border-radius:var(--r); flex:none;
    display:inline-flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg,var(--pp),color-mix(in srgb,var(--pp) 70%,#000));
    color:#fff; font-size:17px;
    box-shadow:0 10px 22px -8px var(--pp-ring);
}
.mentee-plans .dash-subtitle{ color:var(--ink-2); margin-top:6px; }

/* ---------- alerts ---------- */
.mentee-plans .mentee-plans__alert{
    display:flex; align-items:center; gap:12px;
    padding:14px 18px; margin-bottom:18px;
    border-radius:var(--r); border:1px solid transparent;
    font-weight:500; box-shadow:var(--sh-sm);
}
.mentee-plans .alert-icon{
    width:30px; height:30px; border-radius:var(--r); flex:none;
    display:inline-flex; align-items:center; justify-content:center; font-size:14px;
}
.mentee-plans .alert-success{ background:var(--ok-soft); border-color:var(--ok-border); color:var(--ok); }
.mentee-plans .alert-success .alert-icon{ background:var(--ok-soft); color:var(--ok); }
.mentee-plans .alert-error{ background:var(--bad-soft); border-color:color-mix(in srgb,var(--bad) 35%, transparent); color:var(--bad); }
.mentee-plans .alert-error .alert-icon{ background:var(--bad-soft); color:var(--bad); }

/* ---------- current plan ---------- */
.mentee-plans .mentee-plans__current{
    position:relative; overflow:hidden;
    margin-bottom:34px; padding:30px;
    background:
        radial-gradient(520px 220px at 100% 0%, var(--pp-soft-2), transparent 70%),
        var(--surface);
    border:1px solid var(--line); border-radius:var(--r);
    box-shadow:var(--sh-md);
}
.mentee-plans .mentee-plans__current::before{
    content:""; position:absolute; left:0; top:0; bottom:0; width:5px;
    background:linear-gradient(180deg,var(--pp),color-mix(in srgb,var(--pp) 55%,#000));
}
.mentee-plans .mentee-plans__current-top{
    display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:22px;
}
.mentee-plans .mentee-plans__current-eyebrow{
    display:inline-flex; align-items:center; gap:8px;
    font-size:11.5px; font-weight:700; letter-spacing:.12em; text-transform:uppercase;
    color:var(--pp); background:var(--pp-soft); padding:6px 12px; border-radius:var(--r);
}
.mentee-plans .mentee-plans__current-name{
    margin:14px 0 14px; font-size:32px; line-height:1.1; font-weight:800;
    letter-spacing:-.03em; color:var(--ink);
}
.mentee-plans .mentee-plans__current-meta{ display:flex; flex-wrap:wrap; gap:10px; }
.mentee-plans .mentee-plans__meta-pill{
    display:inline-flex; align-items:center; gap:8px;
    padding:8px 14px; border-radius:var(--r); font-size:13px; font-weight:600;
    background:var(--surface-3); color:var(--ink-2); border:1px solid var(--line);
}
.mentee-plans .mentee-plans__meta-pill i{ color:var(--ink-3); }
.mentee-plans .mentee-plans__meta-pill--accent{
    background:var(--pp-soft); color:var(--pp); border-color:var(--pp-ring);
}
.mentee-plans .mentee-plans__meta-pill--accent i{ color:var(--pp); }

.mentee-plans .mentee-plans__cancel,
.mentee-plans .mentee-plans__cancel-note{
    max-width:360px; padding:16px 18px; border-radius:var(--r);
    background:var(--surface-2); border:1px dashed var(--line); color:var(--ink-2); font-size:13.5px; line-height:1.55;
}
.mentee-plans .mentee-plans__cancel-note{
    display:flex; gap:10px; background:var(--warn-soft); border-color:var(--warn-border); color:var(--warn-ink);
}
.mentee-plans .mentee-plans__cancel-note i{ margin-top:3px; }
.mentee-plans .mentee-plans__cancel-summary{ margin:0 0 12px; display:flex; gap:10px; }
.mentee-plans .mentee-plans__cancel-summary i{ color:var(--ink-3); margin-top:3px; }
.mentee-plans .mentee-plans__cancel .btn{
    display:inline-flex; align-items:center; gap:8px; border-radius:var(--r);
    border:1px solid color-mix(in srgb,var(--bad) 40%, transparent); color:var(--bad); background:var(--surface); font-weight:600; transition:.2s;
}
.mentee-plans .mentee-plans__cancel .btn:hover{ background:var(--bad-soft); }

.mentee-plans .mentee-plans__benefits{
    margin-top:24px; padding:20px 22px; border-radius:var(--r);
    background:var(--surface-2); border:1px solid var(--line);
}
.mentee-plans .mentee-plans__benefits-label{
    display:flex; align-items:center; gap:10px; margin-bottom:14px;
    font-size:12px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--ink-2);
}
.mentee-plans .mentee-plans__benefits-label i{ color:var(--pp); font-size:14px; }
.mentee-plans .mentee-benefits{
    display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:12px;
}
.mentee-plans .mentee-benefits__item{
    background:var(--surface); border:1px solid var(--line); border-radius:var(--r); padding:12px 14px;
}
.mentee-plans .mentee-benefits__label,
.mentee-plans .mentee-benefits__left{ color:var(--ink); }
.mentee-plans .mentee-benefits__msg,
.mentee-plans .mentee-benefits__history-label{ color:var(--ink-3); }
.mentee-plans .mentee-benefits__history-row{ color:var(--ink-2); }
.mentee-plans .mentee-benefits__status{ border-radius:var(--r); }
.mentee-plans .mentee-benefits__status--available{ background:var(--ok-soft); color:var(--ok); }
.mentee-plans .mentee-benefits__status--used,
.mentee-plans .mentee-benefits__status--addon{ background:var(--surface-3); color:var(--ink-3); border:1px solid var(--line); }
.mentee-plans .mentee-benefits__status--next_free{ background:var(--pp-soft); color:var(--pp); }
.mentee-plans .mentee-benefits__history-flag.is-counted{ color:var(--ok); }
.mentee-plans .mentee-benefits__history-flag.is-open{ color:var(--pp); }

/* ---------- empty ---------- */
.mentee-plans .mentee-plans__empty{
    text-align:center; padding:60px 20px; background:var(--surface);
    border:1px dashed var(--line); border-radius:var(--r); color:var(--ink-2);
}
.mentee-plans .mentee-plans__empty h3{ color:var(--ink); }
.mentee-plans .mentee-plans__empty .empty-state-icon{
    width:72px; height:72px; margin:0 auto 16px; border-radius:var(--r);
    display:flex; align-items:center; justify-content:center;
    background:var(--pp-soft); color:var(--pp); font-size:28px;
}

/* ---------- billing toggle ---------- */
.mentee-plans .mentee-plans__billing-wrap{ display:flex; justify-content:center; margin:0 0 30px; }
.mentee-plans .plan-billing-toggle{
    display:inline-flex; gap:4px; padding:5px; border-radius:var(--r);
    background:var(--surface-4); border:1px solid var(--line);
}
.mentee-plans .plan-billing-toggle__btn{
    display:inline-flex; align-items:center; gap:9px;
    padding:11px 24px; border:0; border-radius:var(--r); cursor:pointer;
    background:transparent; color:var(--ink-2); font-weight:600; font-size:14px;
    transition:background .25s, color .25s, box-shadow .25s;
}
.mentee-plans .plan-billing-toggle__btn:hover{ color:var(--ink); }
.mentee-plans .plan-billing-toggle__btn.is-active{
    background:var(--surface); color:var(--ink); box-shadow:var(--sh-sm);
}
.mentee-plans .plan-billing-toggle__btn.is-active > i{ color:var(--pp); }
.mentee-plans .plan-billing-toggle__save{
    display:inline-flex; align-items:center; gap:5px;
    padding:3px 9px; border-radius:var(--r); font-size:11px; font-weight:700;
    background:var(--ok-soft); color:var(--ok);
}

/* ---------- plan cards ---------- */
.mentee-plans .plan-cards-grid{
    display:grid; gap:26px; align-items:stretch; margin-bottom:44px;
    grid-template-columns:repeat(3,minmax(0,1fr)); width:100%;
}
@media (max-width:1100px){
    .mentee-plans .plan-cards-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); }
}
@media (max-width:720px){
    .mentee-plans .plan-cards-grid{ grid-template-columns:1fr; }
}
.mentee-plans .plan-period-card{
    position:relative; display:flex; flex-direction:column; overflow:hidden;
    padding:30px 26px 26px; background:var(--surface);
    border:1px solid var(--line); border-radius:var(--r); box-shadow:var(--sh-sm);
    transition:transform .35s cubic-bezier(.2,.8,.2,1), box-shadow .35s, border-color .35s;
}
.mentee-plans .plan-period-card::before{
    content:""; position:absolute; left:0; right:0; top:0; height:5px;
    background:linear-gradient(90deg,var(--plan-accent),color-mix(in srgb,var(--plan-accent) 55%,transparent));
}
.mentee-plans .plan-period-card::after{
    content:""; position:absolute; right:-70px; top:-70px; width:200px; height:200px; border-radius:50%;
    background:radial-gradient(circle,color-mix(in srgb,var(--plan-accent) 14%,transparent),transparent 70%);
    pointer-events:none;
}
.mentee-plans .plan-period-card:hover{
    transform:translateY(-8px); box-shadow:var(--sh-lg);
    border-color:color-mix(in srgb,var(--plan-accent) 45%,var(--line));
}
.mentee-plans .plan-period-card.is-featured{
    border-color:color-mix(in srgb,var(--plan-accent) 55%,var(--line));
    background:linear-gradient(180deg,color-mix(in srgb,var(--plan-accent) 10%,var(--surface)),var(--surface) 38%);
    box-shadow:0 0 0 4px color-mix(in srgb,var(--plan-accent) 12%,transparent), var(--sh-md);
}
.mentee-plans .plan-period-card.is-current{
    border-color:var(--ok); box-shadow:0 0 0 4px color-mix(in srgb,var(--ok) 16%,transparent), var(--sh-sm);
}

.mentee-plans .plan-period-card__badge{
    position:absolute; top:18px; right:18px; z-index:2;
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 12px; border-radius:var(--r);
    font-size:11px; font-weight:800; letter-spacing:.06em; text-transform:uppercase;
    background:var(--plan-badge-bg); color:var(--plan-badge-text);
    box-shadow:0 6px 14px -6px rgba(0,0,0,.3);
}

.mentee-plans .plan-period-card__head{ position:relative; z-index:1; margin-bottom:20px; }
.mentee-plans .plan-period-card__icon{
    width:54px; height:54px; border-radius:var(--r); margin-bottom:16px;
    display:inline-flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg,var(--plan-accent),color-mix(in srgb,var(--plan-accent) 62%,#000));
    color:#fff; font-size:21px;
    box-shadow:0 14px 24px -10px color-mix(in srgb,var(--plan-accent) 70%,transparent);
}
.mentee-plans .plan-period-card__title-row{ display:flex; align-items:center; flex-wrap:wrap; gap:10px; }
.mentee-plans .plan-period-card__name{
    margin:0; font-size:22px; font-weight:800; letter-spacing:-.02em; color:var(--ink);
}
.mentee-plans .plan-current-chip{
    display:inline-flex; align-items:center; gap:5px;
    padding:4px 10px; border-radius:var(--r); font-size:11px; font-weight:700;
    background:var(--ok-soft); color:var(--ok);
}
.mentee-plans .plan-period-card__desc{ margin:10px 0 0; color:var(--ink-2); font-size:14px; line-height:1.6; }

.mentee-plans .plan-period-block{ position:relative; z-index:1; display:flex; flex-direction:column; flex:1; }
.mentee-plans .plan-period-block[hidden]{ display:none; }
.mentee-plans .plan-period-block:not([hidden]){ animation:planFade .35s ease both; }
@keyframes planFade{ from{opacity:0; transform:translateY(8px);} to{opacity:1; transform:none;} }

/* price */
.mentee-plans .plan-period-block__price{
    padding:18px 0 20px; margin-bottom:18px; border-bottom:1px solid var(--line);
}
.mentee-plans .plan-price-row{ display:flex; align-items:baseline; flex-wrap:wrap; gap:6px 8px; }
.mentee-plans .plan-price-amount{
    font-size:46px; line-height:1; font-weight:800; letter-spacing:-.04em; color:var(--ink);
}
.mentee-plans .plan-price-free{ font-size:34px; font-weight:800; letter-spacing:-.03em; color:var(--ok); }
.mentee-plans .plan-price-period{ font-size:15px; font-weight:600; color:var(--ink-3); }
.mentee-plans .plan-discount-badge{
    display:inline-flex; align-items:center; gap:5px; margin-left:4px;
    padding:4px 10px; border-radius:var(--r); font-size:12px; font-weight:700;
    background:var(--ok-soft); color:var(--ok);
}
.mentee-plans .plan-price-savings{ display:flex; flex-wrap:wrap; align-items:center; gap:8px 10px; margin-top:10px; font-size:13px; }
.mentee-plans .plan-price-strike{ color:var(--ink-3); text-decoration:line-through; font-weight:600; }
.mentee-plans .plan-price-save{ color:var(--ok); font-weight:700; }
.mentee-plans .plan-price-until{ color:var(--ink-3); }
.mentee-plans .plan-price-credit{
    display:flex; align-items:center; gap:8px; margin-top:12px;
    padding:9px 12px; border-radius:var(--r); font-size:13px; font-weight:600;
    background:var(--pp-soft); color:var(--pp);
}
.mentee-plans .plan-price-tax{ margin-top:10px; font-size:12px; line-height:1.6; color:var(--ink-3); }
.mentee-plans .plan-price-cycle{
    display:flex; align-items:center; gap:8px; margin-top:12px; font-size:13px; font-weight:600; color:var(--ink-2);
}
.mentee-plans .plan-price-cycle i{ color:var(--plan-accent); }
.mentee-plans .plan-price-credit-detail{ margin-top:8px; font-size:12px; line-height:1.55; color:var(--ink-3); }

/* benefits */
.mentee-plans .plan-benefits{ list-style:none; margin:0 0 24px; padding:0; flex:1; display:grid; gap:12px; }
.mentee-plans .plan-benefits__item{ display:flex; align-items:flex-start; gap:12px; font-size:14px; line-height:1.5; color:var(--ink); }
.mentee-plans .plan-benefits__check{
    width:22px; height:22px; border-radius:var(--r); flex:none; margin-top:1px;
    display:inline-flex; align-items:center; justify-content:center; font-size:10px;
    background:color-mix(in srgb,var(--plan-accent) 18%, transparent); color:var(--plan-accent);
}
.mentee-plans .plan-benefits__empty{ flex:1; margin:0 0 24px; color:var(--ink-3); font-size:13.5px; }

/* CTA */
.mentee-plans .plan-period-block__cta{ margin-top:auto; }
.mentee-plans .plan-period-card__cta{
    width:100%; display:inline-flex; align-items:center; justify-content:center; gap:10px;
    padding:15px 20px; border:0; border-radius:var(--r); cursor:pointer;
    font-size:15px; font-weight:700; color:#fff;
    background:linear-gradient(135deg,var(--plan-accent),color-mix(in srgb,var(--plan-accent) 68%,#000));
    box-shadow:0 14px 26px -12px color-mix(in srgb,var(--plan-accent) 80%,transparent);
    transition:transform .25s, box-shadow .25s, filter .25s;
}
.mentee-plans .plan-period-card__cta i{ transition:transform .25s; }
.mentee-plans .plan-period-card__cta:hover{ transform:translateY(-2px); filter:brightness(1.05); box-shadow:0 20px 32px -12px color-mix(in srgb,var(--plan-accent) 85%,transparent); }
.mentee-plans .plan-period-card__cta:hover i{ transform:translateX(4px); }
.mentee-plans .plan-period-card__cta:active{ transform:translateY(0); }
.mentee-plans .plan-period-card__cta:focus-visible{ outline:3px solid var(--pp-ring); outline-offset:3px; }
.mentee-plans .plan-current-label{
    display:flex; align-items:center; justify-content:center; gap:10px;
    padding:15px 20px; border-radius:var(--r); font-weight:700; font-size:15px;
    background:var(--ok-soft); color:var(--ok); border:1px solid var(--ok-border);
}

/* ---------- history ---------- */
.mentee-plans .plan-history{
    padding:28px; background:var(--surface); border:1px solid var(--line); border-radius:var(--r); box-shadow:var(--sh-md);
}
.mentee-plans .plan-history-head{
    display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:18px; margin-bottom:22px;
}
.mentee-plans .plan-history-title{
    display:flex; align-items:center; gap:12px; margin:0; font-size:20px; font-weight:800; letter-spacing:-.02em; color:var(--ink);
}
.mentee-plans .plan-history-title i{
    width:38px; height:38px; border-radius:var(--r); display:inline-flex; align-items:center; justify-content:center;
    background:var(--pp-soft); color:var(--pp); font-size:15px;
}
.mentee-plans .plan-history-sub{ margin:6px 0 0 50px; color:var(--ink-2); font-size:13.5px; }
.mentee-plans .plan-history-search{ display:flex; align-items:center; gap:10px; }
.mentee-plans .plan-history-search__field{ position:relative; margin:0; }
.mentee-plans .plan-history-search__icon{
    position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--ink-3); font-size:14px; pointer-events:none;
}
.mentee-plans .plan-history-search .form-input{
    min-width:250px; padding:11px 14px 11px 40px; border-radius:var(--r);
    border:1px solid var(--line); background:var(--surface-3); color:var(--ink); transition:.2s;
}
.mentee-plans .plan-history-search .form-input:focus{
    outline:none; background:var(--surface); border-color:var(--pp); box-shadow:0 0 0 4px var(--pp-ring);
}
.mentee-plans .plan-history-search .btn{
    display:inline-flex; align-items:center; gap:8px; padding:11px 18px; border-radius:var(--r); font-weight:600;
    border:1px solid var(--pp); color:var(--pp); background:var(--surface); transition:.2s;
}
.mentee-plans .plan-history-search .btn:hover{ background:var(--pp); color:#fff; }
.mentee-plans .plan-history-empty{ text-align:center; padding:34px 0; color:var(--ink-3); }

.mentee-plans .plan-history-table{ border:1px solid var(--line); border-radius:var(--r); overflow:hidden; }
.mentee-plans .plan-history-table__head,
.mentee-plans .plan-history-item{
    display:grid; align-items:center; gap:16px; padding:16px 22px;
    grid-template-columns:1.8fr .9fr 1fr 2fr 1.7fr;
}
.mentee-plans .plan-history-table__head{
    background:var(--surface-2); border-bottom:1px solid var(--line);
    font-size:11.5px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--ink-3);
}
.mentee-plans .plan-history-item{ border-bottom:1px solid var(--line); transition:background .2s; }
.mentee-plans .plan-history-item:last-child{ border-bottom:0; }
.mentee-plans .plan-history-item:hover{ background:var(--surface-2); }
.mentee-plans .plan-history-item__name{ font-weight:700; color:var(--ink); }
.mentee-plans .plan-history-item__id{ margin-top:3px; font-size:12px; color:var(--ink-3); word-break:break-all; }
.mentee-plans .plan-history-item__amount{ font-weight:800; color:var(--ink); }
.mentee-plans .plan-history-item__period{ display:flex; flex-wrap:wrap; align-items:center; gap:8px; font-size:13px; color:var(--ink-2); }
.mentee-plans .plan-history-item__arrow{ color:var(--ink-3); font-size:11px; }

.mentee-plans .session-status{
    display:inline-flex; align-items:center; gap:7px;
    padding:5px 12px; border-radius:var(--r); font-size:12px; font-weight:700;
}
.mentee-plans .session-status::before{ content:""; width:7px; height:7px; border-radius:50%; background:currentColor; }
.mentee-plans .session-status.completed{ background:var(--ok-soft); color:var(--ok); }
.mentee-plans .session-status.pending{ background:var(--surface-3); color:var(--ink-3); }
.mentee-plans .session-status.cancelled{ background:var(--bad-soft); color:var(--bad); }

.mentee-plans .plan-history-actions{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.mentee-plans .plan-history-invoice-link{
    display:inline-flex; align-items:center; gap:8px; font-weight:600; font-size:13px;
    color:var(--pp); text-decoration:none;
}
.mentee-plans .plan-history-invoice-link:hover{ text-decoration:underline; }
.mentee-plans .plan-history-icon-btn{
    width:36px; height:36px; border-radius:var(--r); display:inline-flex; align-items:center; justify-content:center;
    border:1px solid var(--line); background:var(--surface); color:var(--ink-2); cursor:pointer; text-decoration:none; font-size:13px;
    transition:.2s;
}
.mentee-plans .plan-history-icon-btn:hover{ background:var(--pp); border-color:var(--pp); color:#fff; transform:translateY(-2px); box-shadow:0 8px 16px -8px var(--pp-ring); }

/* ---------- responsive ---------- */
@media (max-width:1100px){
    .mentee-plans .plan-history-table__head,
    .mentee-plans .plan-history-item{ grid-template-columns:1.6fr .9fr 1fr 1.8fr 1.5fr; gap:12px; padding:14px 16px; }
}
@media (max-width:820px){
    .mentee-plans .plan-history-table__head{ display:none; }
    .mentee-plans .plan-history-item{ grid-template-columns:1fr 1fr; gap:14px 16px; padding:18px; }
    .mentee-plans .plan-history-item__plan{ grid-column:1 / -1; }
    .mentee-plans .plan-history-item__period,
    .mentee-plans .plan-history-item__invoice{ grid-column:1 / -1; }
    .mentee-plans .plan-history-item > [data-label]::before{
        content:attr(data-label); display:block; margin-bottom:4px;
        font-size:10.5px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--ink-3);
    }
    .mentee-plans .plan-history-item__period[data-label]::before{ flex-basis:100%; }
    .mentee-plans .plan-history-search{ width:100%; }
    .mentee-plans .plan-history-search__field{ flex:1; }
    .mentee-plans .plan-history-search .form-input{ min-width:0; width:100%; }
    .mentee-plans .plan-history-sub{ margin-left:0; }
}
@media (max-width:560px){
    .mentee-plans .mentee-plans__current{ padding:22px 20px 22px 24px; }
    .mentee-plans .mentee-plans__current-name{ font-size:26px; }
    .mentee-plans .plan-history{ padding:20px 16px; }
    .mentee-plans .plan-billing-toggle__btn{ padding:10px 16px; }
    .mentee-plans .plan-price-amount{ font-size:40px; }
}
@media (prefers-reduced-motion:reduce){
    .mentee-plans *{ animation:none !important; transition:none !important; }
}
</style>

<div class="dash-layout mentee-plans">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between">
            <div>
                <div class="dash-title">
                    <span class="dash-title__icon" aria-hidden="true"><i class="fa-solid fa-crown"></i></span>
                    Subscription Plans
                </div>
                <div class="dash-subtitle">Choose a plan to unlock mentoring benefits.</div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success mentee-plans__alert">
            <span class="alert-icon"><i class="fa-solid fa-check"></i></span>
            <div class="mentee-plans__alert-text">{{ session('success') }}</div>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-error mentee-plans__alert">
            <span class="alert-icon"><i class="fa-solid fa-exclamation"></i></span>
            <div class="mentee-plans__alert-text">{{ session('error') }}</div>
        </div>
        @endif

        @if($current)
        @php
            $cancelPreview = $cancellation ?? ['can_cancel' => false, 'quote' => null, 'request' => null];
            $cancelQuote = $cancelPreview['quote'] ?? null;
            $cancelRequest = $cancelPreview['request'] ?? null;
            $daysLeft = max(0, $current->daysRemaining());
        @endphp
        <section class="card mentee-plans__current" aria-label="Current subscription">
            <div class="mentee-plans__current-top">
                <div class="mentee-plans__current-main">
                    <div class="mentee-plans__current-eyebrow"><i class="fa-solid fa-circle-check"></i> Current plan</div>
                    <h2 class="mentee-plans__current-name">{{ $current->plan->name ?? 'Plan' }}</h2>
                    <div class="mentee-plans__current-meta">
                        <span class="mentee-plans__meta-pill">
                            <i class="fa-regular fa-calendar-check"></i>
                            Active until {{ $current->expires_at?->format('d M Y') }}
                        </span>
                        <span class="mentee-plans__meta-pill mentee-plans__meta-pill--accent">
                            <i class="fa-solid fa-hourglass-half"></i>
                            {{ $daysLeft }} {{ $daysLeft === 1 ? 'day' : 'days' }} left
                        </span>
                    </div>
                </div>

                @if(!empty($cancelRequest) && ($cancelRequest['status'] ?? '') === 'pending')
                <div class="mentee-plans__cancel-note">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>
                        Cancellation requested. Refund ₹{{ number_format((float) ($cancelRequest['refund_amount'] ?? 0), 2) }}
                        pending admin approval.
                    </span>
                </div>
                @elseif(!empty($cancelPreview['can_cancel']) && $cancelQuote)
                <div class="mentee-plans__cancel">
                    <p class="mentee-plans__cancel-summary"><i class="fa-solid fa-circle-info"></i><span>{{ $cancelQuote['summary'] }}</span></p>
                    <form action="{{ route('mentee.plans.cancel') }}" method="POST"
                          onsubmit="return confirm('Submit a yearly cancellation request? Current month is kept and unused months will be refunded after approval.');">
                        @csrf
                        <input type="hidden" name="subscription_id" value="{{ $current->subscription_id }}">
                        <button type="submit" class="btn btn-ghost btn-sm"><i class="fa-solid fa-ban"></i> Request cancellation</button>
                    </form>
                </div>
                @endif
            </div>

            <div class="mentee-plans__benefits">
                <div class="mentee-plans__benefits-label"><i class="fa-solid fa-gift"></i> Free benefits left</div>
                @include('frontend.mentee.partials.benefit-balance', ['benefitBalance' => $benefitBalance ?? []])
            </div>
        </section>
        @endif

        @if($plans->isEmpty())
        <div class="empty-state mentee-plans__empty">
            <div class="empty-state-icon" aria-hidden="true"><i class="fa-regular fa-gem"></i></div>
            <h3>No plans available</h3>
            <p>Check back soon — plans will appear here once published.</p>
        </div>
        @else
        <div class="mentee-plans__billing-wrap">
            <div class="plan-billing-toggle" role="tablist" aria-label="Billing period">
                <button type="button" class="plan-billing-toggle__btn is-active" data-billing-toggle="monthly" role="tab" aria-selected="true">
                    <i class="fa-regular fa-calendar"></i> Monthly
                </button>
                <button type="button" class="plan-billing-toggle__btn" data-billing-toggle="yearly" role="tab" aria-selected="false">
                    <i class="fa-regular fa-calendar-days"></i> Yearly
                    <span class="plan-billing-toggle__save"><i class="fa-solid fa-bolt"></i> Save more</span>
                </button>
            </div>
        </div>

        <div class="plan-cards-grid">
            @foreach($plans as $plan)
            @php
                $periods = [];
                foreach (['monthly' => $quotesMonthly, 'yearly' => $quotesYearly] as $billingKey => $quoteMap) {
                    $pricing = $plan->pricingBreakdown($billingKey);
                    $quote = $quoteMap[$plan->id] ?? null;
                    $isUpgrade = (bool) ($quote['is_upgrade'] ?? false);
                    $credit = $quote['credit'] ?? [];
                    $creditAmount = (float) ($credit['amount'] ?? 0);
                    $price = (float) ($quote['payable'] ?? $pricing['total']);
                    $planTotal = (float) ($quote['plan_total'] ?? $pricing['total']);
                    $isCurrent = $currentPlanId && (int) $currentPlanId === (int) $plan->id;
                    $benefits = $plan->benefitSummary($billingKey);
                    $discountActive = ! empty($pricing['discount_active']) && (float) ($pricing['discount_percent'] ?? 0) > 0;
                    $discountPct = $discountActive
                        ? rtrim(rtrim(number_format((float) $pricing['discount_percent'], 2, '.', ''), '0'), '.')
                        : null;
                    $listTotal = (float) ($pricing['original_total'] ?? $pricing['original_base'] ?? 0);
                    $strikePrice = $discountActive
                        ? $listTotal
                        : (($isUpgrade && $planTotal > $price) ? $planTotal : 0);
                    $periods[$billingKey] = compact(
                        'pricing', 'quote', 'isUpgrade', 'credit', 'creditAmount', 'price', 'planTotal',
                        'isCurrent', 'benefits', 'discountActive', 'discountPct', 'strikePrice'
                    );
                }
                $accent = $plan->color ?: '#f59e0b';
                $badge = $plan->badgePalette();
                $offer = $plan->publicDiscount();
                $isCurrentPlan = $currentPlanId && (int) $currentPlanId === (int) $plan->id;
                // NOTE: trim() hata diya — pehle leading space trim ho jata tha aur class "plan-period-cardis-current" ban jati thi
                $cardMods = ($isCurrentPlan ? ' is-current' : '') . ($plan->is_featured ? ' is-featured' : '');
            @endphp
            <article class="card plan-period-card{{ $cardMods }}" style="--plan-accent: {{ $accent }}; --plan-badge-bg: {{ $badge['bg'] }}; --plan-badge-text: {{ $badge['text'] }};">
                @if($plan->is_featured || $plan->badge_label)
                <div class="plan-period-card__badge">
                    <i class="fa-solid fa-star"></i>
                    {{ $plan->badge_label ?: 'Featured' }}
                </div>
                @endif

                <header class="plan-period-card__head">
                    <div class="plan-period-card__icon" aria-hidden="true">
                        <i class="fa-solid {{ $plan->is_featured ? 'fa-crown' : 'fa-gem' }}"></i>
                    </div>
                    <div class="plan-period-card__title-row">
                        <h3 class="plan-period-card__name">{{ $plan->name }}</h3>
                        @if($isCurrentPlan)
                            <span class="plan-current-chip"><i class="fa-solid fa-circle-check"></i> Current</span>
                        @endif
                    </div>
                    @if($plan->description)
                    <p class="plan-period-card__desc">{{ $plan->description }}</p>
                    @endif
                </header>

                @foreach(['monthly', 'yearly'] as $billingKey)
                @php $p = $periods[$billingKey]; @endphp
                <div class="plan-period-block" data-billing="{{ $billingKey }}" @if($billingKey !== 'monthly') hidden @endif>
                    <div class="plan-period-block__price">
                        @if($p['price'] <= 0)
                        <div class="plan-price-free">{{ $p['isUpgrade'] ? 'No extra cost' : 'Free' }}</div>
                        @else
                        <div class="plan-price-row">
                            <span class="plan-price-amount">₹{{ number_format($p['price'], 0) }}</span>
                            <span class="plan-price-period">/{{ $billingKey === 'yearly' ? 'yr' : 'mo' }}</span>
                            @if($p['discountActive'])
                            <span class="plan-discount-badge"><i class="fa-solid fa-tag"></i> {{ $p['discountPct'] }}% off</span>
                            @endif
                        </div>
                        @if($p['strikePrice'] > $p['price'] || $p['discountActive'])
                        <div class="plan-price-savings">
                            @if($p['strikePrice'] > $p['price'])
                            <span class="plan-price-strike">₹{{ number_format($p['strikePrice'], 0) }}</span>
                            @endif
                            @if($p['discountActive'])
                            <span class="plan-price-save">Save ₹{{ number_format((float) $p['pricing']['discount_amount'], 0) }}</span>
                            @if(!empty($offer['expires_at']))
                            <span class="plan-price-until">until {{ \Carbon\Carbon::parse($offer['expires_at'])->format('d M Y') }}</span>
                            @endif
                            @endif
                        </div>
                        @endif
                        @if($p['isUpgrade'] && $p['creditAmount'] > 0)
                        <div class="plan-price-credit">
                            <i class="fa-solid fa-rotate-left"></i>
                            ₹{{ number_format($p['creditAmount'], 0) }} unused-day credit applied
                        </div>
                        @endif
                        @if($p['pricing']['tax_total'] > 0)
                        <div class="plan-price-tax">
                            @if($p['discountActive'])
                            List ₹{{ number_format((float) $p['pricing']['original_base'], 0) }}
                            − {{ $p['discountPct'] }}% (₹{{ number_format((float) $p['pricing']['discount_amount'], 2) }})
                            = ₹{{ number_format((float) $p['pricing']['base'], 0) }}
                            @else
                            Base ₹{{ number_format((float) $p['pricing']['base'], 0) }}
                            @endif
                            @foreach(($p['pricing']['taxes'] ?? []) as $taxLine)
                            + {{ $taxLine['code'] }} {{ rtrim(rtrim(number_format((float) $taxLine['percent'], 2, '.', ''), '0'), '.') }}% (₹{{ number_format((float) $taxLine['amount'], 2) }})
                            @endforeach
                        </div>
                        @endif
                        @endif
                        <div class="plan-price-cycle">
                            <i class="fa-regular fa-calendar-days"></i>
                            <span>{{ $plan->billingDaysFor($billingKey) }}-day billing cycle{{ $p['isUpgrade'] ? ' · starts today' : '' }}</span>
                        </div>
                        @if($p['isUpgrade'] && $p['creditAmount'] > 0 && (int) ($p['credit']['remaining_days'] ?? 0) > 0)
                        <div class="plan-price-credit-detail">
                            {{ (int) $p['credit']['remaining_days'] }} unused day{{ (int) $p['credit']['remaining_days'] === 1 ? '' : 's' }} of {{ $p['credit']['from_plan_name'] ?? 'your current plan' }} credited at ₹{{ number_format((float) ($p['credit']['daily_rate'] ?? 0), 2) }}/day.
                            Benefits reset with this plan.
                        </div>
                        @endif
                    </div>

                    @if(count($p['benefits']))
                    <ul class="plan-benefits">
                        @foreach($p['benefits'] as $row)
                        <li class="plan-benefits__item">
                            <span class="plan-benefits__check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                            <span>{{ $row['label'] }}{{ $row['value'] !== '' ? ': '.$row['value'] : '' }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="plan-benefits__empty">No {{ $billingKey }} benefits listed for this plan.</p>
                    @endif

                    <div class="plan-period-block__cta">
                        @if($p['isCurrent'])
                        <div class="plan-current-label"><i class="fa-solid fa-circle-check"></i> Current plan</div>
                        @else
                        <button type="button"
                                class="btn btn-primary plan-period-card__cta"
                                data-plan-id="{{ encrypt_id($plan->id) }}"
                                data-plan-name="{{ e($plan->name) }}"
                                data-billing="{{ $billingKey }}"
                                onclick="subscribePlan(this)">
                            <span>{{ $current ? ($p['price'] <= 0 ? 'Switch at no extra cost' : 'Upgrade · ₹'.number_format($p['price'], 0)) : ($p['price'] <= 0 ? 'Activate free' : 'Subscribe') }}</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </article>
            @endforeach
        </div>
        @endif

        @if($history->isNotEmpty() || ($uninvoiced ?? collect())->isNotEmpty() || request()->filled('search'))
        <section class="card plan-history" aria-label="Subscription history">
            <div class="plan-history-head">
                <div>
                    <h3 class="plan-history-title"><i class="fa-solid fa-clock-rotate-left"></i> Subscription history</h3>
                    <p class="plan-history-sub">Invoices and past plan changes</p>
                </div>
                <form method="GET" action="{{ route('mentee.plans') }}" class="plan-history-search">
                    <label class="plan-history-search__field">
                        <span class="plan-history-search__icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="search" name="search" class="form-input" value="{{ $search ?? request('search') }}"
                               placeholder="Search plan or ID…" autocomplete="off" aria-label="Search plan or ID">
                    </label>
                    <button type="submit" class="btn btn-outline btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                </form>
            </div>

            @if($history->isEmpty() && ($uninvoiced ?? collect())->isEmpty())
            <p class="plan-history-empty">No subscriptions match that search.</p>
            @else
            <div class="plan-history-table" role="table" aria-label="Subscription history">
                <div class="plan-history-table__head" role="row">
                    <span role="columnheader">Plan</span>
                    <span role="columnheader">Amount</span>
                    <span role="columnheader">Status</span>
                    <span role="columnheader">Period</span>
                    <span role="columnheader">Invoice</span>
                </div>
                @foreach($uninvoiced ?? [] as $sub)
                @php
                    $subStatus = $sub->status ?: 'expired';
                    $statusClass = match ($subStatus) {
                        'active' => 'completed',
                        'cancelled' => 'cancelled',
                        default => 'pending',
                    };
                @endphp
                <article class="plan-history-item" role="row">
                    <div class="plan-history-item__plan" role="cell">
                        <div class="plan-history-item__name">{{ $sub->plan->name ?? 'N/A' }}</div>
                        @if($sub->subscription_id)
                        <div class="plan-history-item__id">{{ $sub->subscription_id }}</div>
                        @endif
                    </div>
                    <div class="plan-history-item__amount" role="cell" data-label="Amount">₹{{ number_format((float) $sub->amount_paid, 0) }}</div>
                    <div class="plan-history-item__status" role="cell" data-label="Status">
                        <span class="session-status {{ $statusClass }}">{{ ucfirst($subStatus) }}</span>
                    </div>
                    <div class="plan-history-item__period" role="cell" data-label="Period">
                        @if($sub->starts_at && $sub->expires_at)
                            <span>{{ $sub->starts_at->format('d M Y') }}</span>
                            <span class="plan-history-item__arrow" aria-hidden="true"><i class="fa-solid fa-arrow-right-long"></i></span>
                            <span>{{ $sub->expires_at->format('d M Y') }}</span>
                        @else
                            —
                        @endif
                    </div>
                    <div class="plan-history-item__invoice" role="cell" data-label="Invoice">
                        <form method="POST" action="{{ route('mentee.subscriptions.invoice', $sub->id) }}" style="margin:0;">
                            @csrf
                            <button type="submit" class="plan-history-icon-btn" title="Generate invoice" aria-label="Generate invoice">
                                <i class="fa-solid fa-file-circle-plus"></i>
                            </button>
                        </form>
                    </div>
                </article>
                @endforeach
                @foreach($history as $invoice)
                @php
                    $isCurrentRow = $currentInvoiceId && (int) $invoice->id === (int) $currentInvoiceId && $current?->isActive();
                    $rowStatus = $isCurrentRow
                        ? 'active'
                        : (($invoice->subscription->status ?? '') === 'cancelled' && (int) $invoice->id === (int) $currentInvoiceId
                            ? 'cancelled'
                            : 'expired');
                    $statusClass = match ($rowStatus) {
                        'active' => 'completed',
                        'cancelled' => 'cancelled',
                        default => 'pending',
                    };
                    $startsAt = $invoice->subscription_starts_at;
                    $expiresAt = $invoice->subscription_expires_at;
                @endphp
                <article class="plan-history-item" role="row">
                    <div class="plan-history-item__plan" role="cell">
                        <div class="plan-history-item__name">{{ $invoice->plan_name ?: ($invoice->plan->name ?? 'N/A') }}</div>
                        @if($invoice->subscription?->subscription_id)
                        <div class="plan-history-item__id">{{ $invoice->subscription->subscription_id }}</div>
                        @endif
                    </div>
                    <div class="plan-history-item__amount" role="cell" data-label="Amount">₹{{ number_format((float) $invoice->total_amount, 0) }}</div>
                    <div class="plan-history-item__status" role="cell" data-label="Status">
                        <span class="session-status {{ $statusClass }}">{{ ucfirst($rowStatus) }}</span>
                    </div>
                    <div class="plan-history-item__period" role="cell" data-label="Period">
                        @if($startsAt && $expiresAt)
                            <span>{{ $startsAt->format('d M Y') }}</span>
                            <span class="plan-history-item__arrow" aria-hidden="true"><i class="fa-solid fa-arrow-right-long"></i></span>
                            <span>{{ $expiresAt->format('d M Y') }}</span>
                        @else
                            —
                        @endif
                    </div>
                    <div class="plan-history-item__invoice" role="cell" data-label="Invoice">
                        <div class="plan-history-actions">
                            <a href="{{ route('mentee.invoices.show', $invoice) }}" class="plan-history-invoice-link">
                                <i class="fa-solid fa-file-invoice"></i> {{ $invoice->invoice_number }}
                            </a>
                            <a href="{{ route('mentee.invoices.download', $invoice) }}"
                               class="plan-history-icon-btn"
                               title="Download invoice"
                               aria-label="Download invoice">
                                <i class="fa-solid fa-download"></i>
                            </a>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
            @endif

            @include('frontend.partials.pagination', ['paginator' => $history])
        </section>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function initPlanBillingToggle() {
    const buttons = document.querySelectorAll('[data-billing-toggle]');
    if (!buttons.length) return;

    function setBilling(billing) {
        buttons.forEach((btn) => {
            const active = btn.getAttribute('data-billing-toggle') === billing;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        document.querySelectorAll('.plan-period-block').forEach((block) => {
            block.hidden = block.getAttribute('data-billing') !== billing;
        });
    }

    buttons.forEach((btn) => {
        btn.addEventListener('click', () => setBilling(btn.getAttribute('data-billing-toggle')));
    });
})();

function subscribePlan(btn) {
    const planId = btn.getAttribute('data-plan-id');
    const planName = btn.getAttribute('data-plan-name') || 'plan';
    const billing = btn.getAttribute('data-billing') || 'monthly';

    AjaxPost(`{{ url('/mentee/plans') }}/${planId}/subscribe`, { billing }, {
        btn,
        loader: true,
        onSuccess: (order) => {
            if (order.free) {
                showToast('success', order.message || 'Plan activated!');
                setTimeout(() => location.reload(), 900);
                return;
            }

            const options = {
                key: order.key,
                amount: order.amount,
                currency: order.currency || 'INR',
                name: order.name || 'Vedrix',
                description: order.description || ('Subscribe to ' + planName),
                order_id: order.order_id,
                prefill: order.prefill || {},
                theme: { color: '#f59e0b' },
                handler: function (response) {
                    AjaxPost(`{{ url('/mentee/plans') }}/${planId}/verify`, {
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_signature: response.razorpay_signature,
                    }, {
                        loader: true,
                        onSuccess: (data) => {
                            showToast('success', data.message || 'Subscription activated!');
                            setTimeout(() => location.reload(), 1000);
                        },
                        onError: (err) => showToast('error', err.message || 'Could not verify payment.'),
                    });
                },
            };

            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function () {
                showToast('error', 'Payment failed. Please try again.');
            });
            rzp.open();
        },
        onError: (err) => showToast('error', err.message || 'Could not start subscription.'),
    });
}
</script>
@endpush