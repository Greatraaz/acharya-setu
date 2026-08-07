<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $query = JobListing::query()
            ->active()
            ->with('postedBy:id,name')
            ->latest('published_at');

        if ($s = $request->search) {
            $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$s}%")
                ->orWhere('department', 'like', "%{$s}%")
                ->orWhere('location', 'like', "%{$s}%"));
        }

        if ($request->job_type) {
            $query->where('job_type', $request->job_type);
        }

        if ($request->location_type) {
            $query->where('location_type', $request->location_type);
        }

        $jobs = $query->paginate(12)->withQueryString();

        return view('frontend.mentee.jobs', compact('jobs'));
    }

    public function show(int $id)
    {
        $job = JobListing::query()
            ->active()
            ->with('postedBy:id,name')
            ->findOrFail($id);

        $alreadyApplied = JobApplication::where('user_id', auth()->id())
            ->where('jobId', $job->id)
            ->exists();

        return view('frontend.mentee.job-show', compact('job', 'alreadyApplied'));
    }

    public function apply(Request $request, int $id)
    {
        $job = JobListing::query()->active()->findOrFail($id);

        $data = $request->validate([
            'fullname' => 'required|string|max:100',
            'jobRole' => 'required|string|max:100',
            'qualification' => 'required|string|max:255',
            'specification' => 'nullable|string|max:255',
            'skills' => 'nullable|string|max:1000',
            'experience' => 'nullable|string|max:100',
            'lastJob' => 'nullable|string|max:255',
        ]);

        $alreadyApplied = JobApplication::where('user_id', auth()->id())
            ->where('jobId', $job->id)
            ->exists();

        if ($alreadyApplied) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'You have already applied for this job.'], 409);
            }

            return back()->with('error', 'You have already applied for this job.');
        }

        JobApplication::create([
            'user_id' => auth()->id(),
            'jobId' => $job->id,
            'fullname' => $data['fullname'],
            'jobRole' => $data['jobRole'],
            'qualification' => $data['qualification'],
            'specification' => $data['specification'] ?? null,
            'skills' => $data['skills'] ?? null,
            'experience' => $data['experience'] ?? null,
            'lastJob' => $data['lastJob'] ?? null,
        ]);

        if (Schema::hasColumn('job_listings', 'applications_count')) {
            $job->increment('applications_count');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Application submitted successfully!',
                'redirect' => route('mentee.jobs.show', $job->id),
            ]);
        }

        return redirect()
            ->route('mentee.jobs.show', $job->id)
            ->with('success', 'Application submitted successfully!');
    }
}
