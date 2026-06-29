<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoCallLog;
use App\Models\VideoCallParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VideoCallLogController extends Controller
{
    // ── Admin list view ───────────────────────────────────────

    public function index(Request $request)
    {
        $query = VideoCallLog::with(['host', 'participant'])
            ->latest('started_at');

        // Filters
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('channel_name', 'like', "%{$search}%")
                  ->orWhere('session_id', 'like', "%{$search}%")
                  ->orWhereHas('host', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('participant', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }
        if ($status   = $request->status)   $query->where('status', $status);
        if ($provider = $request->provider) $query->where('provider', $provider);
        if ($from     = $request->date_from) $query->whereDate('started_at', '>=', $from);
        if ($to       = $request->date_to)   $query->whereDate('started_at', '<=', $to);
        if ($userId   = $request->user_id)  $query->forUser($userId);

        $logs = $query->paginate(20)->withQueryString();

        // Summary stats for the current filter
        $stats = $this->getStats($request);

        return view('admin.call-logs.index', compact('logs', 'stats'));
    }

    public function show(VideoCallLog $videoCallLog)
    {
        $videoCallLog->load(['host', 'participant', 'participants.user']);
        return view('admin.call-logs.show', compact('videoCallLog'));
    }

    public function destroy(VideoCallLog $videoCallLog)
    {
        $videoCallLog->delete();
        return redirect()->route('admin.call-logs.index')
            ->with('success', 'Call log deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        VideoCallLog::whereIn('id', $request->ids)->delete();
        return redirect()->back()->with('success', count($request->ids) . ' logs deleted.');
    }

    // ── API endpoints (called from your video SDK integration) ─

    /**
     * POST /api/calls/start
     * Called when host initiates a call.
     */
    public function apiStart(Request $request): JsonResponse
    {
        $request->validate([
            'host_id'      => 'required|exists:users,id',
            'participant_id'=> 'nullable|exists:users,id',
            'channel_name' => 'required|string|max:200',
            'provider'     => 'required|in:agora,zoom,google',
            'call_type'    => 'nullable|in:video,audio,screen',
            'booking_id'   => 'nullable|integer',
            'session_id'   => 'nullable|string',
            'meta'         => 'nullable|array',
        ]);

        $log = VideoCallLog::create([
            'host_id'       => $request->host_id,
            'participant_id'=> $request->participant_id,
            'channel_name'  => $request->channel_name,
            'session_id'    => $request->session_id,
            'provider'      => $request->provider,
            'call_type'     => $request->call_type ?? 'video',
            'booking_id'    => $request->booking_id,
            'status'        => VideoCallLog::STATUS_INITIATED,
            'meta'          => $request->meta,
        ]);

        // Add host as first participant
        $log->participants()->create([
            'user_id'      => $request->host_id,
            'role'         => 'host',
            'joined_at'    => now(),
            'display_name' => optional(User::find($request->host_id))->name,
        ]);

        return response()->json([
            'success'       => true,
            'call_log_id'   => $log->id,
            'channel_name'  => $log->channel_name,
        ], 201);
    }

    /**
     * POST /api/calls/{id}/join
     * Called when a participant joins.
     */
    public function apiJoin(Request $request, VideoCallLog $videoCallLog): JsonResponse
    {
        $request->validate([
            'user_id'        => 'nullable|exists:users,id',
            'display_name'   => 'nullable|string|max:100',
            'role'           => 'nullable|in:host,participant,observer',
            'mic_enabled'    => 'nullable|boolean',
            'camera_enabled' => 'nullable|boolean',
        ]);

        // Mark ongoing on first join if still initiated
        if ($videoCallLog->status === VideoCallLog::STATUS_INITIATED) {
            $videoCallLog->markStarted();
        }

        $participant = $videoCallLog->participants()->create([
            'user_id'        => $request->user_id,
            'display_name'   => $request->display_name ?? optional(User::find($request->user_id))->name,
            'role'           => $request->role ?? 'participant',
            'joined_at'      => now(),
            'mic_enabled'    => $request->mic_enabled ?? true,
            'camera_enabled' => $request->camera_enabled ?? true,
        ]);

        return response()->json(['success' => true, 'participant_id' => $participant->id]);
    }

    /**
     * POST /api/calls/{id}/leave
     * Called when a participant leaves.
     */
    public function apiLeave(Request $request, VideoCallLog $videoCallLog): JsonResponse
    {
        $request->validate(['user_id' => 'required|integer']);

        $participant = $videoCallLog->participants()
            ->where('user_id', $request->user_id)
            ->whereNull('left_at')
            ->latest()
            ->first();

        if ($participant) {
            $participant->markLeft();
        }

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/calls/{id}/end
     * Called when host ends the call.
     */
    public function apiEnd(Request $request, VideoCallLog $videoCallLog): JsonResponse
    {
        $request->validate([
            'end_reason'    => 'nullable|string|in:normal,host_left,timeout,error,cancelled',
            'recording_url' => 'nullable|url',
            'recording_size_kb' => 'nullable|integer',
            'is_recorded'   => 'nullable|boolean',
            'meta'          => 'nullable|array',
        ]);

        $videoCallLog->markEnded($request->end_reason ?? 'normal');

        if ($request->recording_url) {
            $videoCallLog->update([
                'is_recorded'       => true,
                'recording_url'     => $request->recording_url,
                'recording_size_kb' => $request->recording_size_kb,
            ]);
        }

        if ($request->meta) {
            $videoCallLog->update(['meta' => array_merge($videoCallLog->meta ?? [], $request->meta)]);
        }

        return response()->json([
            'success'          => true,
            'duration_seconds' => $videoCallLog->fresh()->duration_seconds,
            'duration_formatted' => $videoCallLog->fresh()->duration_formatted,
        ]);
    }

    /**
     * POST /api/calls/{id}/rate
     * Allow host or participant to leave a rating.
     */
    public function apiRate(Request $request, VideoCallLog $videoCallLog): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer',
            'rating'  => 'required|integer|between:1,5',
        ]);

        $field = $videoCallLog->host_id === $request->user_id ? 'host_rating' : 'participant_rating';
        $videoCallLog->update([$field => $request->rating]);

        return response()->json(['success' => true]);
    }

    // ── Stats helper ──────────────────────────────────────────

    private function getStats(Request $request): array
    {
        $base = VideoCallLog::query()
            ->when($request->date_from, fn($q) => $q->whereDate('started_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('started_at', '<=', $request->date_to))
            ->when($request->provider,  fn($q) => $q->where('provider', $request->provider));

        return [
            'total'        => $base->clone()->count(),
            'completed'    => $base->clone()->where('status', 'completed')->count(),
            'ongoing'      => $base->clone()->where('status', 'ongoing')->count(),
            'missed'       => $base->clone()->whereIn('status', ['missed', 'failed'])->count(),
            'total_minutes'=> (int) ($base->clone()->where('status', 'completed')->sum('duration_seconds') / 60),
            'avg_duration' => (int) ($base->clone()->where('status', 'completed')->avg('duration_seconds') ?? 0),
            'by_provider'  => $base->clone()->select('provider', DB::raw('count(*) as count'))
                                ->groupBy('provider')->pluck('count', 'provider')->toArray(),
        ];
    }

    // ── Export ────────────────────────────────────────────────

    public function export(Request $request)
    {
        $logs = VideoCallLog::with(['host', 'participant'])
            ->when($request->date_from, fn($q) => $q->whereDate('started_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('started_at', '<=', $request->date_to))
            ->when($request->status,    fn($q) => $q->where('status', $request->status))
            ->when($request->provider,  fn($q) => $q->where('provider', $request->provider))
            ->latest('started_at')
            ->get();

        $csv = "ID,Host,Participant,Provider,Channel,Status,Started At,Ended At,Duration,Recorded\n";
        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log->id,
                '"' . ($log->host->name ?? '-') . '"',
                '"' . ($log->participant->name ?? '-') . '"',
                $log->provider_label,
                $log->channel_name,
                $log->status,
                $log->started_at?->format('Y-m-d H:i:s') ?? '-',
                $log->ended_at?->format('Y-m-d H:i:s') ?? '-',
                $log->duration_formatted,
                $log->is_recorded ? 'Yes' : 'No',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="video_call_logs_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }
}
