<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use Illuminate\Http\Request;

class AssessmentCategoryController extends Controller
{
    public function index()
    {
        $categories = AssessmentCategory::with('assessment')
            ->withCount('questions')
            ->latest()
            ->paginate(20);

        return view('admin.assessments.categories.index', compact('categories'));
    }

    public function create()
    {
        $assessments = Assessment::orderBy('title')->get();
        $category = new AssessmentCategory();

        return view('admin.assessments.categories.create', compact('assessments', 'category'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'name'          => 'required|string|max:150',
        ]);

        $max = (int) AssessmentCategory::where('assessment_id', $data['assessment_id'])->max('sort_order');
        $data['sort_order'] = $max + 1;

        AssessmentCategory::create($data);

        return redirect()
            ->route('admin.assessment-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(AssessmentCategory $assessment_category)
    {
        $assessments = Assessment::orderBy('title')->get();
        $category = $assessment_category;

        return view('admin.assessments.categories.edit', compact('assessments', 'category'));
    }

    public function update(Request $request, AssessmentCategory $assessment_category)
    {
        $data = $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'name'          => 'required|string|max:150',
        ]);

        $assessment_category->update($data);

        return redirect()
            ->route('admin.assessment-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(AssessmentCategory $assessment_category)
    {
        $assessment_category->questions()->delete();
        $assessment_category->delete();

        return redirect()
            ->route('admin.assessment-categories.index')
            ->with('success', 'Category deleted.');
    }
}
