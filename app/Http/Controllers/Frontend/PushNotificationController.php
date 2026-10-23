<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PushNotificationController extends Controller
{
    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, ['mentor', 'mentee'], true)) {
            return response()->json(['notifications' => [], 'unread_count' => 0, 'unreadCount' => 0], 403);
        }

        if (! Schema::hasTable('notifications')) {
            return response()->json(['notifications' => [], 'unread_count' => 0, 'unreadCount' => 0]);
        }

        $items = Notification::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(40)
            ->get()
            ->map(fn (Notification $n) => $n->toFeedArray())
            ->values();

        $unread = Notification::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $items,
            'unread_count'  => $unread,
            'unreadCount'   => $unread,
        ]);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $row = Notification::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
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
