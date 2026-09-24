<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\CareerServiceRequest;
use App\Services\CareerServiceService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CareerServiceController extends Controller
{
    public function index(CareerServiceService $services)
    {
        $user = auth()->user();
        $requests = CareerServiceRequest::query()
            ->with('invoice')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('frontend.mentee.career-services.index', [
            'requests' => $requests,
            'prices'   => $services->prices(),
            'quotes'   => [
                'resume'   => $services->quote($user, 'resume'),
                'linkedin' => $services->quote($user, 'linkedin'),
            ],
        ]);
    }

    public function create(Request $request, CareerServiceService $services)
    {
        $type = in_array($request->query('type'), ['resume', 'linkedin'], true)
            ? $request->query('type')
            : 'resume';

        return view('frontend.mentee.career-services.create', [
            'type'  => $type,
            'quote' => $services->quote(auth()->user(), $type),
        ]);
    }

    public function store(Request $request, CareerServiceService $services)
    {
        $data = $request->validate([
            'type'            => 'required|in:resume,linkedin',
            'linkedin_url'    => 'nullable|url|max:500',
            'mentee_notes'    => 'nullable|string|max:2000',
            'resume'          => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'payment_method'  => 'nullable|in:wallet,razorpay,hybrid',
        ]);

        try {
            $result = $services->submit(auth()->user(), $data['type'], [
                'linkedin_url'   => $data['linkedin_url'] ?? null,
                'mentee_notes'   => $data['mentee_notes'] ?? null,
                'resume'         => $request->file('resume'),
                'payment_method' => $data['payment_method'] ?? null,
            ]);
        } catch (InvalidArgumentException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }

        return $this->paymentResponse($request, $result);
    }

    public function pay(Request $request, CareerServiceRequest $careerService, CareerServiceService $services)
    {
        abort_unless((int) $careerService->user_id === (int) auth()->id(), 404);

        $data = $request->validate([
            'payment_method' => 'nullable|in:wallet,razorpay,hybrid',
        ]);

        try {
            $result = $services->pay(auth()->user(), $careerService, $data['payment_method'] ?? null);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->paymentResponse($request, $result);
    }

    public function show(CareerServiceRequest $careerService)
    {
        abort_unless((int) $careerService->user_id === (int) auth()->id(), 404);

        $careerService->load('invoice');
        if (in_array($careerService->payment_status, ['paid', 'free'], true) && ! $careerService->invoice) {
            app(\App\Services\CareerServiceInvoiceService::class)->ensureForRequest($careerService, 'system');
            $careerService->load('invoice');
        }

        $paymentChoice = null;
        if ($careerService->status === CareerServiceRequest::STATUS_PENDING_PAYMENT) {
            $paymentChoice = app(CareerServiceService::class)->quote(auth()->user(), $careerService->type)['payment']
                ?? null;
            if ($paymentChoice) {
                $paymentChoice['amount'] = (float) $careerService->amount;
                $paymentChoice['shortfall'] = round(max(0, (float) $careerService->amount - (float) auth()->user()->wallet_balance), 2);
                $paymentChoice['wallet_balance'] = round((float) auth()->user()->wallet_balance, 2);
                $paymentChoice['can_pay_full_wallet'] = (float) auth()->user()->wallet_balance >= (float) $careerService->amount;
                $opts = ['wallet', 'razorpay'];
                if ($paymentChoice['wallet_balance'] > 0 && $paymentChoice['wallet_balance'] < (float) $careerService->amount) {
                    $opts[] = 'hybrid';
                }
                $paymentChoice['payment_options'] = $opts;
            }
        }

        return view('frontend.mentee.career-services.show', [
            'item' => $careerService,
            'paymentChoice' => $paymentChoice,
        ]);
    }

    public function verify(Request $request, CareerServiceRequest $careerService, CareerServiceService $services)
    {
        abort_unless((int) $careerService->user_id === (int) auth()->id(), 404);

        $payload = $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        try {
            $item = $services->verifyPayment(auth()->user(), $careerService, $payload);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Payment successful. Your request is now under review.',
            'data'    => $item->toPublicArray(),
        ]);
    }

    private function paymentResponse(Request $request, array $result)
    {
        $item = $result['request'];
        $payment = $result['payment'] ?? null;
        $needsChoice = (bool) ($result['requires_payment_choice'] ?? false);
        $choice = $result['payment_choice'] ?? null;

        if ($request->expectsJson() || $request->ajax()) {
            $message = 'Request submitted. Our team will review it shortly.';
            if ($needsChoice) {
                $message = 'Choose a payment method to continue.';
            } elseif ($payment) {
                $message = 'Complete payment to submit your request.';
            }

            return response()->json([
                'message' => $message,
                'requires_payment' => (bool) $payment,
                'requires_payment_choice' => $needsChoice,
                'data' => [
                    'request' => $item?->toPublicArray(),
                    'payment' => $payment,
                    'payment_choice' => $choice,
                ],
            ], ($payment || $needsChoice) ? 201 : 200);
        }

        if ($needsChoice) {
            return redirect()
                ->route('mentee.career-services.show', $item)
                ->with('success', 'Choose how you want to pay to submit this request.');
        }

        if ($payment) {
            return redirect()
                ->route('mentee.career-services.show', $item)
                ->with('pay', $payment);
        }

        return redirect()
            ->route('mentee.career-services.show', $item)
            ->with('success', 'Request submitted. Our team will review it shortly.');
    }
}
