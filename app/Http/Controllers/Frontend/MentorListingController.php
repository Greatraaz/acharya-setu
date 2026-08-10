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

        // Full-text search
        if ($q = $request->q) {
            $query->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                   ->orWhere('bio', 'like', "%{$q}%")
                   ->orWhere('designation', 'like', "%{$q}%")
                   ->orWhere('company', 'like', "%{$q}%")
                   ->orWhereJsonContains('expertise', $q);
            });
        }

        // Domain / field filter
        if ($domain = $request->domain) {
            $query->where('field', 'like', "%{$domain}%");
        }

        // Rate range filter  (e.g. "10-20" or "50+")
        if ($range = $request->rate_range) {
            if (str_ends_with($range, '+')) {
                $query->where('rate_per_minute', '>=', rtrim($range, '+'));
            } elseif (str_contains($range, '-')) {
                [$min, $max] = explode('-', $range);
                $query->whereBetween('rate_per_minute', [(float)$min, (float)$max]);
            }
        }

        // Max rate (from quick select)
        if ($max = $request->rate_max) {
            $query->where('rate_per_minute', '<=', $max);
        }

        // Minimum rating
        if ($minRating = $request->min_rating) {
            $query->where('rating', '>=', $minRating);
        }

        // Experience range (e.g. "3-7", "7+")
        if ($exp = $request->exp) {
            if (str_ends_with($exp, '+')) {
                $query->where('experience_years', '>=', rtrim($exp, '+'));
            } elseif (str_contains($exp, '-')) {
                [$min, $max] = explode('-', $exp);
                $query->whereBetween('experience_years', [(int)$min, (int)$max]);
            }
        }

        // Sort
        match ($request->sort ?? 'best') {
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
            Carbon::now('Asia/Kolkata')->startOfDay()->addDay(),
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
                : Carbon::now('Asia/Kolkata')->startOfDay()->addDay();

            $overview = $this->availabilityService->weekOverview($mentor, $start, $days);

            return response()->json([
                'mentor_id' => $mentor->id,
                ...$overview,
            ]);
        }

        $request->validate(['date' => 'required|date|after_or_equal:today']);

        return response()->json(
            $this->availabilityService->slotsForDate($mentor, $request->date)
        );
    }
}