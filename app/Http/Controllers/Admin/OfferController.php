<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\User;
use App\Services\OfferService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfferController extends Controller
{
    public function __construct(private readonly OfferService $offers)
    {
    }

    public function index(Request $request)
    {
        $query = Offer::query()->withCount('mentees')->latest();

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('coupon_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('audience')) {
            $query->where('audience', $request->audience);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $offers = $query->paginate(20)->withQueryString();

        return view('admin.offers.index', compact('offers'));
    }

    public function create()
    {
        $offer = new Offer([
            'audience'   => Offer::AUDIENCE_NEW_JOINEE,
            'is_active'  => true,
            'starts_at'  => now()->toDateString(),
            'expires_at' => now()->addMonth()->toDateString(),
        ]);
        $mentees = $this->menteeOptions();

        return view('admin.offers.create', compact('offer', 'mentees'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();

        if ($data['audience'] === Offer::AUDIENCE_SELECTED_MENTEES && empty($data['coupon_code'])) {
            $data['coupon_code'] = $this->offers->generateCouponCode();
        }

        $menteeIds = $data['mentee_ids'] ?? [];
        unset($data['mentee_ids']);

        $offer = Offer::create($data);

        if ($offer->isCouponOffer()) {
            $offer->mentees()->sync($menteeIds);
        }

        return redirect()
            ->route('admin.offers.index')
            ->with('success', 'Offer created successfully.');
    }

    public function edit(Offer $offer)
    {
        $offer->load('mentees:id,name,email');
        $mentees = $this->menteeOptions();

        return view('admin.offers.edit', compact('offer', 'mentees'));
    }

    public function update(Request $request, Offer $offer)
    {
        $data = $this->validated($request, $offer);
        $menteeIds = $data['mentee_ids'] ?? [];
        unset($data['mentee_ids']);

        if ($data['audience'] === Offer::AUDIENCE_SELECTED_MENTEES && empty($data['coupon_code'])) {
            $data['coupon_code'] = $offer->coupon_code ?: $this->offers->generateCouponCode();
        }

        if ($data['audience'] === Offer::AUDIENCE_NEW_JOINEE) {
            $data['coupon_code'] = null;
            $data['usage_limit'] = null;
            $data['min_session_amount'] = null;
        }

        $offer->update($data);

        if ($offer->isCouponOffer()) {
            $offer->mentees()->sync($menteeIds);
        } else {
            $offer->mentees()->detach();
        }

        return redirect()
            ->route('admin.offers.index')
            ->with('success', 'Offer updated successfully.');
    }

    public function destroy(Offer $offer)
    {
        $offer->delete();

        return redirect()
            ->route('admin.offers.index')
            ->with('success', 'Offer deleted.');
    }

    private function menteeOptions()
    {
        return User::query()
            ->where('role', 'mentee')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function validated(Request $request, ?Offer $offer = null): array
    {
        $audience = $request->input('audience', Offer::AUDIENCE_NEW_JOINEE);
        $isCoupon = $audience === Offer::AUDIENCE_SELECTED_MENTEES;

        $rules = [
            'title'      => 'required|string|max:200',
            'audience'   => ['required', Rule::in([Offer::AUDIENCE_NEW_JOINEE, Offer::AUDIENCE_SELECTED_MENTEES])],
            'amount'     => 'required|numeric|min:1',
            'starts_at'  => 'required|date',
            'expires_at' => 'required|date|after_or_equal:starts_at',
            'is_active'  => 'nullable|boolean',
        ];

        if ($isCoupon) {
            $rules['coupon_code'] = [
                'nullable',
                'string',
                'max:40',
                Rule::unique('offers', 'coupon_code')->ignore($offer?->id),
            ];
            $rules['usage_limit'] = 'required|integer|min:1';
            $rules['min_session_amount'] = 'required|numeric|min:0';
            $rules['mentee_ids'] = 'required|array|min:1';
            $rules['mentee_ids.*'] = 'exists:users,id';
        }

        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['coupon_code'] = $isCoupon && ! empty($data['coupon_code'])
            ? strtoupper(trim($data['coupon_code']))
            : null;

        if (! $isCoupon) {
            $data['mentee_ids'] = [];
            $data['coupon_code'] = null;
            $data['usage_limit'] = null;
            $data['min_session_amount'] = null;
        }

        return $data;
    }
}
