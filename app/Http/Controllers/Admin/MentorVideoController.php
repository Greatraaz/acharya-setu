<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MentorVideoService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MentorVideoController extends Controller
{
    public function __construct(private MentorVideoService $videos) {}

    public function index(Request $request)
    {
        $collections = $this->videos->listForAdmin([
            'search'    => $request->input('search'),
            'mentor_id' => $request->input('mentor_id'),
            'is_active' => $request->input('status'),
        ], 20);

        $mentors = User::mentors()->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.mentor-videos.index', compact('collections', 'mentors'));
    }

    public function create()
    {
        $mentors = User::mentors()->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.mentor-videos.create', compact('mentors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'mentor_id'   => 'required|integer|exists:users,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'is_active'   => 'sometimes|boolean',
            'videos'      => 'required|array|min:1',
            'videos.*'    => 'required|file|mimes:'.MentorVideoService::VIDEO_MIMES.'|max:'.MentorVideoService::VIDEO_MAX_KB,
        ]);

        $mentor = User::mentors()->findOrFail($data['mentor_id']);

        try {
            $this->videos->create($mentor, [
                'mentor_id'   => $mentor->id,
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active'   => $request->has('is_active'),
            ], $request->file('videos', []));
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.mentor-videos.index')
            ->with('success', 'Video collection uploaded successfully.');
    }

    public function show(int $mentorVideo)
    {
        $item = $this->videos->findForAdmin($mentorVideo);

        return view('admin.mentor-videos.show', ['item' => $item]);
    }

    public function edit(int $mentorVideo)
    {
        $item = $this->videos->findForAdmin($mentorVideo);
        $mentors = User::mentors()->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.mentor-videos.edit', compact('item', 'mentors'));
    }

    public function update(Request $request, int $mentorVideo)
    {
        $item = $this->videos->findForAdmin($mentorVideo);

        $data = $request->validate([
            'mentor_id'          => 'required|integer|exists:users,id',
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string|max:5000',
            'is_active'          => 'sometimes|boolean',
            'videos'             => 'nullable|array',
            'videos.*'           => 'file|mimes:'.MentorVideoService::VIDEO_MIMES.'|max:'.MentorVideoService::VIDEO_MAX_KB,
            'remove_video_ids'   => 'nullable|array',
            'remove_video_ids.*' => 'integer|exists:mentor_video_files,id',
        ]);

        User::mentors()->findOrFail($data['mentor_id']);

        try {
            $this->videos->update($item, [
                'mentor_id'        => $data['mentor_id'],
                'name'             => $data['name'],
                'description'      => $data['description'] ?? null,
                'is_active'        => $request->boolean('is_active'),
                'remove_video_ids' => $data['remove_video_ids'] ?? [],
            ], $request->file('videos', []) ?: []);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.mentor-videos.show', $item->id)
            ->with('success', 'Video collection updated.');
    }

    public function destroy(int $mentorVideo)
    {
        $item = $this->videos->findForAdmin($mentorVideo);
        $this->videos->destroy($item);

        return redirect()
            ->route('admin.mentor-videos.index')
            ->with('success', 'Video collection deleted.');
    }
}
