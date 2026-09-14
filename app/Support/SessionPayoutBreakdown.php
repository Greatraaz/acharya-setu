<?php

namespace App\Support;

/**
 * Single source of truth for session payment → mentor payout split.
 * Gross (what mentee paid) − platform fee = mentor net (wallet credit).
 */
final class SessionPayoutBreakdown
{
    public const FEE_RATE = 0.20;

    /**
     * @return array{gross: float, platform_fee: float, net: float, fee_rate: float}
     */
    public static function fromGross(float $gross): array
    {
        $gross = round(max(0, $gross), 2);
        $fee = round($gross * self::FEE_RATE, 2);
        $net = round($gross - $fee, 2);

        return [
            'gross'        => $gross,
            'platform_fee' => $fee,
            'net'          => $net,
            'fee_rate'     => self::FEE_RATE,
        ];
    }

    /**
     * Prefer stored wallet meta when present so history stays consistent
     * with what was actually credited.
     *
     * @param  array<string, mixed>|null  $meta
     * @return array{gross: float, platform_fee: float, net: float, fee_rate: float}
     */
    public static function fromMeta(?array $meta, ?float $fallbackGross = null, ?float $fallbackNet = null): array
    {
        $meta = $meta ?? [];

        if (isset($meta['gross_amount'], $meta['platform_fee'], $meta['net_amount'])) {
            return [
                'gross'        => round((float) $meta['gross_amount'], 2),
                'platform_fee' => round((float) $meta['platform_fee'], 2),
                'net'          => round((float) $meta['net_amount'], 2),
                'fee_rate'     => isset($meta['platform_fee_rate'])
                    ? (float) $meta['platform_fee_rate']
                    : self::FEE_RATE,
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
