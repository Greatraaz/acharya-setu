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
        $caseStudies = CaseStudy::query()
            ->where('status', CaseStudy::STATUS_ACTIVE)
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        return view('frontend.insights.case-studies.index', compact('caseStudies'));
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
