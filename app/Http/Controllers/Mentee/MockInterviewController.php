<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\MockInterviewRequest;
use App\Services\MockInterviewService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MockInterviewController extends Controller
{
    public function index(MockInterviewService $services)
    {
        $user = auth()->user();
        $requests = MockInterviewRequest::query()
            ->with('mentor')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('frontend.mentee.mock-interviews.index', [
            'requests' => $requests,
            'prices'   => $services->prices(),
            'quote'    => $services->quote($user, 60),
        ]);
    }

    public function create(Request $request, MockInterviewService $services)
    {
        $duration = in_array((int) $request->query('duration'), MockInterviewRequest::DURATIONS, true)
            ? (int) $request->query('duration')
            : 60;

        return view('frontend.mentee.mock-interviews.create', [
            'duration' => $duration,
            'quote'    => $services->quote(auth()->user(), $duration),
        ]);
    }

    public function store(Request $request, MockInterviewService $services)
    {
        $data = $request->validate([
            'duration_minutes' => 'required|in:30,45,60,90',
            'preferred_at'     => 'required|date',
            'timezone'         => 'nullable|string|max:64',
            'target_role'      => 'nullable|string|max:255',
            'mentee_notes'     => 'nullable|string|max:2000',
            'payment_method'   => 'nullable|in:wallet,razorpay,hybrid',
        ]);

        try {
            $result = $services->submit(auth()->user(), [
                'duration_minutes' => (int) $data['duration_minutes'],
                'preferred_at'     => $data['preferred_at'],
                'timezone'         => $data['timezone'] ?? null,
                'target_role'      => $data['target_role'] ?? null,
                'mentee_notes'     => $data['mentee_notes'] ?? null,
                'payment_method'   => $data['payment_method'] ?? null,
            ]);
        } catch (InvalidArgumentException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }

        return $this->paymentResponse($request, $result);
    }

    public function show(MockInterviewRequest $mockInterview)
    {
        abort_unless((int) $mockInterview->user_id === (int) auth()->id(), 404);

        $mockInterview->load('mentor');

        return view('frontend.mentee.mock-interviews.show', [
            'item' => $mockInterview,
        ]);
    }

    public function pay(Request $request, $mockInterview, MockInterviewService $services)
    {
        $item = MockInterviewRequest::withoutGlobalScopes()
            ->where('user_id', auth()->id())
            ->where('id', $mockInterview)
            ->firstOrFail();

        $data = $request->validate([
            'payment_method' => 'nullable|in:wallet,razorpay,hybrid',
        ]);

        try {
            $result = $services->pay(auth()->user(), $item, $data['payment_method'] ?? null);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->paymentResponse($request, $result);
    }

    public function verify(Request $request, $mockInterview, MockInterviewService $services)
    {
        $item = MockInterviewRequest::withoutGlobalScopes()
            ->where('user_id', auth()->id())
            ->where('id', $mockInterview)
            ->firstOrFail();

        $payload = $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        try {
            $item = $services->verifyPayment(auth()->user(), $item, $payload);
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

        if ($needsChoice && $item) {
            if ($item->status === MockInterviewRequest::STATUS_PENDING_PAYMENT) {
                return redirect()
                    ->route('mentee.mock-interviews.create', ['duration' => $item->duration_minutes])
                    ->with('success', 'Choose how you want to pay to submit this request.')
                    ->with('pending_request_id', $item->id);
            }

            return redirect()
                ->route('mentee.mock-interviews.show', $item)
                ->with('success', 'Choose how you want to pay to submit this request.');
        }

        if ($payment && $item) {
            if ($item->status === MockInterviewRequest::STATUS_PENDING_PAYMENT) {
                return redirect()
                    ->route('mentee.mock-interviews.create', ['duration' => $item->duration_minutes])
                    ->with('pay', $payment)
                    ->with('pending_request_id', $item->id);
            }

            return redirect()
                ->route('mentee.mock-interviews.show', $item)
                ->with('pay', $payment);
        }

        if ($item) {
            return redirect()
                ->route('mentee.mock-interviews.show', $item)
                ->with('success', 'Request submitted. Our team will review it shortly.');
        }

        return redirect()
            ->route('mentee.mock-interviews.create')
            ->with('success', 'Choose how you want to pay to continue.');
    }
}
