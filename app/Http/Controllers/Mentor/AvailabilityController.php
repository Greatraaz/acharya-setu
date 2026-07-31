<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AvailabilityController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $prefs = $user->preferences ?? [];

        $availability = $prefs['weekly_schedule'] ?? $this->scheduleFromSlots($prefs['weekly_slots'] ?? null);
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
                if (isset($row['from'])) {
                    $daysInput[$day]['from'] = $this->normalizeTime($row['from']);
                }
                if (isset($row['to'])) {
                    $daysInput[$day]['to'] = $this->normalizeTime($row['to']);
                }
            }
            $request->merge(['days' => $daysInput]);
        }

        $data = $request->validate([
            'days'                 => 'required|array',
            'days.*.enabled'       => 'nullable',
            'days.*.from'          => 'nullable|date_format:H:i',
            'days.*.to'            => 'nullable|date_format:H:i',
            'days.*.slot_duration' => 'nullable|integer|in:30,60,90',
        ]);

        $schedule = [];
        $weeklySlots = [];

        foreach ($this->dayKeys() as $day) {
            $row = $data['days'][$day] ?? [];
            $enabled = ! empty($row['enabled']);
            $from = $row['from'] ?? '09:00';
            $to = $row['to'] ?? '18:00';
            $duration = (int) ($row['slot_duration'] ?? 30);

            if ($enabled && $from >= $to) {
                throw ValidationException::withMessages([
                    "days.{$day}.to" => "End time must be after start time for {$day}.",
                ]);
            }

            $schedule[$day] = [
                'enabled'       => $enabled,
                'from'          => $from,
                'to'            => $to,
                'slot_duration' => $duration,
            ];

            $weeklySlots[$day] = $enabled
                ? $this->generateSlots($from, $to, $duration)
                : [];
        }

        $user = auth()->user();
        $prefs = $user->preferences ?? [];
        $prefs['weekly_schedule'] = $schedule;
        $prefs['weekly_slots'] = $weeklySlots;
        $user->update(['preferences' => $prefs]);

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

    private function generateSlots(string $from, string $to, int $durationMinutes): array
    {
        $slots = [];
        $cursor = Carbon::createFromFormat('H:i', $from);
        $end = Carbon::createFromFormat('H:i', $to);

        while ($cursor->copy()->addMinutes($durationMinutes)->lte($end)) {
            $slots[] = $cursor->format('H:i');
            $cursor->addMinutes($durationMinutes);
        }

        return $slots;
    }

    /** Convert old list-of-times format into UI schedule rows. */
    private function scheduleFromSlots(?array $weeklySlots): array
    {
        $schedule = [];
        foreach ($this->dayKeys() as $day) {
            $times = $weeklySlots[$day] ?? [];
            if (is_array($times) && $times !== [] && ! isset($times['from'])) {
                sort($times);
                $schedule[$day] = [
                    'enabled'       => true,
                    'from'          => $times[0],
                    'to'            => end($times) ?: '18:00',
                    'slot_duration' => 30,
                ];
            } else {
                $schedule[$day] = [
                    'enabled'       => in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'], true),
                    'from'          => '09:00',
                    'to'            => '18:00',
                    'slot_duration' => 30,
                ];
            }
        }

        return $schedule;
    }
}
