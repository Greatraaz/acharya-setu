    <?php

    namespace App\Services;

    use App\Models\AppSetting;
    use App\Models\ConsultationSession;
    use App\Models\User;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;
    use InvalidArgumentException;

    class SessionBookingService
    {
        public function __construct(
            private readonly SessionInvoiceService $invoices
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
                return $this->fail('Duration must be 15, 30, 60, or 90 minutes.', 422);
            }
            $data['duration'] = $duration;

            // Session fees are GST-free: charge rate × duration only (no CGST/SGST/IGST).
            $amount = round((float) ($mentor->rate_per_minute ?? 0) * $duration, 2);
            $scheduledAt = Carbon::parse($data['date'].' '.$data['time'], 'Asia/Kolkata');
            if ($scheduledAt->lessThanOrEqualTo(Carbon::now('Asia/Kolkata'))) {
                return $this->fail('That time slot has already passed. Please choose a later time.', 422);
            }
            $planAllowance = $mentee->planSessionAllowance();
            $coveredByPlan = $amount > 0 && ($planAllowance['covered'] ?? false);
            $paymentMethod = isset($data['payment_method']) ? strtolower((string) $data['payment_method']) : null;

            ConsultationSession::expireAbandonedUnpaidPayments();
            ConsultationSession::releaseOwnUnpaidHold($mentee->id, $mentor->id, $scheduledAt);

            $alreadyBooked = ConsultationSession::where('mentor_id', $mentor->id)
                ->where('scheduled_at', $scheduledAt)
                ->occupyingSlot()
                ->exists();

            if ($alreadyBooked) {
                return $this->fail('This mentor already has an appointment at the selected date and time.', 422);
            }

            $channel = Str::random(10);
            $bookingRef = 'AS-'.mt_rand(10000000, 99999999);
            $currency = 'INR';
            $title = $data['title'] ?? ($data['agenda'] ? Str::limit($data['agenda'], 80) : 'Mentorship Session');

            // 1) Free mentor OR included plan sessions → auto book, no Razorpay
            if ($amount <= 0 || $coveredByPlan) {
                $method = $coveredByPlan ? 'plan' : 'free';
                $session = ConsultationSession::create([
                    'mentor_id'         => $mentor->id,
                    'mentee_id'         => $mentee->id,
                    'scheduled_at'      => $scheduledAt,
                    'duration_minutes'  => $data['duration'],
                    'timezone'          => 'Asia/Kolkata',
                    'title'             => $title,
                    'agenda'            => $data['agenda'] ?? null,
                    'status'            => ConsultationSession::STATUS_UPCOMING,
                    'amount'            => $coveredByPlan ? 0 : $amount,
                    'currency'          => $currency,
                    'payment_status'    => 'waived',
                    'payment_method'    => $method,
                    'wallet_amount'     => 0,
                    'razorpay_amount'   => 0,
                    'payment_reference' => $coveredByPlan
                        ? 'PLAN-'.($planAllowance['subscription_id'] ?? 'FREE')
                        : null,
                    'booking_ref'       => $bookingRef,
                    'meeting_channel'   => $channel,
                    'meeting_link'      => url('as/'.$channel),
                ]);

                $invoice = $this->invoices->ensureForSession($session, 'system');

                $msg = $coveredByPlan
                    ? 'Session booked using your '.($planAllowance['plan_name'] ?? 'subscription').' plan'
                        .(! empty($planAllowance['unlimited'])
                            ? ' (unlimited included sessions).'
                            : ' ('.max(0, (int) $planAllowance['remaining'] - 1).' included sessions left this month).')
                    : 'Session booked successfully!';

                return $this->ok($msg, 201, [
                    'requires_payment'        => false,
                    'requires_payment_choice' => false,
                    'booked'                  => true,
                    'session'                 => $this->sessionArray($session),
                    'invoice'                 => $invoice?->toPublicArray(),
                    'payment_method'          => $method,
                    'plan_allowance'          => $planAllowance,
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
                    $session = DB::transaction(function () use ($mentor, $mentee, $scheduledAt, $data, $amount, $currency, $bookingRef, $channel, $title, $source) {
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

                        $mentee->debitWallet(
                            $amount,
                            "Session booking {$bookingRef}",
                            [
                                'reference'            => 'WAL-'.$bookingRef,
                                'transactionable_type' => ConsultationSession::class,
                                'transactionable_id'   => $session->id,
                                'meta'                 => [
                                    'booking_ref' => $bookingRef,
                                    'mentor_id'   => $mentor->id,
                                    'source'      => 'session_booking_wallet_'.$source,
                                ],
                            ]
                        );

                        return $session;
                    });
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
                $walletPart,
                $razorPart,
                $method,
                $currency,
                $bookingRef,
                $channel,
                $title,
                $source
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

            $expectedSig = hash_hmac(
                'sha256',
                $data['razorpay_order_id'].'|'.$data['razorpay_payment_id'],
                $creds['secret']
            );

            if (! hash_equals($expectedSig, $data['razorpay_signature'])) {
                return $this->fail('Payment signature verification failed.', 422);
            }

            $session = ConsultationSession::where('mentee_id', $mentee->id)
                ->where('razorpay_order_id', $data['razorpay_order_id'])
                ->latest('id')
                ->first();

            if (! $session) {
                return $this->fail('Pending session not found for this payment.', 404);
            }

            if (
                $session->payment_status === 'paid'
                && $session->razorpay_payment_id === $data['razorpay_payment_id']
            ) {
                $invoice = $this->invoices->ensureForSession($session, 'system');

                return $this->ok('Session already confirmed.', 200, [
                    'booked'  => true,
                    'session' => $this->sessionArray($session),
                    'invoice' => $invoice?->toPublicArray(),
                ]);
            }

            try {
                DB::transaction(function () use ($session, $mentee, $data) {
                    $locked = ConsultationSession::where('id', $session->id)->lockForUpdate()->firstOrFail();
                    $walletPart = round((float) ($locked->wallet_amount ?? 0), 2);

                    if ($walletPart > 0 && $locked->payment_method === 'hybrid') {
                        $fresh = User::where('id', $mentee->id)->lockForUpdate()->firstOrFail();
                        if ((float) $fresh->wallet_balance < $walletPart) {
                            throw new InvalidArgumentException('Insufficient wallet balance to complete hybrid payment. Please top up and retry.');
                        }
                        $fresh->debitWallet(
                            $walletPart,
                            "Hybrid session booking {$locked->booking_ref}",
                            [
                                'reference'            => 'WAL-'.$locked->booking_ref,
                                'transactionable_type' => ConsultationSession::class,
                                'transactionable_id'   => $locked->id,
                                'meta'                 => [
                                    'booking_ref' => $locked->booking_ref,
                                    'source'      => 'session_booking_hybrid',
                                ],
                            ]
                        );
                    }

                    $locked->update([
                        'status'              => ConsultationSession::STATUS_UPCOMING,
                        'payment_status'      => 'paid',
                        'payment_reference'   => $data['razorpay_payment_id'],
                        'razorpay_payment_id' => $data['razorpay_payment_id'],
                    ]);
                });
            } catch (InvalidArgumentException $e) {
                return $this->fail($e->getMessage(), 422, [
                    'needs_topup' => true,
                    'topup_url'   => route('mentee.wallet'),
                ]);
            } catch (\Throwable $e) {
                Log::error('Session payment verify failed: '.$e->getMessage());

                return $this->fail('Unable to confirm payment right now.', 500);
            }

            $session->refresh();
            $invoice = $this->invoices->ensureForSession($session, 'system');

            return $this->ok('Payment successful! Your session is confirmed.', 200, [
                'booked'         => true,
                'requires_payment'=> false,
                'session'        => $this->sessionArray($session),
                'invoice'        => $invoice?->toPublicArray(),
                'payment_method' => $session->payment_method,
                'booking_ref'    => $session->booking_ref,
            ]);
        }

        private function createRazorpayPending(
            User $mentee,
            User $mentor,
            Carbon $scheduledAt,
            array $data,
            float $amount,
            float $walletPart,
            float $razorPart,
            string $method,
            string $currency,
            string $bookingRef,
            string $channel,
            string $title,
            string $source
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

            $session = ConsultationSession::create([
                'mentor_id'         => $mentor->id,
                'mentee_id'         => $mentee->id,
                'scheduled_at'      => $scheduledAt,
                'duration_minutes'  => $data['duration'],
                'timezone'          => 'Asia/Kolkata',
                'title'             => $title,
                'agenda'            => $data['agenda'] ?? null,
                'status'            => ConsultationSession::STATUS_PENDING,
                'amount'            => $amount,
                'currency'          => $currency,
                'payment_status'    => 'pending',
                'payment_method'    => $method,
                'wallet_amount'     => $walletPart,
                'razorpay_amount'   => $razorPart,
                'razorpay_order_id' => $order['id'] ?? null,
                'booking_ref'       => $bookingRef,
                'meeting_channel'   => $channel,
                'meeting_link'      => url('as/'.$channel),
            ]);

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
                    'session_id'              => $session->id,
                    'booking_ref'             => $bookingRef,
                    'order_id'                => $order['id'] ?? null,
                    'amount'                  => $amountInPaise,
                    'amount_rupees'           => $razorPart,
                    'session_amount'          => $amount,
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
                    'session'                 => $this->sessionArray($session),
                ]
            );
        }

        private function sessionArray(ConsultationSession $session): array
        {
            return [
                'id'               => $session->id,
                'booking_ref'      => $session->booking_ref,
                'status'           => $session->status,
                'payment_status'   => $session->payment_status,
                'payment_method'   => $session->payment_method,
                'amount'           => (float) $session->amount,
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
    }
