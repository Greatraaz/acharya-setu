<?php

namespace App\Http\Controllers\Api\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Services\AssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function __construct(private readonly AssessmentService $assessments)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if (! $this->assessments->tableExists()) {
            return response()->json([
                'status'      => true,
                'statuscode'  => 200,
                'assessments' => [],
                'message'     => 'Assessments table is not available yet.',
            ]);
        }

        $list = $this->assessments->listWithStats($request->user())
            ->map(fn (Assessment $a) => $this->assessments->formatForApi($a));

        return response()->json([
            'status'      => true,
            'statuscode'  => 200,
            'count'       => $list->count(),
            'assessments' => $list,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->assessments->validatedAssessment($request, null, $request->user());
        $assessment = $this->assessments->createFromRequest($request, $request->user()->id);

        return response()->json([
            'status'     => true,
            'statuscode' => 201,
            'message'    => 'Assessment created.',
            'assessment' => $this->assessments->formatForApi($assessment, true),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $assessment = $this->assessments->findForMentorOrFail($id, $request->user());
        $assessment->load('assignedMentees:id,name,email,role');

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'assessment' => $this->assessments->formatForApi($assessment, true),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $assessment = $this->assessments->findForMentorOrFail($id, $request->user());
        $this->assessments->validatedAssessment($request, $assessment->id, $request->user());
        $assessment = $this->assessments->updateFromRequest($request, $assessment);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Assessment updated.',
            'assessment' => $this->assessments->formatForApi($assessment, true),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $assessment = $this->assessments->findForMentorOrFail($id, $request->user());
        $this->assessments->delete($assessment);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Assessment deleted.',
        ]);
    }
}
