<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Message;
use App\Models\MessageReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    public function store(Request $request, Channel $channel)
    {
        $user = Auth::user();

        abort_if(! $channel->canPost($user), 403);

        $request->validate(Message::mediaValidationRules());

        try {
            $attrs = Message::buildPostAttributes($request, $channel);
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        if (! $channel->isMember($user) && $channel->canSelfJoin($user)) {
            $channel->addMember($user);
        }

        Message::create([
            'channel_id' => $channel->id,
            'user_id'    => $user->id,
            'body'       => $attrs['body'],
            'image_path' => $attrs['image_path'],
            'video_path' => $attrs['video_path'],
            'parent_id'  => $attrs['parent_id'],
            'liked_by'   => [],
        ]);

        $channel->markRead($user);

        return redirect()->route($this->communityShowRoute(), $channel->slug);
    }

    public function older(Request $request, Channel $channel)
    {
        $user = Auth::user();
        abort_unless($channel->canAccess($user), 403);

        $data = $request->validate([
            'before_id' => 'required|integer|exists:messages,id',
            'per_page'  => 'nullable|integer|min:1|max:100',
        ]);

        $anchor = Message::query()
            ->where('channel_id', $channel->id)
            ->whereKey($data['before_id'])
            ->firstOrFail();

        $perPage = $data['per_page'] ?? 30;
        $chunk = $channel->olderMessagesForUser($user, $anchor, $perPage);
        $messages = $chunk['messages'];

        $html = '';
        if ($messages->isNotEmpty()) {
            $html = view('partials.community-messages-chunk', [
                'messages'    => $messages,
                'channel'     => $channel,
                'routePrefix' => $this->communityRoutePrefix(),
            ])->render();
        }

        return response()->json([
            'html'      => $html,
            'has_more'  => $chunk['has_more'],
            'oldest_id' => $messages->first()?->id,
            'count'     => $messages->count(),
        ]);
    }

    private function communityRoutePrefix(): string
    {
        if (request()->is('admin/*') || request()->is('admin')) {
            return 'admin.community';
        }

        if (request()->is('mentor/*')) {
            return 'mentor.community';
        }

        return 'mentee.community';
    }

    private function communityShowRoute(): string
    {
        if (request()->is('admin/*') || request()->is('admin')) {
            return 'admin.community.show';
        }

        if (request()->is('mentor/*')) {
            return 'mentor.community.show';
        }

        return 'mentee.community.show';
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

    public function report(Request $request, Message $message)
    {
        $user = Auth::user();
        $channel = $message->channel;

        abort_unless($channel && $channel->canAccess($user), 403);

        if ((int) $message->user_id === (int) $user->id) {
            return back()->with('error', 'You cannot report your own message.');
        }

        $data = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        MessageReport::firstOrCreate(
            [
                'message_id' => $message->id,
                'user_id'    => $user->id,
            ],
            [
                'reason' => $data['reason'] ?? null,
            ]
        );

        return back()->with('success', 'Message reported. It will no longer appear in your feed.');
    }
}
