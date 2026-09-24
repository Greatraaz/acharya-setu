<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Services\MentorVideoService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MentorVideoController extends Controller
{
    public function __construct(private MentorVideoService $videos) {}

    public function index(Request $request)
    {
        $status = $request->input('status', '');
        $filters = [
            'search' => $request->input('search'),
            'is_active' => match ($status) {
                'active' => true,
                'inactive' => false,
                default => null,
            },
        ];

        $collections = $this->videos->listForMentor(auth()->user(), $filters, 15);

        return view('frontend.mentors.videos.index', [
            'collections' => $collections,
            'search' => $request->input('search', ''),
            'status' => $status,
        ]);
    }

    public function create()
    {
        return view('frontend.mentors.videos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'is_active'   => 'sometimes|boolean',
            'videos'      => 'required|array|min:1',
            'videos.*'    => 'required|file|mimes:'.MentorVideoService::VIDEO_MIMES.'|max:'.MentorVideoService::VIDEO_MAX_KB,
        ]);

        try {
            $this->videos->create(auth()->user(), [
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active'   => $request->has('is_active'),
            ], $request->file('videos', []));
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('mentor.videos.index')
            ->with('success', 'Video collection created. Mentees can watch it when active.');
    }

    public function show(int $video)
    {
        $item = $this->videos->findForMentor(auth()->user(), $video);

        return view('frontend.mentors.videos.show', ['item' => $item]);
    }

    public function edit(int $video)
    {
        $item = $this->videos->findForMentor(auth()->user(), $video);

        return view('frontend.mentors.videos.edit', ['item' => $item]);
    }

    public function update(Request $request, int $video)
    {
        $item = $this->videos->findForMentor(auth()->user(), $video);

        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string|max:5000',
            'is_active'          => 'sometimes|boolean',
            'videos'             => 'nullable|array',
            'videos.*'           => 'file|mimes:'.MentorVideoService::VIDEO_MIMES.'|max:'.MentorVideoService::VIDEO_MAX_KB,
            'remove_video_ids'   => 'nullable|array',
            'remove_video_ids.*' => 'integer|exists:mentor_video_files,id',
        ]);

        try {
            $this->videos->update($item, [
                'name'             => $data['name'],
                'description'      => $data['description'] ?? null,
                'is_active'        => $request->boolean('is_active'),
                'remove_video_ids' => $data['remove_video_ids'] ?? [],
            ], $request->file('videos', []) ?: []);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('mentor.videos.show', $item->id)
            ->with('success', 'Video collection updated.');
    }

    public function destroy(int $video)
    {
        $item = $this->videos->findForMentor(auth()->user(), $video);
        $this->videos->destroy($item);

        return redirect()
            ->route('mentor.videos.index')
            ->with('success', 'Video collection deleted.');
    }
}
