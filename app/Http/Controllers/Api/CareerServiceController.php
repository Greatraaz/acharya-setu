<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CareerServiceRequest;
use App\Services\CareerServiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CareerServiceController extends Controller
{
    public function options(Request $request, CareerServiceService $services): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'status'  => true,
            'message' => 'Career service options.',
            'data'    => [
                'prices' => $services->prices(),
                'resume' => $services->quote($user, 'resume'),
                'linkedin' => $services->quote($user, 'linkedin'),
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $items = CareerServiceRequest::query()
            ->with('invoice')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'status'  => true,
            'message' => 'Requests fetched.',
            'data'    => $items->getCollection()->map->toPublicArray()->values(),
            'meta'    => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'total'        => $items->total(),
            ],
        ]);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $item = CareerServiceRequest::query()
            ->with('invoice')
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Request not found.'], 404);
        }

        if (in_array($item->payment_status, ['paid', 'free'], true) && ! $item->invoice) {
            app(\App\Services\CareerServiceInvoiceService::class)->ensureForRequest($item, 'system');
            $item->load('invoice');
        }

        return response()->json([
            'status'  => true,
            'message' => 'Request fetched.',
            'data'    => $item->toPublicArray(),
        ]);
    }

    public function store(Request $request, CareerServiceService $services): JsonResponse
    {
        $data = $request->validate([
            'type'           => 'required|in:resume,linkedin',
            'linkedin_url'   => 'nullable|url|max:500',
            'mentee_notes'   => 'nullable|string|max:2000',
            'resume'         => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'payment_method' => 'nullable|in:wallet,razorpay,hybrid',
        ]);

        try {
            $result = $services->submit($request->user(), $data['type'], [
                'linkedin_url'   => $data['linkedin_url'] ?? null,
                'mentee_notes'   => $data['mentee_notes'] ?? null,
                'resume'         => $request->file('resume'),
                'payment_method' => $data['payment_method'] ?? null,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        return $this->paymentJson($result);
    }

    public function pay(Request $request, $id, CareerServiceService $services): JsonResponse
    {
        $item = CareerServiceRequest::query()
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Request not found.'], 404);
        }

        $data = $request->validate([
            'payment_method' => 'nullable|in:wallet,razorpay,hybrid',
        ]);

        try {
            $result = $services->pay($request->user(), $item, $data['payment_method'] ?? null);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        return $this->paymentJson($result);
    }

    public function verify(Request $request, $id, CareerServiceService $services): JsonResponse
    {
        $item = CareerServiceRequest::query()
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Request not found.'], 404);
        }

        $payload = $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        try {
            $item = $services->verifyPayment($request->user(), $item, $payload);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Payment successful. Your request is now under review.',
            'data'    => $item->toPublicArray(),
        ]);
    }

    private function paymentJson(array $result): JsonResponse
    {
        $payment = $result['payment'] ?? null;
        $needsChoice = (bool) ($result['requires_payment_choice'] ?? false);
        $message = 'Request submitted. Our team will review it shortly.';
        if ($needsChoice) {
            $message = 'Choose a payment method to continue.';
        } elseif ($payment) {
            $message = 'Complete payment to submit your request.';
        }

        return response()->json([
            'status'  => true,
            'message' => $message,
            'requires_payment' => (bool) $payment,
            'requires_payment_choice' => $needsChoice,
            'data' => [
                'request' => $result['request']?->toPublicArray(),
                'payment' => $payment,
                'payment_choice' => $result['payment_choice'] ?? null,
            ],
        ], ($payment || $needsChoice) ? 201 : 200);
    }
}
