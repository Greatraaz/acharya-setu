<?php

namespace App\Support;

use App\Models\ConsultationSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MentorBrowseQuery
{
    public static function fromRequest(Request $request, ?User $mentee = null): Builder
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));
        $field = trim((string) $request->input('field', ''));

        return User::query()
            ->where('role', 'mentor')
            ->where('mentor_status', User::MENTOR_STATUS_APPROVED)
            ->where('is_active', true)
            ->when($mentee?->assigned_mentor_id && $request->boolean('exclude_assigned'), function (Builder $q) use ($mentee) {
                $q->where('id', '!=', $mentee->assigned_mentor_id);
            })
            ->when($search !== '', function (Builder $q) use ($search) {
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('designation', 'like', '%'.$search.'%')
                        ->orWhere('company', 'like', '%'.$search.'%')
                        ->orWhere('field', 'like', '%'.$search.'%')
                        ->orWhere('bio', 'like', '%'.$search.'%')
                        ->orWhere('expertise', 'like', '%'.$search.'%');
                });
            })
            ->when($field !== '', fn (Builder $q) => $q->where('field', 'like', '%'.$field.'%'))
            ->when($request->filled('company'), fn (Builder $q) => $q->where('company', 'like', '%'.$request->input('company').'%'))
            ->when($request->filled('gender'), fn (Builder $q) => $q->where('gender', $request->input('gender')))
            ->when($request->filled('min_rating'), fn (Builder $q) => $q->where('rating', '>=', (float) $request->input('min_rating')))
            ->when(self::durationMinutes($request) !== null, function (Builder $q) use ($request) {
                self::whereSupportsDuration($q, (int) self::durationMinutes($request));
            });
    }

    public static function durationMinutes(Request $request): ?int
    {
        if (! $request->filled('duration')) {
            return null;
        }

        $minutes = (int) $request->input('duration');

        return in_array($minutes, ConsultationSession::BOOKING_DURATIONS, true) ? $minutes : null;
    }

    public static function whereSupportsDuration(Builder $query, int $minutes): Builder
    {
        return $query->where(function (Builder $q) use ($minutes) {
            $q->whereExists(function ($sub) use ($minutes) {
                $sub->selectRaw('1')
                    ->from('mentor_availabilities')
                    ->whereColumn('mentor_availabilities.mentor_id', 'users.id')
                    ->where('is_available', true)
                    ->whereRaw('TIMESTAMPDIFF(MINUTE, TIME(start_time), TIME(end_time)) = ?', [$minutes]);
            })->orWhereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.session_length')) AS UNSIGNED) = ?",
                [$minutes]
            );
        });
    }

    public static function applySort(Builder $query, string $sort = 'best'): Builder
    {
        return match ($sort) {
            'rating'   => $query->orderByDesc('rating'),
            'sessions' => $query->orderByDesc('total_sessions'),
            'name'     => $query->orderBy('name'),
            'rate_asc' => $query->orderBy('rate_per_minute'),
            'rate_desc'=> $query->orderByDesc('rate_per_minute'),
            default    => $query->orderByDesc('rating')->orderByDesc('total_sessions'),
        };
    }

    public const MULTI_FILTERS = ['domain', 'session_type', 'format', 'exp', 'availability'];

    /** @return list<array{0:string,1:string}> */
    public static function domainOptions(): array
    {
        return [
            ['engineering', 'Tech & AI'],
            ['product', 'Product Management'],
            ['design', 'Design & UX'],
            ['leadership', 'Leadership & CXO'],
            ['finance', 'Finance & Strategy'],
            ['marketing', 'Marketing'],
            ['law', 'Law'],
            ['medicine', 'Medicine'],
            ['arts', 'Arts & Humanities'],
        ];
    }

    /** @return list<array{0:string,1:string}> */
    public static function formatOptions(): array
    {
        return [
            ['one_on_one', '1-on-1 Video Sessions'],
            ['sprint', 'Goal-Based Sprints'],
            ['group', 'Group Mentoring'],
        ];
    }

    public static function listingUrl(array $filters = []): string
    {
        $parts = [];
        foreach ($filters as $key => $value) {
            $items = array_values(array_filter(
                (array) $value,
                fn ($item) => $item !== null && $item !== ''
            ));
            $multi = in_array($key, self::MULTI_FILTERS, true);
            foreach ($items as $item) {
                $name = $multi ? $key.'[]' : $key;
                $parts[] = rawurlencode($name).'='.rawurlencode((string) $item);
            }
        }

        $base = route('mentors.search');

        return $parts === [] ? $base : $base.'?'.implode('&', $parts);
    }

    public static function listingTitle(Request $request): string
    {
        $formats = array_values(array_filter((array) $request->input('format', [])));
        $domains = array_values(array_filter((array) $request->input('domain', [])));

        $labels = [];
        foreach ($formats as $format) {
            $labels[] = match ((string) $format) {
                'one_on_one' => '1-on-1 Video Sessions',
                'sprint' => 'Goal-Based Sprints',
                'group' => 'Group Mentoring',
                default => null,
            };
        }

        $domainSet = array_map('strtolower', $domains);
        if (in_array('product', $domainSet, true) && in_array('design', $domainSet, true)) {
            $labels[] = 'Product & Design';
            $domainSet = array_values(array_diff($domainSet, ['product', 'design']));
        }

        foreach ($domainSet as $domain) {
            $labels[] = match ($domain) {
                'engineering', 'tech-ai' => 'Tech & AI',
                'product' => 'Product Management',
                'design' => 'Design & UX',
                'leadership', 'leadership-cxo', 'cxo' => 'Leadership & CXO',
                'finance', 'finance-strategy' => 'Finance & Strategy',
                'marketing' => 'Marketing',
                'law' => 'Law',
                'medicine' => 'Medicine',
                'arts' => 'Arts & Humanities',
                default => null,
            };
        }

        $labels = array_values(array_unique(array_filter($labels)));

        return $labels === [] ? 'Find Your Perfect Mentor' : implode(' · ', $labels);
    }

    /** @return list<string> */
    public static function domainKeywords(string $domain): array
    {
        return match (strtolower(trim($domain))) {
            'engineering', 'tech-ai', 'tech & ai' => [
                'engineering', 'software', 'tech', 'artificial intelligence',
                'machine learning', 'devops', 'cyber', 'data science', 'developer',
            ],
            'product' => ['product', 'product management'],
            'design' => ['design', 'ux', 'ui', 'product design'],
            'product-design', 'product & design' => [
                'product', 'product management', 'design', 'ux', 'ui',
            ],
            'leadership', 'leadership-cxo', 'cxo' => [
                'leadership', 'cxo', 'executive', 'ceo', 'cto', 'cfo', 'coo',
                'director', 'founder', 'management',
            ],
            'finance', 'finance-strategy' => [
                'finance', 'mba', 'investment', 'trading', 'strategy',
                'consulting', 'fintech', 'consultant',
            ],
            'marketing' => ['marketing', 'brand', 'content', 'media'],
            'law' => ['law'],
            'medicine' => ['medicine', 'medical'],
            'arts' => ['arts', 'humanities', 'psychology', 'civil service', 'upsc'],
            default => [$domain],
        };
    }

    /** @return list<string> */
    public static function formatKeywords(string $format): array
    {
        return match ($format) {
            'sprint' => [
                'sprint', 'goal-based', 'goal setting', 'career transition',
                'multi-week', 'accountability',
            ],
            'group' => ['group mentoring', 'group session', 'group mentor', 'cohort'],
            default => [],
        };
    }
}
