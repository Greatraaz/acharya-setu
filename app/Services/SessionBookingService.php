<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\ConsultationSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SessionBookingService
{
    public function __construct(
        private readonly SessionInvoiceService $invoices,
        private readonly MentorAvailabilityService $availability,
        private readonly OfferService $offers
    ) {}

    /**
     * @return array{ok:bool,http?:int,payload:array}
     */
    public function book(User $mentee, array $data, string $source = 'api'): array
    {
        $mentor = User::where('role', 'mentor')
            ->where('mentor_status', 'approved')
            ->find($data['mentor_id'] ?? null);

        if (! $mentor) {
            return $this->fail('Mentor not found.', 404);
        }

        $duration = (int) ($data['duration'] ?? 0);
        if (! in_array($duration, ConsultationSession::BOOKING_DURATIONS, true)) {
            return $this->fail(
                'Duration must be '.implode(', ', ConsultationSession::BOOKING_DURATIONS).' minutes.',
                422
            );
        }
        $data['duration'] = $duration;

        // Session fees are GST-free: charge rate × duration only (no CGST/SGST/IGST).
        $baseAmount = round((float) ($mentor->rate_per_minute ?? 0) * $duration, 2);
        $listAmount = $baseAmount;
        $time = substr((string) ($data['time'] ?? ''), 0, 5);
        $data['time'] = $time;
        $scheduledAt = Carbon::parse($data['date'].' '.$time, 'Asia/Kolkata');
        if ($scheduledAt->lessThanOrEqualTo(Carbon::now('Asia/Kolkata'))) {
            return $this->fail('That time slot has already passed. Please choose a later time.', 422);
        }
        if (! $this->availability->isSlotOpen($mentor, (string) $data['date'], $time, $duration)) {
            return $this->fail(
                'That slot is not available for a '.$duration.'-minute session. Pick a mentor window that matches this duration exactly.',
                422
            );
        }
        $planAllowance = $mentee->planSessionAllowance($duration);
        $coveredByPlan = $baseAmount > 0 && ($planAllowance['covered'] ?? false);

        $couponDiscount = 0.0;
        $appliedOffer = null;
        $amount = $baseAmount;
        $platformSubsidy = 0.0;

        if ($baseAmount > 0 && ! $coveredByPlan) {
            $couponResult = $this->resolveCouponDiscount($mentee, $data, $baseAmount);
            if (isset($couponResult['error'])) {
                return $this->fail($couponResult['error'], 422);
            }
            $amount = $couponResult['amount'];
            $couponDiscount = $couponResult['discount'];
            $appliedOffer = $couponResult['offer'];
            $platformSubsidy = $couponDiscount;
        }

        if ($coveredByPlan) {
            $amount = 0.0;
            $platformSubsidy = $listAmount;
        }

        $paymentMethod = isset($data['payment_method']) ? strtolower((string) $data['payment_method']) : null;

        ConsultationSession::releaseOwnUnpaidHold($mentee->id, $mentor->id, $scheduledAt);

        if (ConsultationSession::isSlotHeldByOther($mentor->id, $scheduledAt, $mentee->id)) {
            return $this->fail(
                'This time slot is temporarily held by another mentee completing payment. Please pick another slot or try again in a few minutes.',
                409,
                ['slot_busy' => true, 'retry_after_seconds' => ConsultationSession::PAYMENT_HOLD_MINUTES * 60]
            );
        }

        if ($this->availability->overlapsExisting($mentor->id, (string) $data['date'], $time, $duration, $mentee->id)) {
            return $this->fail('This mentor already has an appointment that overlaps the selected time.', 409, [
                'slot_busy' => true,
            ]);
        }

        $channel = Str::random(10);
        $bookingRef = 'AS-'.mt_rand(10000000, 99999999);
        $currency = 'INR';
        $title = $data['title'] ?? ($data['agenda'] ? Str::limit($data['agenda'], 80) : 'Mentorship Session');

        // 1) Free mentor OR included plan minutes → auto book (admin funds mentor payout)
        if ($amount <= 0 || $coveredByPlan) {
            $method = $coveredByPlan ? 'plan' : 'free';
            try {
                $session = ConsultationSession::withSlotLock($mentor->id, $scheduledAt, function () use (
                    $mentor, $mentee, $scheduledAt, $data, $listAmount, $currency, $bookingRef, $channel, $title,
                    $method, $platformSubsidy, $planAllowance, $duration, $time
                ) {
                    if ($this->availability->overlapsExisting($mentor->id, (string) $data['date'], $time, $duration, $mentee->id)) {
                        throw new InvalidArgumentException('SLOT_TAKEN');
                    }

                    return ConsultationSession::create([
                        'mentor_id'         => $mentor->id,
                        'mentee_id'         => $mentee->id,
                        'scheduled_at'      => $scheduledAt,
                        'duration_minutes'  => $data['duration'],
                        'timezone'          => 'Asia/Kolkata',
                        'title'             => $title,
                        'agenda'            => $data['agenda'] ?? null,
                        'status'            => ConsultationSession::STATUS_UPCOMING,
                        'amount'            => 0,
                        'list_amount'       => $listAmount,
                        'currency'          => $currency,
                        'payment_status'    => 'waived',
                        'payment_method'    => $method,
                        'coupon_discount'   => 0,
                        'platform_subsidy'  => $platformSubsidy,
                        'wallet_amount'     => 0,
                        'razorpay_amount'   => 0,
                        'payment_reference' => $method === 'plan'
                            ? 'PLAN-'.($planAllowance['subscription_id'] ?? 'FREE')
                            : null,
                        'booking_ref'       => $bookingRef,
                        'meeting_channel'   => $channel,
                        'meeting_link'      => url('as/'.$channel),
                    ]);
                });
            } catch (\RuntimeException $e) {
                if ($e->getMessage() === 'SLOT_BUSY') {
                    return $this->slotBusyResponse();
                }
                throw $e;
            } catch (InvalidArgumentException $e) {
                if ($e->getMessage() === 'SLOT_TAKEN') {
                    return $this->slotBusyResponse('This time slot was just booked by someone else. Please choose another.');
                }
                throw $e;
            }

            $invoice = $this->invoices->ensureForSession($session, 'system');

            $minsLeft = $planAllowance['minutes_remaining'] ?? null;
            $msg = $coveredByPlan
                ? 'Session booked using your '.($planAllowance['plan_name'] ?? 'subscription').' plan free minutes'
                    .(! empty($planAllowance['unlimited'])
                        ? ' (unlimited).'
                        : ' ('.max(0, (int) $minsLeft - $duration).' free minutes left this period).')
                : 'Session booked successfully!';

            return $this->ok($msg, 201, [
                'requires_payment'        => false,
                'requires_payment_choice' => false,
                'booked'                  => true,
                'session'                 => $this->sessionArray($session),
                'invoice'                 => $invoice?->toPublicArray(),
                'payment_method'          => $method,
                'plan_allowance'          => $mentee->planSessionAllowance($duration),
                'pricing'                 => [
                    'list_amount'      => $listAmount,
                    'mentee_paid'      => 0,
                    'coupon_discount'  => 0,
                    'platform_subsidy' => $platformSubsidy,
                ],
            ]);
        }

        $walletBalance = round((float) $mentee->wallet_balance, 2);
        $shortfall = round(max(0, $amount - $walletBalance), 2);

        // 2) Paid session and no method chosen → ask user
        if (! $paymentMethod) {
            $options = ['wallet', 'razorpay'];
            if ($walletBalance > 0 && $walletBalance < $amount) {
                $options[] = 'hybrid';
            }

            return $this->ok('Choose a payment method to continue.', 200, [
                'requires_payment'        => false,
                'requires_payment_choice' => true,
                'booked'                  => false,
                'amount'                  => $amount,
                'base_amount'             => $baseAmount,
                'list_amount'             => $listAmount,
                'coupon_discount'         => $couponDiscount,
                'platform_subsidy'        => $platformSubsidy,
                'currency'                => $currency,
                'wallet_balance'          => $walletBalance,
                'shortfall'               => $shortfall,
                'can_pay_full_wallet'     => $walletBalance >= $amount,
                'payment_options'         => $options,
                'plan_allowance'          => $planAllowance,
                'tax_applicable'          => false,
                'tax_total'               => 0,
                'booking_draft'           => [
                    'mentor_id' => $mentor->id,
                    'date'      => $data['date'],
                    'time'      => $data['time'],
                    'duration'  => (int) $data['duration'],
                    'title'     => $title,
                    'agenda'    => $data['agenda'] ?? null,
                    'coupon_code' => $data['coupon_code'] ?? null,
                ],
            ]);
        }

        if (! in_array($paymentMethod, ['wallet', 'razorpay', 'hybrid'], true)) {
            return $this->fail('Invalid payment_method. Use wallet, razorpay, or hybrid.', 422);
        }

        // 3) Wallet full payment
        if ($paymentMethod === 'wallet') {
            if ($walletBalance < $amount) {
                return $this->fail('Insufficient wallet balance.', 422, [
                    'insufficient_wallet' => true,
                    'amount'              => $amount,
                    'wallet_balance'      => $walletBalance,
                    'shortfall'           => $shortfall,
                    'needs_topup'         => true,
                    'allow_hybrid'        => $walletBalance > 0,
                    'payment_options'     => array_values(array_filter([
                        'topup',
                        $walletBalance > 0 ? 'hybrid' : null,
                        'razorpay',
                    ])),
                    'topup_url'           => route('mentee.wallet'),
                ]);
            }

            try {
                $session = ConsultationSession::withSlotLock($mentor->id, $scheduledAt, function () use (
                    $mentor, $mentee, $scheduledAt, $data, $amount, $listAmount, $currency, $bookingRef,
                    $channel, $title, $source, $appliedOffer, $couponDiscount, $platformSubsidy, $time, $duration
                ) {
                    return DB::transaction(function () use (
                        $mentor, $mentee, $scheduledAt, $data, $amount, $listAmount, $currency, $bookingRef,
                        $channel, $title, $source, $appliedOffer, $couponDiscount, $platformSubsidy, $time, $duration
                    ) {
                        if ($this->availability->overlapsExisting($mentor->id, (string) $data['date'], $time, $duration, $mentee->id)) {
                            throw new InvalidArgumentException('SLOT_TAKEN');
                        }

                        $fresh = User::where('id', $mentee->id)->lockForUpdate()->firstOrFail();
                        if ((float) $fresh->wallet_balance < $amount) {
                            throw new InvalidArgumentException('INSUFFICIENT_WALLET');
                        }

                        $session = ConsultationSession::create([
                            'mentor_id'         => $mentor->id,
                            'mentee_id'         => $mentee->id,
                            'scheduled_at'      => $scheduledAt,
                            'duration_minutes'  => $data['duration'],
                            'timezone'          => 'Asia/Kolkata',
                            'title'             => $title,
                            'agenda'            => $data['agenda'] ?? null,
                            'status'            => ConsultationSession::STATUS_UPCOMING,
                            'amount'            => $amount,
                            'list_amount'       => $listAmount,
                            'offer_id'          => $appliedOffer?->id,
                            'coupon_discount'   => $couponDiscount,
                            'platform_subsidy'  => $platformSubsidy,
                            'currency'          => $currency,
                            'payment_status'    => 'paid',
                            'payment_method'    => 'wallet',
                            'wallet_amount'     => $amount,
                            'razorpay_amount'   => 0,
                            'payment_reference' => 'WAL-'.$bookingRef,
                            'booking_ref'       => $bookingRef,
                            'meeting_channel'   => $channel,
                            'meeting_link'      => url('as/'.$channel),
                        ]);

                        $fresh->debitWallet(
                            $amount,
                            "Session booking {$bookingRef}".($couponDiscount > 0 ? ' (coupon applied)' : ''),
                            [
                                'reference'            => 'WAL-'.$bookingRef,
                                'transactionable_type' => ConsultationSession::class,
                                'transactionable_id'   => $session->id,
                                'meta'                 => [
                                    'booking_ref'      => $bookingRef,
                                    'mentor_id'        => $mentor->id,
                                    'source'           => 'session_booking_wallet_'.$source,
                                    'list_amount'      => $listAmount,
                                    'coupon_discount'  => $couponDiscount,
                                    'platform_subsidy' => $platformSubsidy,
                                    'offer_id'         => $appliedOffer?->id,
                                ],
                            ]
                        );

                        if ($appliedOffer && $couponDiscount > 0) {
                            $this->offers->recordSessionRedemption($appliedOffer, $mentee, $session, $couponDiscount);
                        }

                        return $session;
                    });
                });
            } catch (\RuntimeException $e) {
                if ($e->getMessage() === 'SLOT_BUSY') {
                    return $this->slotBusyResponse();
                }
                throw $e;
            } catch (InvalidArgumentException $e) {
                if ($e->getMessage() === 'SLOT_TAKEN') {
                    return $this->slotBusyResponse('This time slot was just booked by someone else. Please choose another.');
                }
                if ($e->getMessage() === 'INSUFFICIENT_WALLET') {
                    return $this->fail('Insufficient wallet balance.', 422, [
                        'insufficient_wallet' => true,
                        'amount'              => $amount,
                        'needs_topup'         => true,
                        'topup_url'           => route('mentee.wallet'),
                    ]);
                }
                throw $e;
            } catch (\Throwable $e) {
                Log::error('Wallet booking failed.', ['error' => $e->getMessage()]);

                return $this->fail('Unable to complete wallet payment right now.', 500);
            }

            $invoice = $this->invoices->ensureForSession($session->fresh(), 'system');

            return $this->ok('Session booked! ₹'.number_format($amount, 0).' deducted from your wallet.', 201, [
                'requires_payment'        => false,
                'requires_payment_choice' => false,
                'booked'                  => true,
                'session'                 => $this->sessionArray($session->fresh()),
                'invoice'                 => $invoice?->toPublicArray(),
                'payment_method'          => 'wallet',
            ]);
        }

        // 4) Razorpay full OR hybrid (wallet + Razorpay remainder)
        $walletPart = 0.0;
        $razorPart = $amount;
        $method = 'razorpay';

        if ($paymentMethod === 'hybrid') {
            if ($walletBalance <= 0) {
                return $this->fail('No wallet balance available for hybrid payment. Use razorpay or top up.', 422, [
                    'needs_topup' => true,
                    'topup_url'   => route('mentee.wallet'),
                ]);
            }
            if ($walletBalance >= $amount) {
                $data['payment_method'] = 'wallet';

                return $this->book($mentee, $data, $source);
            }
            $walletPart = $walletBalance;
            $razorPart = round($amount - $walletPart, 2);
            $method = 'hybrid';
        }

        return $this->createRazorpayPending(
            $mentee,
            $mentor,
            $scheduledAt,
            $data,
            $amount,
            $listAmount,
            $walletPart,
            $razorPart,
            $method,
            $currency,
            $bookingRef,
            $channel,
            $title,
            $source,
            $appliedOffer,
            $couponDiscount,
            $platformSubsidy
        );
    }

    /**
     * Confirm Razorpay (and debit wallet for hybrid).
     *
     * @return array{ok:bool,http?:int,payload:array}
     */
    public function verify(User $mentee, array $data): array
    {
        $creds = $this->razorpayCredentials();
        if (empty($creds['secret'])) {
            return $this->fail('Payment gateway is not configured.', 503);
        }

        $orderId = (string) ($data['razorpay_order_id'] ?? '');
        $expectedSig = hash_hmac(
            'sha256',
            $orderId.'|'.$data['razorpay_payment_id'],
            $creds['secret']
        );

        if (! hash_equals($expectedSig, $data['razorpay_signature'])) {
            return $this->fail('Payment signature verification failed.', 422);
        }

        // Idempotent: already created after a previous verify
        $existing = ConsultationSession::where('mentee_id', $mentee->id)
            ->where('razorpay_order_id', $orderId)
            ->latest('id')
            ->first();

        if ($existing && $existing->payment_status === 'paid') {
            $invoice = $this->invoices->ensureForSession($existing, 'system');

            return $this->ok('Session already booked.', 200, [
                'booked'  => true,
                'session' => $this->sessionArray($existing),
                'invoice' => $invoice?->toPublicArray(),
            ]);
        }

        $draft = Cache::get(ConsultationSession::bookingDraftCacheKey($orderId));
        if (! is_array($draft) || (int) ($draft['mentee_id'] ?? 0) !== (int) $mentee->id) {
            return $this->fail('Booking draft not found or expired. Please book again.', 404);
        }

        $scheduledAt = Carbon::parse($draft['scheduled_at'], 'Asia/Kolkata');
        $duration = (int) ($draft['duration'] ?? 30);
        $time = substr((string) ($draft['time'] ?? $scheduledAt->format('H:i')), 0, 5);
        $date = (string) ($draft['date'] ?? $scheduledAt->toDateString());
        $paymentId = (string) ($data['razorpay_payment_id'] ?? '');
        $razorAmount = round((float) ($draft['razorpay_amount'] ?? $draft['amount'] ?? 0), 2);

        try {
            $session = ConsultationSession::withSlotLock((int) $draft['mentor_id'], $scheduledAt, function () use (
                $mentee, $data, $draft, $scheduledAt, $orderId, $duration, $time, $date, $paymentId, $razorAmount
            ) {
                if ($this->availability->overlapsExisting((int) $draft['mentor_id'], $date, $time, $duration, $mentee->id)) {
                    throw new InvalidArgumentException('SLOT_TAKEN');
                }

                return DB::transaction(function () use ($mentee, $data, $draft, $scheduledAt, $orderId) {
                    $walletPart = round((float) ($draft['wallet_amount'] ?? 0), 2);
                    $method = (string) ($draft['payment_method'] ?? 'razorpay');

                    if ($walletPart > 0 && $method === 'hybrid') {
                        $fresh = User::where('id', $mentee->id)->lockForUpdate()->firstOrFail();
                        if ((float) $fresh->wallet_balance < $walletPart) {
                            throw new InvalidArgumentException('Insufficient wallet balance to complete hybrid payment. Please top up and retry.');
                        }
                    }

                    $session = ConsultationSession::create([
                        'mentor_id'           => $draft['mentor_id'],
                        'mentee_id'           => $mentee->id,
                        'scheduled_at'        => $scheduledAt,
                        'duration_minutes'    => $draft['duration'],
                        'timezone'            => 'Asia/Kolkata',
                        'title'               => $draft['title'],
                        'agenda'              => $draft['agenda'] ?? null,
                        'status'              => ConsultationSession::STATUS_UPCOMING,
                        'amount'              => $draft['amount'],
                        'list_amount'         => $draft['list_amount'] ?? round((float) $draft['amount'] + (float) ($draft['coupon_discount'] ?? 0), 2),
                        'offer_id'            => $draft['offer_id'] ?? null,
                        'coupon_discount'     => $draft['coupon_discount'] ?? 0,
                        'platform_subsidy'    => $draft['platform_subsidy'] ?? ($draft['coupon_discount'] ?? 0),
                        'currency'            => $draft['currency'] ?? 'INR',
                        'payment_status'      => 'paid',
                        'payment_method'      => $method,
                        'wallet_amount'       => $walletPart,
                        'razorpay_amount'     => $draft['razorpay_amount'] ?? 0,
                        'razorpay_order_id'   => $orderId,
                        'razorpay_payment_id' => $data['razorpay_payment_id'],
                        'payment_reference'   => $data['razorpay_payment_id'],
                        'booking_ref'         => $draft['booking_ref'],
                        'meeting_channel'     => $draft['meeting_channel'],
                        'meeting_link'        => url('as/'.$draft['meeting_channel']),
                    ]);

                    if ($walletPart > 0 && $method === 'hybrid') {
                        $fresh = User::where('id', $mentee->id)->lockForUpdate()->firstOrFail();
                        $fresh->debitWallet(
                            $walletPart,
                            "Hybrid session booking {$session->booking_ref}",
                            [
                                'reference'            => 'WAL-'.$session->booking_ref,
                                'transactionable_type' => ConsultationSession::class,
                                'transactionable_id'   => $session->id,
                                'meta'                 => [
                                    'booking_ref' => $session->booking_ref,
                                    'source'      => 'session_booking_hybrid',
                                ],
                            ]
                        );
                    }

                    if (! empty($draft['offer_id']) && (float) ($draft['coupon_discount'] ?? 0) > 0) {
                        $offer = \App\Models\Offer::find($draft['offer_id']);
                        if ($offer) {
                            $this->offers->recordSessionRedemption(
                                $offer,
                                $mentee,
                                $session,
                                (float) $draft['coupon_discount']
                            );
                        }
                    }

                    return $session;
                });
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'SLOT_BUSY') {
                $this->refundRazorpayPayment($paymentId, $razorAmount);

                return $this->slotBusyResponse(
                    'This time slot is busy. Your online payment has been refunded automatically.'
                );
            }
            throw $e;
        } catch (InvalidArgumentException $e) {
            if ($e->getMessage() === 'SLOT_TAKEN') {
                $refunded = $this->refundRazorpayPayment($paymentId, $razorAmount);
                ConsultationSession::clearSlotHold((int) $draft['mentor_id'], $scheduledAt);
                Cache::forget(ConsultationSession::bookingDraftCacheKey($orderId));

                return $this->fail(
                    $refunded
                        ? 'This time slot was booked by someone else. Your payment has been refunded automatically. Please choose another slot.'
                        : 'This time slot was booked by someone else. Your payment will be refunded shortly — contact support if it does not appear.',
                    409,
                    [
                        'slot_busy'        => true,
                        'payment_refunded' => $refunded,
                        'booked'           => false,
                    ]
                );
            }

            return $this->fail($e->getMessage(), 422, [
                'needs_topup' => true,
                'topup_url'   => route('mentee.wallet'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Session payment verify failed: '.$e->getMessage());

            return $this->fail('Unable to confirm payment right now.', 500);
        }

        ConsultationSession::clearSlotHold((int) $draft['mentor_id'], $scheduledAt);
        Cache::forget(ConsultationSession::bookingDraftCacheKey($orderId));

        $invoice = $this->invoices->ensureForSession($session, 'system');

        return $this->ok('Payment successful! Your session is booked.', 200, [
            'booked'          => true,
            'requires_payment'=> false,
            'session'         => $this->sessionArray($session),
            'invoice'         => $invoice?->toPublicArray(),
            'payment_method'  => $session->payment_method,
            'booking_ref'     => $session->booking_ref,
        ]);
    }

    private function createRazorpayPending(
        User $mentee,
        User $mentor,
        Carbon $scheduledAt,
        array $data,
        float $amount,
        float $listAmount,
        float $walletPart,
        float $razorPart,
        string $method,
        string $currency,
        string $bookingRef,
        string $channel,
        string $title,
        string $source,
        ?\App\Models\Offer $appliedOffer = null,
        float $couponDiscount = 0.0,
        float $platformSubsidy = 0.0
    ): array {
        $creds = $this->razorpayCredentials();
        if (! ($creds['enabled'] ?? true)) {
            return $this->fail('Online payment is disabled. Please top up your wallet.', 422, [
                'needs_topup' => true,
                'topup_url'   => route('mentee.wallet'),
            ]);
        }
        if (empty($creds['key']) || empty($creds['secret'])) {
            return $this->fail('Payment gateway is not configured.', 503, [
                'topup_url' => route('mentee.wallet'),
            ]);
        }

        $amountInPaise = (int) round($razorPart * 100);
        if ($amountInPaise < 100) {
            return $this->fail('Payable online amount must be at least ₹1.', 422);
        }

        $receipt = 'ses_'.$mentee->id.'_'.$mentor->id.'_'.time();

        try {
            $response = Http::withBasicAuth($creds['key'], $creds['secret'])
                ->acceptJson()
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount'   => $amountInPaise,
                    'currency' => $currency,
                    'receipt'  => Str::limit($receipt, 40, ''),
                    'notes'    => [
                        'mentee_id'     => (string) $mentee->id,
                        'mentor_id'     => (string) $mentor->id,
                        'booking_ref'   => $bookingRef,
                        'payment_method'=> $method,
                        'wallet_amount' => (string) $walletPart,
                        'source'        => $source,
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('Razorpay session order failed.', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return $this->fail('Unable to initiate payment right now.', 502);
            }

            $order = $response->json();
        } catch (\Throwable $e) {
            Log::error('Razorpay session order exception: '.$e->getMessage());

            return $this->fail('Unable to initiate payment right now.', 502);
        }

        $orderId = (string) ($order['id'] ?? '');
        if ($orderId === '') {
            return $this->fail('Unable to initiate payment right now.', 502);
        }

        // Claim the slot BEFORE telling the mentee to pay — second mentee fails here (no charge).
        if (! ConsultationSession::tryAcquireSlotHold(
            $mentor->id,
            $mentee->id,
            $scheduledAt,
            $orderId,
            (int) $data['duration']
        )) {
            return $this->slotBusyResponse(
                'This time slot is being booked by another mentee. Please choose another slot.'
            );
        }

        // Re-check DB under lock in case someone wallet/plan booked while we created the order.
        try {
            ConsultationSession::withSlotLock($mentor->id, $scheduledAt, function () use ($mentor, $mentee, $data, $scheduledAt) {
                $duration = (int) $data['duration'];
                $time = substr((string) ($data['time'] ?? ''), 0, 5);
                if ($this->availability->overlapsExisting($mentor->id, (string) $data['date'], $time, $duration, $mentee->id)) {
                    throw new InvalidArgumentException('SLOT_TAKEN');
                }
            });
        } catch (\RuntimeException $e) {
            ConsultationSession::clearSlotHold($mentor->id, $scheduledAt);
            if ($e->getMessage() === 'SLOT_BUSY') {
                return $this->slotBusyResponse();
            }
            throw $e;
        } catch (InvalidArgumentException $e) {
            ConsultationSession::clearSlotHold($mentor->id, $scheduledAt);
            if ($e->getMessage() === 'SLOT_TAKEN') {
                return $this->slotBusyResponse('This time slot was just booked by someone else. Please choose another.');
            }
            throw $e;
        }

        // Cache draft only — no DB row until payment succeeds.
        $draft = [
            'mentee_id'         => $mentee->id,
            'mentor_id'         => $mentor->id,
            'scheduled_at'      => $scheduledAt->format('Y-m-d H:i:s'),
            'date'              => $data['date'],
            'time'              => $data['time'],
            'duration'          => (int) $data['duration'],
            'title'             => $title,
            'agenda'            => $data['agenda'] ?? null,
            'amount'            => $amount,
            'list_amount'       => $listAmount,
            'offer_id'          => $appliedOffer?->id,
            'coupon_discount'   => $couponDiscount,
            'platform_subsidy'  => $platformSubsidy,
            'currency'          => $currency,
            'payment_method'    => $method,
            'wallet_amount'     => $walletPart,
            'razorpay_amount'   => $razorPart,
            'booking_ref'       => $bookingRef,
            'meeting_channel'   => $channel,
            'source'            => $source,
        ];

        Cache::put(
            ConsultationSession::bookingDraftCacheKey($orderId),
            $draft,
            now()->addMinutes(max(30, ConsultationSession::PAYMENT_HOLD_MINUTES))
        );

        return $this->ok(
            $method === 'hybrid'
                ? 'Pay remaining ₹'.number_format($razorPart, 0).' online. ₹'.number_format($walletPart, 0).' will be taken from wallet after payment.'
                : 'Complete payment to confirm your booking.',
            201,
            [
                'requires_payment'        => true,
                'requires_payment_choice' => false,
                'booked'                  => false,
                'payment_method'          => $method,
                'session_id'              => null,
                'booking_ref'             => $bookingRef,
                'order_id'                => $orderId,
                'amount'                  => $amountInPaise,
                'amount_rupees'           => $razorPart,
                'session_amount'          => $amount,
                'base_amount'             => $listAmount,
                'list_amount'             => $listAmount,
                'coupon_discount'         => $couponDiscount,
                'platform_subsidy'        => $platformSubsidy,
                'wallet_amount'           => $walletPart,
                'razorpay_amount'         => $razorPart,
                'currency'                => $currency,
                'key'                     => $creds['key'],
                'name'                    => 'Vedrix',
                'description'             => 'Session with '.$mentor->name,
                'prefill'                 => [
                    'name'    => $mentee->name,
                    'email'   => $mentee->email,
                    'contact' => $mentee->phone ?? '',
                ],
                'wallet_balance'          => (float) $mentee->wallet_balance,
                'tax_applicable'          => false,
                'tax_total'               => 0,
                'booking_draft'           => [
                    'mentor_id' => $mentor->id,
                    'date'      => $data['date'],
                    'time'      => $data['time'],
                    'duration'  => (int) $data['duration'],
                    'title'     => $title,
                    'agenda'    => $data['agenda'] ?? null,
                    'coupon_code' => $data['coupon_code'] ?? null,
                ],
            ]
        );
    }

    /**
     * @return array{amount: float, discount: float, offer: ?\App\Models\Offer}|array{error: string}
     */
    private function resolveCouponDiscount(User $mentee, array $data, float $baseAmount): array
    {
        $code = trim((string) ($data['coupon_code'] ?? ''));
        if ($code === '') {
            return [
                'amount'   => $baseAmount,
                'discount' => 0.0,
                'offer'    => null,
            ];
        }

        $check = $this->offers->validateCoupon($mentee, $code, $baseAmount);
        if (! $check['valid']) {
            return ['error' => $check['message'] ?? 'Invalid coupon.'];
        }

        return [
            'amount'   => round(max(0, $baseAmount - $check['discount']), 2),
            'discount' => (float) $check['discount'],
            'offer'    => $check['offer'],
        ];
    }

    private function sessionArray(ConsultationSession $session): array
    {
        $listAmount = round((float) ($session->list_amount ?? 0), 2);
        if ($listAmount <= 0) {
            $listAmount = round((float) $session->amount + (float) ($session->coupon_discount ?? 0), 2);
        }

        return [
            'id'               => $session->id,
            'booking_ref'      => $session->booking_ref,
            'status'           => $session->status,
            'payment_status'   => $session->payment_status,
            'payment_method'   => $session->payment_method,
            'amount'           => (float) $session->amount,
            'list_amount'      => $listAmount,
            'coupon_discount'  => (float) ($session->coupon_discount ?? 0),
            'platform_subsidy' => (float) ($session->platform_subsidy ?? 0),
            'offer_id'         => $session->offer_id,
            'wallet_amount'    => (float) ($session->wallet_amount ?? 0),
            'razorpay_amount'  => (float) ($session->razorpay_amount ?? 0),
            'currency'         => $session->currency,
            'tax_applicable'   => false,
            'tax_total'        => 0,
            'scheduled_at'     => $session->scheduled_at?->toDateTimeString(),
            'duration_minutes' => $session->duration_minutes,
            'meeting_link'     => $session->meeting_link,
        ];
    }

    private function razorpayCredentials(): array
    {
        $settings = AppSetting::razorpay();

        return [
            'enabled' => $settings['enabled'] ?? true,
            'key'     => $settings['key'] ?: config('services.razorpay.key', env('RAZORPAY_KEY_ID', '')),
            'secret'  => $settings['secret'] ?: config('services.razorpay.secret', env('RAZORPAY_KEY_SECRET', '')),
        ];
    }

    private function ok(string $message, int $http, array $payload): array
    {
        return [
            'ok'      => true,
            'http'    => $http,
            'payload' => array_merge(['message' => $message], $payload),
        ];
    }

    private function fail(string $message, int $http, array $extra = []): array
    {
        return [
            'ok'      => false,
            'http'    => $http,
            'payload' => array_merge(['message' => $message], $extra),
        ];
    }

    private function slotBusyResponse(?string $message = null): array
    {
        return $this->fail(
            $message ?: 'This time slot is being booked by someone else. Please choose another slot.',
            409,
            [
                'slot_busy'           => true,
                'booked'              => false,
                'retry_after_seconds' => ConsultationSession::PAYMENT_HOLD_MINUTES * 60,
            ]
        );
    }

    /**
     * Auto-refund when payment succeeded but the slot was taken by another mentee.
     */
    private function refundRazorpayPayment(string $paymentId, float $amountRupees): bool
    {
        $paymentId = trim($paymentId);
        if ($paymentId === '' || $amountRupees <= 0) {
            return false;
        }

        $creds = $this->razorpayCredentials();
        if (empty($creds['key']) || empty($creds['secret'])) {
            Log::error('Cannot refund session payment — Razorpay not configured.', [
                'payment_id' => $paymentId,
            ]);

            return false;
        }

        try {
            $response = Http::withBasicAuth($creds['key'], $creds['secret'])
                ->acceptJson()
                ->post('https://api.razorpay.com/v1/payments/'.$paymentId.'/refund', [
                    'amount' => (int) round($amountRupees * 100),
                    'notes'  => [
                        'reason' => 'slot_taken_by_another_mentee',
                        'source' => 'session_booking_conflict',
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('Razorpay session refund failed.', [
                    'payment_id' => $paymentId,
                    'status'     => $response->status(),
                    'body'       => $response->body(),
                ]);

                return false;
            }

            Log::info('Razorpay session payment refunded after slot conflict.', [
                'payment_id' => $paymentId,
                'amount'     => $amountRupees,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Razorpay session refund exception: '.$e->getMessage(), [
                'payment_id' => $paymentId,
            ]);

            return false;
        }
    }
}
