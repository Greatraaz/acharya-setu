<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withTrashed()->ordered()->get();
        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.form', ['plan' => new Plan()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Plan::create($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $this->validated($request, $plan);
        $plan->update($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->back()->with('success', 'Plan deleted.');
    }

    public function restore(int $id)
    {
        Plan::withTrashed()->findOrFail($id)->restore();
        return redirect()->back()->with('success', 'Plan restored.');
    }

    public function toggleStatus(Plan $plan)
    {
        $plan->update(['is_active' => ! $plan->is_active]);
        return redirect()->back()->with('success', 'Plan status updated.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array']);
        foreach ($request->order as $i => $id) {
            Plan::where('id', $id)->update(['sort_order' => $i + 1]);
        }
        return response()->json(['success' => true]);
    }

    private function validated(Request $request, ?Plan $plan = null): array
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'description'   => 'nullable|string|max:500',
            'badge_label'   => 'nullable|string|max:50',
            'badge_color'   => 'nullable|string|max:20',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly'  => 'required|numeric|min:0',
            'currency'      => 'nullable|string|size:3',
            'trial_days'    => 'nullable|integer|min:0',
            'is_active'     => 'nullable|boolean',
            'is_featured'   => 'nullable|boolean',
            'sort_order'    => 'nullable|integer',
            'color'         => 'nullable|string|max:20',
            'stripe_monthly_price_id'  => 'nullable|string',
            'stripe_yearly_price_id'   => 'nullable|string',
            'razorpay_monthly_plan_id' => 'nullable|string',
            'razorpay_yearly_plan_id'  => 'nullable|string',
        ]);

        $data['slug'] = $plan?->slug ?: Str::slug($data['name']);
        $data['features'] = $this->parseFeatures($request->features_raw);
        $data['limits'] = [
            'users'    => $request->limit_users,
            'storage'  => $request->limit_storage,
            'calls'    => $request->limit_calls,
            'sessions' => $request->limit_sessions,
        ];
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['currency'] = $data['currency'] ?? 'INR';
        $data['trial_days'] = (int) ($data['trial_days'] ?? 0);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        // Legacy columns used by mobile/API (NOT NULL in older schema).
        $data['plan_name'] = $data['name'];
        $data['price'] = $data['price_monthly'];
        $data['status'] = $data['is_active'] ? 'active' : 'inactive';
        $data['duration'] = $plan?->duration ?? 30;
        $data['level'] = $plan?->level ?: $this->resolveLevel((float) $data['price_monthly']);

        return $data;
    }

    private function resolveLevel(float $priceMonthly): string
    {
        return match (true) {
            $priceMonthly <= 0 => 'basic',
            $priceMonthly < 500 => 'standard',
            $priceMonthly < 2000 => 'premium',
            default => 'enterprise',
        };
    }

    private function parseFeatures(?string $raw): array
    {
        if (! $raw) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode("\n", $raw))));
    }
}
