<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class BulkEmailAudience
{
    public const ALL = 'all';
    public const MENTORS = 'mentors';
    public const MENTEES = 'mentees';
    public const SPECIFIC = 'specific';

    /** @return list<string> */
    public static function options(): array
    {
        return [self::ALL, self::MENTORS, self::MENTEES, self::SPECIFIC];
    }

    public static function label(string $audience): string
    {
        return match ($audience) {
            self::MENTORS => 'mentors',
            self::MENTEES => 'mentees',
            self::SPECIFIC => 'selected users',
            default => 'all mentors and mentees',
        };
    }

    /**
     * @param  list<int>  $userIds
     */
    public static function query(string $audience, array $userIds = []): Builder
    {
        $query = User::query()
            ->whereNotNull('email')
            ->where('email', '!=', '');

        return match ($audience) {
            self::MENTORS => $query->mentors()->active(),
            self::MENTEES => $query->mentees()->active(),
            self::SPECIFIC => $query->whereIn('id', $userIds !== [] ? $userIds : [0]),
            default => $query->whereIn('role', ['mentor', 'mentee'])->active(),
        };
    }

    /**
     * @param  list<int>  $userIds
     * @return list<int>
     */
    public static function recipientIds(string $audience, array $userIds = []): array
    {
        return self::query($audience, $userIds)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
