<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelInvitation;
use App\Models\Message;
use App\Models\MessageReport;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $data = $request->validate(Channel::storeValidationRules());

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

        // Save image/video directly on the channel (not as a message)
        $mediaAttrs = Channel::storeChannelMedia($request, $channel->id);
        if ($mediaAttrs) {
            $channel->update($mediaAttrs);
        }

        return response()->json([
            'message' => 'Channel created.',
            'channel' => $channel->load(['creator:id,name,avatar_url,role'])
                ->loadCount(['allMessages', 'members'])
                ->toApiArray($user),
        ], 201);
    }

    /**
 * Update a channel.
 * Only channel admins/creator/platform admin can update it.
 */
public function updateChannel(Request $request, int $channelId): JsonResponse
{
    $user = $request->user();

    $channel = Channel::findOrFail($channelId);

    // Only channel admin, creator, or platform admin can update
    abort_unless(
        $channel->isAdmin($user)
            || (int) $channel->created_by === (int) $user->id
            || $user->isAdmin(),
        403,
        'You are not allowed to update this channel.'
    );

    $data = $request->validate([
        'name'        => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'icon'        => 'nullable|string|max:50',
        'type'        => 'required|in:' . Channel::TYPE_PUBLIC . ',' . Channel::TYPE_PRIVATE,
        'category'    => 'nullable|string|max:100',
    ]);

    $channel->update([
        'name'        => $data['name'],
        'description' => $data['description'] ?? null,
        'icon'        => $data['icon'] ?? $channel->icon,
        'type'        => $data['type'],
        'category'    => $data['category'] ?? null,
    ]);

    // Update channel image/video if new media is supplied
    $mediaAttrs = Channel::storeChannelMedia($request, $channel->id);

    if ($mediaAttrs) {
        $channel->update($mediaAttrs);
    }

    $channel->refresh();

    return response()->json([
        'message' => 'Channel updated successfully.',
        'channel' => $channel
            ->load(['creator:id,name,avatar_url,role'])
            ->loadCount(['allMessages', 'members'])
            ->toApiArray($user),
    ]);
}
public function deleteChannel(Request $request, int $channelId): JsonResponse
{
    $user = $request->user();
    $channel = Channel::findOrFail($channelId);

    abort_unless(
        $channel->isAdmin($user)
            || (int) $channel->created_by === (int) $user->id
            || $user->isAdmin(),
        403,
        'You are not allowed to delete this channel.'
    );

    $channel->delete();

    return response()->json([
        'message' => 'Channel deleted successfully.'
    ]);
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
                'message' => 'This is a private channel. Accept a mentor invitation to join.',
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
     * Mentor directory: list mentors & mentees with channel invitation status.
     *
     * Query:
     *  - role=mentor|mentee
     *  - channel_id=123   (required when using status; scopes invite flags to this channel)
     *  - status=pending|accepted|rejected|none  (filter by invitation status for channel_id)
     *  - search=name/email
     *  - per_page=20
     */
    public function inviteCandidates(Request $request): JsonResponse
    {
        $auth = $request->user();

        if (! $auth->isMentor()) {
            return response()->json([
                'message' => 'Only mentors can view invite candidates.',
            ], 403);
        }

        // Prefer `status`; keep legacy `invitation_status` as alias
        if (! $request->filled('status') && $request->filled('invitation_status')) {
            $request->merge(['status' => $request->input('invitation_status')]);
        }

        $data = $request->validate([
            'role'       => 'nullable|in:mentor,mentee',
            'channel_id' => 'nullable|integer|exists:channels,id|required_with:status',
            'status'     => 'nullable|in:pending,accepted,rejected,none',
            'search'     => 'nullable|string|max:100',
            'per_page'   => 'nullable|integer|min:1|max:100',
        ]);

        $channelId = isset($data['channel_id']) ? (int) $data['channel_id'] : null;
        $channel   = $channelId ? Channel::findOrFail($channelId) : null;
        $status    = $data['status'] ?? null;

        if ($channel && ! (
            $channel->isMember($auth)
            || (int) $channel->created_by === (int) $auth->id
            || $auth->isAdmin()
        )) {
            return response()->json([
                'message' => 'You must be a member of this channel to filter by it.',
            ], 403);
        }

        $query = User::query()
            ->select(['id', 'name', 'email', 'avatar_url', 'role', 'mentor_status', 'is_active', 'created_at'])
            ->where('id', '!=', $auth->id)
            ->when(! empty($data['search']), function ($q) use ($data) {
                $term = '%' . trim($data['search']) . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            });

        if (($data['role'] ?? null) === 'mentor') {
            $query->where('role', 'mentor')
                ->where('mentor_status', User::MENTOR_STATUS_APPROVED);
        } elseif (($data['role'] ?? null) === 'mentee') {
            $query->where('role', 'mentee');
        } else {
            $query->where(function ($q) {
                $q->where('role', 'mentee')
                    ->orWhere(function ($m) {
                        $m->where('role', 'mentor')
                            ->where('mentor_status', User::MENTOR_STATUS_APPROVED);
                    });
            });
        }

        // Hide users who are already members of the filtered channel
        if ($channelId) {
            $query->whereDoesntHave('channels', fn ($q) => $q->where('channels.id', $channelId));
        }

        // Filter by invitation status for the given channel
        if ($channelId && $status) {
            if ($status === 'none') {
                $query->whereDoesntHave('channelInvitations', function ($q) use ($channelId) {
                    $q->where('channel_id', $channelId);
                });
            } else {
                $query->whereHas('channelInvitations', function ($q) use ($channelId, $status) {
                    $q->where('channel_id', $channelId)->where('status', $status);
                });
            }
        }

        $perPage   = $data['per_page'] ?? 20;
        $paginator = $query->orderBy('name')->paginate($perPage);
        $userIds   = collect($paginator->items())->pluck('id')->all();

        // Prefetch invitations for these users (scoped to channel when provided)
        $invitationsQuery = ChannelInvitation::query()
            ->whereIn('user_id', $userIds)
            ->with(['channel:id,name,slug,icon,type,description', 'invitedBy:id,name,avatar_url,role'])
            ->latest();

        if ($channelId) {
            $invitationsQuery->where('channel_id', $channelId);
        }

        $invitationsByUser = $invitationsQuery->get()->groupBy('user_id');

        $users = collect($paginator->items())->map(function (User $u) use ($invitationsByUser, $channelId, $channel) {
            $userInvites = ($invitationsByUser->get($u->id) ?? collect())
                ->map(fn (ChannelInvitation $inv) => $inv->toApiArray())
                ->values();

            // Prefer invite for the filtered channel; otherwise latest invite
            $channelInvite = $channelId
                ? $userInvites->firstWhere('channel_id', $channelId)
                : $userInvites->first();

            $inviteStatus = $channelInvite['status'] ?? null;

            return [
                'id'                 => $u->id,
                'name'               => $u->name,
                'email'              => $u->email,
                'avatar_url'         => $u->avatar_url,
                'role'               => $u->role,
                'is_active'          => (bool) $u->is_active,
                'is_channel_member'  => $channelId ? false : null,
                'invitation_sent'    => $inviteStatus !== null,
                'invitation_status'  => $inviteStatus,
                'channel_invitation' => $channelInvite,
                'invitations'        => $userInvites,
                'channel'            => $channel ? [
                    'id'   => $channel->id,
                    'name' => $channel->name,
                    'slug' => $channel->slug,
                    'type' => $channel->type,
                ] : null,
            ];
        });

        return response()->json([
            'users' => $users,
            'meta'  => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
            'filters' => [
                'role'       => $data['role'] ?? null,
                'channel_id' => $channelId,
                'status'     => $status,
                'search'     => $data['search'] ?? null,
            ],
        ]);
    }

    /**
     * Invite one or more users to the channel (mentor only).
     * Creates pending invitations — invitees must accept before joining.
     *
     * Body: { "user_ids": [1, 2, 3] }  or legacy { "user_id": 1 }
     */
    public function invite(Request $request, int $channelId): JsonResponse
    {
        $user    = $request->user();
        $channel = Channel::findOrFail($channelId);

        if (! $user->isMentor()) {
            return response()->json([
                'message' => 'Only mentors can send channel invitations.',
            ], 403);
        }

        abort_unless(
            $channel->isAdmin($user)
                || (int) $channel->created_by === (int) $user->id
                || $channel->isMember($user),
            403,
            'You must be a member of this channel to invite others.'
        );

        // Normalise: accept user_ids[] or single user_id
        if (! $request->filled('user_ids') && $request->filled('user_id')) {
            $request->merge(['user_ids' => [(int) $request->input('user_id')]]);
        }

        $data = $request->validate([
            'user_ids'   => 'required|array|min:1|max:50',
            'user_ids.*' => 'required|integer|distinct|exists:users,id',
        ]);

        $invitees = User::whereIn('id', $data['user_ids'])->get()->keyBy('id');

        $invited  = [];
        $skipped  = [];

        DB::transaction(function () use ($channel, $user, $data, $invitees, &$invited, &$skipped) {
            foreach ($data['user_ids'] as $uid) {
                $invitee = $invitees->get($uid);

                if (! $invitee || ! in_array($invitee->role, ['mentor', 'mentee'], true)) {
                    $skipped[] = [
                        'user_id' => $uid,
                        'reason'  => 'Only mentors and mentees can be invited.',
                    ];
                    continue;
                }

                if ((int) $invitee->id === (int) $user->id) {
                    $skipped[] = [
                        'user_id' => $uid,
                        'reason'  => 'Cannot invite yourself.',
                    ];
                    continue;
                }

                if ($channel->isMember($invitee)) {
                    $skipped[] = [
                        'user_id' => $uid,
                        'name'    => $invitee->name,
                        'reason'  => 'Already a member.',
                    ];
                    continue;
                }

                $invitation = ChannelInvitation::updateOrCreate(
                    [
                        'channel_id' => $channel->id,
                        'user_id'    => $invitee->id,
                    ],
                    [
                        'invited_by'   => $user->id,
                        'status'       => ChannelInvitation::STATUS_PENDING,
                        'responded_at' => null,
                    ]
                );

                $invited[] = [
                    'invitation_id' => $invitation->id,
                    'user_id'       => $invitee->id,
                    'name'          => $invitee->name,
                    'role'          => $invitee->role,
                    'status'        => $invitation->status,
                ];
            }
        });

        return response()->json([
            'message'  => count($invited) . ' invitation(s) sent. Users must accept before joining.',
            'invited'  => $invited,
            'skipped'  => $skipped,
        ], 201);
    }

    /**
     * List pending channel invitations for the authenticated user.
     */
    public function myInvitations(Request $request): JsonResponse
    {
        $user = $request->user();

        $invitations = ChannelInvitation::pending()
            ->where('user_id', $user->id)
            ->with([
                'channel:id,name,slug,icon,type,description,is_active',
                'invitedBy:id,name,avatar_url,role',
            ])
            ->latest()
            ->get()
            ->map(fn (ChannelInvitation $inv) => $inv->toApiArray());

        return response()->json([
            'invitations' => $invitations,
            'count'       => $invitations->count(),
        ]);
    }

    /**
     * List pending invitations sent for a channel (mentor view).
     */
    public function channelInvitations(Request $request, int $channelId): JsonResponse
    {
        $user    = $request->user();
        $channel = Channel::findOrFail($channelId);

        abort_unless($channel->isMember($user) || $user->isAdmin(), 403);

        $status = $request->query('status');

        $invitations = $channel->invitations()
            ->with(['user:id,name,avatar_url,role', 'invitedBy:id,name,avatar_url,role'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->get()
            ->map(fn (ChannelInvitation $inv) => $inv->toApiArray());

        return response()->json([
            'invitations' => $invitations,
        ]);
    }

    /**
     * Accept a pending channel invitation — joins the channel.
     */
    public function acceptInvitation(Request $request, int $invitationId): JsonResponse
    {
        $user       = $request->user();
        $invitation = ChannelInvitation::with('channel')->findOrFail($invitationId);

        if ((int) $invitation->user_id !== (int) $user->id) {
            return response()->json(['message' => 'This invitation is not for you.'], 403);
        }

        if (! $invitation->isPending()) {
            return response()->json([
                'message' => 'This invitation is already ' . $invitation->status . '.',
            ], 422);
        }

        $channel = $invitation->channel;

        if (! $channel || ! $channel->is_active) {
            return response()->json(['message' => 'This channel is no longer available.'], 422);
        }

        DB::transaction(function () use ($invitation, $channel, $user) {
            $channel->addMember($user);
            $invitation->update([
                'status'       => ChannelInvitation::STATUS_ACCEPTED,
                'responded_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'Invitation accepted. You have joined the channel.',
            'channel' => $channel->fresh()
                ->load(['creator:id,name,avatar_url,role'])
                ->loadCount(['allMessages', 'members'])
                ->toApiArray($user),
        ]);
    }

    /**
     * Reject a pending channel invitation.
     */
    public function rejectInvitation(Request $request, int $invitationId): JsonResponse
    {
        $user       = $request->user();
        $invitation = ChannelInvitation::findOrFail($invitationId);

        if ((int) $invitation->user_id !== (int) $user->id) {
            return response()->json(['message' => 'This invitation is not for you.'], 403);
        }

        if (! $invitation->isPending()) {
            return response()->json([
                'message' => 'This invitation is already ' . $invitation->status . '.',
            ], 422);
        }

        $invitation->update([
            'status'       => ChannelInvitation::STATUS_REJECTED,
            'responded_at' => now(),
        ]);

        return response()->json([
            'message' => 'Invitation rejected.',
        ]);
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

        $paginator = $channel->messagesForUser($user)
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
     * Accepts JSON or multipart form-data with optional `image` and/or `video`.
     * Fields: body|message (text), parent_id (reply),
     * image (jpeg/png/webp/gif, max 5MB), video (mp4/mov/avi/webm/mpeg, max 10MB).
     * At least one of body, image, or video is required.
     */
    public function postMessage(Request $request, int $channelId): JsonResponse
    {
        $user    = $request->user();
        $channel = Channel::active()->findOrFail($channelId);

        abort_unless($channel->canPost($user), 403, $channel->isRemoved($user)
            ? 'You were removed from this channel. A mentor must invite you again before you can post.'
            : 'You cannot post in this channel.');

        $data = $request->validate(Message::mediaValidationRules());

        try {
            $attrs = Message::buildPostAttributes($request, $channel, $data);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();

            return response()->json([
                'message' => $firstError ?: 'Unable to post this message.',
                'errors'  => $e->errors(),
            ], 422);
        }

        if (! empty($attrs['parent_id'])) {
            $parent = Message::where('channel_id', $channel->id)->findOrFail($attrs['parent_id']);
            if ($parent->parent_id) {
                return response()->json(['message' => 'Cannot reply to a reply. Reply to the parent message.'], 422);
            }
        }

        if (! $channel->isMember($user) && $channel->canSelfJoin($user)) {
            $channel->addMember($user);
        }

        $message = Message::create([
            'channel_id' => $channel->id,
            'user_id'    => $user->id,
            'body'       => $attrs['body'] !== '' ? $attrs['body'] : '',
            'image_path' => $attrs['image_path'],
            'video_path' => $attrs['video_path'],
            'parent_id'  => $attrs['parent_id'],
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

    /**
     * Report a channel post. The post is hidden from the reporting user's feed.
     */
    public function reportMessage(Request $request, int $msgId): JsonResponse
    {
        $user    = $request->user();
        $message = Message::with('channel')->findOrFail($msgId);
        $channel = $message->channel;

        abort_unless($channel && $channel->canAccess($user), 403);

        if ((int) $message->user_id === (int) $user->id) {
            return response()->json(['message' => 'You cannot report your own message.'], 422);
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

        return response()->json([
            'message' => 'Message reported. It will no longer appear in your feed.',
        ]);
    }
}
