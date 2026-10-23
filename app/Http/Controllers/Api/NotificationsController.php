<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Schema;

class NotificationsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! Schema::hasTable('notifications')) {
            return response()->json(['notifications' => [], 'unreadCount' => 0, 'unread_count' => 0]);
        }

        $userId = $request->user()->id;

        $n = Notification::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (Notification $row) => $row->toFeedArray())
            ->values();

        $unread = Notification::query()
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $n,
            'unreadCount'   => $unread,
            'unread_count'  => $unread,
        ]);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $row = Notification::query()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($row) {
            $row->markRead();
        }

        return response()->json(['message' => 'Marked read']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['message' => 'All marked read']);
    }
}
