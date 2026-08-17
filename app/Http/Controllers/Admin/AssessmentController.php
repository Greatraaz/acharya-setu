<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentProgress;
use App\Models\AssessmentScoreBand;
use App\Services\AssessmentService;
use App\Services\PublicFileStorage;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function __construct(private readonly AssessmentService $assessments)
    {
    }

    public function index()
    {
        if (! $this->assessments->tableExists()) {
            return view('admin.assessments.index', [
                'assessments'  => collect(),
                'tableMissing' => true,
            ]);
        }

        $assessments = $this->assessments->listWithStats();

        return view('admin.assessments.index', compact('assessments'));
    }

    public function create()
    {
        $assessment = new Assessment();
        $bands = $this->emptyBands();

        return view('admin.assessments.create', compact('assessment', 'bands'));
    }

    public function store(Request $request)
    {
        $this->assessments->validatedAssessment($request);
        $payload = $this->storeMedia($request);

        $request->merge($payload);
        $this->assessments->createFromRequest($request, auth()->id());

        return redirect()
            ->route('admin.assessments.index')
            ->with('success', 'Assessment created successfully.');
    }

    public function show(Assessment $assessment)
    {
        $assessment->load(['categories.questions', 'scoreBands', 'questions.category']);
        $completions = AssessmentProgress::where('assessment_id', $assessment->id)
            ->whereNotNull('completed_at')
            ->with('user:id,name,email')
            ->latest('completed_at')
            ->limit(20)
            ->get();

        return view('admin.assessments.show', compact('assessment', 'completions'));
    }

    public function edit(Assessment $assessment)
    {
        $assessment->load('scoreBands');
        $bands = $this->bandsForForm($assessment);

        return view('admin.assessments.edit', compact('assessment', 'bands'));
    }

    public function update(Request $request, Assessment $assessment)
    {
        $this->assessments->validatedAssessment($request, $assessment->id);
        $payload = $this->storeMedia($request, $assessment);

        $request->merge($payload);
        $this->assessments->updateFromRequest($request, $assessment);

        return redirect()
            ->route('admin.assessments.index')
            ->with('success', 'Assessment updated successfully.');
    }

    public function destroy(Assessment $assessment)
    {
        PublicFileStorage::deleteByUrl($assessment->image);
        PublicFileStorage::deleteByUrl($assessment->icon);
        $this->assessments->delete($assessment);

        return redirect()
            ->route('admin.assessments.index')
            ->with('success', 'Assessment deleted.');
    }

    private function storeMedia(Request $request, ?Assessment $assessment = null): array
    {
        $data = [
            'image' => $assessment?->image,
            'icon'  => $assessment?->icon,
        ];

        if ($request->hasFile('image_file')) {
            $request->validate(['image_file' => 'image|max:2048']);
            if ($assessment?->image) {
                PublicFileStorage::deleteByUrl($assessment->image);
            }
            $data['image'] = PublicFileStorage::store($request->file('image_file'), 'assessments/images');
        }

        if ($request->hasFile('icon_file')) {
            $request->validate(['icon_file' => 'image|max:1024']);
            if ($assessment?->icon) {
                PublicFileStorage::deleteByUrl($assessment->icon);
            }
            $data['icon'] = PublicFileStorage::store($request->file('icon_file'), 'assessments/icons');
        }

        return $data;
    }

    private function emptyBands(): array
    {
        return collect(range(0, 3))->map(fn ($i) => [
            'from'        => old("bands.$i.from", ''),
            'to'          => old("bands.$i.to", ''),
            'heading'     => old("bands.$i.heading", ''),
            'description' => old("bands.$i.description", ''),
        ])->all();
    }

    private function bandsForForm(Assessment $assessment): array
    {
        return collect(range(0, 3))->map(function ($i) use ($assessment) {
            $band = $assessment->scoreBands->firstWhere('band_index', $i)
                ?? new AssessmentScoreBand(['band_index' => $i]);

            return [
                'from'        => old("bands.$i.from", $band->range_from),
                'to'          => old("bands.$i.to", $band->range_to),
                'heading'     => old("bands.$i.heading", $band->heading),
                'description' => old("bands.$i.description", $band->description),
            ];
        })->all();
    }
}
