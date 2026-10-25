<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Plan;
use App\Models\SubscriptionCancelRequest;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SubscriptionCancelService
{
    public const TOTAL_MONTHS = 12;
    public const AUTO_APPROVE_HOURS = 72;

    public function quote(UserSubscription $subscription, ?Carbon $at = null): array
    {
        $subscription->loadMissing('plan');

        if (! $this->isPaidActive($subscription)) {
            throw new InvalidArgumentException('Active paid subscription not found.');
        }

        if (! $this->isYearly($subscription)) {
            throw new InvalidArgumentException('Only yearly subscriptions can be cancelled. Monthly plans cannot be cancelled.');
        }

        $tz = 'Asia/Kolkata';
        $at = ($at ?? now($tz))->copy()->timezone($tz);
        $starts = $subscription->starts_at?->copy()->timezone($tz) ?? $at->copy();
        $paid = round((float) $subscription->amount_paid, 2);
        $consumedMonths = $this->consumedMonths($starts, $at);
        $refundMonths = max(0, self::TOTAL_MONTHS - $consumedMonths);
        $monthlyAmount = $paid > 0 ? round($paid / self::TOTAL_MONTHS, 2) : 0.0;
        $consumedAmount = $consumedMonths >= self::TOTAL_MONTHS
            ? $paid
            : round($monthlyAmount * $consumedMonths, 2);
        $refundAmount = round(max(0, $paid - $consumedAmount), 2);
        $destination = $this->refundDestination($subscription);

        return [
            'can_cancel'          => true,
            'billing'             => 'yearly',
            'subscription_id'     => $subscription->subscription_id,
            'plan_name'           => $subscription->plan?->name,
            'starts_at'           => $starts->toDateTimeString(),
            'expires_at'          => $subscription->expires_at?->timezone($tz)->toDateTimeString(),
            'quoted_at'           => $at->toDateTimeString(),
            'total_months'        => self::TOTAL_MONTHS,
            'consumed_months'     => $consumedMonths,
            'refund_months'       => $refundMonths,
            'paid_amount'         => $paid,
            'monthly_amount'      => $monthlyAmount,
            'consumed_amount'     => $consumedAmount,
            'refund_amount'       => $refundAmount,
            'currency'            => $subscription->currency ?: 'INR',
            'refund_destination'  => $destination,
            'summary'             => $this->summary($consumedMonths, $refundMonths, $consumedAmount, $refundAmount, $destination),
        ];
    }

    public function previewForUser(User $user): array
    {
        $this->autoApproveDue();

        $subscription = $user->activeSubscription();
        $pending = $subscription
            ? $this->pendingRequest($subscription)
            : $this->pendingForUser($user);

        if ($pending) {
            $pending->loadMissing(['subscription.plan']);
        }

        if (! $subscription) {
            return [
                'can_cancel' => false,
                'reason'     => 'No active subscription.',
                'quote'      => null,
                'request'    => $pending ? $this->formatRequest($pending) : null,
            ];
        }

        if (! $this->isYearly($subscription)) {
            return [
                'can_cancel' => false,
                'reason'     => 'Only yearly subscriptions can be cancelled.',
                'quote'      => null,
                'request'    => $pending ? $this->formatRequest($pending) : null,
            ];
        }

        if ($pending) {
            return [
                'can_cancel' => false,
                'reason'     => 'A cancellation request is already pending admin approval.',
                'quote'      => $this->quoteFromRequest($pending),
                'request'    => $this->formatRequest($pending),
            ];
        }

        $quote = $this->quote($subscription);

        return [
            'can_cancel' => true,
            'reason'     => null,
            'quote'      => $quote,
            'request'    => null,
        ];
    }

    public function requestCancel(User $user, string $subscriptionId, ?string $reason = null): SubscriptionCancelRequest
    {
        return DB::transaction(function () use ($user, $subscriptionId, $reason) {
            $subscription = UserSubscription::query()
                ->where('user_id', $user->id)
                ->where('subscription_id', $subscriptionId)
                ->lockForUpdate()
                ->first();

            if (! $subscription) {
                throw new InvalidArgumentException('Active subscription not found.');
            }

            if ($this->pendingRequest($subscription)) {
                throw new InvalidArgumentException('A cancellation request is already pending for this plan.');
            }

            $quote = $this->quote($subscription);
            $now = now('Asia/Kolkata');

            $request = SubscriptionCancelRequest::create([
                'user_id'              => $user->id,
                'user_subscription_id' => $subscription->id,
                'status'               => SubscriptionCancelRequest::STATUS_PENDING,
                'reason'               => $reason ? mb_substr(trim($reason), 0, 1000) : null,
                'requested_at'         => $now,
                'auto_approve_at'      => $now->copy()->addHours(self::AUTO_APPROVE_HOURS),
                'consumed_months'      => $quote['consumed_months'],
                'refund_months'        => $quote['refund_months'],
                'paid_amount'          => $quote['paid_amount'],
                'monthly_amount'       => $quote['monthly_amount'],
                'consumed_amount'      => $quote['consumed_amount'],
                'refund_amount'        => $quote['refund_amount'],
                'refund_destination'   => $quote['refund_destination'],
                'snapshot'             => $quote,
            ]);

            $this->notifyAdmins($request, $user, $quote);
            $this->notifyMentee(
                $user,
                'subscription_cancel_requested',
                'Cancellation request submitted',
                'Your yearly plan cancellation is waiting for admin approval.'
            );

            ActivityLogger::record(
                'subscription_cancel_requested',
                $user->name.' requested cancellation of yearly subscription '.$subscription->subscription_id,
                'plans',
                'info'
            );

            return $request->fresh(['subscription.plan', 'user']);
        });
    }

    public function approve(SubscriptionCancelRequest $request, ?User $admin = null, bool $auto = false): SubscriptionCancelRequest
    {
        return DB::transaction(function () use ($request, $admin, $auto) {
            $locked = SubscriptionCancelRequest::query()
                ->where('id', $request->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->isPending()) {
                throw new InvalidArgumentException('This cancellation request is no longer pending.');
            }

            $subscription = UserSubscription::query()
                ->where('id', $locked->user_subscription_id)
                ->lockForUpdate()
                ->firstOrFail();

            $refund = $this->payoutRefund($locked, $subscription, $admin);

            $now = now('Asia/Kolkata');
            $locked->update([
                'status'            => SubscriptionCancelRequest::STATUS_APPROVED,
                'reviewed_at'       => $now,
                'reviewed_by'       => $auto ? null : $admin?->id,
                'approved_by'       => $auto
                    ? SubscriptionCancelRequest::APPROVED_BY_AUTO
                    : SubscriptionCancelRequest::APPROVED_BY_ADMIN,
                'razorpay_refunded' => $refund['razorpay_refunded'],
                'wallet_refunded'   => $refund['wallet_refunded'],
                'razorpay_refund_id'=> $refund['razorpay_refund_id'],
                'processed_at'      => $now,
                'snapshot'          => array_merge($locked->snapshot ?? [], [
                    'refund_result' => $refund,
                ]),
            ]);

            $subscription->update([
                'status'     => 'cancelled',
                'expires_at' => $now,
            ]);

            $mentee = $locked->user ?: User::query()->find($locked->user_id);
            if ($mentee) {
                $amount = number_format((float) $locked->refund_amount, 2);
                $via = $refund['wallet_refunded'] > 0 && $refund['razorpay_refunded'] <= 0
                    ? 'your wallet'
                    : 'your original payment method';
                $this->notifyMentee(
                    $mentee,
                    'subscription_cancel_approved',
                    'Yearly plan cancelled',
                    'Your yearly subscription is cancelled. ₹'.$amount.' was refunded to '.$via.'.'
                );
            }

            $actor = $auto ? 'Auto-approval' : ($admin?->name ?? 'Admin');
            ActivityLogger::record(
                'subscription_cancel_approved',
                $actor.' approved cancellation of '.$subscription->subscription_id.' and refunded ₹'.number_format((float) $locked->refund_amount, 2),
                'plans',
                'success'
            );

            return $locked->fresh(['subscription.plan', 'user']);
        });
    }

    public function reject(SubscriptionCancelRequest $request, User $admin, ?string $note = null): SubscriptionCancelRequest
    {
        return DB::transaction(function () use ($request, $admin, $note) {
            $locked = SubscriptionCancelRequest::query()
                ->where('id', $request->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->isPending()) {
                throw new InvalidArgumentException('This cancellation request is no longer pending.');
            }

            $locked->update([
                'status'      => SubscriptionCancelRequest::STATUS_REJECTED,
                'admin_note'  => $note ? mb_substr(trim($note), 0, 1000) : null,
                'reviewed_at' => now('Asia/Kolkata'),
                'reviewed_by' => $admin->id,
            ]);

            $mentee = $locked->user ?: User::query()->find($locked->user_id);
            if ($mentee) {
                $this->notifyMentee(
                    $mentee,
                    'subscription_cancel_rejected',
                    'Cancellation request declined',
                    'Your yearly plan cancellation was not approved. Your subscription is still active.'
                );
            }

            ActivityLogger::record(
                'subscription_cancel_rejected',
                $admin->name.' rejected cancellation of subscription #'.$locked->user_subscription_id,
                'plans',
                'warning'
            );

            return $locked->fresh(['subscription.plan', 'user']);
        });
    }

    public function autoApproveDue(): int
    {
        $ids = SubscriptionCancelRequest::query()
            ->where('status', SubscriptionCancelRequest::STATUS_PENDING)
            ->where('auto_approve_at', '<=', now())
            ->pluck('id');

        $approved = 0;
        foreach ($ids as $id) {
            $request = SubscriptionCancelRequest::query()->find($id);
            if (! $request || ! $request->isPending()) {
                continue;
            }

            try {
                $this->approve($request, null, true);
                $approved++;
            } catch (\Throwable $e) {
                Log::error('Auto-approve subscription cancel failed.', [
                    'request_id' => $id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        return $approved;
    }

    public function formatRequest(SubscriptionCancelRequest $request, bool $forAdmin = false): array
    {
        $data = [
            'id'                 => (int) $request->id,
            'status'             => $request->status,
            'reason'             => $request->reason,
            'admin_note'         => $request->admin_note,
            'requested_at'       => $request->requested_at?->toDateTimeString(),
            'reviewed_at'        => $request->reviewed_at?->toDateTimeString(),
            'approved_by'        => $request->approved_by,
            'consumed_months'    => (int) $request->consumed_months,
            'refund_months'      => (int) $request->refund_months,
            'paid_amount'        => (float) $request->paid_amount,
            'monthly_amount'     => (float) $request->monthly_amount,
            'consumed_amount'    => (float) $request->consumed_amount,
            'refund_amount'      => (float) $request->refund_amount,
            'currency'           => $request->subscription?->currency ?: 'INR',
            'refund_destination' => $request->refund_destination,
            'razorpay_refunded'  => (float) $request->razorpay_refunded,
            'wallet_refunded'    => (float) $request->wallet_refunded,
            'summary'            => $this->summary(
                (int) $request->consumed_months,
                (int) $request->refund_months,
                (float) $request->consumed_amount,
                (float) $request->refund_amount,
                (string) $request->refund_destination
            ),
        ];

        if ($forAdmin) {
            $data['auto_approve_at'] = $request->auto_approve_at?->toDateTimeString();
            $data['hours_until_auto_approve'] = $request->hoursUntilAutoApprove();
        }

        return $data;
    }

    public function isYearly(UserSubscription $subscription): bool
    {
        $meta = is_array($subscription->meta) ? $subscription->meta : [];
        $billing = Plan::normalizeBilling(
            data_get($meta, 'checkout.billing') ?? data_get($meta, 'billing')
        );

        if ($billing === 'yearly') {
            return true;
        }

        if ($subscription->starts_at && $subscription->expires_at) {
            return $subscription->starts_at->diffInDays($subscription->expires_at) >= 300;
        }

        return false;
    }

    public function pendingRequest(UserSubscription $subscription): ?SubscriptionCancelRequest
    {
        return SubscriptionCancelRequest::query()
            ->where('user_subscription_id', $subscription->id)
            ->where('status', SubscriptionCancelRequest::STATUS_PENDING)
            ->latest('id')
            ->first();
    }

    public function pendingForUser(User $user): ?SubscriptionCancelRequest
    {
        return SubscriptionCancelRequest::query()
            ->where('user_id', $user->id)
            ->where('status', SubscriptionCancelRequest::STATUS_PENDING)
            ->latest('id')
            ->first();
    }

    private function isPaidActive(UserSubscription $subscription): bool
    {
        return $subscription->status === 'active'
            && $subscription->payment_status === 'paid'
            && $subscription->expires_at
            && $subscription->expires_at->gt(now());
    }

    private function consumedMonths(Carbon $starts, Carbon $at): int
    {
        $startMonth = $starts->copy()->startOfMonth();
        $nowMonth = $at->copy()->startOfMonth();

        if ($nowMonth->lt($startMonth)) {
            return 1;
        }

        $consumed = (($nowMonth->year - $startMonth->year) * 12) + ($nowMonth->month - $startMonth->month) + 1;

        return max(1, min(self::TOTAL_MONTHS, $consumed));
    }

    private function refundDestination(UserSubscription $subscription): string
    {
        return filled($subscription->razorpay_payment_id) ? 'razorpay' : 'wallet';
    }

    private function quoteFromRequest(SubscriptionCancelRequest $request): array
    {
        $snapshot = is_array($request->snapshot) ? $request->snapshot : [];
        unset($snapshot['auto_approve_hours'], $snapshot['auto_approve_at']);

        return array_merge($snapshot, [
            'consumed_months'    => (int) $request->consumed_months,
            'refund_months'      => (int) $request->refund_months,
            'paid_amount'        => (float) $request->paid_amount,
            'monthly_amount'     => (float) $request->monthly_amount,
            'consumed_amount'    => (float) $request->consumed_amount,
            'refund_amount'      => (float) $request->refund_amount,
            'refund_destination' => $request->refund_destination,
        ]);
    }

    /**
     * @return array{razorpay_refunded: float, wallet_refunded: float, razorpay_refund_id: ?string, razorpay_ok: bool}
     */
    private function payoutRefund(
        SubscriptionCancelRequest $request,
        UserSubscription $subscription,
        ?User $admin
    ): array {
        $amount = round((float) $request->refund_amount, 2);
        $destination = $request->refund_destination ?: $this->refundDestination($subscription);

        $result = [
            'razorpay_refunded'  => 0.0,
            'wallet_refunded'    => 0.0,
            'razorpay_refund_id' => null,
            'razorpay_ok'        => true,
        ];

        if ($amount <= 0) {
            return $result;
        }

        $mentee = $subscription->user ?: User::query()->find($subscription->user_id);
        if (! $mentee) {
            throw new InvalidArgumentException('Mentee not found for this subscription.');
        }

        if ($destination === 'razorpay') {
            $paymentId = (string) ($subscription->razorpay_payment_id ?: '');
            $refundId = $this->refundRazorpay($paymentId, $amount, [
                'reason'          => 'yearly_subscription_cancel',
                'subscription_id' => (string) $subscription->subscription_id,
                'request_id'      => (string) $request->id,
            ]);

            if ($refundId) {
                $result['razorpay_refunded'] = $amount;
                $result['razorpay_refund_id'] = $refundId;
                $result['razorpay_ok'] = true;

                return $result;
            }

            $result['razorpay_ok'] = false;
            Log::warning('Razorpay yearly-cancel refund failed; crediting wallet instead.', [
                'request_id'      => $request->id,
                'subscription_id' => $subscription->subscription_id,
            ]);
        }

        $mentee->refundWallet(
            $amount,
            'Yearly plan cancellation refund for '.$subscription->subscription_id,
            [
                'reference'            => 'REF-PLAN-'.$request->id,
                'transactionable_type' => SubscriptionCancelRequest::class,
                'transactionable_id'   => $request->id,
                'performed_by'         => $admin?->id,
                'meta'                 => [
                    'source'           => 'yearly_subscription_cancel',
                    'subscription_id'  => $subscription->subscription_id,
                    'consumed_months'  => $request->consumed_months,
                    'refund_months'    => $request->refund_months,
                    'fallback'         => $destination === 'razorpay',
                ],
            ]
        );
        $result['wallet_refunded'] = $amount;

        return $result;
    }

    /**
     * @param  array<string, string>  $notes
     */
    private function refundRazorpay(string $paymentId, float $amountRupees, array $notes = []): ?string
    {
        $paymentId = trim($paymentId);
        if ($paymentId === '' || $amountRupees <= 0) {
            return null;
        }

        $settings = AppSetting::razorpay();
        $key = (string) ($settings['key'] ?? '');
        $secret = (string) ($settings['secret'] ?? '');

        if ($key === '' || $secret === '') {
            Log::error('Cannot refund yearly plan — Razorpay not configured.', [
                'payment_id' => $paymentId,
                'amount'     => $amountRupees,
            ]);

            return null;
        }

        try {
            $response = Http::withBasicAuth($key, $secret)
                ->acceptJson()
                ->post('https://api.razorpay.com/v1/payments/'.$paymentId.'/refund', [
                    'amount' => (int) round($amountRupees * 100),
                    'notes'  => $notes,
                ]);

            if (! $response->successful()) {
                Log::error('Razorpay yearly-cancel refund failed.', [
                    'payment_id' => $paymentId,
                    'amount'     => $amountRupees,
                    'status'     => $response->status(),
                    'body'       => $response->body(),
                ]);

                return null;
            }

            return $response->json('id');
        } catch (\Throwable $e) {
            Log::error('Razorpay yearly-cancel refund exception: '.$e->getMessage(), [
                'payment_id' => $paymentId,
                'amount'     => $amountRupees,
            ]);

            return null;
        }
    }

    private function summary(
        int $consumedMonths,
        int $refundMonths,
        float $consumedAmount,
        float $refundAmount,
        string $destination
    ): string {
        $via = $destination === 'razorpay' ? 'original payment method' : 'wallet';

        return $consumedMonths.' month'.($consumedMonths === 1 ? '' : 's')
            .' already used (₹'.number_format($consumedAmount, 2).') will not be refunded. '
            .'₹'.number_format($refundAmount, 2).' for '.$refundMonths
            .' remaining month'.($refundMonths === 1 ? '' : 's')
            .' will be refunded to your '.$via.'.';
    }

    private function notifyAdmins(SubscriptionCancelRequest $request, User $mentee, array $quote): void
    {
        $admins = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->get();

        $notifications = app(NotificationService::class);
        $url = route('admin.subscription-cancels.index');

        foreach ($admins as $admin) {
            $notifications->send(
                $admin,
                'subscription_cancel_request',
                'Yearly plan cancellation request',
                $mentee->name.' requested a ₹'.number_format((float) $quote['refund_amount'], 2).' refund.',
                [
                    'request_id'      => $request->id,
                    'subscription_id' => $quote['subscription_id'] ?? null,
                    'mentee_id'       => $mentee->id,
                ],
                $url
            );
        }
    }

    private function notifyMentee(User $mentee, string $type, string $title, string $body): void
    {
        $notifications = app(NotificationService::class);
        $notifications->send(
            $mentee,
            $type,
            $title,
            $body,
            [],
            $notifications->roleUrl($mentee, 'plans')
        );
    }
}
