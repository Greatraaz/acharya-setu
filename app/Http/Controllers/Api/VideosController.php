<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MentorVideo;
use App\Models\MentorVideoFile;
use App\Models\{Video, VideoAssignment};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VideosController extends Controller
{
    private const VIDEO_MIMES = 'mp4,mov,avi,webm,mpeg';
    private const VIDEO_MAX_KB  = 10240; // 10MB

    // ── Mentee: browse & watch ───────────────────────────────────────

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

    public function menteeMentorVideos(Request $request): JsonResponse
    {
        $videos = MentorVideo::where('is_active', true)
            ->with(['files', 'mentor:id,name,avatar_url'])
            ->latest()
            ->get()
            ->map(fn (MentorVideo $video) => array_merge($this->formatMentorVideo($video), [
                'mentor' => $video->mentor ? [
                    'id'         => $video->mentor->id,
                    'name'       => $video->mentor->name,
                    'avatar_url' => $video->mentor->avatar_url,
                ] : null,
            ]));

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'videos'     => $videos,
            'total'      => $videos->count(),
        ]);
    }

    // ── Mentor: CRUD (name, description, videos[]) ───────────────────

    public function mentorIndex(Request $request): JsonResponse
    {
        $videos = MentorVideo::where('mentor_id', $request->user()->id)
            ->with('files')
            ->latest()
            ->get()
            ->map(fn (MentorVideo $video) => $this->formatMentorVideo($video));

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'videos'     => $videos,
            'total'      => $videos->count(),
        ], 200);
    }

    public function mentorStore(Request $request): JsonResponse
    {
        $d = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'videos'      => 'required|array|min:1',
            'videos.*'    => 'required|file|mimes:' . self::VIDEO_MIMES . '|max:' . self::VIDEO_MAX_KB,
        ]);

        $mentorVideo = DB::transaction(function () use ($request, $d) {
            $mentorVideo = MentorVideo::create([
                'mentor_id'   => $request->user()->id,
                'name'        => $d['name'],
                'description' => $d['description'] ?? null,
                'is_active'   => $request->boolean('is_active', true),
            ]);

            $this->storeUploadedFiles($mentorVideo, $request->file('videos'));

            return $mentorVideo->load('files');
        });

        return response()->json([
            'status'     => true,
            'statuscode' => 201,
            'message'    => 'Video collection created successfully.',
            'video'      => $this->formatMentorVideo($mentorVideo),
        ], 201);
    }

    public function mentorUpdate(Request $request, int $id): JsonResponse
    {
        $mentorVideo = $this->findOwnedMentorVideo($request, $id);

        $d = $request->validate([
            'name'               => 'sometimes|required|string|max:255',
            'description'        => 'nullable|string',
            'videos'             => 'sometimes|array|min:1',
            'videos.*'           => 'required_with:videos|file|mimes:' . self::VIDEO_MIMES . '|max:' . self::VIDEO_MAX_KB,
            'remove_video_ids'   => 'sometimes|array',
            'remove_video_ids.*' => 'integer|exists:mentor_video_files,id',
        ]);

        $mentorVideo = DB::transaction(function () use ($request, $mentorVideo, $d) {
            $updates = [];
            if (array_key_exists('name', $d)) {
                $updates['name'] = $d['name'];
            }
            if (array_key_exists('description', $d)) {
                $updates['description'] = $d['description'];
            }
            if ($request->has('is_active')) {
                $updates['is_active'] = $request->boolean('is_active');
            }
            if ($updates !== []) {
                $mentorVideo->update($updates);
            }

            if (! empty($d['remove_video_ids'])) {
                $filesToRemove = MentorVideoFile::where('mentor_video_id', $mentorVideo->id)
                    ->whereIn('id', $d['remove_video_ids'])
                    ->get();

                foreach ($filesToRemove as $file) {
                    $this->deleteStoredFile($file->video_url);
                    $file->delete();
                }
            }

            if ($request->hasFile('videos')) {
                $this->storeUploadedFiles($mentorVideo, $request->file('videos'));
            }

            return $mentorVideo->fresh('files');
        });

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Video collection updated successfully.',
            'video'      => $this->formatMentorVideo($mentorVideo),
        ], 200);
    }

    public function mentorDestroy(Request $request, int $id): JsonResponse
    {
        $mentorVideo = $this->findOwnedMentorVideo($request, $id);

        DB::transaction(function () use ($mentorVideo) {
            foreach ($mentorVideo->files as $file) {
                $this->deleteStoredFile($file->video_url);
            }

            $mentorVideo->delete();
        });

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Video collection deleted successfully.',
        ], 200);
    }

    public function serveMentorVideoFile(string $filename)
    {
        $filename = basename($filename);
        $path     = 'mentor-videos/' . $filename;

        if (! Storage::disk('public')->exists($path)) {
            abort(404, 'Video not found.');
        }

        return Storage::disk('public')->response($path, $filename, [
            'Content-Type' => Storage::disk('public')->mimeType($path),
        ]);
    }

    private function findOwnedMentorVideo(Request $request, int $id): MentorVideo
    {
        return MentorVideo::where('mentor_id', $request->user()->id)
            ->with('files')
            ->findOrFail($id);
    }

    private function storeUploadedFiles(MentorVideo $mentorVideo, array $files): void
    {
        $sortOrder = (int) $mentorVideo->files()->max('sort_order');

        foreach ($files as $file) {
            $path = $file->store('mentor-videos', 'public');
            $sortOrder++;

            MentorVideoFile::create([
                'mentor_video_id' => $mentorVideo->id,
                'video_url'       => url('/api/v1/media/mentor-videos/' . basename($path)),
                'file_name'       => $file->getClientOriginalName(),
                'sort_order'      => $sortOrder,
            ]);
        }
    }

    private function deleteStoredFile(string $videoUrl): void
    {
        $path = parse_url($videoUrl, PHP_URL_PATH);
        if (! $path) {
            return;
        }

        $storagePath = ltrim(str_replace('/storage/', '', $path), '/');
        if ($storagePath && Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->delete($storagePath);
            return;
        }

        if (preg_match('#/media/mentor-videos/([^/]+)$#', $path, $matches)) {
            $mediaPath = 'mentor-videos/' . $matches[1];
            if (Storage::disk('public')->exists($mediaPath)) {
                Storage::disk('public')->delete($mediaPath);
            }
        }
    }

    private function formatMentorVideo(MentorVideo $video): array
    {
        return [
            'id'          => $video->id,
            'name'        => $video->name,
            'description' => $video->description,
            'is_active'   => $video->is_active,
            'videos'      => $video->files->map(fn (MentorVideoFile $file) => [
                'id'         => $file->id,
                'video_url'  => $file->video_url,
                'file_name'  => $file->file_name,
                'sort_order' => $file->sort_order,
            ])->values(),
            'created_at'  => $video->created_at,
            'updated_at'  => $video->updated_at,
        ];
    }
}
