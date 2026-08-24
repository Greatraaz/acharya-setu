<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use Illuminate\Http\Request;

class AssessmentQuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = AssessmentQuestion::with('assessment')->latest();

        if ($request->filled('assessment_id')) {
            $query->where('assessment_id', $request->assessment_id);
        }

        if ($request->filled('search')) {
            $query->where('question', 'like', '%' . $request->search . '%');
        }

        $questions   = $query->paginate(20)->withQueryString();
        $assessments = Assessment::orderBy('title')->get();

        return view('frontend.mentors.assessment-questions.index', compact('questions', 'assessments'));
    }

    public function create()
    {
        $assessments = Assessment::orderBy('title')->get();
        $question    = new AssessmentQuestion(['options' => AssessmentQuestion::DEFAULT_OPTIONS]);

        return view('frontend.mentors.assessment-questions.create', compact('assessments', 'question'));
    }

    public function store(Request $request)
    {
        $data       = $this->validated($request);
        $assessment = Assessment::findOrFail($data['assessment_id']);
        $max        = (int) AssessmentQuestion::where('assessment_id', $assessment->id)->max('sort_order');

        AssessmentQuestion::create([
            'assessment_id' => $assessment->id,
            'category_id'   => null,
            'question'      => $data['question'],
            'options'       => array_values($data['options']),
            'sort_order'    => $max + 1,
        ]);

        return redirect()
            ->route('mentor.assessment-questions.index')
            ->with('success', 'Question created successfully.');
    }

    public function edit(AssessmentQuestion $assessment_question)
    {
        $assessments             = Assessment::orderBy('title')->get();
        $question                = $assessment_question;
        $question->options       = $question->optionLabels();

        return view('frontend.mentors.assessment-questions.edit', compact('assessments', 'question'));
    }

    public function update(Request $request, AssessmentQuestion $assessment_question)
    {
        $data = $this->validated($request);

        $assessment_question->update([
            'assessment_id' => $data['assessment_id'],
            'category_id'   => null,
            'question'      => $data['question'],
            'options'       => array_values($data['options']),
        ]);

        return redirect()
            ->route('mentor.assessment-questions.index')
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(AssessmentQuestion $assessment_question)
    {
        $assessment_question->delete();

        return redirect()
            ->route('mentor.assessment-questions.index')
            ->with('success', 'Question deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'question'      => 'required|string|max:5000',
            'options'       => 'required|array|size:4',
            'options.0'     => 'required|string|max:200',
            'options.1'     => 'required|string|max:200',
            'options.2'     => 'required|string|max:200',
            'options.3'     => 'required|string|max:200',
        ]);
    }
}
