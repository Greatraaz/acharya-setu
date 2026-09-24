<?php

namespace App\Services;

use App\Models\MentorVideo;
use App\Models\MentorVideoFile;
use App\Models\MentorVideoWatch;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class MentorVideoService
{
    public const VIDEO_MIMES = 'mp4,mov,avi,webm,mpeg';

    public const VIDEO_MAX_KB = 10240; // 10MB

    public function listForMentor(User $mentor, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return MentorVideo::query()
            ->where('mentor_id', $mentor->id)
            ->with('files')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
            ->when(array_key_exists('is_active', $filters) && $filters['is_active'] !== null && $filters['is_active'] !== '', function ($q) use ($filters) {
                $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function listForAdmin(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return MentorVideo::query()
            ->with(['files', 'mentor:id,name,email'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhereHas('mentor', fn ($m) => $m->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->when(! empty($filters['mentor_id']), fn ($q) => $q->where('mentor_id', (int) $filters['mentor_id']))
            ->when(array_key_exists('is_active', $filters) && $filters['is_active'] !== null && $filters['is_active'] !== '', function ($q) use ($filters) {
                $active = $filters['is_active'];
                if ($active === 'active') {
                    $active = true;
                } elseif ($active === 'inactive') {
                    $active = false;
                }
                $q->where('is_active', filter_var($active, FILTER_VALIDATE_BOOLEAN));
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array{paginator: LengthAwarePaginator, summary: array{total_files: int, watched: int, percent: int}}
     */
    public function listForMentee(User $mentee, array $filters = [], int $perPage = 20): array
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $watched = array_key_exists('watched', $filters) && $filters['watched'] !== null && $filters['watched'] !== ''
            ? filter_var($filters['watched'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        $watchedFileIds = MentorVideoWatch::where('mentee_id', $mentee->id)->pluck('mentor_video_file_id');

        $query = MentorVideo::where('is_active', true)
            ->with(['files', 'mentor:id,name,avatar_url'])
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
            ->when(! empty($filters['mentor_id']), fn ($q) => $q->where('mentor_id', (int) $filters['mentor_id']))
            ->latest();

        $allForSummary = (clone $query)->with('files')->get();
        $summaryVideos = $allForSummary->map(fn (MentorVideo $video) => $this->format($video, $watchedFileIds));
        $totalFiles = $summaryVideos->sum(fn ($v) => count($v['videos']));
        $watchedCount = $summaryVideos->sum(fn ($v) => collect($v['videos'])->where('is_watched', true)->count());

        if ($watched !== null) {
            $matchingIds = $summaryVideos
                ->filter(function (array $video) use ($watched) {
                    $files = collect($video['videos'] ?? []);
                    if ($files->isEmpty()) {
                        return ! $watched;
                    }

                    $allWatched = $files->every(fn ($f) => ! empty($f['is_watched']));
                    $anyWatched = $files->contains(fn ($f) => ! empty($f['is_watched']));

                    return $watched ? $anyWatched : ! $allWatched;
                })
                ->pluck('id')
                ->all();

            $query->whereIn('id', $matchingIds ?: [0]);
        }

        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'paginator' => $paginator,
            'watched_file_ids' => $watchedFileIds,
            'summary' => [
                'total_files' => $totalFiles,
                'watched'     => $watchedCount,
                'percent'     => $totalFiles ? (int) round($watchedCount / $totalFiles * 100) : 0,
            ],
        ];
    }

    public function findForMentor(User $mentor, int $id): MentorVideo
    {
        return MentorVideo::where('mentor_id', $mentor->id)
            ->with('files')
            ->findOrFail($id);
    }

    public function findForAdmin(int $id): MentorVideo
    {
        return MentorVideo::with(['files', 'mentor:id,name,email'])->findOrFail($id);
    }

    public function findActiveForMentee(int $id): MentorVideo
    {
        return MentorVideo::where('is_active', true)
            ->with(['files', 'mentor:id,name,avatar_url'])
            ->findOrFail($id);
    }

    /**
     * @param  array{name: string, description?: string|null, is_active?: bool, mentor_id?: int}  $data
     * @param  array<int, UploadedFile>  $files
     */
    public function create(User $owner, array $data, array $files): MentorVideo
    {
        if ($files === []) {
            throw new InvalidArgumentException('Please upload at least one video file.');
        }

        $mentorId = (int) ($data['mentor_id'] ?? $owner->id);

        return DB::transaction(function () use ($mentorId, $data, $files) {
            $mentorVideo = MentorVideo::create([
                'mentor_id'   => $mentorId,
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active'   => (bool) ($data['is_active'] ?? true),
            ]);

            $this->storeUploadedFiles($mentorVideo, $files);

            return $mentorVideo->load(['files', 'mentor:id,name,email']);
        });
    }

    /**
     * @param  array{name?: string, description?: string|null, is_active?: bool, mentor_id?: int, remove_video_ids?: array<int>}  $data
     * @param  array<int, UploadedFile>  $files
     */
    public function update(MentorVideo $mentorVideo, array $data, array $files = []): MentorVideo
    {
        return DB::transaction(function () use ($mentorVideo, $data, $files) {
            $updates = [];
            if (array_key_exists('name', $data)) {
                $updates['name'] = $data['name'];
            }
            if (array_key_exists('description', $data)) {
                $updates['description'] = $data['description'];
            }
            if (array_key_exists('is_active', $data)) {
                $updates['is_active'] = (bool) $data['is_active'];
            }
            if (array_key_exists('mentor_id', $data) && $data['mentor_id']) {
                $updates['mentor_id'] = (int) $data['mentor_id'];
            }
            if ($updates !== []) {
                $mentorVideo->update($updates);
            }

            if (! empty($data['remove_video_ids'])) {
                $filesToRemove = MentorVideoFile::where('mentor_video_id', $mentorVideo->id)
                    ->whereIn('id', $data['remove_video_ids'])
                    ->get();

                foreach ($filesToRemove as $file) {
                    $this->deleteStoredFile($file->video_url);
                    $file->delete();
                }
            }

            if ($files !== []) {
                $this->storeUploadedFiles($mentorVideo, $files);
            }

            $mentorVideo = $mentorVideo->fresh(['files', 'mentor:id,name,email']);

            if ($mentorVideo->files->isEmpty()) {
                throw new InvalidArgumentException('A collection must have at least one video file.');
            }

            return $mentorVideo;
        });
    }

    public function destroy(MentorVideo $mentorVideo): void
    {
        DB::transaction(function () use ($mentorVideo) {
            $mentorVideo->loadMissing('files');
            foreach ($mentorVideo->files as $file) {
                $this->deleteStoredFile($file->video_url);
            }
            $mentorVideo->delete();
        });
    }

    public function markWatched(User $mentee, int $fileId): MentorVideoFile
    {
        $videoFile = MentorVideoFile::where('id', $fileId)
            ->whereHas('mentorVideo', fn ($q) => $q->where('is_active', true))
            ->firstOrFail();

        MentorVideoWatch::updateOrCreate(
            [
                'mentee_id'            => $mentee->id,
                'mentor_video_file_id' => $videoFile->id,
            ],
            ['watched_at' => now()]
        );

        return $videoFile;
    }

    public function watchedFileIds(User $mentee): Collection
    {
        return MentorVideoWatch::where('mentee_id', $mentee->id)->pluck('mentor_video_file_id');
    }

    public function format(MentorVideo $video, $watchedFileIds = null): array
    {
        return [
            'id'          => $video->id,
            'name'        => $video->name,
            'description' => $video->description,
            'is_active'   => $video->is_active,
            'mentor_id'   => $video->mentor_id,
            'videos'      => $video->files->map(fn (MentorVideoFile $file) => [
                'id'         => $file->id,
                'video_url'  => $file->video_url,
                'file_name'  => $file->file_name,
                'sort_order' => $file->sort_order,
                'is_watched' => $watchedFileIds ? $watchedFileIds->contains($file->id) : false,
            ])->values()->all(),
            'created_at'  => $video->created_at,
            'updated_at'  => $video->updated_at,
        ];
    }

    /** @param  array<int, UploadedFile>  $files */
    public function storeUploadedFiles(MentorVideo $mentorVideo, array $files): void
    {
        $sortOrder = (int) $mentorVideo->files()->max('sort_order');

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('mentor-videos', 'public');
            $sortOrder++;

            MentorVideoFile::create([
                'mentor_video_id' => $mentorVideo->id,
                'video_url'       => url('/api/v1/media/mentor-videos/'.basename($path)),
                'file_name'       => $file->getClientOriginalName(),
                'sort_order'      => $sortOrder,
            ]);
        }
    }

    public function deleteStoredFile(string $videoUrl): void
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
            $mediaPath = 'mentor-videos/'.$matches[1];
            if (Storage::disk('public')->exists($mediaPath)) {
                Storage::disk('public')->delete($mediaPath);
            }
        }
    }
}
