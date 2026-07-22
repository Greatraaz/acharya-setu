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
        $items = self::buildNotifications();

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
                'id'    => 'mentor-pending-' . $mentor->id,
                'icon'  => '👨‍💼',
                'title' => 'Mentor approval pending',
                'body'  => "{$mentor->name} is waiting for approval.",
                'url'   => route('admin.mentors.review', $mentor),
                'time'  => optional($mentor->created_at)->diffForHumans(),
                'level' => 'warning',
            ];
        }

        $pendingChanges = MentorPendingChange::pending()
            ->with('mentor:id,name')
            ->latest()
            ->limit(5)
            ->get();

        foreach ($pendingChanges as $change) {
            $items[] = [
                'id'    => 'mentor-change-' . $change->id,
                'icon'  => '📝',
                'title' => 'Profile change request',
                'body'  => ($change->mentor->name ?? 'A mentor') . ' submitted profile updates.',
                'url'   => route('admin.mentors.pending-changes'),
                'time'  => optional($change->created_at)->diffForHumans(),
                'level' => 'info',
            ];
        }

        $recentAlerts = ActivityLog::query()
            ->whereIn('level', ['warning', 'danger'])
            ->latest('logged_at')
            ->limit(8)
            ->get(['id', 'event', 'module', 'level', 'description', 'causer_name', 'logged_at']);

        foreach ($recentAlerts as $log) {
            $items[] = [
                'id'    => 'log-' . $log->id,
                'icon'  => $log->level === 'danger' ? '🚨' : '⚠️',
                'title' => ucfirst(str_replace('_', ' ', $log->event ?? 'System alert')),
                'body'  => $log->description,
                'url'   => route('admin.logs.show', $log),
                'time'  => optional($log->logged_at)->diffForHumans(),
                'level' => $log->level,
            ];
        }

        return array_slice($items, 0, 12);
    }
}
