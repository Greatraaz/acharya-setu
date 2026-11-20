@extends('frontend.layouts.app')
@section('title', 'Mock Interviews — Vedrix')

@php
    $rate = (float) ($quote['rate_per_minute'] ?? 0);
    $ent = $quote['entitlement'] ?? [];
    $isFree = (bool) ($quote['is_free'] ?? false);
    $remaining = (int) ($ent['remaining'] ?? 0);
    $maxFree = $ent['max_free_duration'] ?? null;
    $nextFree = $ent['next_free_at'] ?? null;
    $nextFreeLabel = null;
    if ($nextFree) {
        try { $nextFreeLabel = \Illuminate\Support\Carbon::parse($nextFree)->format('d M Y'); } catch (\Throwable $e) { $nextFreeLabel = $nextFree; }
    }
@endphp

@push('styles')
<style>
.mi-hub{
    --mi: var(--brand, #f59e0b);
    --mi-light: var(--brand-light, #fcd34d);
    --mi-dark: var(--brand-dark, #d97706);
    --mi-soft: var(--brand-muted, rgba(245,158,11,.12));
    --mi-ring: color-mix(in srgb, var(--mi) 28%, transparent);
    --mi-ink: var(--text, #09090b);
    --mi-muted: var(--text-2, #52525b);
    --mi-faint: var(--text-3, #71717a);
    --mi-line: var(--border, rgba(0,0,0,.07));
    --mi-surface: var(--card-bg, #fff);
    --mi-surface-2: var(--bg-2, #f4f4f5);
    --mi-r: var(--radius-btn, 8px);
    --mi-rl: var(--radius-lg, 16px);
    --mi-ok: var(--success, #22c55e);
    --mi-ok-bg: var(--success-muted, rgba(34,197,94,.1));
    --mi-warn: var(--warning, #f59e0b);
    --mi-warn-bg: var(--warning-muted, rgba(245,158,11,.12));
    --mi-err: var(--error, #ef4444);
    --mi-err-bg: var(--error-muted, rgba(239,68,68,.1));
    --mi-sh: var(--shadow, 0 4px 24px rgba(0,0,0,.08));
    --mi-shl: var(--shadow-lg, 0 16px 48px rgba(0,0,0,.12));
    font-family: var(--font-body, "DM Sans", sans-serif);
}
.mi-hub .dash-title{
    font-family: var(--font-head, "Sora", sans-serif);
    display:flex; align-items:center; gap:12px;
    letter-spacing:-.02em;
}
.mi-hub .dash-title__icon{
    width:42px; height:42px; border-radius:var(--mi-r); flex:none;
    display:inline-flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg, var(--mi), var(--mi-dark));
    color:#fff; box-shadow:0 10px 22px -8px var(--mi-ring);
}
.mi-hub .dash-title__icon svg{ width:18px; height:18px; }
.mi-hub .dash-subtitle{ color:var(--mi-muted); margin-top:6px; max-width:560px; }

.mi-hub__intro{
    position:relative; overflow:hidden;
    margin:4px 0 22px; padding:22px 22px 20px;
    border:1px solid var(--mi-line);
    border-radius:var(--mi-rl);
    background:
        radial-gradient(520px 180px at 100% 0%, color-mix(in srgb, var(--mi) 16%, transparent), transparent 70%),
        radial-gradient(380px 160px at 0% 100%, color-mix(in srgb, var(--mi) 8%, transparent), transparent 65%),
        var(--mi-surface);
    box-shadow:var(--mi-sh);
}
.mi-hub__intro::before{
    content:""; position:absolute; left:0; top:0; bottom:0; width:4px;
    background:linear-gradient(180deg, var(--mi), var(--mi-dark));
}
.mi-hub__intro-top{
    display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:16px;
}
.mi-hub__eyebrow{
    display:inline-flex; align-items:center; gap:8px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; font-weight:700; letter-spacing:.12em; text-transform:uppercase;
    color:var(--mi); background:var(--mi-soft); padding:6px 11px; border-radius:var(--mi-r);
}
.mi-hub__intro h2{
    margin:12px 0 8px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:clamp(22px, 3vw, 28px); line-height:1.15; font-weight:800;
    letter-spacing:-.03em; color:var(--mi-ink);
}
.mi-hub__intro p{
    margin:0; max-width:540px; font-size:14px; line-height:1.55; color:var(--mi-muted);
}
.mi-hub__steps{
    display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px;
    margin-top:18px;
}
@media (max-width:720px){ .mi-hub__steps{ grid-template-columns:1fr; } }
.mi-hub__step{
    display:flex; gap:10px; align-items:flex-start;
    padding:12px 13px; border-radius:var(--mi-r);
    background:color-mix(in srgb, var(--mi-surface-2) 88%, transparent);
    border:1px solid var(--mi-line);
}
.mi-hub__step-num{
    width:24px; height:24px; border-radius:999px; flex:none;
    display:inline-flex; align-items:center; justify-content:center;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; font-weight:800; color:#fff;
    background:linear-gradient(135deg, var(--mi), var(--mi-dark));
}
.mi-hub__step strong{ display:block; font-size:12.5px; font-weight:700; color:var(--mi-ink); margin-bottom:2px; }
.mi-hub__step span{ font-size:12px; line-height:1.4; color:var(--mi-faint); }

.mi-book{
    position:relative; overflow:hidden;
    margin-bottom:24px;
    background:var(--mi-surface);
    border:1px solid var(--mi-line);
    border-radius:var(--mi-rl);
    box-shadow:var(--mi-sh);
    display:grid;
    grid-template-columns:minmax(0,1.4fr) minmax(220px,.8fr);
    gap:0;
}
@media (max-width:860px){ .mi-book{ grid-template-columns:1fr; } }
.mi-book__main{ position:relative; z-index:1; padding:22px 22px 20px; }
.mi-book__glow{
    position:absolute; inset:auto -10% -40% auto; width:240px; height:240px;
    border-radius:50%; pointer-events:none;
    background:radial-gradient(circle, color-mix(in srgb, var(--mi) 22%, transparent), transparent 68%);
}
.mi-book__icon{
    width:48px; height:48px; border-radius:12px;
    display:inline-flex; align-items:center; justify-content:center;
    background:linear-gradient(145deg, var(--mi-soft), color-mix(in srgb, var(--mi) 4%, transparent));
    color:var(--mi); border:1px solid color-mix(in srgb, var(--mi) 18%, transparent);
    margin-bottom:14px;
}
.mi-book__icon svg{ width:22px; height:22px; }
.mi-book__title{
    margin:0 0 6px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:18px; font-weight:800; letter-spacing:-.02em; color:var(--mi-ink);
}
.mi-book__hint{ margin:0 0 14px; font-size:13px; line-height:1.5; color:var(--mi-muted); max-width:480px; }
.mi-book__meta{ display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px; }
.mi-pill{
    display:inline-flex; align-items:center; gap:6px;
    font-size:11px; font-weight:700; padding:5px 10px; border-radius:999px;
    font-family: var(--font-head, "Sora", sans-serif);
}
.mi-pill--ok{ background:var(--mi-ok-bg); color:var(--mi-ok); }
.mi-pill--warn{ background:var(--mi-warn-bg); color:var(--mi-warn); }
.mi-pill--muted{ background:var(--mi-surface-2); color:var(--mi-muted); border:1px solid var(--mi-line); }
.mi-book__perks{ display:grid; gap:8px; }
.mi-book__perk{
    display:flex; align-items:center; gap:8px;
    font-size:12.5px; color:var(--mi-muted);
}
.mi-book__perk svg{ width:14px; height:14px; color:var(--mi); flex:none; }
.mi-book__side{
    position:relative; z-index:1;
    padding:22px;
    border-left:1px solid var(--mi-line);
    background:
        radial-gradient(220px 120px at 100% 0%, color-mix(in srgb, var(--mi) 12%, transparent), transparent 70%),
        var(--mi-surface-2);
    display:flex; flex-direction:column; justify-content:center; gap:12px;
}
@media (max-width:860px){
    .mi-book__side{ border-left:none; border-top:1px solid var(--mi-line); }
}
.mi-book__price{
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:32px; font-weight:800; letter-spacing:-.03em; color:var(--mi); line-height:1;
}
.mi-book__price small{
    display:block; margin-top:6px;
    font-family: var(--font-body, "DM Sans", sans-serif);
    font-size:12px; font-weight:600; color:var(--mi-faint); letter-spacing:0;
}
.mi-book__cta{
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    width:100%; padding:12px 16px;
    border-radius:var(--mi-r)!important;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:13px; font-weight:700;
}
.mi-book__cta svg{ width:15px; height:15px; }

.mi-table-card{
    background:var(--mi-surface);
    border:1px solid var(--mi-line);
    border-radius:var(--mi-rl);
    overflow:hidden;
    box-shadow:var(--mi-sh);
}
.mi-table-card__head{
    display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
    padding:16px 18px;
    border-bottom:1px solid var(--mi-line);
    background:
        linear-gradient(90deg, color-mix(in srgb, var(--mi) 7%, transparent), transparent 55%),
        var(--mi-surface);
}
.mi-table-card__head h2{
    margin:0;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:15px; font-weight:800; letter-spacing:-.02em; color:var(--mi-ink);
}
.mi-table-card__count{
    display:inline-flex; align-items:center; gap:6px;
    font-size:12px; font-weight:700; color:var(--mi-muted);
    padding:5px 10px; border-radius:999px;
    background:var(--mi-surface-2); border:1px solid var(--mi-line);
}
.mi-hub .data-table{ width:100%; }
.mi-hub .data-table th{
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; letter-spacing:.06em; font-weight:700;
}
.mi-hub .data-table tbody tr{ transition: background .15s ease; }
.mi-hub .data-table tbody tr:hover{ background:color-mix(in srgb, var(--mi) 5%, transparent); }
.mi-empty{
    text-align:center; padding:36px 20px; color:var(--mi-faint);
}
.mi-empty strong{
    display:block; margin-bottom:6px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:15px; font-weight:800; color:var(--mi-ink);
}
.mi-actions{ display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap; }
</style>
@endpush

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content mi-hub">
        <div class="dash-title">
            <span class="dash-title__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
            </span>
            Mock Interviews
        </div>
        <p class="dash-subtitle">Practice with Vedrix interviewers — book a slot, get confirmed, and join from here.</p>

        @if(session('success') || request('submitted'))
        <div class="alert alert-success" style="margin:16px 0;"><span class="alert-icon">✓</span><div style="font-size:13px;">{{ session('success') ?? 'Payment successful. Your mock interview request is under review.' }}</div></div>
        @endif
        @if(session('error'))
        <div class="alert alert-error" style="margin:16px 0;"><span class="alert-icon">!</span><div style="font-size:13px;">{{ session('error') }}</div></div>
        @endif

        <section class="mi-hub__intro" aria-label="How mock interviews work">
            <div class="mi-hub__intro-top">
                <div>
                    <span class="mi-hub__eyebrow">Interview Gym</span>
                    <h2>Real practice. Structured feedback.</h2>
                    <p>Request a timed mock interview, get confirmed by Vedrix, then join the live session when your slot opens.</p>
                </div>
            </div>
            <div class="mi-hub__steps">
                <div class="mi-hub__step">
                    <span class="mi-hub__step-num">1</span>
                    <div><strong>Request a slot</strong><span>Pick time, duration, and target role.</span></div>
                </div>
                <div class="mi-hub__step">
                    <span class="mi-hub__step-num">2</span>
                    <div><strong>Get confirmed</strong><span>Our team reviews and locks your interview.</span></div>
                </div>
                <div class="mi-hub__step">
                    <span class="mi-hub__step-num">3</span>
                    <div><strong>Join &amp; improve</strong><span>Live session with shared notes after.</span></div>
                </div>
            </div>
        </section>

        <article class="mi-book">
            <div class="mi-book__glow" aria-hidden="true"></div>
            <div class="mi-book__main">
                <div class="mi-book__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="mi-book__title">Book a mock interview</h3>
                <p class="mi-book__hint">{{ $ent['label'] ?? 'Paid add-on billed per minute for your chosen duration.' }}</p>
                <div class="mi-book__meta">
                    @if($isFree)
                        <span class="mi-pill mi-pill--ok">Plan credit available</span>
                        @if($maxFree)
                        <span class="mi-pill mi-pill--muted">Up to {{ $maxFree }} min included</span>
                        @endif
                    @elseif($remaining <= 0 && $nextFreeLabel)
                        <span class="mi-pill mi-pill--warn">Next free on {{ $nextFreeLabel }}</span>
                        <span class="mi-pill mi-pill--muted">₹{{ number_format($rate, 0) }}/min add-on</span>
                    @else
                        <span class="mi-pill mi-pill--warn">Paid add-on</span>
                        <span class="mi-pill mi-pill--muted">₹{{ number_format($rate, 0) }}/min</span>
                    @endif
                </div>
                <div class="mi-book__perks">
                    <div class="mi-book__perk">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Durations: 30 · 45 · 60 · 90 minutes
                    </div>
                    <div class="mi-book__perk">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Preferred time at least 2 hours ahead (IST)
                    </div>
                    <div class="mi-book__perk">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Hosted by Vedrix admin interviewers
                    </div>
                </div>
            </div>
            <aside class="mi-book__side">
                <div class="mi-book__price">
                    @if($isFree)
                        Included
                        <small>Uses your plan entitlement</small>
                    @else
                        ₹{{ number_format($rate, 0) }}/min
                        <small>Est. ₹{{ number_format($rate * 60, 0) }} for 60 min</small>
                    @endif
                </div>
                <a href="{{ route('mentee.mock-interviews.create') }}" class="btn btn-primary mi-book__cta">
                    Request mock interview
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </aside>
        </article>

        <div class="mi-table-card">
            <div class="mi-table-card__head">
                <h2>Your bookings</h2>
                <span class="mi-table-card__count">{{ $requests->total() }} total</span>
            </div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Preferred time</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                        @php
                            $tone = match ($req->status) {
                                'completed' => 'success',
                                'confirmed' => 'success',
                                'submitted' => 'warning',
                                'cancelled' => 'muted',
                                default => 'muted',
                            };
                        @endphp
                        <tr>
                            <td style="font-weight:600;color:var(--mi-muted);">#{{ $req->id }}</td>
                            <td>{{ $req->preferred_at?->timezone($req->timezone ?: 'Asia/Kolkata')->format('d M Y, h:i A') }}</td>
                            <td>{{ $req->duration_minutes }} min</td>
                            <td><span class="cs-status cs-status--{{ $tone }}">{{ $req->statusLabel() }}</span></td>
                            <td>
                                @if($req->is_paid_addon)
                                    ₹{{ number_format((float) $req->amount, 0) }}
                                @else
                                    <span style="color:var(--mi-faint);font-size:12px;">Included</span>
                                @endif
                            </td>
                            <td>
                                <div class="mi-actions">
                                    @if($req->canJoinCall())
                                        <a href="{{ route('mock-interviews.call', $req->id) }}" class="btn btn-primary btn-sm">Join interview</a>
                                    @endif
                                    <a href="{{ route('mentee.mock-interviews.show', $req) }}" class="btn btn-ghost btn-sm">View</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="mi-empty">
                                    <strong>No mock interviews yet</strong>
                                    Request your first practice session when you’re ready.
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requests->hasPages())
            <div style="padding:12px 18px;">@include('frontend.partials.pagination', ['paginator' => $requests])</div>
            @endif
        </div>
    </div>
</div>
@endsection
