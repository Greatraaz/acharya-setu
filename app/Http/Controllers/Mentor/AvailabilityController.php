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
            ->where('status', 'pending')
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

            foreach ($row['ranges'] ?? [] as $range) {
                $from = $range['from'] ?? null;
                $to = $range['to'] ?? null;
                if (! $from || ! $to) {
                    continue;
                }
                if ($from >= $to) {
                    throw ValidationException::withMessages([
                        "days.{$day}.ranges" => "Each slot end time must be after start time for {$day}.",
                    ]);
                }
                $mins = Carbon::createFromFormat('H:i', $from)->diffInMinutes(Carbon::createFromFormat('H:i', $to));
                if ($mins < 15) {
                    throw ValidationException::withMessages([
                        "days.{$day}.ranges" => "Each slot on {$day} must be at least 15 minutes ({$from}–{$to}).",
                    ]);
                }
                $ranges[] = [
                    'from'     => $from,
                    'to'       => $to,
                    'duration' => $mins,
                ];
            }

            $ranges = $this->uniqueSortedRanges($ranges);

            if ($enabled && $ranges === []) {
                throw ValidationException::withMessages([
                    "days.{$day}.ranges" => "Add at least one time slot for {$day}, or turn the day off.",
                ]);
            }

            foreach ($ranges as $i => $a) {
                foreach ($ranges as $j => $b) {
                    if ($i >= $j) {
                        continue;
                    }
                    // Adjacent is OK (09:00–10:00 then 10:00–10:30); only reject true overlap
                    if ($a['from'] < $b['to'] && $b['from'] < $a['to']) {
                        throw ValidationException::withMessages([
                            "days.{$day}.ranges" => "Overlapping slots on {$day}: {$a['from']}–{$a['to']} and {$b['from']}–{$b['to']}.",
                        ]);
                    }
                }
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

        $user = auth()->user();
        $prefs = $user->preferences ?? [];
        $prefs['session_settings'] = $data;
        $user->update(['preferences' => $prefs]);

        return response()->json(['message' => 'Settings saved.']);
    }

    public function toggleLive()
    {
        $user = auth()->user();
        $user->update(['is_active' => ! $user->is_active]);

        $live = (bool) $user->is_active;

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'message'   => $live ? 'You are now live and bookable.' : 'You are now unavailable.',
                'is_active' => $live,
            ]);
        }

        return back()->with('success', $live ? 'You are now live.' : 'You are now unavailable.');
    }

    public function blockDate(Request $request)
    {
        $data = $request->validate([
            'blocked_date' => 'required|date|after_or_equal:today',
        ]);

        $user = auth()->user();
        $prefs = $user->preferences ?? [];
        $dates = collect($prefs['blocked_dates'] ?? []);
        $dates->push($data['blocked_date']);
        $prefs['blocked_dates'] = $dates->unique()->sort()->values()->all();
        $user->update(['preferences' => $prefs]);

        return response()->json(['message' => 'Date blocked.']);
    }

    public function unblockDate(string $date)
    {
        $user = auth()->user();
        $prefs = $user->preferences ?? [];
        $prefs['blocked_dates'] = collect($prefs['blocked_dates'] ?? [])
            ->reject(fn ($d) => $d === $date)
            ->values()
            ->all();
        $user->update(['preferences' => $prefs]);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['message' => 'Date unblocked.']);
        }

        return back()->with('success', 'Date unblocked.');
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
        usort($ranges, fn ($a, $b) => strcmp($a['from'], $b['from']));
        $unique = [];
        foreach ($ranges as $range) {
            $from = $range['from'];
            $to = $range['to'];
            $mins = (int) ($range['duration'] ?? Carbon::createFromFormat('H:i', $from)->diffInMinutes(Carbon::createFromFormat('H:i', $to)));
            $unique[$from.'-'.$to] = [
                'from'     => $from,
                'to'       => $to,
                'duration' => $mins,
            ];
        }

        return array_values($unique);
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
                        $mins = Carbon::createFromFormat('H:i', $from)->diffInMinutes(Carbon::createFromFormat('H:i', $to));
                        $ranges[] = ['from' => $from, 'to' => $to, 'duration' => $mins];
                    }
                }
            }
            if ($ranges === [] && ! empty($row['from']) && ! empty($row['to'])) {
                $from = substr((string) $row['from'], 0, 5);
                $to = substr((string) $row['to'], 0, 5);
                $mins = Carbon::createFromFormat('H:i', $from)->diffInMinutes(Carbon::createFromFormat('H:i', $to));
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
