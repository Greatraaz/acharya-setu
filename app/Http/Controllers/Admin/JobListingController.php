<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Http\Request;

class JobListingController extends Controller
{
    public function index(Request $request)
    {
        $query = JobListing::with('postedBy')->latest();

        if ($s = $request->search) {
            $query->where(fn($q) => $q->where('title', 'like', "%$s%")->orWhere('department', 'like', "%$s%"));
        }
        if ($request->status)      $query->where('status', $request->status);
        if ($request->department)  $query->where('department', $request->department);
        if ($request->job_type)    $query->where('job_type', $request->job_type);
        if ($request->location_type) $query->where('location_type', $request->location_type);

        $jobs = $query->paginate(15)->withQueryString();

        $stats = [
            'total'  => JobListing::count(),
            'active' => JobListing::where('status', 'active')->count(),
            'draft'  => JobListing::where('status', 'draft')->count(),
            'closed' => JobListing::where('status', 'closed')->count(),
        ];

        $departments = JobListing::distinct()->pluck('department')->filter()->sort()->values();

        return view('admin.jobs.index', compact('jobs', 'stats', 'departments'));
    }

    public function create()
    {
        return view('admin.jobs.form', ['job' => new JobListing()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateJob($request);
        $data['skills'] = $this->parseTags($request->skills_raw);
        $data['posted_by'] = auth()->id();
        if ($data['status'] === 'active') {
            $data['published_at'] = now();
        }

        JobListing::create($data);
        return redirect()->route('admin.jobs.index')->with('success', 'Job listing created successfully.');
    }

    public function show(JobListing $job)
    {
        $job->load('postedBy', 'applications');
        $applications = $job->applications()->latest()->paginate(10);
        return view('admin.jobs.show', compact('job', 'applications'));
    }

    public function edit(JobListing $job)
    {
        return view('admin.jobs.form', compact('job'));
    }

    public function update(Request $request, JobListing $job)
    {
        $data = $this->validateJob($request);
        $data['skills'] = $this->parseTags($request->skills_raw);
        if ($data['status'] === 'active' && !$job->published_at) {
            $data['published_at'] = now();
        }

        $job->update($data);
        return redirect()->route('admin.jobs.index')->with('success', 'Job listing updated successfully.');
    }

    public function destroy(JobListing $job)
    {
        $job->delete();
        return redirect()->route('admin.jobs.index')->with('success', 'Job listing deleted.');
    }

    public function toggleStatus(JobListing $job)
    {
        $newStatus = $job->status === 'active' ? 'paused' : 'active';
        if ($newStatus === 'active' && !$job->published_at) {
            $job->update(['status' => $newStatus, 'published_at' => now()]);
        } else {
            $job->update(['status' => $newStatus]);
        }
        return redirect()->back()->with('success', "Job " . ($newStatus === 'active' ? 'published' : 'paused') . ".");
    }


    private function validateJob(Request $request): array
    {
        return $request->validate([
            'title'           => 'required|string|max:200',
            'department'      => 'nullable|string|max:100',
            'location'        => 'required|string|max:200',
            'location_type'   => 'required|in:onsite,remote,hybrid',
            'job_type'        => 'required|in:full_time,part_time,contract,internship,freelance',
            'experience_level'=> 'required|in:entry,mid,senior,lead,executive',
            'salary_min'      => 'nullable|numeric|min:0',
            'salary_max'      => 'nullable|numeric|min:0',
            'salary_currency' => 'nullable|string|size:3',
            'salary_period'   => 'nullable|in:monthly,yearly',
            'salary_hidden'   => 'nullable|boolean',
            'description'     => 'required|string',
            'responsibilities'=> 'nullable|string',
            'requirements'    => 'nullable|string',
            'benefits'        => 'nullable|string',
            'apply_url'       => 'nullable|url',
            'apply_email'     => 'nullable|email',
            'deadline'        => 'nullable|date',
            'openings'        => 'nullable|integer|min:1',
            'status'          => 'required|in:draft,active,paused,closed',
            'is_featured'     => 'nullable|boolean',
        ]);
    }

    private function parseTags(?string $raw): array
    {
        if (!$raw) return [];
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}