<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MentorshipGuide;
use App\Models\MentorshipGuideDownload;
use App\Services\PublicFileStorage;
use Illuminate\Http\Request;

class MentorshipGuideController extends Controller
{
    public function index(Request $request)
    {
        $query = MentorshipGuide::query()->latest('id');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $guides = $query->paginate(20)->withQueryString();

        return view('admin.mentorship-guides.index', compact('guides'));
    }

    public function create()
    {
        $guide = new MentorshipGuide([
            'status' => MentorshipGuide::STATUS_ACTIVE,
        ]);

        return view('admin.mentorship-guides.create', compact('guide'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $data['created_by'] = auth()->id();
        $data['image'] = PublicFileStorage::store($request->file('image'), 'mentorship-guides');

        MentorshipGuide::create($data);

        return redirect()
            ->route('admin.mentorship-guides.index')
            ->with('success', 'Mentorship guide created successfully.');
    }

    public function edit(MentorshipGuide $mentorshipGuide)
    {
        return view('admin.mentorship-guides.edit', ['guide' => $mentorshipGuide]);
    }

    public function update(Request $request, MentorshipGuide $mentorshipGuide)
    {
        $data = $this->validated($request, false);

        if ($request->hasFile('image')) {
            PublicFileStorage::deleteByUrl($mentorshipGuide->image);
            $data['image'] = PublicFileStorage::store($request->file('image'), 'mentorship-guides');
        }

        $mentorshipGuide->update($data);

        return redirect()
            ->route('admin.mentorship-guides.index')
            ->with('success', 'Mentorship guide updated successfully.');
    }

    public function destroy(MentorshipGuide $mentorshipGuide)
    {
        PublicFileStorage::deleteByUrl($mentorshipGuide->image);
        $mentorshipGuide->delete();

        return redirect()
            ->route('admin.mentorship-guides.index')
            ->with('success', 'Mentorship guide deleted.');
    }

    public function downloads(Request $request)
    {
        $query = MentorshipGuideDownload::query()
            ->with('guide:id,title,slug')
            ->latest('id');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('organization', 'like', "%{$search}%")
                    ->orWhereHas('guide', fn ($g) => $g->where('title', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('guide_id')) {
            $query->where('mentorship_guide_id', $request->integer('guide_id'));
        }

        $downloads = $query->paginate(30)->withQueryString();
        $guides = MentorshipGuide::query()->orderBy('title')->get(['id', 'title']);

        return view('admin.mentorship-guides.downloads', compact('downloads', 'guides'));
    }

    private function validated(Request $request, bool $requireImage): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'description' => 'required|string',
            'image' => ($requireImage ? 'required' : 'nullable').'|image|max:4096',
        ]);

        unset($data['image']);

        return $data;
    }
}
