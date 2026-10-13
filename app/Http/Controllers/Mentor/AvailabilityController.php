<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use App\Services\MentorAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AvailabilityController extends Controller
{
    public function __construct(
        private readonly MentorAvailabilityService $availabilityService
    ) {}

    public function show()
    {
        $user = auth()->user();
        $prefs = $user->preferences ?? [];

        $availability = $this->normalizeScheduleForUi(
            $prefs['weekly_schedule'] ?? $this->scheduleFromSlots($prefs['weekly_slots'] ?? null)
        );
        $settings = $prefs['session_settings'] ?? [
            'buffer_minutes'   => 0,
            'advance_days'     => 7,
            'min_notice_hours' => 2,
        ];
        $blockedDates = collect($prefs['blocked_dates'] ?? [])
            ->map(fn ($date) => (object) ['date' => $date])
            ->values();

        $pendingCount = ConsultationSession::where('mentor_id', $user->id)
            ->where('status', 'upcoming')
            ->where('scheduled_at', '>', now())
            ->count();

        return view('frontend.mentors.availability', compact(
            'availability',
            'settings',
            'blockedDates',
            'pendingCount'
        ));
    }

    public function update(Request $request)
    {
        $daysInput = $request->input('days', []);
        if (is_array($daysInput)) {
            foreach ($daysInput as $day => $row) {
                if (! is_array($row)) {
                    continue;
                }
                if (isset($row['ranges']) && is_array($row['ranges'])) {
                    foreach ($row['ranges'] as $i => $range) {
                        if (! is_array($range)) {
                            continue;
                        }
                        if (isset($range['from'])) {
                            $daysInput[$day]['ranges'][$i]['from'] = $this->normalizeTime($range['from']);
                        }
                        if (isset($range['to'])) {
                            $daysInput[$day]['ranges'][$i]['to'] = $this->normalizeTime($range['to']);
                        }
                    }
                }
                // Legacy single from/to → one range
                if (empty($row['ranges']) && (isset($row['from']) || isset($row['to']))) {
                    $daysInput[$day]['ranges'] = [[
                        'from' => $this->normalizeTime($row['from'] ?? '09:00'),
                        'to'   => $this->normalizeTime($row['to'] ?? '18:00'),
                    ]];
                }
            }
            $request->merge(['days' => $daysInput]);
        }

        $data = $request->validate([
            'days'                 => 'required|array',
            'days.*.enabled'       => 'nullable',
            'days.*.ranges'        => 'nullable|array',
            'days.*.ranges.*.from' => 'nullable|date_format:H:i',
            'days.*.ranges.*.to'   => 'nullable|date_format:H:i',
        ]);

        $schedule = [];
        $weeklySlots = [];

        foreach ($this->dayKeys() as $day) {
            $row = $data['days'][$day] ?? [];
            $enabled = ! empty($row['enabled']);
            $ranges = [];

            if ($enabled) {
                foreach ($row['ranges'] ?? [] as $range) {
                    $from = $range['from'] ?? null;
                    $to = $range['to'] ?? null;
                    if (! $from || ! $to) {
                        continue;
                    }
                    if ($from >= $to) {
                        throw ValidationException::withMessages([
                            "days.{$day}.ranges" => 'This cannot be done. The end time must be after the start time on '.ucfirst($day).'.',
                        ]);
                    }
                    $mins = (int) abs(Carbon::createFromFormat('H:i', $from)->diffInMinutes(Carbon::createFromFormat('H:i', $to)));
                    if ($mins < 15) {
                        throw ValidationException::withMessages([
                            "days.{$day}.ranges" => "Each slot on {$day} must be at least 15 minutes ({$from}–{$to}).",
                        ]);
                    }
                    if (! in_array($mins, \App\Models\ConsultationSession::BOOKING_DURATIONS, true)) {
                        throw ValidationException::withMessages([
                            "days.{$day}.ranges" => "Each slot on {$day} must be exactly 30, 60, 90, or 120 minutes ({$from}–{$to} is {$mins} min).",
                        ]);
                    }
                    $ranges[] = [
                        'from'     => $from,
                        'to'       => $to,
                        'duration' => $mins,
                    ];
                }
            }

            $ranges = $this->uniqueSortedRanges($ranges);
            $this->assertRangesDoNotOverlap($day, $ranges);

            if ($enabled && $ranges === []) {
                throw ValidationException::withMessages([
                    "days.{$day}.ranges" => "Add at least one time slot for {$day}, or turn the day off.",
                ]);
            }

            $schedule[$day] = [
                'enabled' => $enabled,
                'ranges'  => $enabled ? $ranges : [],
                'from'    => $enabled && $ranges ? $ranges[0]['from'] : null,
                'to'      => $enabled && $ranges ? $ranges[count($ranges) - 1]['to'] : null,
            ];

            $weeklySlots[$day] = $enabled
                ? array_values(array_unique(array_column($ranges, 'from')))
                : [];
        }

        $user = auth()->user();
        $kept = [];
        foreach ($schedule as $day => $row) {
            $kept[$day] = array_map(
                fn ($range) => ['from' => $range['from'], 'to' => $range['to']],
                $row['ranges'] ?? []
            );
        }
        $this->availabilityService->assertBookedWindowsRemain($user, $kept);
        $prefs = $user->preferences ?? [];
        $prefs['weekly_schedule'] = $schedule;
        $prefs['weekly_slots'] = $weeklySlots;
        $user->update(['preferences' => $prefs]);
        $user->refresh();

        $this->availabilityService->syncTableFromPreferences($user);

        return response()->json(['message' => 'Availability saved.']);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'buffer_minutes'   => 'required|integer|in:0,15,30,60',
            'advance_days'     => 'required|integer|in:3,7,14,30',
            'min_notice_hours' => 'required|integer|in:1,2,6,12,24',
        ]);

        $this->availabilityService->saveSessionSettings(auth()->user(), $data);

        return response()->json(['message' => 'Settings saved.']);
    }

    public function toggleLive(Request $request)
    {
        $user = auth()->user();
        $live = ! $this->availabilityService->isAcceptingBookings($user);
        $this->availabilityService->setAcceptingBookings($user, $live);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message'            => $live ? 'You are now live and bookable.' : 'You are now unavailable.',
                'accepting_bookings' => $live,
            ]);
        }

        return back()->with('success', $live ? 'You are now live.' : 'You are now unavailable.');
    }

    public function blockDate(Request $request)
    {
        $data = $request->validate([
            'blocked_date' => 'required|date|after_or_equal:today',
        ]);

        $this->availabilityService->blockDate(auth()->user(), $data['blocked_date']);

        return response()->json(['message' => 'Date blocked.']);
    }

    public function unblockDate(Request $request, string $date)
    {
        $this->availabilityService->unblockDate(auth()->user(), $date);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Date unblocked.']);
        }

        return back()->with('success', 'Date unblocked.');
    }

    /**
     * Identical and overlapping windows are rejected. Touching windows (10:00–11:00 then 11:00–11:30) are allowed.
     *
     * @param  list<array{from:string,to:string}>  $ranges
     */
    private function assertRangesDoNotOverlap(string $day, array $ranges): void
    {
        $label = ucfirst($day);

        for ($i = 0; $i < count($ranges); $i++) {
            for ($j = $i + 1; $j < count($ranges); $j++) {
                $a = $ranges[$i];
                $b = $ranges[$j];
                $same = $a['from'] === $b['from'] && $a['to'] === $b['to'];
                $overlaps = $a['from'] < $b['to'] && $b['from'] < $a['to'];
                if ($same || $overlaps) {
                    throw ValidationException::withMessages([
                        "days.{$day}.ranges" => "Overlapping times cannot be allowed on {$label}: {$a['from']}–{$a['to']} and {$b['from']}–{$b['to']}.",
                    ]);
                }
            }
        }
    }

    private function dayKeys(): array
    {
        return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    }

    private function normalizeTime(?string $time): ?string
    {
        if ($time === null || $time === '') {
            return $time;
        }

        try {
            return Carbon::parse($time)->format('H:i');
        } catch (\Throwable) {
            return $time;
        }
    }

    /**
     * @param  list<array{from:string,to:string,duration?:int}>  $ranges
     * @return list<array{from:string,to:string,duration:int}>
     */
    private function uniqueSortedRanges(array $ranges): array
    {
        usort($ranges, fn ($a, $b) => strcmp($a['from'].$a['to'], $b['from'].$b['to']));

        return array_values(array_map(function ($range) {
            $from = $range['from'];
            $to = $range['to'];
            $mins = (int) ($range['duration'] ?? abs(Carbon::createFromFormat('H:i', $from)->diffInMinutes(Carbon::createFromFormat('H:i', $to))));

            return [
                'from'     => $from,
                'to'       => $to,
                'duration' => $mins,
            ];
        }, $ranges));
    }

    /** Ensure UI always gets ranges[], including legacy from/to schedules. */
    private function normalizeScheduleForUi(?array $schedule): array
    {
        $out = [];
        foreach ($this->dayKeys() as $day) {
            $row = is_array($schedule[$day] ?? null) ? $schedule[$day] : [];
            $ranges = [];
            if (! empty($row['ranges']) && is_array($row['ranges'])) {
                foreach ($row['ranges'] as $range) {
                    if (! is_array($range)) {
                        continue;
                    }
                    $from = isset($range['from']) ? substr((string) $range['from'], 0, 5) : null;
                    $to = isset($range['to']) ? substr((string) $range['to'], 0, 5) : null;
                    if ($from && $to) {
                        $mins = (int) abs(Carbon::createFromFormat('H:i', $from)->diffInMinutes(Carbon::createFromFormat('H:i', $to)));
                        $ranges[] = ['from' => $from, 'to' => $to, 'duration' => $mins];
                    }
                }
            }
            if ($ranges === [] && ! empty($row['from']) && ! empty($row['to'])) {
                $from = substr((string) $row['from'], 0, 5);
                $to = substr((string) $row['to'], 0, 5);
                $mins = (int) abs(Carbon::createFromFormat('H:i', $from)->diffInMinutes(Carbon::createFromFormat('H:i', $to)));
                $ranges[] = ['from' => $from, 'to' => $to, 'duration' => $mins];
            }
            if ($ranges === []) {
                $ranges[] = ['from' => '09:00', 'to' => '10:00', 'duration' => 60];
            }

            $out[$day] = [
                'enabled' => (bool) ($row['enabled'] ?? in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'], true)),
                'ranges'  => $ranges,
                'from'    => $ranges[0]['from'],
                'to'      => $ranges[count($ranges) - 1]['to'],
            ];
        }

        return $out;
    }

    /** Convert old list-of-times format into UI schedule rows. */
    private function scheduleFromSlots(?array $weeklySlots): array
    {
        $schedule = [];
        foreach ($this->dayKeys() as $day) {
            $times = $weeklySlots[$day] ?? [];
            if (is_array($times) && $times !== [] && ! isset($times['from'])) {
                sort($times);
                $ranges = [];
                foreach ($times as $t) {
                    $from = substr((string) $t, 0, 5);
                    $to = Carbon::createFromFormat('H:i', $from)->addMinutes(30)->format('H:i');
                    $ranges[] = ['from' => $from, 'to' => $to, 'duration' => 30];
                }
                $schedule[$day] = [
                    'enabled' => true,
                    'from'    => $ranges[0]['from'],
                    'to'      => $ranges[count($ranges) - 1]['to'],
                    'ranges'  => $ranges,
                ];
            } else {
                $schedule[$day] = [
                    'enabled' => in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'], true),
                    'from'    => '09:00',
                    'to'      => '10:00',
                    'ranges'  => [['from' => '09:00', 'to' => '10:00', 'duration' => 60]],
                ];
            }
        }

        return $schedule;
    }
}
