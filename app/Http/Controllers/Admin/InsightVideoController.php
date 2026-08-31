<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InsightVideo;
use App\Support\YoutubeUrl;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InsightVideoController extends Controller
{
    public function index(Request $request)
    {
        $query = InsightVideo::query()->latest('id');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $videos = $query->paginate(20)->withQueryString();

        return view('admin.videos.index', compact('videos'));
    }

    public function create()
    {
        $video = new InsightVideo([
            'status' => InsightVideo::STATUS_ACTIVE,
        ]);

        return view('admin.videos.create', compact('video'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();

        InsightVideo::create($data);

        return redirect()
            ->route('admin.videos.index')
            ->with('success', 'Video created successfully.');
    }

    public function edit(InsightVideo $video)
    {
        return view('admin.videos.edit', compact('video'));
    }

    public function update(Request $request, InsightVideo $video)
    {
        $video->update($this->validated($request));

        return redirect()
            ->route('admin.videos.index')
            ->with('success', 'Video updated successfully.');
    }

    public function destroy(InsightVideo $video)
    {
        $video->delete();

        return redirect()
            ->route('admin.videos.index')
            ->with('success', 'Video deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('insight_videos', 'slug')->ignore($request->route('video')),
            ],
            'description' => 'required|string',
            'youtube_url' => 'required|url|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        if (! YoutubeUrl::extractId($data['youtube_url'] ?? null)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'youtube_url' => 'Enter a valid YouTube video URL.',
            ]);
        }

        return $data;
    }
}
