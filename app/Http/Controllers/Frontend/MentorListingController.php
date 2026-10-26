<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SessionReview;
use App\Services\MentorAvailabilityService;
use App\Support\MentorBrowseQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MentorListingController extends Controller
{
    public function __construct(
        private readonly MentorAvailabilityService $availabilityService
    ) {}

    // ── Public mentor listing with filters ────────────────────
    public function index(Request $request)
    {
        $query = User::where('role', 'mentor')
            ->where('mentor_status', 'approved')
            ->where('is_active', true);

        $this->applyListingFilters($query, $request);

        $sort = $request->input('sort', 'best') ?: 'best';
        match ($sort) {
            'rating'    => $query->orderByDesc('rating'),
            'rate_asc'  => $query->orderBy('rate_per_minute'),
            'rate_desc' => $query->orderByDesc('rate_per_minute'),
            'sessions'  => $query->orderByDesc('total_sessions'),
            default     => $query->orderByDesc('rating')->orderByDesc('total_sessions'),
        };

        $mentors = $query->paginate(12)->withQueryString();

        // Live DBs may have the slug column before backfill ran — heal missing values.
        foreach ($mentors as $mentor) {
            if (blank($mentor->slug)) {
                $mentor->ensureSlug();
            }
        }

        // AJAX filter refresh only — never JSON for normal browser navigations
        // (Back button must get HTML; same URL was previously used by AjaxGet.)
        if ($request->ajax()) {
            return response()->json([
                'data'         => $mentors->items(),
                'total'        => $mentors->total(),
                'current_page' => $mentors->currentPage(),
                'last_page'    => $mentors->lastPage(),
                'per_page'     => $mentors->perPage(),
                'from'         => $mentors->firstItem(),
                'to'           => $mentors->lastItem(),
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate')
              ->header('Vary', 'Accept, X-Requested-With');
        }

        $mentorFields = User::query()
            ->where('role', 'mentor')
            ->where('mentor_status', User::MENTOR_STATUS_APPROVED)
            ->where('is_active', true)
            ->whereNotNull('field')
            ->where('field', '!=', '')
            ->distinct()
            ->orderBy('field')
            ->pluck('field');

        return response()
            ->view('frontend.search', compact('mentors', 'mentorFields'))
            ->header('Vary', 'Accept, X-Requested-With');
    }

    // ── Public mentor profile ─────────────────────────────────
    public function show(string $slug)
    {
        // Old numeric URLs → permanent redirect to slug
        if (ctype_digit($slug)) {
            $mentor = User::where('role', 'mentor')
                ->where('mentor_status', 'approved')
                ->where('is_active', true)
                ->findOrFail((int) $slug);

            if ($mentor->slug) {
                return redirect()->route('mentors.show', $mentor->slug, 301);
            }
        }

        $mentor = User::where('role', 'mentor')
            ->where('mentor_status', 'approved')
            ->where('is_active', true)
            ->where('slug', $slug)
            ->firstOrFail();

        $reviews = SessionReview::query()
            ->where('reviewee_id', $mentor->id)
            ->where('reviewer_role', 'mentee')
            ->where('is_public', true)
            ->with('reviewer:id,name')
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get();
        $availabilityOverview = $this->availabilityService->weekOverview(
            $mentor,
            Carbon::now('Asia/Kolkata')->startOfDay(),
            14
        );

        return view('frontend.mentors.profile', compact('mentor', 'reviews', 'availabilityOverview'));
    }

    /**
     * Availability for booking widget.
     * - ?date=YYYY-MM-DD → slots for that day
     * - ?week=1&days=14  → week overview (which days are free)
     */
    public function availability(int $id, Request $request)
    {
        $mentor = User::where('role', 'mentor')
            ->where('mentor_status', 'approved')
            ->findOrFail($id);

        if ($request->boolean('week') || (! $request->filled('date') && $request->filled('days'))) {
            $days = min(28, max(7, (int) $request->input('days', 14)));
            $start = $request->filled('start')
                ? Carbon::parse($request->input('start'), 'Asia/Kolkata')->startOfDay()
                : Carbon::now('Asia/Kolkata')->startOfDay();

            $overview = $this->availabilityService->weekOverview($mentor, $start, $days);

            return response()->json([
                'mentor_id' => $mentor->id,
                ...$overview,
            ]);
        }

        $request->validate([
            'date'     => 'required|date|after_or_equal:today',
            'duration' => 'nullable|integer|in:'.implode(',', \App\Models\ConsultationSession::BOOKING_DURATIONS),
        ]);

        return response()->json(
            $this->availabilityService->slotsForDate(
                $mentor,
                $request->date,
                $request->filled('duration') ? (int) $request->input('duration') : null
            )
        );
    }

    private function applyListingFilters($query, Request $request): void
    {
        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $query->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                   ->orWhere('bio', 'like', "%{$q}%")
                   ->orWhere('field', 'like', "%{$q}%")
                   ->orWhere('designation', 'like', "%{$q}%")
                   ->orWhere('company', 'like', "%{$q}%")
                   ->orWhere('expertise', 'like', "%{$q}%");
            });
        }

        $field = trim((string) $request->input('field', ''));
        if ($field !== '') {
            $query->where('field', $field);
        }

        $domains = array_filter((array) $request->input('domain', []));
        if ($domains) {
            $query->where(function ($q2) use ($domains) {
                foreach ($domains as $domain) {
                    foreach (MentorBrowseQuery::domainKeywords((string) $domain) as $keyword) {
                        $q2->orWhere('field', 'like', "%{$keyword}%")
                           ->orWhere('designation', 'like', "%{$keyword}%")
                           ->orWhere('expertise', 'like', "%{$keyword}%");
                    }
                }
            });
        }

        $range = (string) $request->input('rate_range', '');
        if ($range !== '') {
            if (str_ends_with($range, '+')) {
                $query->where('rate_per_minute', '>=', (float) rtrim($range, '+'));
            } elseif (str_contains($range, '-')) {
                [$min, $max] = explode('-', $range, 2);
                $query->whereBetween('rate_per_minute', [(float) $min, (float) $max]);
            }
        }

        if ($request->filled('rate_max')) {
            $query->where('rate_per_minute', '<=', (float) $request->rate_max);
        }

        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', (float) $request->min_rating);
        }

        $expRanges = array_filter((array) $request->input('exp', $request->input('experience', [])));
        if ($expRanges) {
            $query->where(function ($q2) use ($expRanges) {
                foreach ($expRanges as $exp) {
                    $exp = (string) $exp;
                    if (str_ends_with($exp, '+')) {
                        $q2->orWhere('experience_years', '>=', (int) rtrim($exp, '+'));
                    } elseif (str_contains($exp, '-')) {
                        [$min, $max] = explode('-', $exp, 2);
                        $q2->orWhereBetween('experience_years', [(int) $min, (int) $max]);
                    }
                }
            });
        }

        $this->applySessionTypeFilter($query, $request);
        $this->applyFormatFilter($query, $request);
        $this->applyAvailabilityFilter($query, $request);

        $duration = MentorBrowseQuery::durationMinutes($request);
        if ($duration !== null) {
            MentorBrowseQuery::whereSupportsDuration($query, $duration);
        }
    }

    private function applySessionTypeFilter($query, Request $request): void
    {
        $types = array_values(array_intersect(
            array_filter((array) $request->input('session_type', [])),
            ['video', 'audio', 'chat']
        ));
        if ($types === []) {
            return;
        }

        $query->where(function ($q) use ($types) {
            if (in_array('video', $types, true)) {
                $q->orWhere(function ($video) {
                    $video->whereRaw("JSON_CONTAINS(COALESCE(preferences, JSON_OBJECT()), '\"video\"', '$.session_modes')")
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.mentoring_format')) IN ('video', 'hybrid', 'one_on_one', 'group')")
                        ->orWhere(function ($unset) {
                            $unset->whereRaw("JSON_EXTRACT(preferences, '$.session_modes') IS NULL")
                                ->where(function ($format) {
                                    $format->whereRaw("JSON_EXTRACT(preferences, '$.mentoring_format') IS NULL")
                                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.mentoring_format')) IN ('null', '')");
                                });
                        });
                });
            }
            if (in_array('audio', $types, true)) {
                $q->orWhereRaw("JSON_CONTAINS(COALESCE(preferences, JSON_OBJECT()), '\"audio\"', '$.session_modes')")
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.mentoring_format')) = 'audio'");
            }
            if (in_array('chat', $types, true)) {
                $q->orWhereRaw("JSON_CONTAINS(COALESCE(preferences, JSON_OBJECT()), '\"chat\"', '$.session_modes')")
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.mentoring_format')) = 'chat'");
            }
        });
    }

    private function applyAvailabilityFilter($query, Request $request): void
    {
        $flags = array_values(array_intersect(
            array_filter((array) $request->input('availability', [])),
            ['today', 'weekend', 'evening']
        ));
        if ($flags === []) {
            return;
        }

        $now = Carbon::now('Asia/Kolkata');
        $query->whereRaw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.accepting_bookings')), 'true') NOT IN ('false', '0')");

        $query->where(function ($outer) use ($flags, $now) {
            foreach ($flags as $flag) {
                $outer->orWhere(function ($q) use ($flag, $now) {
                    if ($flag === 'today') {
                        $day = strtolower($now->format('l'));
                        $q->whereRaw(
                            "JSON_SEARCH(preferences, 'one', ?, NULL, '$.blocked_dates') IS NULL",
                            [$now->toDateString()]
                        );
                        $this->whereHasWindow($q, [$day], $now->copy()->addMinutes(5)->format('H:i:s'));

                        return;
                    }
                    if ($flag === 'weekend') {
                        $this->whereHasWindow($q, ['saturday', 'sunday']);

                        return;
                    }
                    $this->whereHasWindow($q, [
                        'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
                    ], null, true);
                });
            }
        });
    }

    /**
     * @param  list<string>  $days
     */
    private function whereHasWindow($query, array $days, ?string $endAfter = null, bool $evening = false): void
    {
        $query->where(function ($q) use ($days, $endAfter, $evening) {
            $q->whereExists(function ($sub) use ($days, $endAfter, $evening) {
                $sub->selectRaw('1')
                    ->from('mentor_availabilities')
                    ->whereColumn('mentor_availabilities.mentor_id', 'users.id')
                    ->where('is_available', true)
                    ->whereRaw(
                        'LOWER(day_of_week) IN ('.implode(',', array_fill(0, count($days), '?')).')',
                        $days
                    );
                if ($endAfter) {
                    $sub->whereRaw('TIME(end_time) > ?', [$endAfter]);
                }
                if ($evening) {
                    $sub->whereRaw("TIME(start_time) < '22:00:00' AND TIME(end_time) > '18:00:00'");
                }
            });

            foreach ($days as $day) {
                $q->orWhere(function ($json) use ($day, $endAfter, $evening) {
                    $json->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.weekly_schedule.{$day}.enabled')) IN ('true', '1')");
                    if ($endAfter) {
                        $json->whereRaw(
                            "JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.weekly_schedule.{$day}.to')) > ?",
                            [substr($endAfter, 0, 5)]
                        );
                    }
                    if ($evening) {
                        $json->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.weekly_schedule.{$day}.from')) < '22:00'")
                            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.weekly_schedule.{$day}.to')) > '18:00'");
                    }
                });
            }
        });
    }

    private function applyFormatFilter($query, Request $request): void
    {
        $formats = array_values(array_intersect(
            array_filter((array) $request->input('format', [])),
            ['one_on_one', 'sprint', 'group']
        ));
        if ($formats === []) {
            return;
        }

        $query->where(function ($outer) use ($formats) {
            foreach ($formats as $format) {
                $outer->orWhere(function ($q) use ($format) {
                    $this->whereMentorshipFormat($q, $format);
                });
            }
        });
    }

    private function whereMentorshipFormat($query, string $format): void
    {
        if ($format === 'one_on_one') {
            $query->where(function ($q) {
                $q->whereRaw("JSON_CONTAINS(COALESCE(preferences, JSON_OBJECT()), '\"video\"', '$.session_modes')")
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.mentoring_format')) IN ('video', 'hybrid', 'one_on_one')")
                    ->orWhere(function ($unset) {
                        $unset->whereRaw("JSON_EXTRACT(preferences, '$.mentoring_format') IS NULL")
                            ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.mentoring_format')) IN ('null', '')");
                    });
            })->whereRaw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.mentoring_format')), '') != 'group'");

            return;
        }

        if ($format === 'group') {
            $query->where(function ($q) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.mentoring_format')) = 'group'");
                foreach (MentorBrowseQuery::formatKeywords('group') as $keyword) {
                    $q->orWhere('bio', 'like', "%{$keyword}%")
                        ->orWhere('field', 'like', "%{$keyword}%")
                        ->orWhere('expertise', 'like', "%{$keyword}%")
                        ->orWhere('strengths', 'like', "%{$keyword}%");
                }
            });

            return;
        }

        $query->where(function ($q) {
            foreach (MentorBrowseQuery::formatKeywords('sprint') as $keyword) {
                $q->orWhere('bio', 'like', "%{$keyword}%")
                    ->orWhere('field', 'like', "%{$keyword}%")
                    ->orWhere('designation', 'like', "%{$keyword}%")
                    ->orWhere('expertise', 'like', "%{$keyword}%")
                    ->orWhere('strengths', 'like', "%{$keyword}%");
            }
        });
    }
}