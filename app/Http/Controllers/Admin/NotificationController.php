<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MentorPendingChange;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $items = self::visibleNotifications();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'notifications' => $items,
                'unreadCount'   => count($items),
            ]);
        }

        return view('admin.notifications.index', [
            'notifications' => $items,
        ]);
    }

    public function markSeen(Request $request)
    {
        $request->session()->put('admin_notifications_seen_at', now()->toIso8601String());

        return response()->json([
            'status'  => 200,
            'message' => 'Notifications marked as seen.',
        ]);
    }

    public static function buildNotifications(): array
    {
        $items = [];

        $pendingMentors = User::mentors()
            ->where('mentor_status', User::MENTOR_STATUS_PENDING)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'name', 'email', 'created_at']);

        foreach ($pendingMentors as $mentor) {
            $items[] = [
                'id'          => 'mentor-pending-' . $mentor->id,
                'icon'        => '👨‍💼',
                'title'       => 'Mentor approval pending',
                'body'        => "{$mentor->name} is waiting for approval.",
                'url'         => route('admin.mentors.review', $mentor),
                'time'        => optional($mentor->created_at)->diffForHumans(),
                'at'          => optional($mentor->created_at)?->timestamp,
                'level'       => 'warning',
                'dismissable' => false,
            ];
        }

        $pendingChanges = MentorPendingChange::pending()
            ->with('mentor:id,name')
            ->latest()
            ->limit(5)
            ->get();

        foreach ($pendingChanges as $change) {
            $items[] = [
                'id'          => 'mentor-change-' . $change->id,
                'icon'        => '📝',
                'title'       => 'Profile change request',
                'body'        => ($change->mentor->name ?? 'A mentor') . ' submitted profile updates.',
                'url'         => route('admin.mentors.pending-changes'),
                'time'        => optional($change->created_at)->diffForHumans(),
                'at'          => optional($change->created_at)?->timestamp,
                'level'       => 'info',
                'dismissable' => false,
            ];
        }

        $adminIds = User::query()->where('role', 'admin')->pluck('id');
        $dismissedLogIds = collect(session('admin_dismissed_log_ids', []))->filter()->map(fn ($id) => (int) $id);

        $recentAlerts = ActivityLog::query()
            ->whereIn('level', ['warning', 'danger'])
            ->whereNotIn('event', ['login_failed', 'login', 'logout'])
            ->when($dismissedLogIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $dismissedLogIds))
            ->where(function ($q) use ($adminIds) {
                $q->whereNull('causer_id');
                if ($adminIds->isNotEmpty()) {
                    $q->orWhereNotIn('causer_id', $adminIds);
                }
            })
            ->latest('logged_at')
            ->limit(8)
            ->get(['id', 'event', 'module', 'level', 'description', 'causer_name', 'logged_at']);

        foreach ($recentAlerts as $log) {
            $items[] = [
                'id'          => 'log-' . $log->id,
                'icon'        => $log->level === 'danger' ? '🚨' : '⚠️',
                'title'       => ucfirst(str_replace('_', ' ', $log->event ?? 'System alert')),
                'body'        => $log->description,
                'url'         => route('admin.logs.show', $log),
                'time'        => optional($log->logged_at)->diffForHumans(),
                'at'          => optional($log->logged_at)?->timestamp,
                'level'       => $log->level,
                'dismissable' => true,
            ];
        }

        return array_slice($items, 0, 12);
    }

    public static function visibleNotifications(): array
    {
        $seenAt = session('admin_notifications_seen_at');
        $seenTs = $seenAt ? strtotime($seenAt) : null;

        return array_values(array_filter(self::buildNotifications(), function (array $item) use ($seenTs) {
            if (!($item['dismissable'] ?? false)) {
                return true;
            }

            if (!$seenTs || empty($item['at'])) {
                return true;
            }

            return (int) $item['at'] > $seenTs;
        }));
    }

    public static function dismissLog(int $logId): void
    {
        $ids = collect(session('admin_dismissed_log_ids', []))
            ->push($logId)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->slice(-200)
            ->values()
            ->all();

        session(['admin_dismissed_log_ids' => $ids]);
    }
}
