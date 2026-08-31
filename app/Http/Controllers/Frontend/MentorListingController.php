<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SessionReview;
use App\Services\MentorAvailabilityService;
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

        // AJAX request — return JSON for JS rendering
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'data'         => $mentors->items(),
                'total'        => $mentors->total(),
                'current_page' => $mentors->currentPage(),
                'last_page'    => $mentors->lastPage(),
                'per_page'     => $mentors->perPage(),
                'from'         => $mentors->firstItem(),
                'to'           => $mentors->lastItem(),
            ]);
        }

        return view('frontend.search', compact('mentors'));
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

        $reviews = [];
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
            'duration' => 'nullable|integer|in:15,30,45,60,90,120',
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

        $domains = array_filter((array) $request->input('domain', []));
        if ($domains) {
            $query->where(function ($q2) use ($domains) {
                foreach ($domains as $domain) {
                    foreach ($this->domainKeywords((string) $domain) as $keyword) {
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
    }

    /** @return list<string> */
    private function domainKeywords(string $domain): array
    {
        return match (strtolower(trim($domain))) {
            'engineering' => ['engineering', 'software', 'tech', 'devops', 'cyber', 'data science', 'developer'],
            'product'     => ['product'],
            'design'      => ['design', 'ux', 'ui'],
            'finance'     => ['finance', 'mba', 'investment', 'trading'],
            'marketing'   => ['marketing', 'brand', 'content', 'media'],
            'law'         => ['law'],
            'medicine'    => ['medicine', 'medical'],
            'arts'        => ['arts', 'humanities', 'psychology', 'civil service', 'upsc'],
            default       => [$domain],
        };
    }
}