<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Services\PublicFileStorage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PodcastController extends Controller
{
    public function index(Request $request)
    {
        $query = Podcast::query()->latest('id');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('podcast_type')) {
            $query->where('podcast_type', $request->podcast_type);
        }

        $podcasts = $query->paginate(20)->withQueryString();

        return view('admin.podcasts.index', compact('podcasts'));
    }

    public function create()
    {
        $podcast = new Podcast([
            'status' => Podcast::STATUS_ACTIVE,
            'podcast_type' => Podcast::TYPE_AUDIO,
        ]);

        return view('admin.podcasts.create', compact('podcast'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $data['created_by'] = auth()->id();
        $data['image'] = PublicFileStorage::store($request->file('image'), 'podcasts');

        if ($data['podcast_type'] === Podcast::TYPE_AUDIO) {
            $data['audio'] = PublicFileStorage::store($request->file('audio'), 'podcasts/audio');
            $data['youtube_url'] = null;
        } else {
            $data['audio'] = null;
        }

        Podcast::create($data);

        return redirect()
            ->route('admin.podcasts.index')
            ->with('success', 'Podcast created successfully.');
    }

    public function edit(Podcast $podcast)
    {
        return view('admin.podcasts.edit', compact('podcast'));
    }

    public function update(Request $request, Podcast $podcast)
    {
        $data = $this->validated($request, false, $podcast);

        if ($request->hasFile('image')) {
            PublicFileStorage::deleteByUrl($podcast->image);
            $data['image'] = PublicFileStorage::store($request->file('image'), 'podcasts');
        }

        if ($data['podcast_type'] === Podcast::TYPE_AUDIO) {
            if ($request->hasFile('audio')) {
                PublicFileStorage::deleteByUrl($podcast->audio);
                $data['audio'] = PublicFileStorage::store($request->file('audio'), 'podcasts/audio');
            }
            $data['youtube_url'] = null;
        } else {
            if ($podcast->audio) {
                PublicFileStorage::deleteByUrl($podcast->audio);
            }
            $data['audio'] = null;
        }

        $podcast->update($data);

        return redirect()
            ->route('admin.podcasts.index')
            ->with('success', 'Podcast updated successfully.');
    }

    public function destroy(Podcast $podcast)
    {
        PublicFileStorage::deleteByUrl($podcast->image);
        PublicFileStorage::deleteByUrl($podcast->audio);
        $podcast->delete();

        return redirect()
            ->route('admin.podcasts.index')
            ->with('success', 'Podcast deleted.');
    }

    private function validated(Request $request, bool $isCreate, ?Podcast $podcast = null): array
    {
        $type = $request->input('podcast_type', $podcast->podcast_type ?? Podcast::TYPE_AUDIO);

        $rules = [
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('podcasts', 'slug')->ignore($podcast?->id),
            ],
            'podcast_type' => 'required|in:audio,youtube_url',
            'status' => 'required|in:active,inactive',
            'description' => 'required|string',
            'image' => ($isCreate ? 'required' : 'nullable').'|image|max:4096',
        ];

        if ($type === Podcast::TYPE_AUDIO) {
            $rules['audio'] = ($isCreate || ! $podcast?->audio ? 'required' : 'nullable').'|file|mimes:mp3,wav,m4a,ogg,aac,webm|max:51200';
            $rules['youtube_url'] = 'nullable';
        } else {
            $rules['youtube_url'] = 'required|url|max:500';
            $rules['audio'] = 'nullable';
        }

        $data = $request->validate($rules);
        unset($data['image'], $data['audio']);

        if ($type === Podcast::TYPE_YOUTUBE && ! Podcast::extractYoutubeId($data['youtube_url'] ?? null)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'youtube_url' => 'Enter a valid YouTube video URL.',
            ]);
        }

        return $data;
    }
}
