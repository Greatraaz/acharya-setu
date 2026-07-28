<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    /**
     * Create a public or private channel.
     * Creator becomes channel admin and can invite members.
     */
    public function createChannel(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isMentee()) {
            return response()->json([
                'message' => 'Mentees cannot create channels. Ask a mentor or admin.',
            ], 403);
        }

        if (! $request->filled('slug')) {
            $request->merge(['slug' => null]);
        }

        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:channels,name',
            'slug'        => 'nullable|string|max:120|unique:channels,slug|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:10',
            'type'        => 'required|in:public,private',
            'category'    => 'nullable|string|max:50',
        ]);

        $channel = Channel::create([
            'name'        => $data['name'],
            'slug'        => $data['slug'] ?? null,
            'description' => $data['description'] ?? null,
            'icon'        => $data['icon'] ?? '💬',
            'type'        => $data['type'],
            'category'    => $data['category'] ?? null,
            'is_active'   => true,
            'created_by'  => $user->id,
        ]);

        // Channel-level admin so mentor creators can invite & manage members
        $channel->members()->attach($user->id, [
            'role'         => Channel::ROLE_ADMIN,
            'last_read_at' => now(),
        ]);

        return response()->json([
            'message' => 'Channel created.',
            'channel' => $channel->load(['creator:id,name,avatar_url,role'])
                ->loadCount(['allMessages', 'members'])
                ->toApiArray($user),
        ], 201);
    }

    /**
     * List channels visible to the authenticated mentor/mentee.
     * Public channels + private channels the user belongs to.
     */
    public function channels(Request $request): JsonResponse
    {
        $user = $request->user();

        $channels = Channel::visibleTo($user)
            ->withCount(['allMessages', 'members'])
            ->with('creator:id,name,avatar_url,role')
            ->latest()
            ->get()
            ->map(fn (Channel $c) => $c->toApiArray($user));

        return response()->json([
            'channels'   => $channels,
            'categories' => Channel::CATEGORIES,
        ]);
    }

    /**
     * Channel detail + membership info.
     */
    public function showChannel(Request $request, int $channelId): JsonResponse
    {
        $user    = $request->user();
        $channel = Channel::withCount(['allMessages', 'members'])
            ->with('creator:id,name,avatar_url,role')
            ->findOrFail($channelId);

        abort_unless($channel->canAccess($user), 403, 'You do not have access to this channel.');

        return response()->json([
            'channel' => $channel->toApiArray($user),
        ]);
    }

    /**
     * Join a public channel (role = mentor|mentee based on user).
     */
    public function join(Request $request, int $channelId): JsonResponse
    {
        $user    = $request->user();
        $channel = Channel::active()->findOrFail($channelId);

        if ($channel->type === Channel::TYPE_PRIVATE) {
            return response()->json([
                'message' => 'This is a private channel. Ask an admin or channel admin to invite you.',
            ], 403);
        }

        if ($channel->isRemoved($user)) {
            return response()->json([
                'message' => 'You were removed from this channel. A mentor must invite you again before you can rejoin.',
            ], 403);
        }

        if (! $channel->isMember($user)) {
            $channel->addMember($user);
        }

        return response()->json([
            'message' => 'Joined channel.',
            'channel' => $channel->fresh()
                ->load(['creator:id,name,avatar_url,role'])
                ->loadCount(['allMessages', 'members'])
                ->toApiArray($user),
        ]);
    }

    /**
     * Leave a channel (creators/admins can leave non-owned channels only if not sole admin — keep simple).
     */
    public function leave(Request $request, int $channelId): JsonResponse
    {
        $user    = $request->user();
        $channel = Channel::findOrFail($channelId);

        if (! $channel->isMember($user)) {
            return response()->json(['message' => 'You are not a member of this channel.'], 422);
        }

        if ((int) $channel->created_by === (int) $user->id) {
            return response()->json(['message' => 'Channel creator cannot leave. Delete the channel instead.'], 422);
        }

        $channel->members()->detach($user->id);

        return response()->json(['message' => 'Left channel.']);
    }

    /**
     * List members (mentors + mentees) in a channel.
     */
    public function members(Request $request, int $channelId): JsonResponse
    {
        $user    = $request->user();
        $channel = Channel::findOrFail($channelId);

        abort_unless($channel->canAccess($user), 403);

        $members = $channel->members()
            ->select('users.id', 'users.name', 'users.avatar_url', 'users.role')
            ->orderByPivot('role')
            ->get()
            ->map(fn (User $m) => [
                'id'         => $m->id,
                'name'       => $m->name,
                'avatar_url' => $m->avatar_url,
                'role'       => $m->role,
                'channel_role' => $m->pivot->role,
                'joined_at'  => $m->pivot->created_at,
            ]);

        return response()->json([
            'members'        => $members,
            'mentors_count'  => $members->where('channel_role', Channel::ROLE_MENTOR)->count()
                + $members->where('channel_role', Channel::ROLE_ADMIN)->count(),
            'mentees_count'  => $members->where('channel_role', Channel::ROLE_MENTEE)->count(),
        ]);
    }

    /**
     * Invite a user to the channel (clears any previous removal so they can rejoin).
     */
    public function invite(Request $request, int $channelId): JsonResponse
    {
        $user    = $request->user();
        $channel = Channel::findOrFail($channelId);

        abort_unless(
            $channel->isAdmin($user) || (int) $channel->created_by === (int) $user->id || $user->isAdmin(),
            403,
            'Only channel admins can invite members.'
        );

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $invitee = User::findOrFail($data['user_id']);

        if (! in_array($invitee->role, ['mentor', 'mentee', 'admin'], true)) {
            return response()->json(['message' => 'Only mentors and mentees can join community channels.'], 422);
        }

        $channel->addMember($invitee);

        return response()->json([
            'message' => 'Member invited.',
            'member'  => [
                'id'           => $invitee->id,
                'name'         => $invitee->name,
                'role'         => $invitee->role,
                'channel_role' => Channel::roleForUser($invitee),
            ],
        ], 201);
    }

    /**
     * Remove a member. Removed mentees cannot self-rejoin until invited again.
     */
    public function removeMember(Request $request, int $channelId, int $userId): JsonResponse
    {
        $user    = $request->user();
        $channel = Channel::findOrFail($channelId);

        abort_unless(
            $channel->isAdmin($user) || (int) $channel->created_by === (int) $user->id || $user->isAdmin(),
            403
        );

        if ((int) $channel->created_by === (int) $userId) {
            return response()->json(['message' => 'Cannot remove the channel creator.'], 422);
        }

        $target = User::findOrFail($userId);
        $channel->removeMember($target, $user);

        return response()->json(['message' => 'Member removed. They cannot rejoin until invited again.']);
    }

    /**
     * Paginated top-level messages with replies.
     */
    public function messages(Request $request, int $channelId): JsonResponse
    {
        $user    = $request->user();
        $channel = Channel::findOrFail($channelId);

        abort_unless($channel->canAccess($user), 403);

        // Auto-join public channels when reading (unless previously removed)
        if ($channel->type === Channel::TYPE_PUBLIC && ! $channel->isMember($user) && $channel->canSelfJoin($user)) {
            $channel->addMember($user);
        }

        $paginator = $channel->messages()
            ->with(['user:id,name,avatar_url,role', 'replies.user:id,name,avatar_url,role'])
            ->withCount('replies')
            ->paginate(30);

        $channel->markRead($user);

        $items = collect($paginator->items())->map(
            fn (Message $m) => $m->toApiArray($user->id)
        );

        return response()->json([
            'channel'  => $channel->toApiArray($user),
            'messages' => $items,
            'meta'     => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    /**
     * Post a message or thread reply.
     * Accepts JSON or multipart form-data with optional `image` file.
     * Fields: body|message (text), parent_id (reply), image (jpeg/png/webp/gif, max 5MB).
     * At least body or image is required.
     */
    public function postMessage(Request $request, int $channelId): JsonResponse
    {
        $user    = $request->user();
        $channel = Channel::active()->findOrFail($channelId);

        abort_unless($channel->canPost($user), 403, $channel->isRemoved($user)
            ? 'You were removed from this channel. A mentor must invite you again before you can post.'
            : 'You cannot post in this channel.');

        $data = $request->validate([
            'body'      => 'nullable|string|max:5000',
            'message'   => 'nullable|string|max:5000',
            'parent_id' => 'nullable|exists:messages,id',
            'image'     => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ]);

        $body = trim((string) ($data['body'] ?? $data['message'] ?? ''));
        $hasImage = $request->hasFile('image');

        if ($body === '' && ! $hasImage) {
            return response()->json([
                'message' => 'Provide a message body and/or an image.',
                'errors'  => ['body' => ['Message text or image is required.']],
            ], 422);
        }

        if (! empty($data['parent_id'])) {
            $parent = Message::where('channel_id', $channel->id)->findOrFail($data['parent_id']);
            if ($parent->parent_id) {
                return response()->json(['message' => 'Cannot reply to a reply. Reply to the parent message.'], 422);
            }
        }

        if (! $channel->isMember($user) && $channel->canSelfJoin($user)) {
            $channel->addMember($user);
        }

        $imagePath = null;
        if ($hasImage) {
            $imagePath = $request->file('image')->store(
                'community/' . $channel->id,
                'public'
            );
        }

        $message = Message::create([
            'channel_id' => $channel->id,
            'user_id'    => $user->id,
            'body'       => $body !== '' ? $body : '',
            'image_path' => $imagePath,
            'parent_id'  => $data['parent_id'] ?? null,
            'liked_by'   => [],
        ]);

        $channel->markRead($user);

        return response()->json([
            'message' => $message->load('user:id,name,avatar_url,role')->toApiArray($user->id),
        ], 201);
    }

    /**
     * Toggle like / reaction on a message.
     */
    public function likeMessage(Request $request, int $msgId): JsonResponse
    {
        $user    = $request->user();
        $message = Message::findOrFail($msgId);
        $channel = $message->channel;

        abort_unless($channel && $channel->canAccess($user), 403);

        $message->toggleLike($user->id);

        return response()->json([
            'likes'      => $message->likes_count,
            'likes_count'=> $message->likes_count,
            'is_liked'   => $message->isLikedBy($user->id),
            'liked_by'   => $message->liked_by ?? [],
        ]);
    }

    /**
     * Soft-delete own message (or any if platform/channel admin).
     */
    public function deleteMessage(Request $request, int $msgId): JsonResponse
    {
        $user    = $request->user();
        $message = Message::findOrFail($msgId);
        $channel = $message->channel;

        $allowed = (int) $message->user_id === (int) $user->id
            || $user->isAdmin()
            || ($channel && $channel->isAdmin($user));

        abort_unless($allowed, 403);

        $message->delete();

        return response()->json(['message' => 'Message deleted.']);
    }

    /**
     * Mark channel as read.
     */
    public function markRead(Request $request, int $channelId): JsonResponse
    {
        $user    = $request->user();
        $channel = Channel::findOrFail($channelId);

        abort_unless($channel->isMember($user), 403);

        $channel->markRead($user);

        return response()->json(['message' => 'Marked as read.', 'unread_count' => 0]);
    }
}
