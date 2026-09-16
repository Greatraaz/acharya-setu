<?php

namespace App\Http\Controllers\Api\Mentee;

use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use App\Models\Offer;
use App\Models\User;
use App\Services\OfferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(private readonly OfferService $offers) {}

    /**
     * List coupons assigned to the logged-in mentee.
     * GET /api/v1/mentee/coupons
     */
    public function index(Request $request): JsonResponse
    {
        $coupons = $this->offers->availableCouponsFor($request->user())
            ->map(fn (Offer $offer) => $this->formatCoupon($offer))
            ->values();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Available coupons fetched successfully.',
            'data'       => $coupons,
        ]);
    }

    /**
     * Preview / validate a coupon against a mentor + duration.
     * POST /api/v1/mentee/coupons/validate
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $data = $request->validate([
            'coupon_code' => 'required|string|max:40',
            'mentor_id'   => 'required|exists:users,id',
            'duration'    => 'required|integer|in:'.implode(',', ConsultationSession::BOOKING_DURATIONS),
        ]);

        $mentor = User::where('role', 'mentor')
            ->where('mentor_status', 'approved')
            ->find($data['mentor_id']);

        if (! $mentor) {
            return response()->json([
                'status'     => false,
                'statuscode' => 404,
                'message'    => 'Mentor not found.',
            ], 404);
        }

        $baseAmount = round((float) ($mentor->rate_per_minute ?? 0) * (int) $data['duration'], 2);
        $check = $this->offers->validateCoupon($request->user(), $data['coupon_code'], $baseAmount);

        if (! ($check['valid'] ?? false)) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => $check['message'] ?? 'Invalid coupon.',
                'data'       => [
                    'valid'           => false,
                    'base_amount'     => $baseAmount,
                    'coupon_discount' => 0,
                    'payable_amount'  => $baseAmount,
                    'currency'        => 'INR',
                ],
            ], 422);
        }

        $discount = round((float) $check['discount'], 2);
        $payable = round(max(0, $baseAmount - $discount), 2);
        /** @var Offer $offer */
        $offer = $check['offer'];

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Coupon applied successfully.',
            'data'       => [
                'valid'           => true,
                'coupon'          => $this->formatCoupon($offer),
                'base_amount'     => $baseAmount,
                'coupon_discount' => $discount,
                'payable_amount'  => $payable,
                'currency'        => 'INR',
                'duration'        => (int) $data['duration'],
                'mentor_id'       => $mentor->id,
            ],
        ]);
    }

    private function formatCoupon(Offer $offer): array
    {
        return [
            'id'                 => $offer->id,
            'title'              => $offer->title,
            'coupon_code'        => $offer->coupon_code,
            'discount_amount'    => (float) $offer->amount,
            'min_session_amount' => (float) ($offer->min_session_amount ?? 0),
            'starts_at'          => $offer->starts_at?->toDateString(),
            'expires_at'         => $offer->expires_at?->toDateString(),
        ];
    }
}
