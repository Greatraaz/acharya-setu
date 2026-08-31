<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentProgress;
use App\Models\AssessmentScoreBand;
use App\Services\AssessmentService;
use App\Services\PublicFileStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AssessmentController extends Controller
{
    public function __construct(private readonly AssessmentService $assessments)
    {
    }

    public function index(Request $request)
    {
        if (! $this->assessments->tableExists()) {
            return view('frontend.mentors.assessments', [
                'assessments'  => collect(),
                'menteeCount'  => 0,
                'tableMissing' => true,
            ]);
        }

        $assessments = $this->assessments->listWithStatsPaginated(20, $request);
        $menteeCount = \App\Models\ConsultationSession::where('mentor_id', auth()->id())
            ->distinct()
            ->count('mentee_id');

        $search = trim((string) $request->input('search', $request->input('q', '')));
        $status = $request->input('status', '');

        return view('frontend.mentors.assessments', compact('assessments', 'menteeCount', 'search', 'status'));
    }

    public function create()
    {
        $assessment = new Assessment();
        $bands = $this->emptyBands();

        return view('frontend.mentors.assessments-create', compact('assessment', 'bands'));
    }

    public function store(Request $request)
    {
        $this->assessments->validatedAssessment($request);
        $payload = $this->storeMedia($request);
        $request->merge($payload);
        $this->assessments->createFromRequest($request, auth()->id());

        return redirect()
            ->route('mentor.assessments.index')
            ->with('success', 'Assessment created successfully.');
    }

    public function show(Assessment $assessment)
    {
        $assessment->load(['categories.questions', 'scoreBands', 'questions.category']);
        $questions = $assessment->questions;
        $completions = Schema::hasTable('assessment_progress')
            ? AssessmentProgress::where('assessment_id', $assessment->id)
                ->whereNotNull('completed_at')
                ->with('user:id,name,email')
                ->latest('completed_at')
                ->limit(20)
                ->get()
            : collect();

        return view('frontend.mentors.assessments-show', compact('assessment', 'questions', 'completions'));
    }

    public function edit(Assessment $assessment)
    {
        $assessment->load('scoreBands');
        $bands = $this->bandsForForm($assessment);

        return view('frontend.mentors.assessments-edit', compact('assessment', 'bands'));
    }

    public function update(Request $request, Assessment $assessment)
    {
        $this->assessments->validatedAssessment($request, $assessment->id);
        $payload = $this->storeMedia($request, $assessment);
        $request->merge($payload);
        $this->assessments->updateFromRequest($request, $assessment);

        return redirect()
            ->route('mentor.assessments.index')
            ->with('success', 'Assessment updated successfully.');
    }

    public function destroy(Assessment $assessment)
    {
        PublicFileStorage::deleteByUrl($assessment->image);
        PublicFileStorage::deleteByUrl($assessment->icon);
        $this->assessments->delete($assessment);

        return redirect()
            ->route('mentor.assessments.index')
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
