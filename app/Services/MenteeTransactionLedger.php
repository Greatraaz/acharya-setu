<?php

namespace App\Services;

use App\Models\CareerServiceRequest;
use App\Models\ConsultationSession;
use App\Models\MockInterviewRequest;
use App\Models\PlanInvoice;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class MenteeTransactionLedger
{
    public function paginate(User $mentee, int $perPage = 12): LengthAwarePaginator
    {
        $rows = $this->collect($mentee);
        $page = Paginator::resolveCurrentPage('txn_page');
        $slice = $rows->slice(max(0, ($page - 1) * $perPage), $perPage)->values();

        return new Paginator(
            $slice,
            $rows->count(),
            $perPage,
            $page,
            [
                'path'     => request()->url(),
                'pageName' => 'txn_page',
                'query'    => request()->query(),
            ]
        );
    }

    /**
     * @return Collection<int, object>
     */
    public function collect(User $mentee): Collection
    {
        return $this->walletRows($mentee)
            ->concat($this->sessionRazorpayRows($mentee))
            ->concat($this->planRows($mentee))
            ->concat($this->careerRows($mentee))
            ->concat($this->mockRows($mentee))
            ->sortByDesc(fn ($row) => $row->at?->getTimestamp() ?? 0)
            ->values();
    }

    private function walletRows(User $mentee): Collection
    {
        if (! Schema::hasTable('wallet_transactions')) {
            return collect();
        }

        return WalletTransaction::query()
            ->where('user_id', $mentee->id)
            ->with(['transactionable' => function ($morphTo) {
                $morphTo->morphWith([
                    ConsultationSession::class => ['mentor:id,name'],
                ]);
            }])
            ->latest('id')
            ->get()
            ->map(function (WalletTransaction $txn) {
                $incoming = in_array($txn->type, ['credit', 'refund', 'transfer_in'], true);
                $meta = is_array($txn->meta) ? $txn->meta : [];
                $session = $txn->transactionable instanceof ConsultationSession ? $txn->transactionable : null;
                $category = match ($txn->category) {
                    'session' => 'Session',
                    'plan' => 'Plan',
                    'topup' => 'Wallet',
                    'bonus' => 'Wallet',
                    default => 'Wallet',
                };
                $method = $txn->category === 'topup' ? 'Razorpay' : 'Wallet';
                $title = $txn->description ?: $txn->type_label;
                if ($session?->title) {
                    $title = $session->title;
                } elseif ($txn->category === 'topup') {
                    $title = 'Wallet top-up';
                }

                return $this->row(
                    $txn->created_at,
                    $category,
                    $title,
                    $session?->mentor?->name ? 'with '.$session->mentor->name : ($txn->type_label),
                    $meta['booking_ref'] ?? $session?->booking_ref ?? $txn->reference,
                    $method,
                    $incoming ? 'in' : 'out',
                    (float) $txn->amount,
                    ucfirst((string) ($txn->status ?: 'completed')),
                    $session ? route('mentee.sessions.show', $session->id) : route('mentee.wallet')
                );
            });
    }

    private function sessionRazorpayRows(User $mentee): Collection
    {
        if (! Schema::hasTable('consultation_sessions')) {
            return collect();
        }

        return ConsultationSession::query()
            ->where('mentee_id', $mentee->id)
            ->whereIn('payment_status', ['paid', 'refunded', 'partially_refunded'])
            ->where(function ($query) {
                $query->where('razorpay_amount', '>', 0)
                    ->orWhere(function ($razorpay) {
                        $razorpay->where('payment_method', 'razorpay')->where('amount', '>', 0);
                    });
            })
            ->with(['mentor:id,name', 'sessionInvoice'])
            ->latest('id')
            ->get()
            ->map(function (ConsultationSession $session) {
                $amount = (float) ($session->razorpay_amount ?? 0);
                if ($amount <= 0) {
                    $amount = (float) $session->amount;
                }

                return $this->row(
                    $session->created_at,
                    'Session',
                    $session->title ?: 'Session booking',
                    'with '.($session->mentor?->name ?: 'mentor'),
                    $session->razorpay_payment_id ?: $session->booking_ref,
                    'Razorpay',
                    'out',
                    $amount,
                    $this->paymentStatusLabel($session->payment_status),
                    route('mentee.sessions.show', $session->id)
                );
            });
    }

    private function planRows(User $mentee): Collection
    {
        $rows = collect();

        if (Schema::hasTable('plan_invoices')) {
            $invoices = PlanInvoice::query()
                ->where('user_id', $mentee->id)
                ->latest('id')
                ->get();

            foreach ($invoices as $invoice) {
                $amount = (float) $invoice->total_amount;
                if ($amount <= 0) {
                    continue;
                }
                $rows->push($this->row(
                    $invoice->created_at ?? $invoice->invoice_date,
                    'Plan',
                    $invoice->plan_name ?: 'Plan purchase',
                    $invoice->invoice_number,
                    $invoice->razorpay_payment_id ?: $invoice->invoice_number,
                    'Razorpay',
                    'out',
                    $amount,
                    $this->paymentStatusLabel($invoice->status === 'refunded' ? 'refunded' : 'paid'),
                    route('mentee.invoices.download', $invoice)
                ));
            }

            $covered = $invoices->pluck('user_subscription_id')->filter()->all();
        } else {
            $covered = [];
        }

        if (! Schema::hasTable('user_subscriptions')) {
            return $rows;
        }

        $subscriptions = UserSubscription::query()
            ->where('user_id', $mentee->id)
            ->where('payment_status', 'paid')
            ->when($covered !== [], fn ($query) => $query->whereNotIn('id', $covered))
            ->with('plan:id,name')
            ->latest('id')
            ->get();

        foreach ($subscriptions as $subscription) {
            $amount = (float) $subscription->amount_paid;
            if ($amount <= 0) {
                continue;
            }
            $rows->push($this->row(
                $subscription->created_at,
                'Plan',
                $subscription->plan?->name ?: 'Plan purchase',
                $subscription->subscription_id,
                $subscription->razorpay_payment_id ?: $subscription->payment_reference,
                'Razorpay',
                'out',
                $amount,
                $this->paymentStatusLabel($subscription->payment_status),
                route('mentee.plans')
            ));
        }

        return $rows;
    }

    private function careerRows(User $mentee): Collection
    {
        if (! Schema::hasTable('career_service_requests')) {
            return collect();
        }

        return CareerServiceRequest::query()
            ->where('user_id', $mentee->id)
            ->where('payment_status', 'paid')
            ->where('razorpay_amount', '>', 0)
            ->latest('id')
            ->get()
            ->map(function (CareerServiceRequest $request) {
                $label = $request->type === CareerServiceRequest::TYPE_LINKEDIN
                    ? 'LinkedIn review'
                    : 'Resume review';

                return $this->row(
                    $request->created_at,
                    'Career service',
                    $label,
                    'Paid online',
                    $request->razorpay_payment_id ?: $request->payment_reference,
                    'Razorpay',
                    'out',
                    (float) $request->razorpay_amount,
                    'Paid',
                    route('mentee.career-services.show', $request)
                );
            });
    }

    private function mockRows(User $mentee): Collection
    {
        if (! Schema::hasTable('mock_interview_requests')) {
            return collect();
        }

        return MockInterviewRequest::query()
            ->where('user_id', $mentee->id)
            ->where('payment_status', 'paid')
            ->where('razorpay_amount', '>', 0)
            ->latest('id')
            ->get()
            ->map(function (MockInterviewRequest $request) {
                return $this->row(
                    $request->created_at,
                    'Mock interview',
                    'Mock interview',
                    ($request->duration_minutes ?: 30).' min',
                    $request->razorpay_payment_id ?: $request->payment_reference,
                    'Razorpay',
                    'out',
                    (float) $request->razorpay_amount,
                    'Paid',
                    route('mentee.mock-interviews.show', $request)
                );
            });
    }

    private function paymentStatusLabel(?string $status): string
    {
        return match ($status) {
            'paid', 'issued' => 'Paid',
            'refunded' => 'Refunded',
            'partially_refunded' => 'Partially refunded',
            'failed' => 'Failed',
            'pending' => 'Pending',
            default => $status ? ucfirst(str_replace('_', ' ', $status)) : 'Completed',
        };
    }

    private function row(
        mixed $at,
        string $category,
        string $title,
        ?string $detail,
        ?string $reference,
        string $method,
        string $direction,
        float $amount,
        string $status,
        ?string $url
    ): object {
        return (object) [
            'at'        => $at instanceof Carbon ? $at : ($at ? Carbon::parse($at) : null),
            'category'  => $category,
            'title'     => $title,
            'detail'    => $detail,
            'reference' => $reference ?: '—',
            'method'    => $method,
            'direction' => $direction,
            'amount'    => round($amount, 2),
            'status'    => $status,
            'url'       => $url,
        ];
    }
}
