<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\MockInterviewRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MockInterviewService
{
    private const MAX_FREE_DURATION_MINUTES = 60;

    /** @return array{rate_per_minute: float, currency: string} */
    public function prices(): array
    {
        return [
            'rate_per_minute' => round((float) AppSetting::get('addon_mock_interview_rate_per_minute', 15), 2),
            'currency'        => 'INR',
        ];
    }

    public function quote(User $user, ?int $durationMinutes = null): array
    {
        $prices = $this->prices();
        $rate = (float) $prices['rate_per_minute'];
        $duration = $durationMinutes ?? 60;

        if (! in_array($duration, MockInterviewRequest::DURATIONS, true)) {
            throw new InvalidArgumentException('Invalid duration. Choose 30, 45, 60, or 90 minutes.');
        }

        $amount = round($rate * $duration, 2);

        $subscription = $user->activeSubscription();
        $planSlug = $subscription?->plan?->slug;
        $months = $this->freeWindowMonths($planSlug);

        $included = $months !== null;
        $used = 0;
        $windowStarts = null;
        $nextFreeAt = null;
        $lastFreeAt = null;

        if ($included) {
            $windowStarts = Carbon::now()->subMonths($months);
            $freeRequests = MockInterviewRequest::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->where('is_paid_addon', false)
                ->whereIn('status', [
                    MockInterviewRequest::STATUS_SUBMITTED,
                    MockInterviewRequest::STATUS_CONFIRMED,
                    MockInterviewRequest::STATUS_COMPLETED,
                ])
                ->where('created_at', '>=', $windowStarts)
                ->orderBy('created_at')
                ->get(['id', 'created_at']);

            $used = $freeRequests->count();
            $blocking = $freeRequests->first();
            if ($blocking && $used >= 1) {
                $lastFreeAt = $blocking->created_at->copy();
                $nextFreeAt = $lastFreeAt->copy()->addMonths($months);
            }
        }

        $remaining = $included ? max(0, 1 - $used) : 0;
        $isFree = $included && $remaining > 0 && $duration <= self::MAX_FREE_DURATION_MINUTES;
        $payable = $isFree ? 0.0 : $amount;
        $walletBalance = round((float) $user->wallet_balance, 2);

        if ($isFree) {
            $nextFreeAt = null;
        }

        return [
            'rate_per_minute'  => $rate,
            'duration_minutes' => $duration,
            'plan_slug'        => $planSlug,
            'is_free'          => $isFree,
            'amount'           => $payable,
            'currency'         => $prices['currency'],
            'wallet_balance'   => $walletBalance,
            'payment'          => $isFree ? null : $this->paymentChoicePayload($payable, $walletBalance),
            'durations'        => MockInterviewRequest::DURATIONS,
            'entitlement'      => [
                'included'           => $included,
                'months'             => $months,
                'used'               => $used,
                'remaining'          => $remaining,
                'included_limit'     => $included ? 1 : 0,
                'payment_required'   => ! $isFree,
                'status'             => ! $included
                    ? 'addon'
                    : ($remaining > 0 ? 'available' : ($nextFreeAt ? 'next_free' : 'used')),
                'window_starts_at'   => $windowStarts?->toDateTimeString(),
                'last_used_at'       => $lastFreeAt?->toDateTimeString(),
                'next_free_at'       => $remaining > 0 ? null : $nextFreeAt?->toDateTimeString(),
                'max_free_duration'  => self::MAX_FREE_DURATION_MINUTES,
                'label'              => $this->entitlementLabel(
                    $planSlug,
                    $included,
                    $remaining,
                    $months,
                    $nextFreeAt
                ),
            ],
        ];
    }

    /**
     * @param  array{
     *     preferred_at: string,
     *     duration_minutes?: int|null,
     *     target_role?: string|null,
     *     mentee_notes?: string|null,
     *     payment_method?: string|null
     * }  $input
     * @return array{
     *     request: MockInterviewRequest|null,
     *     payment: array|null,
     *     requires_payment_choice?: bool,
     *     payment_choice?: array
     * }
     */
    public function submit(User $user, array $input): array
    {
        $this->purgePendingFor($user);

        $duration = isset($input['duration_minutes'])
            ? (int) $input['duration_minutes']
            : 60;

        if (! in_array($duration, MockInterviewRequest::DURATIONS, true)) {
            throw new InvalidArgumentException('Invalid duration. Choose 30, 45, 60, or 90 minutes.');
        }

        $preferredRaw = trim((string) ($input['preferred_at'] ?? ''));
        if ($preferredRaw === '') {
            throw new InvalidArgumentException('Please choose a preferred date and time for your mock interview.');
        }

        $preferredAt = Carbon::parse($preferredRaw, 'Asia/Kolkata');
        $minPreferred = Carbon::now('Asia/Kolkata')->addHours(2);
        if ($preferredAt->lt($minPreferred)) {
            throw new InvalidArgumentException('Preferred time must be at least 2 hours from now (Asia/Kolkata).');
        }

        $quote = $this->quote($user, $duration);
        $isFree = (bool) $quote['is_free'];
        $amount = (float) $quote['amount'];
        $rate = (float) $quote['rate_per_minute'];

        $targetRole = trim((string) ($input['target_role'] ?? '')) ?: null;
        $menteeNotes = trim((string) ($input['mentee_notes'] ?? '')) ?: null;

        $basePayload = [
            'user_id'          => $user->id,
            'duration_minutes' => $duration,
            'preferred_at'     => $preferredAt,
            'timezone'         => 'Asia/Kolkata',
            'target_role'      => $targetRole,
            'mentee_notes'     => $menteeNotes,
            'rate_per_minute'  => $rate,
            'currency'         => $quote['currency'],
            'plan_slug'        => $quote['plan_slug'],
        ];

        if ($isFree) {
            $request = MockInterviewRequest::create(array_merge($basePayload, [
                'status'            => MockInterviewRequest::STATUS_SUBMITTED,
                'is_paid_addon'     => false,
                'amount'            => 0,
                'payment_status'    => 'free',
                'payment_method'    => 'plan',
                'wallet_amount'     => 0,
                'razorpay_amount'   => 0,
                'payment_reference' => null,
            ]));

            return [
                'request'                   => $request->fresh(),
                'payment'                   => null,
                'requires_payment_choice'   => false,
            ];
        }

        $paymentMethod = isset($input['payment_method'])
            ? strtolower(trim((string) $input['payment_method']))
            : null;

        if (! $paymentMethod) {
            $choice = $this->paymentChoicePayload($amount, round((float) $user->wallet_balance, 2));

            return [
                'request'                 => null,
                'payment'                 => null,
                'requires_payment_choice' => true,
                'payment_choice'          => $choice,
            ];
        }

        if ($paymentMethod === 'wallet') {
            $walletBalance = round((float) $user->wallet_balance, 2);
            if ($walletBalance < $amount) {
                throw new InvalidArgumentException(
                    'Insufficient wallet balance. Top up, or pay with Razorpay'.($walletBalance > 0 ? ' / Wallet + Razorpay' : '').'.'
                );
            }

            $request = DB::transaction(function () use ($user, $basePayload, $amount) {
                $fresh = User::where('id', $user->id)->lockForUpdate()->firstOrFail();
                if ((float) $fresh->wallet_balance < $amount) {
                    throw new InvalidArgumentException('Insufficient wallet balance.');
                }

                $request = MockInterviewRequest::create(array_merge($basePayload, [
                    'status'            => MockInterviewRequest::STATUS_SUBMITTED,
                    'is_paid_addon'     => true,
                    'amount'            => $amount,
                    'payment_status'    => 'paid',
                    'payment_method'    => 'wallet',
                    'wallet_amount'     => $amount,
                    'razorpay_amount'   => 0,
                    'payment_reference' => null,
                ]));

                $ref = 'WAL-MI-'.$request->id;
                $request->update(['payment_reference' => $ref]);

                $fresh->debitWallet(
                    $amount,
                    'Mock interview session',
                    [
                        'reference'            => $ref,
                        'transactionable_type' => MockInterviewRequest::class,
                        'transactionable_id'   => $request->id,
                        'meta'                 => [
                            'source'     => 'mock_interview_wallet',
                            'request_id' => $request->id,
                        ],
                    ]
                );

                return $request->fresh();
            });

            return [
                'request'                 => $request,
                'payment'                 => null,
                'requires_payment_choice' => false,
            ];
        }

        if (! in_array($paymentMethod, ['razorpay', 'hybrid'], true)) {
            throw new InvalidArgumentException('Invalid payment_method. Use wallet, razorpay, or hybrid.');
        }

        $request = MockInterviewRequest::create(array_merge($basePayload, [
            'status'          => MockInterviewRequest::STATUS_PENDING_PAYMENT,
            'is_paid_addon'   => true,
            'amount'          => $amount,
            'payment_status'  => 'pending',
            'payment_method'  => null,
            'wallet_amount'   => 0,
            'razorpay_amount' => 0,
        ]));

        return $this->applyPaymentMethod($user, $request, $paymentMethod);
    }

    /**
     * @return array{
     *     request: MockInterviewRequest,
     *     payment: array|null,
     *     requires_payment_choice?: bool,
     *     payment_choice?: array
     * }
     */
    public function pay(User $user, MockInterviewRequest $request, ?string $paymentMethod): array
    {
        if ((int) $request->user_id !== (int) $user->id) {
            throw new InvalidArgumentException('Request not found.');
        }

        if ($request->status === MockInterviewRequest::STATUS_SUBMITTED
            && in_array($request->payment_status, ['paid', 'free'], true)) {
            return [
                'request'                 => $request->fresh(),
                'payment'                 => null,
                'requires_payment_choice' => false,
            ];
        }

        if ($request->status !== MockInterviewRequest::STATUS_PENDING_PAYMENT) {
            throw new InvalidArgumentException('This request is not awaiting payment.');
        }

        $amount = round((float) $request->amount, 2);
        $method = $paymentMethod ? strtolower(trim($paymentMethod)) : null;

        if (! $method) {
            return [
                'request'                 => $request->fresh(),
                'payment'                 => null,
                'requires_payment_choice' => true,
                'payment_choice'          => $this->paymentChoicePayload($amount, round((float) $user->wallet_balance, 2)),
            ];
        }

        return $this->applyPaymentMethod($user, $request, $method);
    }

    /**
     * @return array{request: MockInterviewRequest, payment: array|null, requires_payment_choice: bool}
     */
    private function applyPaymentMethod(User $user, MockInterviewRequest $request, string $paymentMethod): array
    {
        if (! in_array($paymentMethod, ['wallet', 'razorpay', 'hybrid'], true)) {
            throw new InvalidArgumentException('Invalid payment_method. Use wallet, razorpay, or hybrid.');
        }

        $amount = round((float) $request->amount, 2);
        $walletBalance = round((float) $user->wallet_balance, 2);

        if ($paymentMethod === 'wallet') {
            if ($walletBalance < $amount) {
                throw new InvalidArgumentException(
                    'Insufficient wallet balance. Top up, or pay with Razorpay'.($walletBalance > 0 ? ' / Wallet + Razorpay' : '').'.'
                );
            }

            $request = DB::transaction(function () use ($user, $request, $amount) {
                $fresh = User::where('id', $user->id)->lockForUpdate()->firstOrFail();
                if ((float) $fresh->wallet_balance < $amount) {
                    throw new InvalidArgumentException('Insufficient wallet balance.');
                }

                $ref = 'WAL-MI-'.$request->id;
                $request->update([
                    'status'              => MockInterviewRequest::STATUS_SUBMITTED,
                    'payment_status'      => 'paid',
                    'payment_method'      => 'wallet',
                    'wallet_amount'       => $amount,
                    'razorpay_amount'     => 0,
                    'payment_reference'   => $ref,
                    'razorpay_order_id'   => null,
                    'razorpay_payment_id' => null,
                ]);

                $fresh->debitWallet(
                    $amount,
                    'Mock interview session',
                    [
                        'reference'            => $ref,
                        'transactionable_type' => MockInterviewRequest::class,
                        'transactionable_id'   => $request->id,
                        'meta'                 => [
                            'source'     => 'mock_interview_wallet',
                            'request_id' => $request->id,
                        ],
                    ]
                );

                return $request->fresh();
            });

            return [
                'request'                 => $request,
                'payment'                 => null,
                'requires_payment_choice' => false,
            ];
        }

        $walletPart = 0.0;
        $razorPart = $amount;
        $method = 'razorpay';

        if ($paymentMethod === 'hybrid') {
            if ($walletBalance <= 0) {
                throw new InvalidArgumentException('No wallet balance available for hybrid payment. Use razorpay or top up.');
            }
            if ($walletBalance >= $amount) {
                return $this->applyPaymentMethod($user, $request, 'wallet');
            }
            $walletPart = $walletBalance;
            $razorPart = round($amount - $walletPart, 2);
            $method = 'hybrid';
        }

        $request->update([
            'payment_method'  => $method,
            'wallet_amount'   => $walletPart,
            'razorpay_amount' => $razorPart,
            'payment_status'  => 'pending',
            'status'          => MockInterviewRequest::STATUS_PENDING_PAYMENT,
        ]);

        $payment = $this->createRazorpayOrder($user, $request->fresh(), $razorPart);

        return [
            'request'                 => $request->fresh(),
            'payment'                 => $payment,
            'requires_payment_choice' => false,
        ];
    }

    public function verifyPayment(User $user, MockInterviewRequest $request, array $payload): MockInterviewRequest
    {
        if ((int) $request->user_id !== (int) $user->id) {
            throw new InvalidArgumentException('Request not found.');
        }

        if ($request->status === MockInterviewRequest::STATUS_SUBMITTED && $request->payment_status === 'paid') {
            return $request->fresh();
        }

        if ($request->status !== MockInterviewRequest::STATUS_PENDING_PAYMENT) {
            throw new InvalidArgumentException('This request is not awaiting payment.');
        }

        $orderId = (string) ($payload['razorpay_order_id'] ?? '');
        $paymentId = (string) ($payload['razorpay_payment_id'] ?? '');
        $signature = (string) ($payload['razorpay_signature'] ?? '');

        if ($orderId === '' || $paymentId === '' || $signature === '') {
            throw new InvalidArgumentException('Payment verification details are missing.');
        }

        if ($request->razorpay_order_id && $request->razorpay_order_id !== $orderId) {
            throw new InvalidArgumentException('Payment order mismatch.');
        }

        $creds = AppSetting::razorpay();
        if (empty($creds['secret'])) {
            throw new InvalidArgumentException('Payment gateway is not configured.');
        }

        $expected = hash_hmac('sha256', $orderId.'|'.$paymentId, $creds['secret']);
        if (! hash_equals($expected, $signature)) {
            throw new InvalidArgumentException('Payment signature verification failed.');
        }

        $method = (string) ($request->payment_method ?: 'razorpay');
        $walletPart = round((float) ($request->wallet_amount ?? 0), 2);
        $razorPart = round((float) ($request->razorpay_amount ?? $request->amount), 2);

        $request = DB::transaction(function () use ($user, $request, $orderId, $paymentId, $method, $walletPart, $razorPart) {
            if ($walletPart > 0 && $method === 'hybrid') {
                $fresh = User::where('id', $user->id)->lockForUpdate()->firstOrFail();
                if ((float) $fresh->wallet_balance < $walletPart) {
                    throw new InvalidArgumentException('Insufficient wallet balance to complete hybrid payment. Please top up and retry.');
                }

                $ref = 'WAL-MI-'.$request->id;
                $fresh->debitWallet(
                    $walletPart,
                    'Mock interview session (wallet part)',
                    [
                        'reference'            => $ref,
                        'transactionable_type' => MockInterviewRequest::class,
                        'transactionable_id'   => $request->id,
                        'meta'                 => [
                            'source'     => 'mock_interview_hybrid',
                            'request_id' => $request->id,
                        ],
                    ]
                );
            }

            $request->update([
                'status'              => MockInterviewRequest::STATUS_SUBMITTED,
                'payment_status'      => 'paid',
                'payment_method'      => in_array($method, ['wallet', 'razorpay', 'hybrid'], true) ? $method : 'razorpay',
                'wallet_amount'       => $method === 'hybrid' ? $walletPart : ($method === 'wallet' ? $walletPart : 0),
                'razorpay_amount'     => $razorPart,
                'razorpay_order_id'   => $orderId,
                'razorpay_payment_id' => $paymentId,
                'payment_reference'   => $paymentId,
            ]);

            return $request->fresh();
        });

        return $request->fresh();
    }

    public function confirm(
        MockInterviewRequest $request,
        User $admin,
        ?int $mentorId = null,
        ?string $notes = null,
    ): MockInterviewRequest {
        if ($request->status !== MockInterviewRequest::STATUS_SUBMITTED) {
            throw new InvalidArgumentException('Only submitted requests can be confirmed.');
        }

        $mentorId = $mentorId ?: $request->mentor_id;
        if (! $mentorId) {
            throw new InvalidArgumentException('Assign a mentor before confirming so the Agora meeting can start.');
        }

        $mentor = User::query()->where('id', $mentorId)->where('role', 'mentor')->first();
        if (! $mentor) {
            throw new InvalidArgumentException('Selected mentor is invalid.');
        }

        $channel = $request->meeting_channel ?: strtoupper(Str::random(10));

        $updates = [
            'status'           => MockInterviewRequest::STATUS_CONFIRMED,
            'confirmed_at'     => now(),
            'mentor_id'        => $mentor->id,
            'assigned_by'      => $admin->id,
            'assigned_at'      => now(),
            'meeting_channel'  => $channel,
            'meeting_provider' => 'agora',
            'meeting_link'     => url('as/'.$channel),
        ];

        if ($notes !== null && trim($notes) !== '') {
            $updates['admin_notes'] = trim($notes);
        }

        $request->update($updates);

        return $request->fresh(['user', 'mentor', 'assigner']);
    }

    public function complete(MockInterviewRequest $request, User $admin, ?string $feedback = null): MockInterviewRequest
    {
        if (! in_array($request->status, [
            MockInterviewRequest::STATUS_CONFIRMED,
            MockInterviewRequest::STATUS_SUBMITTED,
        ], true)) {
            throw new InvalidArgumentException('Only confirmed or submitted requests can be completed.');
        }

        $request->update([
            'status'       => MockInterviewRequest::STATUS_COMPLETED,
            'feedback'     => $feedback !== null && trim($feedback) !== '' ? trim($feedback) : null,
            'reviewed_by'  => $admin->id,
            'reviewed_at'  => now(),
            'completed_at' => now(),
        ]);

        return $request->fresh(['user', 'mentor', 'reviewer']);
    }

    public function cancel(MockInterviewRequest $request): MockInterviewRequest
    {
        if ($request->status === MockInterviewRequest::STATUS_COMPLETED) {
            throw new InvalidArgumentException('Completed requests cannot be cancelled.');
        }

        if ($request->status === MockInterviewRequest::STATUS_CANCELLED) {
            return $request->fresh();
        }

        $request->update([
            'status' => MockInterviewRequest::STATUS_CANCELLED,
        ]);

        return $request->fresh();
    }

    public function purgePendingFor(User $user): void
    {
        MockInterviewRequest::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('status', MockInterviewRequest::STATUS_PENDING_PAYMENT)
            ->delete();
    }

    private function createRazorpayOrder(User $user, MockInterviewRequest $request, float $razorAmount): array
    {
        $creds = AppSetting::razorpay();
        if (empty($creds['key']) || empty($creds['secret'])) {
            throw new InvalidArgumentException('Payment gateway is not configured.');
        }
        if (! ($creds['enabled'] ?? true)) {
            throw new InvalidArgumentException('Online payments are currently disabled.');
        }

        $amountPaise = (int) round($razorAmount * 100);
        if ($amountPaise < 100) {
            throw new InvalidArgumentException('Online payment amount must be at least ₹1.');
        }

        $response = Http::withBasicAuth($creds['key'], $creds['secret'])
            ->acceptJson()
            ->post('https://api.razorpay.com/v1/orders', [
                'amount'   => $amountPaise,
                'currency' => $request->currency ?: 'INR',
                'receipt'  => Str::limit('mir_'.$request->id.'_'.$user->id, 40, ''),
                'notes'    => [
                    'user_id'         => (string) $user->id,
                    'request_id'      => (string) $request->id,
                    'purpose'         => 'mock_interview_addon',
                    'payment_method'  => (string) $request->payment_method,
                    'wallet_amount'   => (string) $request->wallet_amount,
                    'razorpay_amount' => (string) $razorAmount,
                    'duration_minutes' => (string) $request->duration_minutes,
                ],
            ]);

        if (! $response->successful()) {
            Log::error('Mock interview Razorpay order failed.', [
                'request_id' => $request->id,
                'status'     => $response->status(),
                'body'       => $response->body(),
            ]);
            throw new InvalidArgumentException('Unable to start payment right now. Please try again.');
        }

        $order = $response->json();
        $request->update(['razorpay_order_id' => $order['id'] ?? null]);

        $method = (string) ($request->payment_method ?: 'razorpay');

        return [
            'key'             => $creds['key'],
            'order_id'        => $order['id'] ?? null,
            'amount'          => $razorAmount,
            'amount_paise'    => $amountPaise,
            'amount_rupees'   => $razorAmount,
            'razorpay_amount' => $razorAmount,
            'wallet_amount'   => (float) $request->wallet_amount,
            'session_amount'  => (float) $request->amount,
            'total_amount'    => (float) $request->amount,
            'payment_method'  => $method,
            'currency'        => $request->currency ?: 'INR',
            'name'            => 'Vedrix',
            'description'     => 'Mock interview session'.($method === 'hybrid' ? ' (online part)' : ''),
            'prefill'         => [
                'name'    => $user->name,
                'email'   => $user->email,
                'contact' => $user->phone ?? '',
            ],
        ];
    }

    private function paymentChoicePayload(float $amount, float $walletBalance): array
    {
        $shortfall = round(max(0, $amount - $walletBalance), 2);
        $options = ['wallet', 'razorpay'];
        if ($walletBalance > 0 && $walletBalance < $amount) {
            $options[] = 'hybrid';
        }

        return [
            'amount'              => $amount,
            'currency'            => 'INR',
            'wallet_balance'      => $walletBalance,
            'shortfall'           => $shortfall,
            'can_pay_full_wallet' => $walletBalance >= $amount,
            'payment_options'     => $options,
            'allow_hybrid'        => $walletBalance > 0 && $walletBalance < $amount,
        ];
    }

    private function freeWindowMonths(?string $planSlug): ?int
    {
        return match (true) {
            $planSlug === 'premium' => 1,
            $planSlug === 'growth'  => 3,
            default                 => null,
        };
    }

    private function entitlementLabel(
        ?string $planSlug,
        bool $included,
        int $remaining,
        ?int $months,
        ?Carbon $nextFreeAt = null,
    ): string {
        if (! $included) {
            return 'Mock interviews are a paid add-on on your current plan.';
        }
        if ($remaining > 0) {
            $cap = self::MAX_FREE_DURATION_MINUTES;

            return "Included in your plan: 1 free mock interview (up to {$cap} min) every {$months} month"
                .($months === 1 ? '' : 's')
                .' (available now).';
        }
        if ($nextFreeAt) {
            return 'You\'ve used your free mock interview. Next free benefit on '
                .$nextFreeAt->timezone('Asia/Kolkata')->format('d M Y')
                .'. Longer sessions or extra bookings require payment.';
        }

        return "Your free mock interview entitlement for this {$months}-month window is used. Extra bookings require payment.";
    }
}
