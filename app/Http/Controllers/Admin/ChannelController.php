<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ChannelController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $channels = Channel::visibleTo($user)
            ->withCount(['allMessages', 'members'])
            ->with('creator:id,name')
            ->latest()
            ->get()
            ->map(function (Channel $ch) use ($user) {
                $ch->unread_count = $ch->isMember($user) ? $ch->unreadCountFor($user) : 0;
                return $ch;
            });

        return view('admin.community.index', compact('channels'));
    }

    public function create()
    {
        abort_if(Auth::user()->isMentee(), 403, 'Mentees cannot create channels.');
        $role = Auth::user()->role;

        if ($role === 'mentor') {
            return view('frontend.mentors.community-create', [
                'categories' => Channel::CATEGORIES,
            ]);
        }

        return view('admin.community.create', [
            'categories' => Channel::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        abort_if(Auth::user()->isMentee(), 403, 'Mentees cannot create channels.');

        if (! $request->filled('slug')) {
            $request->merge(['slug' => null]);
        }

        $request->validate([
            'name'        => 'required|string|max:100|unique:channels,name',
            'slug'        => 'nullable|string|max:120|unique:channels,slug|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:10',
            'type'        => 'required|in:public,private',
            'category'    => 'nullable|string|max:50',
            'youtube_url' => 'nullable|string|max:500',
            'video_url'   => 'nullable|string|max:500',
        ]);

        $channel = Channel::create([
            'name'        => $request->name,
            'slug'        => $request->slug,
            'description' => $request->description,
            'icon'        => $request->icon ?: '💬',
            'type'        => $request->type,
            'category'    => $request->category,
            'is_active'   => true,
            'created_by'  => Auth::id(),
        ]);

        $channel->members()->attach(Auth::id(), [
            'role'         => Channel::ROLE_ADMIN,
            'last_read_at' => now(),
        ]);

        $youtubeInput = Message::youtubeUrlFromInput($request->all());
        $initialVideoUrl = Message::resolveStoredYoutubeUrl($youtubeInput);

        if ($initialVideoUrl) {
            Message::create([
                'channel_id' => $channel->id,
                'user_id'    => (int) Auth::id(),
                'body'       => '',
                'video_path' => $initialVideoUrl,
                'parent_id'  => null,
                'liked_by'   => [],
            ]);
        }

        return redirect()->route($this->communityRouteBase() . '.show', $channel->slug)
            ->with('success', 'Channel created!');
    }

    public function show(Channel $channel)
    {
        $user = Auth::user();
        abort_unless($channel->canAccess($user), 403);

        if ($channel->isMember($user)) {
            $channel->markRead($user);
        }

        $messages = $channel->messages()
            ->with(['user', 'replies.user'])
            ->withCount('replies')
            ->paginate(30);

        $channels = Channel::visibleTo($user)
            ->get()
            ->map(function (Channel $ch) use ($user) {
                $ch->unread_count = $ch->isMember($user) ? $ch->unreadCountFor($user) : 0;
                return $ch;
            });

        $members = $channel->members()
            ->select('users.id', 'users.name', 'users.avatar_url', 'users.role')
            ->orderByPivot('role')
            ->get();

        $inviteCandidates = User::query()
            ->whereIn('role', ['mentor', 'mentee'])
            ->where('is_active', true)
            ->whereNotIn('id', $members->pluck('id'))
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'role']);

        return view('admin.community.show', compact('channel', 'messages', 'channels', 'members', 'inviteCandidates'));
    }

    public function join(Channel $channel)
    {
        abort_if($channel->type === Channel::TYPE_PRIVATE, 403, 'This is a private channel. Invite members instead.');

        $user = Auth::user();

        if ($channel->isRemoved($user)) {
            return back()->with('error', 'You were removed from this channel. A mentor must invite you again before you can rejoin.');
        }

        if (! $channel->isMember($user)) {
            $channel->addMember($user);
        }

        return back()->with('success', 'Joined channel!');
    }

    public function leave(Channel $channel)
    {
        $user = Auth::user();

        if ((int) $channel->created_by === (int) $user->id) {
            return back()->with('error', 'Channel creator cannot leave. Delete the channel instead.');
        }

        // Voluntary leave — do not block rejoining
        $channel->members()->detach($user->id);

        return redirect()->route($this->communityRouteBase() . '.index')->with('success', 'Left channel.');
    }

    public function invite(Request $request, Channel $channel)
    {
        $user = Auth::user();

        abort_unless(
            $channel->isAdmin($user) || (int) $channel->created_by === (int) $user->id || $user->isAdmin(),
            403
        );

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $invitee = User::findOrFail($data['user_id']);

        if (! in_array($invitee->role, ['mentor', 'mentee', 'admin'], true)) {
            return back()->with('error', 'Only mentors and mentees can be invited.');
        }

        $channel->addMember($invitee);

        return back()->with('success', $invitee->name . ' invited to #' . $channel->name);
    }

    public function removeMember(Channel $channel, User $user)
    {
        $actor = Auth::user();

        abort_unless(
            $channel->isAdmin($actor) || (int) $channel->created_by === (int) $actor->id || $actor->isAdmin(),
            403
        );

        if ((int) $channel->created_by === (int) $user->id) {
            return back()->with('error', 'Cannot remove the channel creator.');
        }

        $channel->removeMember($user, $actor);

        return back()->with('success', 'Member removed. They cannot rejoin until invited again.');
    }

    public function destroy(Channel $channel)
    {
        $user = Auth::user();
        abort_unless(
            (int) $channel->created_by === (int) $user->id || $user->isAdmin(),
            403
        );

        $channelName = $channel->name;
        $channelId   = $channel->id;

        DB::transaction(function () use ($channel) {
            // Clean image files (DB cascade would skip model events)
            Message::withTrashed()
                ->where('channel_id', $channel->id)
                ->get()
                ->each(function (Message $message) {
                    $message->deleteStoredImage();
                    $message->forceDelete();
                });

            $channel->members()->detach();
            $channel->removals()->delete();
            $channel->delete();
        });

        $uploadDir = public_path('upload/community/' . $channelId);
        if (is_dir($uploadDir)) {
            File::deleteDirectory($uploadDir);
        }

        return redirect()
            ->route($this->communityRouteBase() . '.index')
            ->with('success', 'Channel #' . $channelName . ' deleted.');
    }

    private function communityRouteBase(): string
    {
        $role = Auth::user()?->role;

        if ($role === 'admin') {
            return 'admin.community';
        }

        if ($role === 'mentor') {
            return 'mentor.community';
        }

        return 'mentee.community';
    }
}
