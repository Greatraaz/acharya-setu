<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SessionReview;
use App\Models\ConsultationSession;
use Illuminate\Http\Request;

class MentorListingController extends Controller
{
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

        return view('frontend.mentors.profile', compact('mentor', 'reviews'));
    }

    // ── Availability slots for booking widget ─────────────────
    public function availability(int $id, Request $request)
    {
        $request->validate(['date' => 'required|date|after_or_equal:today']);

        $mentor = User::where('role', 'mentor')
            ->where('mentor_status', 'approved')
            ->findOrFail($id);

        $date    = $request->date;
        $dayName = strtolower(date('l', strtotime($date)));

        // Load mentor's weekly availability preference (stored in JSON)
        $weeklySlots = $mentor->preferences['weekly_slots']
            ?? $mentor->preferences['weekly_schedule']
            ?? null;

        $defaults = ['09:00','10:00','11:00','12:00','14:00','15:00','16:00','17:00','18:00','19:00'];

        if (is_array($weeklySlots) && isset($weeklySlots[$dayName]) && is_array($weeklySlots[$dayName]) && count($weeklySlots[$dayName])) {
            $allSlots = array_values($weeklySlots[$dayName]);
        } else {
            $allSlots = $defaults;
        }

        // Soft-expire abandoned unpaid checkouts so slots free without a cancel API
        ConsultationSession::expireAbandonedUnpaidPayments();

        // Remove already-booked slots for this date
        $bookedTimes = ConsultationSession::where('mentor_id', $id)
            ->whereDate('scheduled_at', $date)
            ->occupyingSlot()
            ->pluck('scheduled_at')
            ->map(fn ($dt) => \Carbon\Carbon::parse($dt)->format('H:i'))
            ->toArray();

        $available = array_values(array_diff($allSlots, $bookedTimes));

        return response()->json([
            'date'   => $date,
            'slots'  => $available,
            'booked' => $bookedTimes,
        ]);
    }
}