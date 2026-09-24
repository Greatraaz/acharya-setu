<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Services\MentorVideoService;
use Illuminate\Http\Request;

class MentorVideoController extends Controller
{
    public function __construct(private MentorVideoService $videos) {}

    public function index(Request $request)
    {
        $result = $this->videos->listForMentee(auth()->user(), [
            'search'    => $request->input('search'),
            'mentor_id' => $request->input('mentor_id'),
            'watched'   => $request->input('watched'),
        ], 12);

        $collections = $result['paginator']->through(function ($item) use ($result) {
            $item->formatted = $this->videos->format($item, $result['watched_file_ids']);

            return $item;
        });

        return view('frontend.mentee.mentor-videos.index', [
            'collections' => $collections,
            'summary'     => $result['summary'],
            'search'      => $request->input('search', ''),
            'watched'     => $request->input('watched', ''),
        ]);
    }

    public function show(int $video)
    {
        $item = $this->videos->findActiveForMentee($video);
        $watchedIds = $this->videos->watchedFileIds(auth()->user());
        $formatted = $this->videos->format($item, $watchedIds);

        return view('frontend.mentee.mentor-videos.show', [
            'item'      => $item,
            'formatted' => $formatted,
        ]);
    }

    public function markWatched(Request $request, int $file)
    {
        $this->videos->markWatched(auth()->user(), $file);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Marked as watched.', 'file_id' => $file]);
        }

        return back()->with('success', 'Marked as watched.');
    }
}
