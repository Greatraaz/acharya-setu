<?php

namespace App\Support;

use App\Models\Channel;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ChannelIndexQuery
{
    public static function paginate(User $user, Request $request, int $perPage = 20): LengthAwarePaginator
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));
        $type = $request->input('type');
        $category = trim((string) $request->input('category', ''));
        $joined = $request->has('joined') ? $request->boolean('joined') : null;

        $paginator = Channel::visibleTo($user)
            ->withCount(['allMessages', 'members'])
            ->with('creator:id,name')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
            ->when(in_array($type, [Channel::TYPE_PUBLIC, Channel::TYPE_PRIVATE], true), fn ($q) => $q->where('type', $type))
            ->when($category !== '', fn ($q) => $q->where('category', $category))
            ->when($joined === true, fn ($q) => $q->whereHas('members', fn ($m) => $m->where('user_id', $user->id)))
            ->when($joined === false, fn ($q) => $q->whereDoesntHave('members', fn ($m) => $m->where('user_id', $user->id)))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return $paginator->through(function (Channel $channel) use ($user) {
            $channel->unread_count = $channel->isMember($user) ? $channel->unreadCountFor($user) : 0;

            return $channel;
        });
    }
}
