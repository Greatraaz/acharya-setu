<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use Illuminate\Support\Facades\Auth;

class CommunityController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $channels = Channel::query()
            ->where(function ($q) use ($user) {
                $q->where('type', Channel::TYPE_PUBLIC)
                    ->orWhereHas('members', fn ($m) => $m->where('user_id', $user->id));
            })
            ->withCount(['allMessages', 'members'])
            ->with('creator:id,name')
            ->latest()
            ->get()
            ->map(function (Channel $ch) use ($user) {
                $ch->unread_count = $ch->isMember($user) ? $ch->unreadCountFor($user) : 0;
                return $ch;
            });

        return view('frontend.mentee.community', compact('channels'));
    }

    public function show(Channel $channel)
    {
        $user = Auth::user();
        abort_unless($channel->canAccess($user), 403);

        if ($channel->isMember($user)) {
            $channel->markRead($user);
        }

        $messages = $channel->messagesForUser($user)
            ->latest()
            ->paginate(30);

        return view('frontend.mentee.community-show', compact('channel', 'messages'));
    }

    public function join(Channel $channel)
    {
        $user = Auth::user();

        abort_if($channel->type === Channel::TYPE_PRIVATE, 403, 'This is a private channel.');

        if ($channel->isRemoved($user)) {
            return back()->with('error', 'You were removed from this channel. Ask a mentor to invite you again.');
        }

        if (! $channel->isMember($user)) {
            $channel->addMember($user, Channel::ROLE_MENTEE);
        }

        return redirect()
            ->route('mentee.community.show', $channel->slug)
            ->with('success', 'Joined channel.');
    }

    public function leave(Channel $channel)
    {
        $user = Auth::user();

        if ((int) $channel->created_by === (int) $user->id) {
            return back()->with('error', 'Channel creator cannot leave.');
        }

        $channel->members()->detach($user->id);

        return redirect()
            ->route('mentee.community.index')
            ->with('success', 'Left channel.');
    }
}
