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
        Plan::create($this->validated($request));

        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $plan->update($this->validated($request, $plan));

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
            'name'                    => 'required|string|max:100',
            'description'             => 'nullable|string|max:500',
            'badge_label'             => 'nullable|string|max:50',
            'badge_color'             => 'nullable|string|max:20',
            'price_monthly'           => 'required|numeric|min:0',
            'price_yearly'            => 'required|numeric|min:0',
            'currency'                => 'nullable|string|size:3',
            'cgst_percent'            => 'nullable|numeric|min:0|max:100',
            'sgst_percent'            => 'nullable|numeric|min:0|max:100',
            'duration'                => 'nullable|integer|min:1|max:3650',
            'limit_sessions'          => 'nullable|integer|min:-1',
            'progress_report_enabled' => 'nullable|boolean',
            'trial_days'              => 'nullable|integer|min:0',
            'is_active'               => 'nullable|boolean',
            'is_featured'             => 'nullable|boolean',
            'sort_order'              => 'nullable|integer',
            'color'                   => 'nullable|string|max:20',
            'stripe_monthly_price_id' => 'nullable|string',
            'stripe_yearly_price_id'  => 'nullable|string',
            'razorpay_monthly_plan_id'=> 'nullable|string',
            'razorpay_yearly_plan_id' => 'nullable|string',
        ]);

        $sessions = $request->input('limit_sessions');
        $sessions = ($sessions === null || $sessions === '') ? null : (int) $sessions;

        $payload = [
            'name'                    => $data['name'],
            'description'             => $data['description'] ?? null,
            'badge_label'             => $data['badge_label'] ?? null,
            'badge_color'             => $data['badge_color'] ?? null,
            'price_monthly'           => $data['price_monthly'],
            'price_yearly'            => $data['price_yearly'],
            'currency'                => $data['currency'] ?? 'INR',
            'cgst_percent'            => $data['cgst_percent'] ?? null,
            'sgst_percent'            => $data['sgst_percent'] ?? null,
            'duration'                => (int) ($data['duration'] ?? $plan?->duration ?? 30),
            'features'                => $this->parseFeatures($request->features_raw),
            'limits'                  => ['sessions' => $sessions],
            'progress_report_enabled' => $request->boolean('progress_report_enabled'),
            'trial_days'              => (int) ($data['trial_days'] ?? 0),
            'is_active'               => $request->boolean('is_active'),
            'is_featured'             => $request->boolean('is_featured'),
            'sort_order'              => (int) ($data['sort_order'] ?? 0),
            'color'                   => $data['color'] ?? null,
            'stripe_monthly_price_id' => $data['stripe_monthly_price_id'] ?? null,
            'stripe_yearly_price_id'  => $data['stripe_yearly_price_id'] ?? null,
            'razorpay_monthly_plan_id'=> $data['razorpay_monthly_plan_id'] ?? null,
            'razorpay_yearly_plan_id' => $data['razorpay_yearly_plan_id'] ?? null,
            'slug'                    => $plan?->slug ?: Str::slug($data['name']),
            'plan_name'               => $data['name'],
            'price'                   => $data['price_monthly'],
            'status'                  => $request->boolean('is_active') ? 'active' : 'inactive',
            'level'                   => $plan?->level ?: $this->resolveLevel((float) $data['price_monthly']),
        ];

        return $payload;
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
