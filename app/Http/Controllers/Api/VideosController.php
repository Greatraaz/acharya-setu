<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Video, VideoAssignment};
use Illuminate\Http\{Request, JsonResponse};


class VideosController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $u      = $request->user();
        $q      = Video::where('is_active', true);
        if ($cat = $request->category) $q->where('category', $cat);
        $videos = $q->latest()->get()->map(function ($v) use ($u) {
            $w = VideoAssignment::where('video_id', $v->id)->where('mentee_id', $u->id)->where('watched', true)->exists();
            return array_merge($v->toArray(), [
                'mentorName'   => $v->mentor?->name,
                'mentorAvatar' => $v->mentor?->avatar_url,
                'watched'      => $w,
            ]);
        });
        return response()->json(['status' => true, 'success' => true, 'videos' => $videos]);
    }

    public function singleVideo($id): JsonResponse
    {
        $video = Video::where('is_active', true)->find($id);

        if (!$video) {
            return response()->json([
                'status'     => false,
                'success'    => false,
                'message'    => 'Video not found',
            ], 404);
        }

        return response()->json([
            'status'     => true,
            'success'    => true,
            'video'      => $video,
        ], 201);
    }

   
    public function store(Request $request): JsonResponse
    {
        $d = $request->validate([
            'title'         => 'required|string',
            'description'   => 'nullable|string',
            'video_url'     => 'required|string',
            'thumbnail_url' => 'nullable|string',
            'category'      => 'required|string',
            'duration'      => 'nullable|string',
            'is_premium'    => 'sometimes|boolean',
        ]);
        return response()->json([
            'video' => Video::create(array_merge($d, ['mentor_id' => $request->user()->id])),
        ], 201);
    }

    public function markWatched(Request $request, int $id): JsonResponse
    {
        VideoAssignment::updateOrCreate(
            ['video_id' => $id, 'mentee_id' => $request->user()->id],
            ['watched' => true, 'watched_at' => now()]
        );
        Video::find($id)?->increment('views');
        return response()->json(['message' => 'Marked as watched']);
    }
}
