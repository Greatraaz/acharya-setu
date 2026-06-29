<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JobsController extends Controller
{
   
    public function index(Request $request): JsonResponse
    {
        $q = JobListing::where('is_active', true);
        if ($s = $request->search) {
            $q->where(fn($x) => $x->where('title', 'like', "%$s%")->orWhere('company', 'like', "%$s%"));
        }
        if ($t = $request->type) $q->where('type', $t);
        if ($m = $request->mode) $q->where('mode', $m);
        return response()->json($q->latest()->paginate(20));
    }

    
    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'job' => JobListing::create($request->validate([
                'title'        => 'required|string',
                'company'      => 'required|string',
                'location'     => 'nullable|string',
                'salary_range' => 'nullable|string',
                'type'         => 'required|in:full_time,part_time,internship,contract',
                'mode'         => 'required|in:remote,onsite,hybrid',
                'description'  => 'nullable|string',
                'requirements' => 'nullable|string',
                'apply_url'    => 'nullable|url',
                'category'     => 'nullable|string',
                'expires_at'   => 'nullable|date',
            ])),
        ], 201);
    }

    public function applyJob(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'jobId'         => ['required', 'integer'],
            'fullname'      => ['required', 'string', 'max:100'],
            'jobRole'       => ['required', 'string', 'max:100'],
            'qualification' => ['required'],
            'specification' => ['nullable', 'string'],
            'skills'        => ['nullable', 'string'],
            'experience'    => ['nullable', 'string', 'max:100'],
            'lastJob'       => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        DB::table('job_applications')->insert([
            'jobId'         => $data['jobId'],
            'fullname'      => $data['fullname'],
            'jobRole'       => $data['jobRole'],
            'qualification' => $data['qualification'],
            'specification' => $data['specification'] ?? null,
            'skills'        => $data['skills'] ?? null,
            'experience'    => $data['experience'] ?? null,
            'lastJob'       => $data['lastJob'] ?? null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'Job application submitted successfully.',
        ], 201);
    }
}
