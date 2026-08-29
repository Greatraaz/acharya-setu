<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Services\PublicFileStorage;
use Illuminate\Http\Request;

class CaseStudyController extends Controller
{
    public function index(Request $request)
    {
        $query = CaseStudy::query()->latest('id');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('industry', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $caseStudies = $query->paginate(20)->withQueryString();

        return view('admin.case-studies.index', compact('caseStudies'));
    }

    public function create()
    {
        $caseStudy = new CaseStudy([
            'status' => CaseStudy::STATUS_ACTIVE,
        ]);

        return view('admin.case-studies.create', compact('caseStudy'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $data['created_by'] = auth()->id();
        $data['image'] = PublicFileStorage::store($request->file('image'), 'case-studies');

        CaseStudy::create($data);

        return redirect()
            ->route('admin.case-studies.index')
            ->with('success', 'Case study created successfully.');
    }

    public function edit(CaseStudy $caseStudy)
    {
        return view('admin.case-studies.edit', compact('caseStudy'));
    }

    public function update(Request $request, CaseStudy $caseStudy)
    {
        $data = $this->validated($request, false);

        if ($request->hasFile('image')) {
            PublicFileStorage::deleteByUrl($caseStudy->image);
            $data['image'] = PublicFileStorage::store($request->file('image'), 'case-studies');
        }

        $caseStudy->update($data);

        return redirect()
            ->route('admin.case-studies.index')
            ->with('success', 'Case study updated successfully.');
    }

    public function destroy(CaseStudy $caseStudy)
    {
        PublicFileStorage::deleteByUrl($caseStudy->image);
        $caseStudy->delete();

        return redirect()
            ->route('admin.case-studies.index')
            ->with('success', 'Case study deleted.');
    }

    private function validated(Request $request, bool $requireImage): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'industry' => 'required|string|max:120',
            'status' => 'required|in:active,inactive',
            'description' => 'required|string',
            'result' => 'nullable|string',
            'image' => ($requireImage ? 'required' : 'nullable').'|image|max:4096',
        ];

        $data = $request->validate($rules);
        unset($data['image']);

        return $data;
    }
}
