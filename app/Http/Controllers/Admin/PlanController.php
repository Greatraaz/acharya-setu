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
        $data = $this->validate($request);
        $data['slug'] = Str::slug($data['name']);
        $data['features'] = $this->parseFeatures($request->features_raw);
        $data['limits']   = [
            'users'    => $request->limit_users,
            'storage'  => $request->limit_storage,
            'calls'    => $request->limit_calls,
            'sessions' => $request->limit_sessions,
        ];

        Plan::create($data);
        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $this->validate($request);
        $data['features'] = $this->parseFeatures($request->features_raw);
        $data['limits']   = [
            'users'    => $request->limit_users,
            'storage'  => $request->limit_storage,
            'calls'    => $request->limit_calls,
            'sessions' => $request->limit_sessions,
        ];

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
        $plan->update(['is_active' => !$plan->is_active]);
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

    private function validate(Request $request): array
    {
        return $request->validate([
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
    }

    private function parseFeatures(?string $raw): array
    {
        if (!$raw) return [];
        return array_values(array_filter(array_map('trim', explode("\n", $raw))));
    }
}