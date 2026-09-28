<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\ConsultationSession;
use App\Models\User;
use App\Support\SessionPayoutBreakdown;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Cancel consultation sessions and settle refunds / mentor retention.
 *
 * Policy:
 * - Mentor or admin cancel → 100% refund to original payment sources; restore coupon;
 *   plan free minutes auto-return (session cancelled without forfeit).
 * - Mentee cancel ≥ 24h before start → same as full refund.
 * - Mentee cancel ≥ 6h and < 24h before start → 50% refund to wallet/Razorpay only;
 *   coupon NOT restored; plan benefit forfeited; retained 50% → mentor after admin fee.
 * - Mentee cancel < 6h before start → not allowed.
 */
class SessionCancellationService
{
    public const FULL_REFUND_HOURS = 24;

    public const PARTIAL_REFUND_HOURS = 6;

    public const PARTIAL_REFUND_PERCENT = 50.0;

    public const ROLE_MENTEE = 'mentee';

    public const ROLE_MENTOR = 'mentor';

    public const ROLE_ADMIN = 'admin';

    public function __construct(
        private readonly OfferService $offers,
    ) {}

    /**
     * Preview policy without mutating (useful for API/UI).
     *
     * @return array{
     *   allowed: bool,
     *   policy: string|null,
     *   refund_percent: float,
     *   hours_until_start: float|null,
     *   message: string|null
     * }
     */
    public function preview(ConsultationSession $session, string $cancelledByRole): array
    {
        try {
            $policy = $this->resolvePolicy($session, $cancelledByRole);
        } catch (InvalidArgumentException $e) {
            return [
                'allowed'           => false,
                'policy'            => null,
                'refund_percent'    => 0.0,
                'hours_until_start' => $this->hoursUntilStart($session),
                'message'           => $e->getMessage(),
            ];
        }

        return [
            'allowed'           => true,
            'policy'            => $policy['policy'],
            'refund_percent'    => $policy['refund_percent'],
            'hours_until_start' => $this->hoursUntilStart($session),
            'message'           => null,
        ];
    }

    /**
     * Cancel session and settle refunds / mentor share.
     *
     * @return array{
     *   session: ConsultationSession,
     *   policy: string,
     *   refund_percent: float,
     *   wallet_refunded: float,
     *   razorpay_refunded: float,
     *   coupon_restored: bool,
     *   plan_benefit_restored: bool,
     *   mentor_credited: float,
     *   platform_fee: float,
     *   message: string
     * }
     */
    public function cancel(
        ConsultationSession $session,
        User $actor,
        string $cancelledByRole,
        ?string $reason = null,
    ): array {
        if ($session->status !== ConsultationSession::STATUS_UPCOMING) {
            throw new InvalidArgumentException('Only upcoming sessions can be cancelled.');
        }

        if ($session->cancellation_settlement) {
            throw new InvalidArgumentException('This session has already been settled for cancellation.');
        }

        $policy = $this->resolvePolicy($session, $cancelledByRole);
        $refundPercent = (float) $policy['refund_percent'];
        $isFull = $refundPercent >= 100.0;

        $defaultReason = match ($cancelledByRole) {
            self::ROLE_MENTOR => 'Cancelled by mentor',
            self::ROLE_ADMIN  => 'Cancelled by admin',
            default           => 'Cancelled by mentee',
        };

        return DB::transaction(function () use (
            $session,
            $actor,
            $cancelledByRole,
            $reason,
            $defaultReason,
            $policy,
            $refundPercent,
            $isFull,
        ) {
            $locked = ConsultationSession::query()->lockForUpdate()->findOrFail($session->id);

            if ($locked->status !== ConsultationSession::STATUS_UPCOMING) {
                throw new InvalidArgumentException('Only upcoming sessions can be cancelled.');
            }

            if ($locked->cancellation_settlement) {
                throw new InvalidArgumentException('This session has already been settled for cancellation.');
            }

            [$walletPaid, $razorPaid] = $this->resolvePaidSplit($locked);

            $walletRefund = round($walletPaid * ($refundPercent / 100), 2);
            $razorRefund = round($razorPaid * ($refundPercent / 100), 2);
            $retainedGross = round(($walletPaid + $razorPaid) - ($walletRefund + $razorRefund), 2);

            $forfeitPlan = ! $isFull
                && in_array($locked->payment_method, ['plan', 'free'], true);

            $couponRestored = false;
            if ($isFull) {
                $couponRestored = $this->offers->restoreSessionRedemption($locked);
            }

            $walletTxn = null;
            if ($walletRefund > 0) {
                $mentee = $locked->mentee ?? User::find($locked->mentee_id);
                if ($mentee) {
                    $walletTxn = $mentee->refundWallet(
                        $walletRefund,
                        'Refund for cancelled session '.$locked->booking_ref,
                        [
                            'reference'            => 'REF-W-'.$locked->booking_ref.($isFull ? '' : '-P'.$refundPercent),
                            'transactionable_type' => ConsultationSession::class,
                            'transactionable_id'   => $locked->id,
                            'performed_by'         => $actor->id,
                            'meta'                 => [
                                'source'          => 'session_cancellation',
                                'policy'          => $policy['policy'],
                                'refund_percent'  => $refundPercent,
                                'booking_ref'     => $locked->booking_ref,
                                'session_id'      => $locked->id,
                            ],
                        ]
                    );
                }
            }

            $razorpayOk = true;
            if ($razorRefund > 0) {
                $paymentId = (string) ($locked->razorpay_payment_id ?: '');
                $razorpayOk = $this->refundRazorpayPayment(
                    $paymentId,
                    $razorRefund,
                    [
                        'reason'         => 'session_cancellation',
                        'policy'         => $policy['policy'],
                        'booking_ref'    => (string) $locked->booking_ref,
                        'session_id'     => (string) $locked->id,
                        'refund_percent' => (string) $refundPercent,
                    ]
                );
            }

            $mentorCredited = 0.0;
            $platformFee = 0.0;
            if ($retainedGross > 0 && $cancelledByRole === self::ROLE_MENTEE && ! $isFull) {
                $fee = round($retainedGross * SessionPayoutBreakdown::FEE_RATE, 2);
                $net = round($retainedGross - $fee, 2);
                $platformFee = $fee;
                $mentorCredited = $net;

                if ($net > 0) {
                    $mentor = $locked->mentor ?? User::find($locked->mentor_id);
                    if ($mentor) {
                        $mentor->creditWallet(
                            $net,
                            'Cancellation retention: '.($locked->title ?: ('Session #'.$locked->id)),
                            [
                                'reference'            => 'SES-CANCEL-EARN-'.$locked->id,
                                'transactionable_type' => ConsultationSession::class,
                                'transactionable_id'   => $locked->id,
                                'performed_by'         => $actor->id,
                                'meta'                 => [
                                    'source'            => 'session_cancellation_retention',
                                    'policy'            => $policy['policy'],
                                    'booking_ref'       => $locked->booking_ref,
                                    'session_id'        => $locked->id,
                                    'retained_gross'    => $retainedGross,
                                    'platform_fee'      => $fee,
                                    'platform_fee_rate' => SessionPayoutBreakdown::FEE_RATE,
                                    'net_amount'        => $net,
                                    'refund_percent'    => $refundPercent,
                                ],
                            ]
                        );
                    }
                }
            }

            $paymentStatus = $locked->payment_status;
            if (in_array($locked->payment_status, ['paid', 'pending'], true)) {
                if ($isFull && ($walletPaid + $razorPaid) > 0) {
                    $paymentStatus = 'refunded';
                } elseif (! $isFull && ($walletRefund + $razorRefund) > 0) {
                    $paymentStatus = 'partially_refunded';
                }
            }

            $settlement = [
                'policy'                 => $policy['policy'],
                'cancelled_by_role'      => $cancelledByRole,
                'refund_percent'         => $refundPercent,
                'hours_until_start'      => $this->hoursUntilStart($locked),
                'wallet_paid'            => $walletPaid,
                'razorpay_paid'          => $razorPaid,
                'wallet_refunded'        => $walletRefund,
                'razorpay_refunded'      => $razorRefund,
                'razorpay_refund_ok'     => $razorpayOk,
                'coupon_restored'        => $couponRestored,
                'plan_benefit_restored'  => in_array($locked->payment_method, ['plan', 'free'], true) && ! $forfeitPlan,
                'plan_benefit_forfeited' => $forfeitPlan,
                'retained_gross'         => $retainedGross,
                'platform_fee'           => $platformFee,
                'mentor_credited'        => $mentorCredited,
                'wallet_txn_id'          => $walletTxn?->id,
                'settled_at'             => now()->toDateTimeString(),
            ];

            $locked->update([
                'status'                   => ConsultationSession::STATUS_CANCELLED,
                'cancelled_by'             => $actor->id,
                'cancelled_at'             => now(),
                'cancellation_reason'      => $reason ?: $defaultReason,
                'forfeit_plan_benefit'     => $forfeitPlan,
                'cancellation_settlement'  => $settlement,
                'payment_status'           => $paymentStatus,
            ]);

            return [
                'session'               => $locked->fresh(['mentor', 'mentee']),
                'policy'                => $policy['policy'],
                'refund_percent'        => $refundPercent,
                'wallet_refunded'       => $walletRefund,
                'razorpay_refunded'     => $razorRefund,
                'coupon_restored'       => $couponRestored,
                'plan_benefit_restored' => (bool) $settlement['plan_benefit_restored'],
                'mentor_credited'       => $mentorCredited,
                'platform_fee'          => $platformFee,
                'message'               => $this->buildMessage($settlement),
            ];
        });
    }

    /**
     * @return array{policy: string, refund_percent: float}
     */
    private function resolvePolicy(ConsultationSession $session, string $cancelledByRole): array
    {
        if (in_array($cancelledByRole, [self::ROLE_MENTOR, self::ROLE_ADMIN], true)) {
            return [
                'policy'         => $cancelledByRole.'_full_refund',
                'refund_percent' => 100.0,
            ];
        }

        if ($cancelledByRole !== self::ROLE_MENTEE) {
            throw new InvalidArgumentException('Invalid cancellation role.');
        }

        $hours = $this->hoursUntilStart($session);

        if ($hours === null) {
            throw new InvalidArgumentException('Session schedule is missing.');
        }

        if ($hours < self::PARTIAL_REFUND_HOURS) {
            throw new InvalidArgumentException(
                'Sessions can only be cancelled at least '.self::PARTIAL_REFUND_HOURS.' hours before the start time.'
            );
        }

        if ($hours >= self::FULL_REFUND_HOURS) {
            return [
                'policy'         => 'mentee_full_refund',
                'refund_percent' => 100.0,
            ];
        }

        return [
            'policy'         => 'mentee_partial_refund',
            'refund_percent' => self::PARTIAL_REFUND_PERCENT,
        ];
    }

    public function hoursUntilStart(ConsultationSession $session): ?float
    {
        if (! $session->scheduled_at) {
            return null;
        }

        return round(($session->scheduled_at->getTimestamp() - now()->getTimestamp()) / 3600, 2);
    }

    /**
     * @return array{0: float, 1: float} [wallet, razorpay]
     */
    private function resolvePaidSplit(ConsultationSession $session): array
    {
        $wallet = round((float) ($session->wallet_amount ?? 0), 2);
        $razor = round((float) ($session->razorpay_amount ?? 0), 2);
        $amount = round((float) ($session->amount ?? 0), 2);

        if ($wallet <= 0 && $razor <= 0 && $amount > 0 && $session->payment_status === 'paid') {
            return match ($session->payment_method) {
                'razorpay' => [0.0, $amount],
                'hybrid'   => [$amount, 0.0], // legacy incomplete split — prefer wallet credit
                default    => [$amount, 0.0],
            };
        }

        // Prefer stored split; if both zero but waived/plan, nothing to refund as cash.
        return [max(0, $wallet), max(0, $razor)];
    }

    /**
     * @param  array<string, string>  $notes
     */
    private function refundRazorpayPayment(string $paymentId, float $amountRupees, array $notes = []): bool
    {
        $paymentId = trim($paymentId);
        if ($paymentId === '' || $amountRupees <= 0) {
            return false;
        }

        $settings = AppSetting::razorpay();
        $key = $settings['key'] ?: config('services.razorpay.key', env('RAZORPAY_KEY_ID', ''));
        $secret = $settings['secret'] ?: config('services.razorpay.secret', env('RAZORPAY_KEY_SECRET', ''));

        if ($key === '' || $secret === '') {
            Log::error('Cannot refund session cancellation — Razorpay not configured.', [
                'payment_id' => $paymentId,
                'amount'     => $amountRupees,
            ]);

            return false;
        }

        try {
            $response = Http::withBasicAuth($key, $secret)
                ->acceptJson()
                ->post('https://api.razorpay.com/v1/payments/'.$paymentId.'/refund', [
                    'amount' => (int) round($amountRupees * 100),
                    'notes'  => $notes,
                ]);

            if (! $response->successful()) {
                Log::error('Razorpay cancellation refund failed.', [
                    'payment_id' => $paymentId,
                    'amount'     => $amountRupees,
                    'status'     => $response->status(),
                    'body'       => $response->body(),
                ]);

                return false;
            }

            Log::info('Razorpay cancellation refund succeeded.', [
                'payment_id' => $paymentId,
                'amount'     => $amountRupees,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Razorpay cancellation refund exception: '.$e->getMessage(), [
                'payment_id' => $paymentId,
                'amount'     => $amountRupees,
            ]);

            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $settlement
     */
    private function buildMessage(array $settlement): string
    {
        $parts = ['Session cancelled.'];

        $wallet = (float) ($settlement['wallet_refunded'] ?? 0);
        $razor = (float) ($settlement['razorpay_refunded'] ?? 0);
        $percent = (float) ($settlement['refund_percent'] ?? 0);

        if ($wallet > 0 || $razor > 0) {
            $bits = [];
            if ($wallet > 0) {
                $bits[] = '₹'.number_format($wallet, 2).' to wallet';
            }
            if ($razor > 0) {
                $bits[] = '₹'.number_format($razor, 2).' via Razorpay';
            }
            $label = $percent >= 100 ? 'Full refund' : number_format($percent, 0).'% refund';
            $parts[] = $label.': '.implode(', ', $bits).'.';
        }

        if (! empty($settlement['coupon_restored'])) {
            $parts[] = 'Coupon restored.';
        }

        if (! empty($settlement['plan_benefit_restored'])) {
            $parts[] = 'Plan free minutes restored.';
        } elseif (! empty($settlement['plan_benefit_forfeited'])) {
            $parts[] = 'Plan free benefit forfeited.';
        }

        $mentor = (float) ($settlement['mentor_credited'] ?? 0);
        if ($mentor > 0) {
            $parts[] = 'Mentor credited ₹'.number_format($mentor, 2).' (after platform fee).';
        }

        return implode(' ', $parts);
    }
}
