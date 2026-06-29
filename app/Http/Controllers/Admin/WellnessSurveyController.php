<?php

namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;

use App\Models\WellnessSurvey;
use App\Models\WellnessQuestion;
use App\Models\WellnessResponse;
use App\Models\WellnessAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
 
class WellnessSurveyController extends Controller
{
 
    public function index()
    {
        $surveys = WellnessSurvey::where('is_active', true)
            ->with('creator')
            ->withCount('responses')
            ->latest()
            ->get();
 
        return view('admin.wellness.index', compact('surveys'));
    }
 
    public function create()
    {
        return view('admin.wellness.create');
    }
 
    public function store(Request $request)
    {
        $request->validate([
            'title'              => 'required|string|max:200',
            'description'        => 'nullable|string|max:1000',
            'expires_at'         => 'nullable|date|after:today',
            'questions'          => 'required|array|min:1',
            'questions.*.text'   => 'required|string',
            'questions.*.type'   => 'required|in:scale,text,multiple_choice,yes_no',
            'questions.*.options' => 'nullable|array',
        ]);
 
        DB::transaction(function () use ($request) {
            $survey = WellnessSurvey::create([
                'title'       => $request->title,
                'description' => $request->description,
                'expires_at'  => $request->expires_at,
                'is_active'   => true,
                'created_by'  => Auth::id(),
            ]);
 
            foreach ($request->questions as $i => $q) {
                WellnessQuestion::create([
                    'survey_id' => $survey->id,
                    'question'  => $q['text'],
                    'type'      => $q['type'],
                    'options'   => $q['options'] ?? null,
                    'order'     => $i,
                    'required'  => true,
                ]);
            }
        });
 
        return redirect()->route('admin.wellness.index')->with('success', 'Survey created!');
    }
 
    public function show(WellnessSurvey $wellnessSurvey)
    {
        $hasResponded = $wellnessSurvey->hasResponded(Auth::user());
        $wellnessSurvey->load('questions');
 
        return view('admin.wellness.show', compact('wellnessSurvey', 'hasResponded'));
    }
 
    public function respond(Request $request, WellnessSurvey $wellnessSurvey)
    {
        abort_if($wellnessSurvey->hasResponded(Auth::user()), 422, 'Already responded.');
 
        $request->validate(['answers' => 'required|array']);
 
        DB::transaction(function () use ($request, $wellnessSurvey) {
            $response = WellnessResponse::create([
                'survey_id' => $wellnessSurvey->id,
                'user_id'   => Auth::id(),
            ]);
 
            foreach ($request->answers as $questionId => $answer) {
                WellnessAnswer::create([
                    'response_id' => $response->id,
                    'question_id' => $questionId,
                    'answer'      => is_array($answer) ? implode(', ', $answer) : $answer,
                ]);
            }
        });
 
        return redirect()->route('admin.wellness.results', $wellnessSurvey)
            ->with('success', 'Thank you for your response!');
    }
 
    public function results(WellnessSurvey $wellnessSurvey)
    {
        $wellnessSurvey->load(['questions.answers']);
 
        $stats = $wellnessSurvey->questions->map(function ($question) {
            $answers = $question->answers->pluck('answer');
 
            if ($question->type === 'scale') {
                $values  = $answers->map(fn($a) => (int)$a)->filter();
                $average = $values->count() ? round($values->average(), 1) : 0;
                return ['question' => $question->question, 'type' => 'scale', 'average' => $average, 'count' => $values->count()];
            }
 
            $grouped = $answers->countBy()->sortDesc();
            return ['question' => $question->question, 'type' => $question->type, 'answers' => $grouped, 'count' => $answers->count()];
        });
 
        return view('admin.wellness.results', compact('wellnessSurvey', 'stats'));
    }
 
    public function destroy(WellnessSurvey $wellnessSurvey)
    {
        abort_unless($wellnessSurvey->created_by === Auth::id(), 403);
        $wellnessSurvey->delete();
        return redirect()->route('admin.wellness.index')->with('success', 'Survey deleted.');
    }
}