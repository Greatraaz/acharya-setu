<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LearnerSuccessStory;
use App\Services\PublicFileStorage;
use Illuminate\Http\Request;

class LearnerSuccessStoryController extends Controller
{
    public function index(Request $request)
    {
        $query = LearnerSuccessStory::query()->latest('id');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $stories = $query->paginate(20)->withQueryString();

        return view('admin.learner-success-stories.index', compact('stories'));
    }

    public function create()
    {
        $story = new LearnerSuccessStory([
            'status' => LearnerSuccessStory::STATUS_ACTIVE,
        ]);

        return view('admin.learner-success-stories.create', compact('story'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $data['created_by'] = auth()->id();
        $data['image'] = PublicFileStorage::store($request->file('image'), 'learner-success-stories');

        LearnerSuccessStory::create($data);

        return redirect()
            ->route('admin.learner-success-stories.index')
            ->with('success', 'Learner success story created successfully.');
    }

    public function edit(LearnerSuccessStory $learnerSuccessStory)
    {
        return view('admin.learner-success-stories.edit', ['story' => $learnerSuccessStory]);
    }

    public function update(Request $request, LearnerSuccessStory $learnerSuccessStory)
    {
        $data = $this->validated($request, false);

        if ($request->hasFile('image')) {
            PublicFileStorage::deleteByUrl($learnerSuccessStory->image);
            $data['image'] = PublicFileStorage::store($request->file('image'), 'learner-success-stories');
        }

        $learnerSuccessStory->update($data);

        return redirect()
            ->route('admin.learner-success-stories.index')
            ->with('success', 'Learner success story updated successfully.');
    }

    public function destroy(LearnerSuccessStory $learnerSuccessStory)
    {
        PublicFileStorage::deleteByUrl($learnerSuccessStory->image);
        $learnerSuccessStory->delete();

        return redirect()
            ->route('admin.learner-success-stories.index')
            ->with('success', 'Learner success story deleted.');
    }

    private function validated(Request $request, bool $requireImage): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'message' => 'required|string',
            'image' => ($requireImage ? 'required' : 'nullable').'|image|max:4096',
        ]);

        unset($data['image']);

        return $data;
    }
}
