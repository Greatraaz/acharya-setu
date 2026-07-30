<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(Request $request, Channel $channel)
    {
        $user = Auth::user();

        abort_if(! $channel->canPost($user), 403);

        $request->validate([
            'body'      => 'nullable|string|max:5000',
            'parent_id' => 'nullable|exists:messages,id',
            'image'     => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ]);

        $body = trim((string) $request->input('body', ''));
        $hasImage = $request->hasFile('image');

        if ($body === '' && ! $hasImage) {
            return back()->withErrors(['body' => 'Message text or image is required.']);
        }

        if (! $channel->isMember($user) && $channel->canSelfJoin($user)) {
            $channel->addMember($user);
        }

        $imagePath = null;
        if ($hasImage) {
            $imagePath = Message::storeUploadedImage($request->file('image'), $channel->id);
        }

        Message::create([
            'channel_id' => $channel->id,
            'user_id'    => $user->id,
            'body'       => $body,
            'image_path' => $imagePath,
            'parent_id'  => $request->parent_id,
            'liked_by'   => [],
        ]);

        $channel->markRead($user);

        return back();
    }

    public function like(Message $message)
    {
        $user = Auth::user();
        $channel = $message->channel;

        abort_unless($channel && $channel->canAccess($user), 403);

        $message->toggleLike($user->id);

        return back();
    }

    public function destroy(Message $message)
    {
        $user = Auth::user();
        $channel = $message->channel;

        $allowed = (int) $message->user_id === (int) $user->id
            || $user->isAdmin()
            || ($channel && $channel->isAdmin($user));

        abort_unless($allowed, 403);

        $message->delete();

        return back()->with('success', 'Message deleted.');
    }
}
