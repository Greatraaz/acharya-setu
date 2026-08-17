<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentProgress;
use App\Services\AssessmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AssessmentController extends Controller
{
    public function __construct(private readonly AssessmentService $assessments)
    {
    }

    public function index()
    {
        if (! $this->assessments->tableExists()) {
            return view('frontend.mentors.assessments', [
                'assessments'  => collect(),
                'menteeCount'  => 0,
                'tableMissing' => true,
            ]);
        }

        $assessments = $this->assessments->listWithStats();
        $menteeCount = \App\Models\ConsultationSession::where('mentor_id', auth()->id())
            ->distinct()
            ->count('mentee_id');

        return view('frontend.mentors.assessments', compact('assessments', 'menteeCount'));
    }

    public function create()
    {
        $assessment = new Assessment(['month' => 1, 'questions' => []]);

        return view('frontend.mentors.assessments-create', compact('assessment'));
    }

    public function store(Request $request)
    {
        $this->assessments->createFromRequest($request, auth()->id());

        return redirect()
            ->route('mentor.assessments.index')
            ->with('success', 'Assessment created successfully.');
    }

    public function show(Assessment $assessment)
    {
        $questions = $assessment->questions()->with('category')->get();
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
        return view('frontend.mentors.assessments-edit', compact('assessment'));
    }

    public function update(Request $request, Assessment $assessment)
    {
        $this->assessments->updateFromRequest($request, $assessment);

        return redirect()
            ->route('mentor.assessments.index')
            ->with('success', 'Assessment updated successfully.');
    }

    public function destroy(Assessment $assessment)
    {
        $this->assessments->delete($assessment);

        return redirect()
            ->route('mentor.assessments.index')
            ->with('success', 'Assessment deleted.');
    }
}
