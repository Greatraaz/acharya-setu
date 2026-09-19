@extends('frontend.layouts.app')
@section('title', 'Plans — Vedrix')

@section('content')
@php
    $currentPlanId = $current?->plan_id;
@endphp
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between">
            <div>
                <div class="dash-title">Subscription Plans</div>
                <div class="dash-subtitle">Choose a plan to unlock mentoring benefits — same as the mobile app.</div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">
            <span class="alert-icon">✓</span>
            <div style="font-size:13px;">{{ session('success') }}</div>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-error" style="margin-bottom:16px;">
            <span class="alert-icon">!</span>
            <div style="font-size:13px;">{{ session('error') }}</div>
        </div>
        @endif

        @if($current)
        <div class="card" style="margin-bottom:20px;padding:18px 20px;border-color:rgba(245,158,11,.35);background:linear-gradient(135deg,rgba(245,158,11,.08),transparent);">
            <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;align-items:center;">
                <div>
                    <div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--brand);margin-bottom:4px;">Current plan</div>
                    <div style="font-size:18px;font-weight:800;color:var(--text);">{{ $current->plan->name ?? 'Plan' }}</div>
                    <div style="font-size:13px;color:var(--text-2);margin-top:4px;">
                        Active until {{ $current->expires_at?->format('d M Y') }}
                        · {{ max(0, $current->daysRemaining()) }} days left
                    </div>
                </div>
                <form action="{{ route('mentee.plans.cancel') }}" method="POST" onsubmit="return confirm('Cancel your active subscription?');">
                    @csrf
                    <input type="hidden" name="subscription_id" value="{{ $current->subscription_id }}">
                    <button type="submit" class="btn btn-ghost btn-sm">Cancel plan</button>
                </form>
            </div>
        </div>
        @endif

        @if($plans->isEmpty())
        <div class="empty-state" style="padding:48px 0;">
            <div style="font-size:15px;font-weight:700;">No plans available</div>
            <p style="font-size:13px;color:var(--text-2);">Check back soon — plans will appear here once published.</p>
        </div>
        @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin-bottom:28px;">
            @foreach($plans as $plan)
            @php
                $pricing = $plan->pricingBreakdown('monthly');
                $quote = $quotes[$plan->id] ?? null;
                $isUpgrade = (bool) ($quote['is_upgrade'] ?? false);
                $credit = $quote['credit'] ?? [];
                $creditAmount = (float) ($credit['amount'] ?? 0);
                $price = (float) ($quote['payable'] ?? $pricing['total']);
                $planTotal = (float) ($quote['plan_total'] ?? $pricing['total']);
                $basePrice = (float) $pricing['base'];
                $isCurrent = $currentPlanId && (int) $currentPlanId === (int) $plan->id;
                $benefits = $plan->benefitSummary();
                $accent = $plan->color ?: '#f59e0b';
                $badge = $plan->badgePalette();
                $offer = $plan->publicDiscount();
                $discountActive = ! empty($pricing['discount_active']) && (float) ($pricing['discount_percent'] ?? 0) > 0;
                $discountPct = $discountActive
                    ? rtrim(rtrim(number_format((float) $pricing['discount_percent'], 2, '.', ''), '0'), '.')
                    : null;
                $listTotal = (float) ($pricing['original_total'] ?? $pricing['original_base'] ?? 0);
                $strikePrice = $discountActive
                    ? $listTotal
                    : (($isUpgrade && $planTotal > $price) ? $planTotal : 0);
            @endphp
            <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:12px;position:relative;{{ $plan->is_featured ? 'border-color:'.$accent.';box-shadow:0 0 0 1px '.$accent.'33;' : '' }}">
                @if($plan->is_featured || $plan->badge_label)
                <div style="position:absolute;top:12px;right:12px;font-size:10px;font-weight:700;padding:4px 8px;border-radius:999px;background:{{ $badge['bg'] }};color:{{ $badge['text'] }};">
                    {{ $plan->badge_label ?: 'Featured' }}
                </div>
                @endif

                <div>
                    <div style="width:40px;height:40px;border-radius:12px;background:{{ $accent }};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;margin-bottom:10px;">
                        {{ strtoupper(substr($plan->name ?? 'P', 0, 1)) }}
                    </div>
                    <div style="font-size:17px;font-weight:800;color:var(--text);">{{ $plan->name }}</div>
                    @if($plan->description)
                    <div style="font-size:12px;color:var(--text-2);margin-top:4px;line-height:1.45;">{{ $plan->description }}</div>
                    @endif
                </div>

                <div>
                    @if($price <= 0)
                    <div style="font-size:28px;font-weight:800;color:var(--success);">{{ $isUpgrade ? 'No extra cost' : 'Free' }}</div>
                    @else
                    <div class="plan-price-row">
                        <span style="font-size:28px;font-weight:800;color:var(--text);line-height:1;">₹{{ number_format($price, 0) }}</span>
                        <span class="plan-price-period">/mo</span>
                        @if($discountActive)
                        <span class="plan-discount-badge">{{ $discountPct }}% off</span>
                        @endif
                    </div>
                    @if($strikePrice > $price || $discountActive)
                    <div style="font-size:12px;color:var(--text-3);margin-top:6px;display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                        @if($strikePrice > $price)
                        <span style="text-decoration:line-through;">₹{{ number_format($strikePrice, 0) }}</span>
                        @endif
                        @if($discountActive)
                        <span style="color:#15803d;font-weight:700;">Save ₹{{ number_format((float) $pricing['discount_amount'], 0) }}</span>
                        @if(!empty($offer['expires_at']))
                        <span>until {{ \Carbon\Carbon::parse($offer['expires_at'])->format('d M Y') }}</span>
                        @endif
                        @endif
                    </div>
                    @endif
                    @if($isUpgrade && $creditAmount > 0)
                    <div style="font-size:12px;color:#15803d;font-weight:700;margin-top:4px;">
                        ₹{{ number_format($creditAmount, 0) }} unused-day credit applied
                    </div>
                    @endif
                    @if($pricing['tax_total'] > 0)
                    <div style="font-size:11px;color:var(--text-3);margin-top:4px;line-height:1.45;">
                        @if($discountActive)
                        List ₹{{ number_format((float) $pricing['original_base'], 0) }}
                        − {{ $discountPct }}% (₹{{ number_format((float) $pricing['discount_amount'], 2) }})
                        = ₹{{ number_format($basePrice, 0) }}
                        @else
                        Base ₹{{ number_format($basePrice, 0) }}
                        @endif
                        @foreach(($pricing['taxes'] ?? []) as $taxLine)
                        + {{ $taxLine['code'] }} {{ rtrim(rtrim(number_format((float) $taxLine['percent'], 2, '.', ''), '0'), '.') }}% (₹{{ number_format((float) $taxLine['amount'], 2) }})
                        @endforeach
                    </div>
                    @endif
                    @endif
                    <div style="font-size:11px;color:var(--text-3);margin-top:4px;">{{ $plan->billingDays() }}-day billing cycle{{ $isUpgrade ? ' · starts today' : '' }}</div>
                    @if($isUpgrade && (int) ($credit['remaining_days'] ?? 0) > 0)
                    <div style="font-size:11px;color:#15803d;margin-top:4px;line-height:1.45;">
                        {{ (int) $credit['remaining_days'] }} unused day{{ (int) $credit['remaining_days'] === 1 ? '' : 's' }} of {{ $credit['from_plan_name'] ?? 'your current plan' }} credited at ₹{{ number_format((float) ($credit['daily_rate'] ?? 0), 2) }}/day.
                        Benefits reset with this plan.
                    </div>
                    @endif
                </div>

                @if(count($benefits))
                <ul style="list-style:none;padding:0;margin:0;display:grid;gap:8px;flex:1;">
                    @foreach($benefits as $row)
                    <li style="display:flex;gap:8px;align-items:flex-start;font-size:12px;color:var(--text-2);line-height:1.4;">
                        <span style="color:{{ $accent }};font-weight:700;">✓</span>
                        <span>{{ $row['label'] }}{{ $row['value'] !== '' ? ': '.$row['value'] : '' }}</span>
                    </li>
                    @endforeach
                </ul>
                @endif

                @if($isCurrent)
                <button type="button" class="btn btn-ghost" disabled style="width:100%;opacity:.7;">Current plan</button>
                @else
                <button type="button"
                        class="btn btn-primary"
                        style="width:100%;{{ $plan->is_featured ? 'background:'.$accent.';border-color:'.$accent.';' : '' }}"
                        data-plan-id="{{ $plan->id }}"
                        data-plan-name="{{ e($plan->name) }}"
                        onclick="subscribePlan(this)">
                    {{ $current ? ($price <= 0 ? 'Switch at no extra cost' : 'Upgrade · ₹'.number_format($price, 0)) : ($price <= 0 ? 'Activate free' : 'Subscribe') }}
                </button>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        @if($history->isNotEmpty() || request()->filled('search'))
        <div class="card">
            <div class="plan-history-head">
                <h3 style="font-size:15px;font-weight:700;margin:0;">Subscription history</h3>
                <form method="GET" action="{{ route('mentee.plans') }}" class="session-toolbar-controls" style="margin:0;">
                    <div class="session-search-field">
                        <span class="session-search-icon" aria-hidden="true">🔍</span>
                        <input type="search" name="search" class="form-input" value="{{ $search ?? request('search') }}"
                               placeholder="Search plan or ID…" autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-outline btn-sm">Search</button>
                </form>
            </div>

            <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Period</th>
                        <th>Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $sub)
                    @php
                        $statusClass = match ($sub->status) {
                            'active' => 'completed',
                            'cancelled' => 'cancelled',
                            'expired' => 'pending',
                            default => 'pending',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight:600;">{{ $sub->plan->name ?? 'N/A' }}</div>
                            @if($sub->subscription_id)
                            <div style="font-size:11px;color:var(--text-3);margin-top:2px;">{{ $sub->subscription_id }}</div>
                            @endif
                        </td>
                        <td style="white-space:nowrap;font-weight:600;">₹{{ number_format((float) $sub->amount_paid, 0) }}</td>
                        <td><span class="session-status {{ $statusClass }}">{{ ucfirst($sub->status) }}</span></td>
                        <td style="font-size:12px;white-space:nowrap;color:var(--text-2);">
                            @if($sub->starts_at && $sub->expires_at)
                                {{ $sub->starts_at->format('d M Y') }} → {{ $sub->expires_at->format('d M Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($sub->invoice)
                                <div class="plan-history-actions">
                                    <a href="{{ route('mentee.invoices.show', $sub->invoice) }}" style="color:var(--brand);font-weight:600;font-size:12px;white-space:nowrap;">{{ $sub->invoice->invoice_number }}</a>
                                    <a href="{{ route('mentee.invoices.download', $sub->invoice) }}"
                                       class="plan-history-icon-btn"
                                       title="Download invoice"
                                       aria-label="Download invoice">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </a>
                                </div>
                            @elseif(($sub->payment_status ?? '') === 'paid')
                                <form method="POST" action="{{ route('mentee.subscriptions.invoice', $sub->id) }}" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="plan-history-icon-btn" title="Generate invoice" aria-label="Generate invoice">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </form>
                            @else
                                <span style="font-size:12px;color:var(--text-3);">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:36px 16px;color:var(--text-2);">No subscriptions match that search.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            @include('frontend.partials.pagination', ['paginator' => $history])
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function subscribePlan(btn) {
    const planId = btn.getAttribute('data-plan-id');
    const planName = btn.getAttribute('data-plan-name') || 'plan';

    AjaxPost(`{{ url('/mentee/plans') }}/${planId}/subscribe`, {}, {
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
