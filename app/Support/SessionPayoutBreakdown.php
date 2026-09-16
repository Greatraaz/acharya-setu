<?php

namespace App\Support;

use App\Models\ConsultationSession;

/**
 * Single source of truth for session payment → mentor payout split.
 *
 * Mentor always earns against the FULL session list price (rate × duration).
 * Coupon discounts reduce what the mentee pays; the platform absorbs that cost.
 * Platform fee = 20% of list price; mentor net = 80% of list price.
 */
final class SessionPayoutBreakdown
{
    public const FEE_RATE = 0.20;

    /**
     * @return array{
     *   gross: float,
     *   platform_fee: float,
     *   net: float,
     *   fee_rate: float,
     *   list_amount: float,
     *   mentee_paid: float,
     *   coupon_discount: float,
     *   platform_subsidy: float
     * }
     */
    public static function fromGross(float $gross, float $couponDiscount = 0.0, ?float $menteePaid = null): array
    {
        $list = round(max(0, $gross), 2);
        $discount = round(max(0, $couponDiscount), 2);
        $paid = $menteePaid !== null
            ? round(max(0, $menteePaid), 2)
            : round(max(0, $list - $discount), 2);

        $fee = round($list * self::FEE_RATE, 2);
        $net = round($list - $fee, 2);
        // Promo cost absorbed by platform (can make platform net negative vs fee alone).
        $subsidy = $discount;

        return [
            'gross'            => $list,
            'platform_fee'     => $fee,
            'net'              => $net,
            'fee_rate'         => self::FEE_RATE,
            'list_amount'      => $list,
            'mentee_paid'      => $paid,
            'coupon_discount'  => $discount,
            'platform_subsidy' => $subsidy,
        ];
    }

    /**
     * Mentor payout base = list price (amount paid + coupon discount).
     *
     * @return array{
     *   gross: float,
     *   platform_fee: float,
     *   net: float,
     *   fee_rate: float,
     *   list_amount: float,
     *   mentee_paid: float,
     *   coupon_discount: float,
     *   platform_subsidy: float
     * }
     */
    public static function fromSession(ConsultationSession $session): array
    {
        $menteePaid = round((float) $session->amount, 2);
        $discount = round((float) ($session->coupon_discount ?? 0), 2);
        $list = round($menteePaid + $discount, 2);

        return self::fromGross($list, $discount, $menteePaid);
    }

    /**
     * Prefer stored wallet meta when present so history stays consistent
     * with what was actually credited.
     *
     * @param  array<string, mixed>|null  $meta
     * @return array{
     *   gross: float,
     *   platform_fee: float,
     *   net: float,
     *   fee_rate: float,
     *   list_amount: float,
     *   mentee_paid: float,
     *   coupon_discount: float,
     *   platform_subsidy: float
     * }
     */
    public static function fromMeta(?array $meta, ?float $fallbackGross = null, ?float $fallbackNet = null): array
    {
        $meta = $meta ?? [];

        if (isset($meta['gross_amount'], $meta['platform_fee'], $meta['net_amount'])) {
            $list = round((float) $meta['gross_amount'], 2);
            $discount = round((float) ($meta['coupon_discount'] ?? 0), 2);
            $paid = isset($meta['mentee_paid'])
                ? round((float) $meta['mentee_paid'], 2)
                : round(max(0, $list - $discount), 2);

            return [
                'gross'            => $list,
                'platform_fee'     => round((float) $meta['platform_fee'], 2),
                'net'              => round((float) $meta['net_amount'], 2),
                'fee_rate'         => isset($meta['platform_fee_rate'])
                    ? (float) $meta['platform_fee_rate']
                    : self::FEE_RATE,
                'list_amount'      => $list,
                'mentee_paid'      => $paid,
                'coupon_discount'  => $discount,
                'platform_subsidy' => $discount,
            ];
        }

        if ($fallbackGross !== null && $fallbackGross > 0) {
            return self::fromGross($fallbackGross);
        }

        if ($fallbackNet !== null && $fallbackNet > 0) {
            $net = round($fallbackNet, 2);
            $rate = 1 - self::FEE_RATE;
            $gross = $rate > 0 ? round($net / $rate, 2) : $net;

            return self::fromGross($gross);
        }

        return self::fromGross(0);
    }
}
