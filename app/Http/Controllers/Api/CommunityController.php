<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{CommunityChannel, CommunityMessage};
use Illuminate\Http\{Request, JsonResponse};


class CommunityController extends Controller
{
   
    public function channels(): JsonResponse
    {
        return response()->json([
            'channels' => CommunityChannel::where('is_active', true)->withCount('messages')->get(),
        ]);
    }

   
    public function messages(int $channelId): JsonResponse
    {
        return response()->json(
            CommunityMessage::where('channel_id', $channelId)
                ->with('user:id,name,avatar_url,role')
                ->orderByDesc('created_at')
                ->paginate(30)
        );
    }

    
    public function postMessage(Request $request, int $channelId): JsonResponse
    {
        $d = $request->validate(['message' => 'required|string|max:1000']);
        $m = CommunityMessage::create([
            'channel_id' => $channelId,
            'user_id'    => $request->user()->id,
            'message'    => $d['message'],
        ]);
        return response()->json(['message' => $m->load('user:id,name,avatar_url,role')], 201);
    }

   
    public function likeMessage(Request $request, int $msgId): JsonResponse
    {
        $m     = CommunityMessage::findOrFail($msgId);
        $uid   = $request->user()->id;
        $liked = collect($m->liked_by ?? []);
        if ($liked->contains($uid)) {
            $liked = $liked->filter(fn($id) => $id !== $uid);
            $m->decrement('likes');
        } else {
            $liked->push($uid);
            $m->increment('likes');
        }
        $m->update(['liked_by' => $liked->values()->all()]);
        return response()->json(['likes' => $m->fresh()->likes]);
    }
}
