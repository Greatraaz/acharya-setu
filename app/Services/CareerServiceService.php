<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\CareerServiceRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CareerServiceService
{
    /** @return array{resume: float, linkedin: float, currency: string} */
    public function prices(): array
    {
        return [
            'resume'   => round((float) AppSetting::get('addon_resume_price', 499), 2),
            'linkedin' => round((float) AppSetting::get('addon_linkedin_price', 499), 2),
            'currency' => 'INR',
        ];
    }

    public function quote(User $user, string $type): array
    {
        $type = $this->normalizeType($type);
        $prices = $this->prices();
        $amount = $type === CareerServiceRequest::TYPE_LINKEDIN ? $prices['linkedin'] : $prices['resume'];

        $subscription = $user->activeSubscription();
        $planSlug = $subscription?->plan?->slug;
        $months = $this->freeWindowMonths($planSlug, $type);

        $included = $months !== null;
        $used = 0;
        $windowStarts = null;
        $nextFreeAt = null;
        $lastFreeAt = null;

        if ($included) {
            $windowStarts = Carbon::now()->subMonths($months);
            $freeRequests = CareerServiceRequest::query()
                ->where('user_id', $user->id)
                ->where('type', $type)
                ->where('is_paid_addon', false)
                ->whereIn('status', [
                    CareerServiceRequest::STATUS_SUBMITTED,
                    CareerServiceRequest::STATUS_COMPLETED,
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
        $isFree = $included && $remaining > 0;
        $payable = $isFree ? 0.0 : $amount;
        $walletBalance = round((float) $user->wallet_balance, 2);

        if ($isFree) {
            $nextFreeAt = null;
        }

        return [
            'type'        => $type,
            'plan_slug'   => $planSlug,
            'is_free'     => $isFree,
            'amount'      => $payable,
            'currency'    => $prices['currency'],
            'wallet_balance' => $walletBalance,
            'payment'     => $isFree ? null : $this->paymentChoicePayload($payable, $walletBalance),
            'entitlement' => [
                'included'         => $included,
                'months'           => $months,
                'used'             => $used,
                'remaining'        => $remaining,
                'included_limit'   => $included ? 1 : 0,
                'payment_required' => ! $isFree,
                'status'           => ! $included
                    ? 'addon'
                    : ($remaining > 0 ? 'available' : ($nextFreeAt ? 'next_free' : 'used')),
                'window_starts_at' => $windowStarts?->toDateTimeString(),
                'last_used_at'     => $lastFreeAt?->toDateTimeString(),
                'next_free_at'     => $remaining > 0 ? null : $nextFreeAt?->toDateTimeString(),
                'label'            => $this->entitlementLabel(
                    $planSlug,
                    $type,
                    $included,
                    $remaining,
                    $months,
                    $nextFreeAt
                ),
            ],
        ];
    }

    /**
     * Months in the free entitlement window, or null when always paid.
     */
    private function freeWindowMonths(?string $planSlug, string $type): ?int
    {
        return match (true) {
            $planSlug === 'premium' => 3,
            $planSlug === 'growth' => 6,
            $planSlug === 'essential' && $type === CareerServiceRequest::TYPE_RESUME => 6,
            default => null,
        };
    }

    /**
     * @param  array{linkedin_url?: string|null, mentee_notes?: string|null, resume?: UploadedFile|null, payment_method?: string|null}  $input
     * @return array{request: CareerServiceRequest|null, payment: array|null, requires_payment_choice?: bool, payment_choice?: array}
     */
    public function submit(User $user, string $type, array $input): array
    {
        $type = $this->normalizeType($type);
        $quote = $this->quote($user, $type);

        if ($type === CareerServiceRequest::TYPE_RESUME) {
            if (empty($input['resume']) || ! ($input['resume'] instanceof UploadedFile)) {
                throw new InvalidArgumentException('Please upload your current resume (PDF or DOC).');
            }
        }

        if ($type === CareerServiceRequest::TYPE_LINKEDIN) {
            $url = trim((string) ($input['linkedin_url'] ?? ''));
            if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
                throw new InvalidArgumentException('Please provide a valid LinkedIn profile URL.');
            }
        }

        $open = CareerServiceRequest::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->whereIn('status', [
                CareerServiceRequest::STATUS_PENDING_PAYMENT,
                CareerServiceRequest::STATUS_SUBMITTED,
            ])
            ->exists();

        if ($open) {
            throw new InvalidArgumentException('You already have an open '.$this->typeLabel($type).' request. Wait for it to finish before submitting again.');
        }

        $resumePath = null;
        if (! empty($input['resume']) && $input['resume'] instanceof UploadedFile) {
            $resumePath = PublicFileStorage::store($input['resume'], 'career-services/resumes/'.$user->id);
        }

        $isFree = (bool) $quote['is_free'];
        $amount = (float) $quote['amount'];

        if ($isFree) {
            $request = CareerServiceRequest::create([
                'user_id'           => $user->id,
                'type'              => $type,
                'status'            => CareerServiceRequest::STATUS_SUBMITTED,
                'linkedin_url'      => $type === CareerServiceRequest::TYPE_LINKEDIN
                    ? trim((string) ($input['linkedin_url'] ?? ''))
                    : null,
                'resume_path'       => $resumePath,
                'mentee_notes'      => trim((string) ($input['mentee_notes'] ?? '')) ?: null,
                'is_paid_addon'     => false,
                'amount'            => 0,
                'currency'          => $quote['currency'],
                'payment_status'    => 'free',
                'payment_method'    => 'plan',
                'wallet_amount'     => 0,
                'razorpay_amount'   => 0,
                'payment_reference' => null,
                'plan_slug'         => $quote['plan_slug'],
            ]);

            app(CareerServiceInvoiceService::class)->ensureForRequest($request->fresh(), 'system');

            return [
                'request' => $request->fresh(['invoice']),
                'payment' => null,
                'requires_payment_choice' => false,
            ];
        }

        $paymentMethod = isset($input['payment_method'])
            ? strtolower(trim((string) $input['payment_method']))
            : null;

        $request = CareerServiceRequest::create([
            'user_id'           => $user->id,
            'type'              => $type,
            'status'            => CareerServiceRequest::STATUS_PENDING_PAYMENT,
            'linkedin_url'      => $type === CareerServiceRequest::TYPE_LINKEDIN
                ? trim((string) ($input['linkedin_url'] ?? ''))
                : null,
            'resume_path'       => $resumePath,
            'mentee_notes'      => trim((string) ($input['mentee_notes'] ?? '')) ?: null,
            'is_paid_addon'     => true,
            'amount'            => $amount,
            'currency'          => $quote['currency'],
            'payment_status'    => 'pending',
            'payment_method'    => null,
            'wallet_amount'     => 0,
            'razorpay_amount'   => 0,
            'plan_slug'         => $quote['plan_slug'],
        ]);

        if (! $paymentMethod) {
            $choice = $this->paymentChoicePayload($amount, round((float) $user->wallet_balance, 2));

            return [
                'request' => $request->fresh(['invoice']),
                'payment' => null,
                'requires_payment_choice' => true,
                'payment_choice' => $choice,
            ];
        }

        return $this->applyPaymentMethod($user, $request, $paymentMethod);
    }

    /**
     * Continue payment for an existing pending request (wallet / razorpay / hybrid).
     *
     * @return array{request: CareerServiceRequest, payment: array|null, requires_payment_choice?: bool, payment_choice?: array}
     */
    public function pay(User $user, CareerServiceRequest $request, ?string $paymentMethod): array
    {
        if ((int) $request->user_id !== (int) $user->id) {
            throw new InvalidArgumentException('Request not found.');
        }

        if ($request->status === CareerServiceRequest::STATUS_SUBMITTED && in_array($request->payment_status, ['paid', 'free'], true)) {
            app(CareerServiceInvoiceService::class)->ensureForRequest($request, 'system');

            return [
                'request' => $request->fresh(['invoice']),
                'payment' => null,
                'requires_payment_choice' => false,
            ];
        }

        if ($request->status !== CareerServiceRequest::STATUS_PENDING_PAYMENT) {
            throw new InvalidArgumentException('This request is not awaiting payment.');
        }

        $amount = round((float) $request->amount, 2);
        $method = $paymentMethod ? strtolower(trim($paymentMethod)) : null;

        if (! $method) {
            return [
                'request' => $request->fresh(['invoice']),
                'payment' => null,
                'requires_payment_choice' => true,
                'payment_choice' => $this->paymentChoicePayload($amount, round((float) $user->wallet_balance, 2)),
            ];
        }

        return $this->applyPaymentMethod($user, $request, $method);
    }

    /**
     * @return array{request: CareerServiceRequest, payment: array|null, requires_payment_choice: bool}
     */
    private function applyPaymentMethod(User $user, CareerServiceRequest $request, string $paymentMethod): array
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

                $ref = 'WAL-CS-'.$request->id;
                $request->update([
                    'status'              => CareerServiceRequest::STATUS_SUBMITTED,
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
                    $request->typeLabel().' career service',
                    [
                        'reference'            => $ref,
                        'transactionable_type' => CareerServiceRequest::class,
                        'transactionable_id'   => $request->id,
                        'meta'                 => [
                            'source'     => 'career_service_wallet',
                            'request_id' => $request->id,
                            'type'       => $request->type,
                        ],
                    ]
                );

                return $request->fresh(['invoice']);
            });

            app(CareerServiceInvoiceService::class)->ensureForRequest($request, 'system');

            return [
                'request' => $request->fresh(['invoice']),
                'payment' => null,
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
            'status'          => CareerServiceRequest::STATUS_PENDING_PAYMENT,
        ]);

        $payment = $this->createRazorpayOrder($user, $request->fresh(), $razorPart);

        return [
            'request' => $request->fresh(['invoice']),
            'payment' => $payment,
            'requires_payment_choice' => false,
        ];
    }

    public function verifyPayment(User $user, CareerServiceRequest $request, array $payload): CareerServiceRequest
    {
        if ((int) $request->user_id !== (int) $user->id) {
            throw new InvalidArgumentException('Request not found.');
        }

        if ($request->status === CareerServiceRequest::STATUS_SUBMITTED && $request->payment_status === 'paid') {
            app(CareerServiceInvoiceService::class)->ensureForRequest($request, 'system');

            return $request->fresh(['invoice']);
        }

        if ($request->status !== CareerServiceRequest::STATUS_PENDING_PAYMENT) {
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

                $ref = 'WAL-CS-'.$request->id;
                $fresh->debitWallet(
                    $walletPart,
                    $request->typeLabel().' career service (wallet part)',
                    [
                        'reference'            => $ref,
                        'transactionable_type' => CareerServiceRequest::class,
                        'transactionable_id'   => $request->id,
                        'meta'                 => [
                            'source'     => 'career_service_hybrid',
                            'request_id' => $request->id,
                            'type'       => $request->type,
                        ],
                    ]
                );
            }

            $request->update([
                'status'              => CareerServiceRequest::STATUS_SUBMITTED,
                'payment_status'      => 'paid',
                'payment_method'      => in_array($method, ['wallet', 'razorpay', 'hybrid'], true) ? $method : 'razorpay',
                'wallet_amount'       => $method === 'hybrid' ? $walletPart : ($method === 'wallet' ? $walletPart : 0),
                'razorpay_amount'     => $razorPart,
                'razorpay_order_id'   => $orderId,
                'razorpay_payment_id' => $paymentId,
                'payment_reference'   => $paymentId,
            ]);

            return $request->fresh(['invoice']);
        });

        app(CareerServiceInvoiceService::class)->ensureForRequest($request, 'system');

        return $request->fresh(['invoice']);
    }

    /**
     * @param  array{admin_notes?: string|null, deliverable: UploadedFile}  $input
     */
    public function complete(CareerServiceRequest $request, User $admin, array $input): CareerServiceRequest
    {
        if ($request->status !== CareerServiceRequest::STATUS_SUBMITTED) {
            throw new InvalidArgumentException('Only submitted requests can be completed.');
        }

        if (empty($input['deliverable']) || ! ($input['deliverable'] instanceof UploadedFile)) {
            throw new InvalidArgumentException(
                $request->type === CareerServiceRequest::TYPE_LINKEDIN
                    ? 'Upload the LinkedIn optimisation document for the mentee.'
                    : 'Upload the updated resume for the mentee.'
            );
        }

        $folder = $request->type === CareerServiceRequest::TYPE_LINKEDIN
            ? 'career-services/linkedin/'.$request->user_id
            : 'career-services/deliverables/'.$request->user_id;

        if ($request->deliverable_path) {
            PublicFileStorage::deleteByUrl($request->deliverable_path);
        }

        $path = PublicFileStorage::store($input['deliverable'], $folder);

        $request->update([
            'status'           => CareerServiceRequest::STATUS_COMPLETED,
            'admin_notes'      => trim((string) ($input['admin_notes'] ?? '')) ?: null,
            'deliverable_path' => $path,
            'reviewed_by'      => $admin->id,
            'reviewed_at'      => now(),
            'completed_at'     => now(),
        ]);

        return $request->fresh(['user', 'reviewer']);
    }

    private function createRazorpayOrder(User $user, CareerServiceRequest $request, float $razorAmount): array
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
                'receipt'  => Str::limit('csr_'.$request->id.'_'.$user->id, 40, ''),
                'notes'    => [
                    'user_id'         => (string) $user->id,
                    'request_id'      => (string) $request->id,
                    'type'            => $request->type,
                    'purpose'         => 'career_service_addon',
                    'payment_method'  => (string) $request->payment_method,
                    'wallet_amount'   => (string) $request->wallet_amount,
                    'razorpay_amount' => (string) $razorAmount,
                ],
            ]);

        if (! $response->successful()) {
            Log::error('Career service Razorpay order failed.', [
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
            'description'     => $request->typeLabel().($method === 'hybrid' ? ' (online part)' : ' addon'),
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

    private function normalizeType(string $type): string
    {
        $type = strtolower(trim($type));
        if (! in_array($type, [CareerServiceRequest::TYPE_RESUME, CareerServiceRequest::TYPE_LINKEDIN], true)) {
            throw new InvalidArgumentException('Type must be resume or linkedin.');
        }

        return $type;
    }

    private function typeLabel(string $type): string
    {
        return $type === CareerServiceRequest::TYPE_LINKEDIN ? 'LinkedIn optimisation' : 'Resume development';
    }

    private function entitlementLabel(
        ?string $planSlug,
        string $type,
        bool $included,
        int $remaining,
        ?int $months,
        ?Carbon $nextFreeAt = null,
    ): string {
        $label = $this->typeLabel($type);
        if (! $included) {
            return $label.' is a paid add-on on your current plan.';
        }
        if ($remaining > 0) {
            return "Included in your plan: 1 {$label} every {$months} months (available now).";
        }
        if ($nextFreeAt) {
            return "You've used your free {$label}. Next free benefit on "
                .$nextFreeAt->timezone('Asia/Kolkata')->format('d M Y')
                .'. Extra requests require payment.';
        }

        return "Your free {$label} entitlement for this {$months}-month window is used. Extra requests require payment.";
    }
}
