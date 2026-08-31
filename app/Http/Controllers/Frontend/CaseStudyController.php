<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CaseStudyController extends Controller
{
    public function index(Request $request): View
    {
        $industry = trim((string) $request->input('industry', ''));
        $search = trim((string) $request->input('search', $request->input('q', '')));

        $caseStudies = CaseStudy::query()
            ->where('status', CaseStudy::STATUS_ACTIVE)
            ->when($industry !== '', fn ($q) => $q->where('industry', $industry))
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('title', 'like', '%'.$search.'%')
                    ->orWhere('industry', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            }))
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        $industries = CaseStudy::query()
            ->where('status', CaseStudy::STATUS_ACTIVE)
            ->whereNotNull('industry')
            ->where('industry', '!=', '')
            ->distinct()
            ->orderBy('industry')
            ->pluck('industry');

        return view('frontend.insights.case-studies.index', compact('caseStudies', 'industries', 'industry', 'search'));
    }

    public function show(string $slug): View
    {
        $caseStudy = CaseStudy::query()
            ->where('slug', $slug)
            ->where('status', CaseStudy::STATUS_ACTIVE)
            ->firstOrFail();

        $recent = CaseStudy::query()
            ->where('status', CaseStudy::STATUS_ACTIVE)
            ->where('id', '!=', $caseStudy->id)
            ->latest('id')
            ->limit(4)
            ->get();

        return view('frontend.insights.case-studies.show', compact('caseStudy', 'recent'));
    }
}
