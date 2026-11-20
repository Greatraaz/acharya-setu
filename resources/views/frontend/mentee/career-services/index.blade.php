@extends('frontend.layouts.app')
@section('title', 'Career Services — Vedrix')

@push('styles')
<style>
/* Career Services hub — premium, theme-aligned */
.cs-hub{
    --cs: var(--brand, #f59e0b);
    --cs-light: var(--brand-light, #fcd34d);
    --cs-dark: var(--brand-dark, #d97706);
    --cs-soft: var(--brand-muted, rgba(245,158,11,.12));
    --cs-ring: color-mix(in srgb, var(--cs) 28%, transparent);
    --cs-ink: var(--text, #09090b);
    --cs-muted: var(--text-2, #52525b);
    --cs-faint: var(--text-3, #71717a);
    --cs-line: var(--border, rgba(0,0,0,.07));
    --cs-line-2: var(--border-2, rgba(0,0,0,.12));
    --cs-surface: var(--card-bg, #fff);
    --cs-surface-2: var(--bg-2, #f4f4f5);
    --cs-surface-3: var(--bg-3, #fff);
    --cs-r: var(--radius-btn, 8px);
    --cs-rl: var(--radius-lg, 16px);
    --cs-ok: var(--success, #22c55e);
    --cs-ok-bg: var(--success-muted, rgba(34,197,94,.1));
    --cs-warn: var(--warning, #f59e0b);
    --cs-warn-bg: var(--warning-muted, rgba(245,158,11,.12));
    --cs-err: var(--error, #ef4444);
    --cs-err-bg: var(--error-muted, rgba(239,68,68,.1));
    --cs-sh: var(--shadow, 0 4px 24px rgba(0,0,0,.08));
    --cs-shl: var(--shadow-lg, 0 16px 48px rgba(0,0,0,.12));
    font-family: var(--font-body, "DM Sans", sans-serif);
}

.cs-hub .dash-title{
    font-family: var(--font-head, "Sora", sans-serif);
    display:flex; align-items:center; gap:12px;
    letter-spacing:-.02em;
}
.cs-hub .dash-title__icon{
    width:42px; height:42px; border-radius:var(--cs-r); flex:none;
    display:inline-flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg, var(--cs), var(--cs-dark));
    color:#fff; box-shadow:0 10px 22px -8px var(--cs-ring);
}
.cs-hub .dash-title__icon svg{ width:18px; height:18px; }
.cs-hub .dash-subtitle{ color:var(--cs-muted); margin-top:6px; max-width:560px; }

/* Intro band */
.cs-hub__intro{
    position:relative; overflow:hidden;
    margin:4px 0 22px; padding:22px 22px 20px;
    border:1px solid var(--cs-line);
    border-radius:var(--cs-rl);
    background:
        radial-gradient(520px 180px at 100% 0%, color-mix(in srgb, var(--cs) 16%, transparent), transparent 70%),
        radial-gradient(380px 160px at 0% 100%, color-mix(in srgb, var(--cs) 8%, transparent), transparent 65%),
        var(--cs-surface);
    box-shadow:var(--cs-sh);
}
.cs-hub__intro::before{
    content:""; position:absolute; left:0; top:0; bottom:0; width:4px;
    background:linear-gradient(180deg, var(--cs), var(--cs-dark));
}
.cs-hub__intro-top{
    display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:16px;
}
.cs-hub__eyebrow{
    display:inline-flex; align-items:center; gap:8px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; font-weight:700; letter-spacing:.12em; text-transform:uppercase;
    color:var(--cs); background:var(--cs-soft); padding:6px 11px; border-radius:var(--cs-r);
}
.cs-hub__intro h2{
    margin:12px 0 8px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:clamp(22px, 3vw, 28px); line-height:1.15; font-weight:800;
    letter-spacing:-.03em; color:var(--cs-ink);
}
.cs-hub__intro p{
    margin:0; max-width:520px; font-size:14px; line-height:1.55; color:var(--cs-muted);
}
.cs-hub__steps{
    display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px;
    margin-top:18px;
}
@media (max-width:720px){
    .cs-hub__steps{ grid-template-columns:1fr; }
}
.cs-hub__step{
    display:flex; gap:10px; align-items:flex-start;
    padding:12px 13px; border-radius:var(--cs-r);
    background:color-mix(in srgb, var(--cs-surface-2) 88%, transparent);
    border:1px solid var(--cs-line);
}
.cs-hub__step-num{
    width:24px; height:24px; border-radius:999px; flex:none;
    display:inline-flex; align-items:center; justify-content:center;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; font-weight:800; color:#fff;
    background:linear-gradient(135deg, var(--cs), var(--cs-dark));
}
.cs-hub__step strong{
    display:block; font-size:12.5px; font-weight:700; color:var(--cs-ink); margin-bottom:2px;
}
.cs-hub__step span{ font-size:12px; line-height:1.4; color:var(--cs-faint); }

/* Service cards */
.cs-hub__grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:18px;
    margin-bottom:24px;
}
@media (max-width:860px){
    .cs-hub__grid{ grid-template-columns:1fr; }
}
.cs-service{
    position:relative; overflow:hidden;
    background:var(--cs-surface);
    border:1px solid var(--cs-line);
    border-radius:var(--cs-rl);
    padding:0;
    display:flex; flex-direction:column;
    min-height:100%;
    box-shadow:var(--cs-sh);
    transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
}
.cs-service:hover{
    transform:translateY(-2px);
    border-color:color-mix(in srgb, var(--cs) 35%, var(--cs-line));
    box-shadow:var(--cs-shl);
}
.cs-service__glow{
    position:absolute; inset:auto -20% -40% auto; width:220px; height:220px;
    border-radius:50%; pointer-events:none;
    background:radial-gradient(circle, color-mix(in srgb, var(--cs) 22%, transparent), transparent 68%);
}
.cs-service__body{
    position:relative; z-index:1;
    padding:20px 20px 18px;
    display:flex; flex-direction:column; gap:14px;
    flex:1;
}
.cs-service__top{ display:flex; align-items:flex-start; gap:14px; }
.cs-service__icon{
    width:48px; height:48px; border-radius:12px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    background:linear-gradient(145deg, var(--cs-soft), color-mix(in srgb, var(--cs) 4%, transparent));
    color:var(--cs);
    border:1px solid color-mix(in srgb, var(--cs) 18%, transparent);
}
.cs-service__icon svg{ width:22px; height:22px; }
.cs-service__title{
    margin:0 0 4px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:16px; font-weight:800; letter-spacing:-.02em; color:var(--cs-ink);
}
.cs-service__hint{
    margin:0; font-size:13px; line-height:1.5; color:var(--cs-muted);
}
.cs-service__ent{
    display:flex; gap:10px; align-items:flex-start;
    padding:12px 13px; border-radius:var(--cs-r);
    background:var(--cs-surface-2);
    border:1px solid var(--cs-line);
}
.cs-service__ent--ok{
    background:var(--cs-ok-bg);
    border-color:color-mix(in srgb, var(--cs-ok) 25%, transparent);
}
.cs-service__ent--warn{
    background:var(--cs-warn-bg);
    border-color:color-mix(in srgb, var(--cs-warn) 28%, transparent);
}
.cs-service__ent-ico{
    width:28px; height:28px; border-radius:8px; flex:none;
    display:inline-flex; align-items:center; justify-content:center;
    background:var(--cs-surface); color:var(--cs); border:1px solid var(--cs-line);
}
.cs-service__ent--ok .cs-service__ent-ico{ color:var(--cs-ok); }
.cs-service__ent--warn .cs-service__ent-ico{ color:var(--cs-warn); }
.cs-service__ent-ico svg{ width:14px; height:14px; }
.cs-service__ent p{
    margin:0; font-size:12.5px; line-height:1.5; color:var(--cs-muted);
}
.cs-service__ent strong{ color:var(--cs-ink); font-weight:700; }
.cs-service__meta{
    display:flex; flex-wrap:wrap; gap:8px; align-items:center;
}
.cs-pill{
    display:inline-flex; align-items:center; gap:6px;
    font-size:11px; font-weight:700; padding:5px 10px; border-radius:999px;
    font-family: var(--font-head, "Sora", sans-serif);
}
.cs-pill--ok{ background:var(--cs-ok-bg); color:var(--cs-ok); }
.cs-pill--warn{ background:var(--cs-warn-bg); color:var(--cs-warn); }
.cs-pill--muted{ background:var(--cs-surface-2); color:var(--cs-muted); border:1px solid var(--cs-line); }
.cs-service__footer{
    margin-top:auto; padding-top:4px;
    display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:14px;
}
.cs-service__price{
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:28px; font-weight:800; letter-spacing:-.03em; color:var(--cs); line-height:1;
}
.cs-service__price small{
    display:block; margin-top:6px;
    font-family: var(--font-body, "DM Sans", sans-serif);
    font-size:12px; font-weight:600; color:var(--cs-faint); letter-spacing:0;
}
.cs-service__cta{
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    min-width:180px; padding:11px 16px;
    border-radius:var(--cs-r)!important;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:13px; font-weight:700;
}
.cs-service__cta svg{ width:15px; height:15px; }
.cs-service__perks{
    display:grid; gap:8px;
    padding:0 20px 18px;
    position:relative; z-index:1;
}
.cs-service__perk{
    display:flex; align-items:center; gap:8px;
    font-size:12px; color:var(--cs-muted);
}
.cs-service__perk i,
.cs-service__perk svg{
    width:14px; height:14px; color:var(--cs); flex:none;
}

/* Requests table */
.cs-table-card{
    background:var(--cs-surface);
    border:1px solid var(--cs-line);
    border-radius:var(--cs-rl);
    overflow:hidden;
    box-shadow:var(--cs-sh);
}
.cs-table-card__head{
    display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
    padding:16px 18px;
    border-bottom:1px solid var(--cs-line);
    background:
        linear-gradient(90deg, color-mix(in srgb, var(--cs) 7%, transparent), transparent 55%),
        var(--cs-surface);
}
.cs-table-card__head h2{
    margin:0;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:15px; font-weight:800; letter-spacing:-.02em; color:var(--cs-ink);
}
.cs-table-card__count{
    display:inline-flex; align-items:center; gap:6px;
    font-size:12px; font-weight:700; color:var(--cs-muted);
    padding:5px 10px; border-radius:999px;
    background:var(--cs-surface-2); border:1px solid var(--cs-line);
}
.cs-hub .data-table{ width:100%; }
.cs-hub .data-table th{
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; letter-spacing:.06em; font-weight:700;
}
.cs-hub .data-table tbody tr{ transition: background .15s ease; }
.cs-hub .data-table tbody tr:hover{ background:color-mix(in srgb, var(--cs) 5%, transparent); }
.cs-type{
    display:inline-flex; align-items:center; gap:9px;
    font-weight:700; color:var(--cs-ink);
}
.cs-type__badge{
    width:28px; height:28px; border-radius:8px; flex:none;
    display:inline-flex; align-items:center; justify-content:center;
    background:var(--cs-soft); color:var(--cs);
}
.cs-type__badge svg{ width:14px; height:14px; }
.cs-invoice{
    display:inline-flex; align-items:center;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size:11.5px; font-weight:600; color:var(--cs-muted);
    padding:5px 9px; border-radius:999px;
    background:var(--cs-surface-2); border:1px solid var(--cs-line);
    text-decoration:none;
}
.cs-invoice:hover{ color:var(--cs); border-color:color-mix(in srgb, var(--cs) 30%, var(--cs-line)); }
.cs-empty{
    text-align:center; padding:44px 18px; color:var(--cs-faint);
}
.cs-empty__icon{
    width:52px; height:52px; margin:0 auto 12px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    background:var(--cs-soft); color:var(--cs);
}
.cs-empty__icon svg{ width:24px; height:24px; }
.cs-empty strong{
    display:block; color:var(--cs-ink); margin-bottom:4px; font-size:14px;
    font-family: var(--font-head, "Sora", sans-serif);
}
</style>
@endpush

@section('content')
@php
    $resumeQ = $quotes['resume'] ?? [];
    $linkedinQ = $quotes['linkedin'] ?? [];
    $freeLeft = ((int) data_get($resumeQ, 'entitlement.remaining', 0)) + ((int) data_get($linkedinQ, 'entitlement.remaining', 0));
@endphp
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content cs-hub">
        <div class="dash-header flex-between" style="gap:12px;flex-wrap:wrap;">
            <div>
                <div class="dash-title">
                    <span class="dash-title__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    Career Services
                </div>
                <div class="dash-subtitle">Expert resume &amp; LinkedIn reviews — included in your plan, or as a quick paid add-on.</div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;"><span class="alert-icon">✓</span><div style="font-size:13px;">{{ session('success') }}</div></div>
        @endif
        @if(session('error'))
        <div class="alert alert-error" style="margin-bottom:16px;"><span class="alert-icon">!</span><div style="font-size:13px;">{{ session('error') }}</div></div>
        @endif

        <section class="cs-hub__intro">
            <div class="cs-hub__intro-top">
                <div>
                    <div class="cs-hub__eyebrow">Vedrix career desk</div>
                    <h2>Polish what recruiters see first</h2>
                    <p>Submit once, get a reviewed deliverable on your dashboard. Plan benefits refresh on a rolling window — extras are available anytime as paid add-ons.</p>
                </div>
                @if($freeLeft > 0)
                <span class="cs-pill cs-pill--ok">{{ $freeLeft }} plan credit{{ $freeLeft === 1 ? '' : 's' }} available</span>
                @endif
            </div>
            <div class="cs-hub__steps">
                <div class="cs-hub__step">
                    <span class="cs-hub__step-num">1</span>
                    <div>
                        <strong>Choose a service</strong>
                        <span>Resume polish or LinkedIn rewrite guidance</span>
                    </div>
                </div>
                <div class="cs-hub__step">
                    <span class="cs-hub__step-num">2</span>
                    <div>
                        <strong>Share your file / URL</strong>
                        <span>Add notes about the role you’re targeting</span>
                    </div>
                </div>
                <div class="cs-hub__step">
                    <span class="cs-hub__step-num">3</span>
                    <div>
                        <strong>Download the deliverable</strong>
                        <span>Reviewed by Vedrix and ready to use</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="cs-hub__grid">
            @foreach([
                'resume' => [
                    'label' => 'Resume development',
                    'hint' => 'Turn your current CV into a sharper, role-ready resume.',
                    'cta' => 'Request resume review',
                    'perks' => ['Structure & impact rewrite', 'ATS-friendly formatting', 'Downloadable PDF deliverable'],
                ],
                'linkedin' => [
                    'label' => 'LinkedIn optimisation',
                    'hint' => 'Strengthen headline, about, and experience for better discovery.',
                    'cta' => 'Request LinkedIn review',
                    'perks' => ['Profile narrative refresh', 'Keyword & positioning tips', 'Actionable written guide'],
                ],
            ] as $key => $meta)
            @php
                $q = $quotes[$key] ?? [];
                $open = $q['open_request'] ?? null;
                $ent = $q['entitlement'] ?? [];
                $remaining = (int) ($ent['remaining'] ?? 0);
                $included = (bool) ($ent['included'] ?? false);
                $nextFree = $ent['next_free_at'] ?? null;
                $nextFreeLabel = null;
                if ($nextFree) {
                    try { $nextFreeLabel = \Illuminate\Support\Carbon::parse($nextFree)->format('d M Y'); } catch (\Throwable $e) { $nextFreeLabel = $nextFree; }
                }
                $entClass = $open || (!($q['is_free'] ?? false) && $included) ? 'cs-service__ent--warn' : (($q['is_free'] ?? false) ? 'cs-service__ent--ok' : '');
            @endphp
            <article class="cs-service">
                <div class="cs-service__glow" aria-hidden="true"></div>
                <div class="cs-service__body">
                    <div class="cs-service__top">
                        <div class="cs-service__icon" aria-hidden="true">
                            @if($key === 'resume')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            @endif
                        </div>
                        <div style="min-width:0;flex:1;">
                            <h3 class="cs-service__title">{{ $meta['label'] }}</h3>
                            <p class="cs-service__hint">{{ $meta['hint'] }}</p>
                        </div>
                    </div>

                    <div class="cs-service__ent {{ $entClass }}">
                        <span class="cs-service__ent-ico" aria-hidden="true">
                            @if($open)
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif($q['is_free'] ?? false)
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </span>
                        <p>
                            @if($open)
                                <strong>Open request · {{ $open['status_label'] }}</strong><br>
                                Finish this review before starting another {{ $key === 'resume' ? 'resume' : 'LinkedIn' }} request.
                            @elseif($q['is_free'] ?? false)
                                <strong>Included in your plan</strong><br>
                                {{ $ent['label'] ?? ($remaining.' free credit available now.') }}
                            @elseif($included && $nextFreeLabel)
                                <strong>Free benefit used</strong><br>
                                Next free credit on {{ $nextFreeLabel }}. Extra requests are ₹{{ number_format((float) ($q['amount'] ?? 0), 0) }}.
                            @else
                                <strong>Paid add-on</strong><br>
                                {{ $ent['label'] ?? ('Request anytime for ₹'.number_format((float) ($q['amount'] ?? 0), 0).'.') }}
                            @endif
                        </p>
                    </div>

                    <div class="cs-service__meta">
                        @if($open)
                            <span class="cs-pill cs-pill--warn">In progress</span>
                        @elseif($q['is_free'] ?? false)
                            <span class="cs-pill cs-pill--ok">{{ $remaining }} free left</span>
                            <span class="cs-pill cs-pill--muted">Plan benefit</span>
                        @elseif($included)
                            <span class="cs-pill cs-pill--muted">Next free {{ $nextFreeLabel ?: 'later' }}</span>
                            <span class="cs-pill cs-pill--warn">Add-on available</span>
                        @else
                            <span class="cs-pill cs-pill--muted">Paid add-on</span>
                        @endif
                    </div>

                    <div class="cs-service__footer">
                        <div class="cs-service__price">
                            @if($open)
                                Active
                                <small>View progress below</small>
                            @elseif($q['is_free'] ?? false)
                                Included
                                <small>no charge this time</small>
                            @else
                                ₹{{ number_format((float) ($q['amount'] ?? 0), 0) }}
                                <small>one-time add-on</small>
                            @endif
                        </div>
                        @if($open)
                        <a href="{{ route('mentee.career-services.show', $open['id']) }}" class="btn btn-primary cs-service__cta">
                            View open request
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        @else
                        <a href="{{ route('mentee.career-services.create', ['type' => $key]) }}" class="btn btn-primary cs-service__cta">
                            {{ $meta['cta'] }}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        @endif
                    </div>
                </div>
                <div class="cs-service__perks">
                    @foreach($meta['perks'] as $perk)
                    <div class="cs-service__perk">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ $perk }}
                    </div>
                    @endforeach
                </div>
            </article>
            @endforeach
        </div>

        <div class="cs-table-card">
            <div class="cs-table-card__head">
                <h2>Your requests</h2>
                <span class="cs-table-card__count">{{ $requests->total() }} total</span>
            </div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Invoice</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                        @php
                            $tone = match ($req->status) {
                                'completed' => 'success',
                                'submitted' => 'warning',
                                'pending_payment' => 'error',
                                default => 'muted',
                            };
                        @endphp
                        <tr>
                            <td>
                                <span class="cs-type">
                                    <span class="cs-type__badge" aria-hidden="true">
                                        @if($req->type === 'linkedin')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        @endif
                                    </span>
                                    {{ $req->typeLabel() }}
                                </span>
                            </td>
                            <td><span class="cs-status cs-status--{{ $tone }}">{{ $req->statusLabel() }}</span></td>
                            <td>
                                <div style="font-weight:600;color:var(--cs-ink);">{{ $req->created_at?->format('d M Y') }}</div>
                                <div style="font-size:11px;color:var(--cs-faint);">{{ $req->created_at?->format('h:i A') }}</div>
                            </td>
                            <td>
                                @if($req->invoice)
                                    <a href="{{ route('mentee.career-service-invoices.download', $req->invoice) }}" class="cs-invoice">{{ $req->invoice->invoice_number }}</a>
                                @else
                                    <span style="color:var(--cs-faint);font-size:12px;">—</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('mentee.career-services.show', $req) }}" class="btn btn-ghost btn-sm" style="border-radius:8px!important;">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <div class="cs-empty">
                                    <div class="cs-empty__icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <strong>No requests yet</strong>
                                    Start with a resume or LinkedIn review above — your history will show here.
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requests->hasPages())
            <div style="padding:12px 18px;border-top:1px solid var(--cs-line);">@include('frontend.partials.pagination', ['paginator' => $requests])</div>
            @endif
        </div>
    </div>
</div>
@endsection
