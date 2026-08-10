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
                $price = (float) $pricing['total'];
                $basePrice = (float) $pricing['base'];
                $isCurrent = $currentPlanId && (int) $currentPlanId === (int) $plan->id;
                $features = $plan->features_list;
                $accent = $plan->color ?: '#f59e0b';
            @endphp
            <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:12px;position:relative;{{ $plan->is_featured ? 'border-color:'.$accent.';box-shadow:0 0 0 1px '.$accent.'33;' : '' }}">
                @if($plan->is_featured || $plan->badge_label)
                <div style="position:absolute;top:12px;right:12px;font-size:10px;font-weight:700;padding:4px 8px;border-radius:999px;background:{{ $accent }}22;color:{{ $accent }};">
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
                    <div style="font-size:28px;font-weight:800;color:var(--success);">Free</div>
                    @else
                    <div style="font-size:28px;font-weight:800;color:var(--text);">
                        ₹{{ number_format($price, 0) }}
                        <span style="font-size:13px;font-weight:500;color:var(--text-3);">/mo</span>
                    </div>
                    @if($pricing['tax_total'] > 0)
                    <div style="font-size:11px;color:var(--text-3);margin-top:4px;line-height:1.45;">
                        Base ₹{{ number_format($basePrice, 0) }}
                        @if($pricing['cgst_percent'] > 0)+ CGST {{ rtrim(rtrim(number_format($pricing['cgst_percent'], 2, '.', ''), '0'), '.') }}% (₹{{ number_format($pricing['cgst_amount'], 2) }})@endif
                        @if($pricing['sgst_percent'] > 0)+ SGST {{ rtrim(rtrim(number_format($pricing['sgst_percent'], 2, '.', ''), '0'), '.') }}% (₹{{ number_format($pricing['sgst_amount'], 2) }})@endif
                    </div>
                    @endif
                    @endif
                    <div style="font-size:11px;color:var(--text-3);margin-top:4px;">{{ $plan->billingDays() }}-day billing cycle</div>
                </div>

                @if(count($features) || $plan->progress_report_enabled || $plan->sessions_per_month !== null)
                <ul style="list-style:none;padding:0;margin:0;display:grid;gap:8px;flex:1;">
                    @if($plan->sessions_per_month !== null)
                    <li style="display:flex;gap:8px;align-items:flex-start;font-size:12px;color:var(--text-2);line-height:1.4;">
                        <span style="color:{{ $accent }};font-weight:700;">✓</span>
                        <span>{{ (int) $plan->sessions_per_month < 0 ? 'Unlimited sessions / month' : ((int) $plan->sessions_per_month).' sessions / month' }}</span>
                    </li>
                    @endif
                    @if($plan->progress_report_enabled)
                    <li style="display:flex;gap:8px;align-items:flex-start;font-size:12px;color:var(--text-2);line-height:1.4;">
                        <span style="color:{{ $accent }};font-weight:700;">✓</span>
                        <span>Progress report &amp; scores</span>
                    </li>
                    @endif
                    @foreach(array_slice($features, 0, 6) as $feature)
                    <li style="display:flex;gap:8px;align-items:flex-start;font-size:12px;color:var(--text-2);line-height:1.4;">
                        <span style="color:{{ $accent }};font-weight:700;">✓</span>
                        <span>{{ $feature }}</span>
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
                    {{ $current ? 'Switch to this plan' : ($price <= 0 ? 'Activate free' : 'Subscribe') }}
                </button>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        @if($history->isNotEmpty())
        <div class="card">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:14px;">Subscription history</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Period</th>
                        <th>Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $sub)
                    <tr>
                        <td style="font-weight:600;">{{ $sub->plan->name ?? 'N/A' }}</td>
                        <td>₹{{ number_format((float) $sub->amount_paid, 0) }}</td>
                        <td><span class="session-status {{ $sub->status === 'active' ? 'completed' : ($sub->status === 'cancelled' ? 'cancelled' : 'pending') }}">{{ ucfirst($sub->status) }}</span></td>
                        <td style="font-size:12px;">{{ ucfirst($sub->payment_status ?? '—') }}</td>
                        <td style="font-size:12px;white-space:nowrap;">
                            @if($sub->starts_at && $sub->expires_at)
                                {{ $sub->starts_at->format('d M Y') }} → {{ $sub->expires_at->format('d M Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td style="font-size:12px;">
                            @if($sub->invoice)
                                <a href="{{ route('mentee.invoices.show', $sub->invoice) }}" style="color:var(--brand);font-weight:600;">{{ $sub->invoice->invoice_number }}</a>
                                <a href="{{ route('mentee.invoices.download', $sub->invoice) }}" style="margin-left:8px;color:var(--text-2);">Download</a>
                            @elseif(($sub->payment_status ?? '') === 'paid')
                                <form method="POST" action="{{ route('mentee.subscriptions.invoice', $sub->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost btn-sm" style="padding:4px 8px;font-size:11px;">Generate</button>
                                </form>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
