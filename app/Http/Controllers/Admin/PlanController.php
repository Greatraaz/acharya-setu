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
        $planStats = [
            'total' => Plan::whereNull('deleted_at')->count(),
            'active' => Plan::whereNull('deleted_at')->where('is_active', true)->count(),
            'featured' => Plan::whereNull('deleted_at')->where('is_featured', true)->count(),
            'archived' => Plan::onlyTrashed()->count(),
        ];

        $plans = Plan::withTrashed()->ordered()->paginate(20)->withQueryString();

        return view('admin.plans.index', compact('plans', 'planStats'));
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
            'badge_color'             => 'nullable|in:blue,green,orange',
            'price_monthly'           => 'required|numeric|min:0',
            'price_yearly'            => 'required|numeric|min:0',
            'discount_percent'        => 'nullable|numeric|min:0|max:100',
            'discount_expires_at'     => 'nullable|date',
            'currency'                => 'nullable|string|size:3',
            'cgst_percent'            => 'nullable|numeric|min:0|max:100',
            'sgst_percent'            => 'nullable|numeric|min:0|max:100',
            'igst_percent'            => 'nullable|numeric|min:0|max:100',
            'duration'                => 'nullable|integer|min:1|max:3650',
            'benefits'                => 'nullable|array',
            'benefits.*.label'        => 'nullable|string|max:120',
            'benefits.*.value'        => 'nullable|string|max:255',
            'trial_days'              => 'nullable|integer|min:0',
            'is_active'               => 'nullable|boolean',
            'is_featured'             => 'nullable|boolean',
            'sort_order'              => 'nullable|integer',
            'color'                   => 'nullable|string|max:20',
        ]);

        return [
            'name'                    => $data['name'],
            'description'             => $data['description'] ?? null,
            'badge_label'             => $data['badge_label'] ?? null,
            'badge_color'             => $data['badge_color'] ?? null,
            'price_monthly'           => $data['price_monthly'],
            'price_yearly'            => $data['price_yearly'],
            'discount_percent'        => (float) ($data['discount_percent'] ?? 0),
            'discount_expires_at'     => ((float) ($data['discount_percent'] ?? 0) > 0)
                ? ($data['discount_expires_at'] ?? null)
                : null,
            'currency'                => $data['currency'] ?? 'INR',
            'cgst_percent'            => Plan::filledTaxPercent($request->input('cgst_percent')),
            'sgst_percent'            => Plan::filledTaxPercent($request->input('sgst_percent')),
            'igst_percent'            => Plan::filledTaxPercent($request->input('igst_percent')),
            'duration'                => (int) ($data['duration'] ?? $plan?->duration ?? 30),
            'limits'                  => is_array($plan?->limits) ? $plan->limits : [],
            'benefits'                => Plan::normalizeBenefits($request->input('benefits', [])),
            'trial_days'              => (int) ($data['trial_days'] ?? 0),
            'is_active'               => $request->boolean('is_active'),
            'is_featured'             => $request->boolean('is_featured'),
            'sort_order'              => (int) ($data['sort_order'] ?? 0),
            'color'                   => $data['color'] ?? null,
            'slug'                    => $plan?->slug ?: $this->uniqueSlug($data['name']),
        ];
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'plan';
        $slug = $base;
        $i = 2;

        while (Plan::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
