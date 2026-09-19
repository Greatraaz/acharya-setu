@php
    $availableCoupons = $availableCoupons
        ?? (auth()->check()
            ? app(\App\Services\OfferService::class)->availableCouponsFor(auth()->user())
            : collect());
@endphp
@if(auth()->check() && $availableCoupons->isNotEmpty())
<div class="form-group coupon-picker" data-coupon-picker style="margin-top:14px;margin-bottom:0;">
    <label class="form-label">Coupons</label>
    <input type="hidden" id="booking-coupon" value="">
    <p class="form-hint coupon-picker__hint" id="coupon-hint" hidden></p>

    <div class="coupon-list" role="listbox" aria-label="Available coupons">
        @foreach($availableCoupons as $coupon)
            @php
                $off = (float) $coupon->amount;
                $min = (float) $coupon->min_session_amount;
            @endphp
            <button type="button"
                    class="coupon-card"
                    role="option"
                    aria-selected="false"
                    data-code="{{ $coupon->coupon_code }}"
                    data-discount="{{ $off }}"
                    data-min="{{ $min }}">
                <span class="coupon-card__body">
                    <span class="coupon-card__title">{{ $coupon->coupon_code }}</span>
                    @if($min > 0)
                    <span class="coupon-card__meta">Min ₹{{ number_format($min, 0) }}</span>
                    @endif
                </span>
                <span class="coupon-card__off">₹{{ number_format($off, 0) }} off</span>
            </button>
        @endforeach
    </div>
</div>
@endif
