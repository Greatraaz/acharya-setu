<?php

namespace App\Services;

use App\Models\ConsultationSession;
use App\Models\Offer;
use App\Models\OfferRedemption;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfferService
{
    /**
     * Credit wallet for the best active new-joinee offer when a mentee completes onboarding.
     */
    public function creditNewJoineeIfEligible(User $mentee): ?float
    {
        if (! $mentee->isMentee()) {
            return null;
        }

        $offer = Offer::query()
            ->where('audience', Offer::AUDIENCE_NEW_JOINEE)
            ->active()
            ->withinDates()
            ->orderByDesc('amount')
            ->orderByDesc('id')
            ->first();

        if (! $offer) {
            return null;
        }

        $alreadyCredited = OfferRedemption::query()
            ->where('offer_id', $offer->id)
            ->where('user_id', $mentee->id)
            ->where('type', OfferRedemption::TYPE_WALLET_CREDIT)
            ->exists();

        if ($alreadyCredited) {
            return null;
        }

        $amount = round((float) $offer->amount, 2);
        if ($amount <= 0) {
            return null;
        }

        DB::transaction(function () use ($mentee, $offer, $amount) {
            $mentee->creditWallet(
                $amount,
                "Welcome offer: {$offer->title}",
                [
                    'performed_by' => $offer->created_by,
                    'reference'    => 'OFFER-'.$offer->id.'-'.$mentee->id,
                    'meta'         => [
                        'offer_id' => $offer->id,
                        'source'   => 'new_joinee_offer',
                    ],
                ]
            );

            OfferRedemption::create([
                'offer_id' => $offer->id,
                'user_id'  => $mentee->id,
                'amount'   => $amount,
                'type'     => OfferRedemption::TYPE_WALLET_CREDIT,
            ]);

            $offer->increment('usage_count');
        });

        return $amount;
    }

    /**
     * @return array{valid: bool, message?: string, discount?: float, offer?: Offer}
     */
    public function validateCoupon(User $mentee, string $code, float $sessionAmount): array
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return ['valid' => false, 'message' => 'Please enter a coupon code.'];
        }

        $offer = Offer::query()
            ->whereIn('audience', Offer::COUPON_AUDIENCES)
            ->whereRaw('UPPER(coupon_code) = ?', [$code])
            ->first();

        if (! $offer) {
            return ['valid' => false, 'message' => 'Invalid coupon code.'];
        }

        if (! $offer->is_active) {
            return ['valid' => false, 'message' => 'This coupon is no longer active.'];
        }

        if (! $offer->isWithinDates()) {
            return ['valid' => false, 'message' => 'This coupon has expired or is not yet valid.'];
        }

        if (! $offer->hasRemainingUses()) {
            return ['valid' => false, 'message' => 'This coupon has reached its usage limit.'];
        }

        if ($offer->isSelectedMenteesOffer()) {
            if (! $offer->mentees()->where('users.id', $mentee->id)->exists()) {
                return ['valid' => false, 'message' => 'This coupon is not assigned to your account.'];
            }
        } elseif ($offer->isUnsubscribedMenteesOffer()) {
            if (! $mentee->hasNeverSubscribed()) {
                return ['valid' => false, 'message' => 'This coupon is only for mentees who have never subscribed to a plan.'];
            }
        } else {
            return ['valid' => false, 'message' => 'Invalid coupon code.'];
        }

        $minAmount = round((float) ($offer->min_session_amount ?? 0), 2);
        $sessionAmount = round($sessionAmount, 2);

        if ($sessionAmount < $minAmount) {
            return [
                'valid'   => false,
                'message' => 'Minimum session booking amount for this coupon is ₹'.number_format($minAmount, 0).'.',
            ];
        }

        $discount = round(min((float) $offer->amount, $sessionAmount), 2);
        if ($discount <= 0) {
            return ['valid' => false, 'message' => 'This coupon cannot be applied to this booking.'];
        }

        return [
            'valid'    => true,
            'discount' => $discount,
            'offer'    => $offer,
        ];
    }

    public function availableCouponsFor(?User $mentee): Collection
    {
        if (! $mentee || ! $mentee->isMentee()) {
            return collect();
        }

        return Offer::query()
            ->whereIn('audience', Offer::COUPON_AUDIENCES)
            ->active()
            ->withinDates()
            ->where(function ($q) use ($mentee) {
                $q->where(function ($selected) use ($mentee) {
                    $selected->where('audience', Offer::AUDIENCE_SELECTED_MENTEES)
                        ->whereHas('mentees', fn ($mq) => $mq->where('users.id', $mentee->id));
                })->orWhere(function ($unsubscribed) {
                    $unsubscribed->where('audience', Offer::AUDIENCE_UNSUBSCRIBED_MENTEES);
                });
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhereColumn('usage_count', '<', 'usage_limit');
            })
            ->orderBy('title')
            ->get()
            ->filter(function (Offer $offer) use ($mentee) {
                if ($offer->isUnsubscribedMenteesOffer()) {
                    return $mentee->hasNeverSubscribed();
                }

                return true;
            })
            ->values();
    }

    public function recordSessionRedemption(Offer $offer, User $mentee, ConsultationSession $session, float $discount): void
    {
        DB::transaction(function () use ($offer, $mentee, $session, $discount) {
            $locked = Offer::query()->lockForUpdate()->find($offer->id);
            if (! $locked || ! $locked->hasRemainingUses()) {
                return;
            }

            OfferRedemption::create([
                'offer_id'                => $locked->id,
                'user_id'                 => $mentee->id,
                'consultation_session_id' => $session->id,
                'amount'                  => round($discount, 2),
                'type'                    => OfferRedemption::TYPE_SESSION_DISCOUNT,
            ]);

            $locked->increment('usage_count');
        });
    }

    /**
     * Undo a session coupon redemption so the mentee can reuse the coupon.
     */
    public function restoreSessionRedemption(ConsultationSession $session): bool
    {
        return (bool) DB::transaction(function () use ($session) {
            $redemptions = OfferRedemption::query()
                ->where('consultation_session_id', $session->id)
                ->where('type', OfferRedemption::TYPE_SESSION_DISCOUNT)
                ->lockForUpdate()
                ->get();

            if ($redemptions->isEmpty()) {
                if (! $session->offer_id) {
                    return false;
                }

                // Fallback: offer stored on session but redemption row missing.
                $offer = Offer::query()->lockForUpdate()->find($session->offer_id);
                if ($offer && (int) $offer->usage_count > 0) {
                    $offer->decrement('usage_count');

                    return true;
                }

                return false;
            }

            foreach ($redemptions as $redemption) {
                $offer = Offer::query()->lockForUpdate()->find($redemption->offer_id);
                if ($offer && (int) $offer->usage_count > 0) {
                    $offer->decrement('usage_count');
                }
                $redemption->delete();
            }

            return true;
        });
    }

    public function generateCouponCode(): string
    {
        do {
            $code = 'VED-'.strtoupper(Str::random(6));
        } while (Offer::where('coupon_code', $code)->exists());

        return $code;
    }
}
