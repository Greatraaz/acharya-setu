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

    public function index(): JsonResponse
    {
        if (! $this->assessments->tableExists()) {
            return response()->json([
                'status'      => true,
                'statuscode'  => 200,
                'assessments' => [],
                'message'     => 'Assessments table is not available yet.',
            ]);
        }

        $list = $this->assessments->listWithStats()
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
        $assessment = $this->assessments->createFromRequest($request, $request->user()->id);

        return response()->json([
            'status'     => true,
            'statuscode' => 201,
            'message'    => 'Assessment created.',
            'assessment' => $this->assessments->formatForApi($assessment, true),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $assessment = Assessment::findOrFail($id);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'assessment' => $this->assessments->formatForApi($assessment, true),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $assessment = Assessment::findOrFail($id);
        $assessment = $this->assessments->updateFromRequest($request, $assessment);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Assessment updated.',
            'assessment' => $this->assessments->formatForApi($assessment, true),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $assessment = Assessment::findOrFail($id);
        $this->assessments->delete($assessment);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Assessment deleted.',
        ]);
    }
}
