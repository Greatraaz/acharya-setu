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
        $data = $request->validate([
            'search'            => 'nullable|string|max:100',
            'type'              => 'nullable|in:full_time,part_time,internship,contract,freelance',
            'job_type'          => 'nullable|in:full_time,part_time,internship,contract,freelance',
            'mode'              => 'nullable|in:remote,onsite,hybrid',
            'location_type'     => 'nullable|in:remote,onsite,hybrid',
            'department'        => 'nullable|string|max:100',
            'experience_level'  => 'nullable|in:entry,mid,senior,lead,executive',
            'per_page'          => 'nullable|integer|min:1|max:100',
        ]);

        $search   = trim((string) ($data['search'] ?? ''));
        $jobType  = $data['job_type'] ?? $data['type'] ?? null;
        $location = $data['location_type'] ?? $data['mode'] ?? null;
        $perPage  = $data['per_page'] ?? 20;

        $query = JobListing::query()
            ->active()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhere('department', 'like', '%'.$search.'%')
                        ->orWhere('location', 'like', '%'.$search.'%');
                });
            })
            ->when($jobType, fn ($q) => $q->where('job_type', $jobType))
            ->when($location, fn ($q) => $q->where('location_type', $location))
            ->when(! empty($data['department']), fn ($q) => $q->where('department', $data['department']))
            ->when(! empty($data['experience_level']), fn ($q) => $q->where('experience_level', $data['experience_level']))
            ->latest('published_at')
            ->latest('id');

        $paginator = $query->paginate($perPage)->withQueryString();

        $appliedJobIds = JobApplication::where('user_id', $request->user()->id)
            ->whereIn('jobId', collect($paginator->items())->pluck('id'))
            ->pluck('jobId')
            ->flip();

        $jobs = collect($paginator->items())->map(function (JobListing $job) use ($appliedJobIds) {
            $applied = $appliedJobIds->has($job->id);
            $row = $job->toArray();
            $row['applied'] = $applied;
            $row['application_status'] = $applied ? 'applied' : null;

            return $row;
        })->values();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'jobs'       => $jobs,
            'meta'       => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
            'filters'    => [
                'search'           => $search !== '' ? $search : null,
                'job_type'         => $jobType,
                'location_type'    => $location,
                'department'       => $data['department'] ?? null,
                'experience_level' => $data['experience_level'] ?? null,
            ],
        ]);
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
        $data = $request->validate([
            'search'   => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $search  = trim((string) ($data['search'] ?? ''));
        $perPage = $data['per_page'] ?? 20;

        $paginator = JobApplication::query()
            ->where('user_id', $request->user()->id)
            ->with(['job' => fn ($q) => $q->select([
                'id', 'title', 'slug', 'department', 'location', 'location_type',
                'job_type', 'experience_level', 'salary_min', 'salary_max',
                'salary_currency', 'salary_period', 'salary_hidden', 'status',
                'deadline', 'is_featured', 'published_at',
            ])])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('fullname', 'like', '%'.$search.'%')
                        ->orWhere('jobRole', 'like', '%'.$search.'%')
                        ->orWhereHas('job', fn ($job) => $job->where('title', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'success'      => true,
            'status'       => true,
            'statuscode'   => 200,
            'applications' => collect($paginator->items())->values(),
            'meta'         => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
            'filters'      => [
                'search' => $search !== '' ? $search : null,
            ],
        ]);
    }
}
