<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\{Request, JsonResponse};

class NotificationsController extends Controller
{
   
    public function index(Request $request): JsonResponse
    {
        $n = Notification::where('user_id', $request->user()->id)->orderByDesc('created_at')->limit(50)->get();
        return response()->json(['notifications' => $n, 'unreadCount' => $n->where('is_read', false)->count()]);
    }

    
    public function markRead(Request $request, int $id): JsonResponse
    {
        Notification::where('id', $id)->where('user_id', $request->user()->id)
            ->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['message' => 'Marked read']);
    }

    
    public function markAllRead(Request $request): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['message' => 'All marked read']);
    }
}
