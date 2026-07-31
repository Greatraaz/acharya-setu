<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobListing;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Validator;

class JobsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = JobListing::where('is_active', true);
        if ($s = $request->search) {
            $q->where(fn ($x) => $x->where('title', 'like', "%$s%")->orWhere('company', 'like', "%$s%"));
        }
        if ($t = $request->type) {
            $q->where('type', $t);
        }
        if ($m = $request->mode) {
            $q->where('mode', $m);
        }

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
            'jobId'         => ['required', 'integer', 'exists:job_listings,id'],
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
        $user = $request->user();

        $alreadyApplied = JobApplication::where('user_id', $user->id)
            ->where('jobId', $data['jobId'])
            ->exists();

        if ($alreadyApplied) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'message' => 'You have already applied for this job.',
            ], 409);
        }

        $application = JobApplication::create([
            'user_id'         => $user->id,
            'jobId'           => $data['jobId'],
            'fullname' => $data['fullname'],
            'jobRole'         => $data['jobRole'],
            'qualification'   => $data['qualification'],
            'specification'   => $data['specification'] ?? null,
            'skills'          => $data['skills'] ?? null,
            'experience'      => $data['experience'] ?? null,
            'lastJob'         => $data['lastJob'] ?? null,
        ]);

        JobListing::where('id', $data['jobId'])->increment('applications_count');

        return response()->json([
            'success'     => true,
            'status'      => true,
            'message'     => 'Job application submitted successfully.',
            'application' => $application,
        ], 201);
    }

    public function myApplications(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);

        $applications = JobApplication::query()
            ->where('user_id', $request->user()->id)
            ->with(['job' => fn ($q) => $q->select([
                'id', 'title', 'slug', 'department', 'location', 'location_type',
                'job_type', 'experience_level', 'salary_min', 'salary_max',
                'salary_currency', 'salary_period', 'salary_hidden', 'status',
                'deadline', 'is_featured', 'published_at',
            ])])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success'      => true,
            'status'       => true,
            'applications' => $applications->items(),
            'pagination'   => [
                'total'        => $applications->total(),
                'per_page'     => $applications->perPage(),
                'current_page' => $applications->currentPage(),
                'last_page'    => $applications->lastPage(),
                'from'         => $applications->firstItem(),
                'to'           => $applications->lastItem(),
            ],
        ]);
    }
}
