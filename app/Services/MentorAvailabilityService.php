<?php

namespace App\Services;

use App\Models\ConsultationSession;
use App\Models\MentorAvailability;
use App\Models\User;
use Carbon\Carbon;

class MentorAvailabilityService
{
    private const DAY_KEYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    /**
     * @return array{
     *   has_schedule: bool,
     *   weekly_summary: list<array{day:string,label:string,enabled:bool,from:?string,to:?string,ranges:list<array{from:string,to:string}>,windows:?string}>,
     *   days: list<array{date:string,day:string,available:bool,slot_count:int,label:?string}>
     * }
     */
    public function weekOverview(User $mentor, ?Carbon $start = null, int $days = 14): array
    {
        $start = ($start ?? Carbon::now('Asia/Kolkata')->startOfDay())->copy()->timezone('Asia/Kolkata')->startOfDay();
        $schedule = $this->resolvedSchedule($mentor);
        $hasSchedule = $schedule['has_schedule'];

        $daysOut = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $dayKey = strtolower($date->format('l'));
            $payload = $this->slotsForDate($mentor, $date->toDateString());
            $slots = $payload['slots'];
            $meta = $schedule['days'][$dayKey] ?? null;
            $enabled = $meta['enabled'] ?? false;
            $label = null;
            if ($enabled && ($payload['available'] ?? false)) {
                $label = $meta['label'] ?? null;
                if (! $label && ($payload['ranges'][0]['from'] ?? null)) {
                    $first = $payload['ranges'][0]['from'];
                    $last = $payload['ranges'][count($payload['ranges']) - 1]['to'] ?? $first;
                    $label = $first.'–'.$last;
                }
            }

            $daysOut[] = [
                'date'       => $date->toDateString(),
                'day'        => $dayKey,
                'available'  => (bool) ($payload['available'] ?? false),
                'slot_count' => (int) ($payload['available'] ? max(1, count($slots)) : 0),
                'label'      => $label,
            ];
        }

        $summary = [];
        foreach (self::DAY_KEYS as $day) {
            $row = $schedule['days'][$day] ?? ['enabled' => false, 'from' => null, 'to' => null, 'ranges' => []];
            $summary[] = [
                'day'     => $day,
                'label'   => ucfirst(substr($day, 0, 3)),
                'enabled' => (bool) ($row['enabled'] ?? false),
                'from'    => $row['from'] ?? null,
                'to'      => $row['to'] ?? null,
                'ranges'  => $row['ranges'] ?? [],
                'windows' => $row['label'] ?? null,
            ];
        }

        return [
            'has_schedule'   => $hasSchedule,
            'weekly_summary' => $summary,
            'days'           => $daysOut,
        ];
    }

    /**
     * @return array{
     *   date:string,
     *   slots:list<string>,
     *   slot_options:list<array{start_time:string,end_time:string,duration:int}>,
     *   booked:list<string>,
     *   available:bool,
     *   day:string,
     *   label:?string,
     *   ranges:list<array{from:string,to:string,duration:int}>
     * }
     */
    public function slotsForDate(User $mentor, string $date, ?int $duration = null): array
    {
        $schedule = $this->resolvedSchedule($mentor);
        $dayKey = strtolower(Carbon::parse($date, 'Asia/Kolkata')->format('l'));
        $occupied = $this->occupiedIntervals($mentor->id, $date);
        $meta = $schedule['days'][$dayKey] ?? null;
        $ranges = ($meta['enabled'] ?? false) ? ($meta['ranges'] ?? []) : [];

        if (in_array($date, $schedule['blocked_dates'] ?? [], true)) {
            $ranges = [];
        }

        $duration = ($duration !== null && $duration > 0) ? (int) $duration : null;

        // Optional discrete list (1-min grid) when a duration is requested — useful for clients
        // that still render chips. Booking itself accepts ANY HH:MM via isSlotOpen().
        $options = [];
        if ($duration !== null) {
            $options = $this->expandWindowStarts($ranges, $duration, 1);
            $options = array_values(array_filter(
                $options,
                fn (array $opt) => ! $this->overlapsAny(
                    $opt['start_time'],
                    $opt['end_time'],
                    $occupied
                )
            ));
            $starts = $this->excludePastSlots($date, array_column($options, 'start_time'));
            $options = array_values(array_filter(
                $options,
                fn ($opt) => in_array($opt['start_time'], $starts, true)
            ));
        } else {
            $starts = [];
            foreach ($ranges as $range) {
                $from = substr((string) ($range['from'] ?? ''), 0, 5);
                if ($from !== '') {
                    $starts[] = $from;
                }
            }
            $starts = $this->excludePastSlots($date, $starts);
        }

        $available = $this->dayHasBookableMoment($date, $ranges, $occupied, $duration);

        return [
            'date'             => $date,
            'day'              => $dayKey,
            'slots'            => array_values(array_unique(array_column($options, 'start_time') ?: $starts)),
            'slot_options'     => $options,
            'booked'           => array_values(array_unique(array_column($occupied, 'start'))),
            'booked_intervals' => array_map(fn ($b) => [
                'start' => $b['start'],
                'end'   => $b['end'],
            ], $occupied),
            'available'        => $available,
            'has_schedule'     => (bool) $schedule['has_schedule'],
            'label'            => ($meta['enabled'] ?? false) ? ($meta['label'] ?? null) : null,
            'ranges'           => $ranges,
            'free_start'       => true,
        ];
    }

    public function isSlotOpen(User $mentor, string $date, string $time, ?int $duration = null): bool
    {
        $time = substr(trim($time), 0, 5);
        if (! preg_match('/^\d{2}:\d{2}$/', $time)) {
            return false;
        }

        $duration = ($duration !== null && $duration > 0) ? (int) $duration : null;
        if ($duration === null) {
            return false;
        }
        if (! in_array($duration, ConsultationSession::BOOKING_DURATIONS, true)) {
            return false;
        }

        if ($this->excludePastSlots($date, [$time]) === []) {
            return false;
        }

        $schedule = $this->resolvedSchedule($mentor);
        if (! $this->fitsInsideWindows($schedule, $date, $time, $duration)) {
            return false;
        }

        $end = $this->addMinutes($time, $duration);

        return ! $this->overlapsAny($time, $end, $this->occupiedIntervals($mentor->id, $date));
    }

    /**
     * True when [time, time+duration) overlaps an existing booking/hold.
     */
    public function overlapsExisting(
        int $mentorId,
        string $date,
        string $time,
        int $duration,
        ?int $exceptMenteeId = null
    ): bool {
        $time = substr($time, 0, 5);
        $end = $this->addMinutes($time, $duration);

        return $this->overlapsAny($time, $end, $this->occupiedIntervals($mentorId, $date, $exceptMenteeId));
    }

    /**
     * Start + duration must fall entirely inside one mentor availability window.
     *
     * @param  array{has_schedule:bool,days:array,blocked_dates?:array}  $schedule
     */
    private function fitsInsideWindows(array $schedule, string $date, string $time, int $duration): bool
    {
        if (! ($schedule['has_schedule'] ?? false)) {
            return false;
        }
        if (in_array($date, $schedule['blocked_dates'] ?? [], true)) {
            return false;
        }

        $dayKey = strtolower(Carbon::parse($date, 'Asia/Kolkata')->format('l'));
        $day = $schedule['days'][$dayKey] ?? null;
        if (! ($day['enabled'] ?? false)) {
            return false;
        }

        $end = $this->addMinutes($time, $duration);
        $startMin = $this->timeToMinutes($time);
        $endMin = $this->timeToMinutes($end);
        if ($endMin <= $startMin) {
            return false;
        }

        foreach ($day['ranges'] ?? [] as $range) {
            $from = substr((string) ($range['from'] ?? ''), 0, 5);
            $to = substr((string) ($range['to'] ?? ''), 0, 5);
            if (! $from || ! $to) {
                continue;
            }
            $fromMin = $this->timeToMinutes($from);
            $toMin = $this->timeToMinutes($to);
            // Must start on/after window start and finish on/before window end.
            if ($startMin >= $fromMin && $endMin <= $toMin) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array{from:string,to:string,duration?:int}>  $ranges
     * @param  list<array{start:string,end:string}>  $occupied
     */
    private function dayHasBookableMoment(
        string $date,
        array $ranges,
        array $occupied,
        ?int $duration = null
    ): bool {
        $need = $duration ?? min(ConsultationSession::BOOKING_DURATIONS);
        foreach ($this->expandWindowStarts($ranges, $need, 1) as $opt) {
            if ($this->excludePastSlots($date, [$opt['start_time']]) === []) {
                continue;
            }
            if (! $this->overlapsAny($opt['start_time'], $opt['end_time'], $occupied)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array{from:string,to:string,duration?:int}>  $ranges
     * @return list<array{start_time:string,end_time:string,duration:int}>
     */
    private function expandWindowStarts(array $ranges, int $duration, int $stepMinutes = 1): array
    {
        $options = [];
        $stepMinutes = max(1, $stepMinutes);

        foreach ($ranges as $range) {
            $from = substr((string) ($range['from'] ?? ''), 0, 5);
            $to = substr((string) ($range['to'] ?? ''), 0, 5);
            if (! $from || ! $to || $from >= $to) {
                continue;
            }

            try {
                $cursor = Carbon::createFromFormat('H:i', $from, 'Asia/Kolkata');
                $windowEnd = Carbon::createFromFormat('H:i', $to, 'Asia/Kolkata');
            } catch (\Throwable) {
                continue;
            }

            $latestStart = $windowEnd->copy()->subMinutes($duration);
            if ($latestStart->lt($cursor)) {
                continue;
            }

            while ($cursor->lte($latestStart)) {
                $end = $cursor->copy()->addMinutes($duration);
                $options[] = [
                    'start_time' => $cursor->format('H:i'),
                    'end_time'   => $end->format('H:i'),
                    'duration'   => $duration,
                ];
                $cursor->addMinutes($stepMinutes);
            }
        }

        return $options;
    }

    /**
     * Build preference schedule + weekly_slots from discrete mentor_availabilities rows.
     * Each row is one bookable window; duration = end − start.
     *
     * @param  iterable<MentorAvailability|array{day_of_week:string,start_time:string,end_time:string,is_available?:bool}>  $rows
     * @return array{weekly_schedule:array,weekly_slots:array}
     */
    public function preferencesFromRows(iterable $rows, int $slotDuration = 30): array
    {
        $schedule = [];
        $weeklySlots = [];

        foreach (self::DAY_KEYS as $day) {
            $schedule[$day] = [
                'enabled' => false,
                'ranges'  => [],
                'from'    => null,
                'to'      => null,
            ];
            $weeklySlots[$day] = [];
        }

        foreach ($rows as $row) {
            $available = is_array($row)
                ? ($row['is_available'] ?? true)
                : (bool) $row->is_available;
            if (! $available) {
                continue;
            }

            $dayRaw = is_array($row) ? ($row['day_of_week'] ?? '') : $row->day_of_week;
            $day = strtolower((string) $dayRaw);
            if (! isset($schedule[$day])) {
                continue;
            }

            $from = substr((string) (is_array($row) ? $row['start_time'] : $row->start_time), 0, 5);
            $to = substr((string) (is_array($row) ? $row['end_time'] : $row->end_time), 0, 5);
            if (! $from || ! $to || $from >= $to) {
                continue;
            }

            $schedule[$day]['enabled'] = true;
            $schedule[$day]['ranges'][] = $this->makeRange($from, $to);
        }

        foreach (self::DAY_KEYS as $day) {
            $ranges = $this->normalizeRanges($schedule[$day]['ranges']);
            $schedule[$day]['ranges'] = $ranges;
            $schedule[$day]['from'] = $ranges[0]['from'] ?? null;
            $schedule[$day]['to'] = $ranges ? $ranges[count($ranges) - 1]['to'] : null;
            $weeklySlots[$day] = $schedule[$day]['enabled']
                ? $this->startsFromRanges($ranges)
                : [];
        }

        return [
            'weekly_schedule' => $schedule,
            'weekly_slots'    => $weeklySlots,
        ];
    }

    /**
     * Persist preference weekly_schedule ranges into mentor_availabilities rows.
     */
    public function syncTableFromPreferences(User $mentor): void
    {
        $prefs = is_array($mentor->preferences) ? $mentor->preferences : [];
        $weekly = $prefs['weekly_schedule'] ?? [];
        if (! is_array($weekly)) {
            return;
        }

        MentorAvailability::where('mentor_id', $mentor->id)->delete();

        foreach (self::DAY_KEYS as $day) {
            $row = $weekly[$day] ?? null;
            if (! is_array($row) || empty($row['enabled'])) {
                continue;
            }

            $ranges = $this->extractRanges($row);
            foreach ($ranges as $range) {
                MentorAvailability::create([
                    'mentor_id'    => $mentor->id,
                    'day_of_week'  => ucfirst($day),
                    'start_time'   => $range['from'],
                    'end_time'     => $range['to'],
                    'is_available' => true,
                ]);
            }
        }
    }

    /**
     * Mirror mentor_availabilities rows into user preferences (source of truth for web booking).
     */
    public function syncPreferencesFromTable(User $mentor, int $slotDuration = 30): void
    {
        $rows = MentorAvailability::where('mentor_id', $mentor->id)->get();
        $built = $this->preferencesFromRows($rows, $slotDuration);
        $prefs = is_array($mentor->preferences) ? $mentor->preferences : [];
        $prefs['weekly_schedule'] = $built['weekly_schedule'];
        $prefs['weekly_slots'] = $built['weekly_slots'];
        $mentor->update(['preferences' => $prefs]);
    }

    /**
     * @return array{
     *   has_schedule: bool,
     *   days: array<string, array{enabled:bool,from:?string,to:?string,ranges:list<array{from:string,to:string}>,label:?string,slots:list<string>}>,
     *   blocked_dates: list<string>
     * }
     */
    private function resolvedSchedule(User $mentor): array
    {
        $prefs = is_array($mentor->preferences) ? $mentor->preferences : [];
        $weeklySlots = $prefs['weekly_slots'] ?? null;
        $weeklySchedule = $prefs['weekly_schedule'] ?? null;
        $blocked = collect($prefs['blocked_dates'] ?? [])->map(fn ($d) => (string) $d)->all();

        $days = [];
        foreach (self::DAY_KEYS as $day) {
            $days[$day] = [
                'enabled' => false,
                'from'    => null,
                'to'      => null,
                'ranges'  => [],
                'label'   => null,
                'slots'   => [],
            ];
        }

        $hasSchedule = false;

        if (is_array($weeklySchedule)) {
            foreach ($weeklySchedule as $day => $row) {
                $day = strtolower((string) $day);
                if (! isset($days[$day]) || ! is_array($row)) {
                    continue;
                }

                $enabled = ! empty($row['enabled']);
                $ranges = $enabled ? $this->extractRanges($row) : [];
                $slots = [];

                if ($enabled && is_array($weeklySlots[$day] ?? null) && count($weeklySlots[$day]) && $ranges === []) {
                    // Legacy list of start times only — keep as-is
                    $slots = array_values(array_unique(array_map(
                        fn ($t) => substr((string) $t, 0, 5),
                        $weeklySlots[$day]
                    )));
                    sort($slots);
                    if ($slots) {
                        $ranges = array_map(
                            fn ($t) => $this->makeRange($t, Carbon::createFromFormat('H:i', $t, 'Asia/Kolkata')->addMinutes(30)->format('H:i')),
                            $slots
                        );
                    }
                } elseif ($enabled && $ranges) {
                    $slots = $this->startsFromRanges($ranges);
                }

                $days[$day] = [
                    'enabled' => $enabled,
                    'from'    => $ranges[0]['from'] ?? null,
                    'to'      => $ranges ? $ranges[count($ranges) - 1]['to'] : null,
                    'ranges'  => $ranges,
                    'label'   => $this->labelForRanges($ranges),
                    'slots'   => $slots,
                ];

                if ($enabled && ($ranges || $slots)) {
                    $hasSchedule = true;
                }
            }
        } elseif (is_array($weeklySlots)) {
            foreach ($weeklySlots as $day => $slots) {
                $day = strtolower((string) $day);
                if (! isset($days[$day])) {
                    continue;
                }
                $list = is_array($slots)
                    ? array_values(array_unique(array_map(fn ($t) => substr((string) $t, 0, 5), $slots)))
                    : [];
                sort($list);
                $enabled = count($list) > 0;
                $ranges = $enabled
                    ? array_map(
                        fn ($t) => $this->makeRange($t, Carbon::createFromFormat('H:i', $t, 'Asia/Kolkata')->addMinutes(30)->format('H:i')),
                        $list
                    )
                    : [];
                $days[$day] = [
                    'enabled' => $enabled,
                    'from'    => $list[0] ?? null,
                    'to'      => $list ? end($list) : null,
                    'ranges'  => $ranges,
                    'label'   => $this->labelForRanges($ranges),
                    'slots'   => $list,
                ];
                if ($enabled) {
                    $hasSchedule = true;
                }
            }
        }

        // Fallback: mentor_availabilities table (API/mobile) — already multi-range capable
        if (! $hasSchedule) {
            $rows = MentorAvailability::where('mentor_id', $mentor->id)
                ->where('is_available', true)
                ->get();
            if ($rows->isNotEmpty()) {
                $hasSchedule = true;
                foreach ($rows as $row) {
                    $day = strtolower((string) $row->day_of_week);
                    if (! isset($days[$day])) {
                        continue;
                    }
                    $from = substr((string) $row->start_time, 0, 5);
                    $to = substr((string) $row->end_time, 0, 5);
                    $ranges = $days[$day]['ranges'];
                    $ranges[] = $this->makeRange($from, $to);
                    $ranges = $this->normalizeRanges($ranges);
                    $merged = $this->startsFromRanges($ranges);
                    $days[$day] = [
                        'enabled' => true,
                        'from'    => $ranges[0]['from'] ?? $from,
                        'to'      => $ranges ? $ranges[count($ranges) - 1]['to'] : $to,
                        'ranges'  => $ranges,
                        'label'   => $this->labelForRanges($ranges),
                        'slots'   => $merged,
                    ];
                }
            }
        }

        return [
            'has_schedule'  => $hasSchedule,
            'days'          => $days,
            'blocked_dates' => $blocked,
        ];
    }

    /**
     * @param  list<string>  $slots
     * @return list<string>
     */
    private function excludePastSlots(string $date, array $slots): array
    {
        $now = Carbon::now('Asia/Kolkata');
        if ($date !== $now->toDateString()) {
            return array_values($slots);
        }

        $cutoff = $now->copy()->addMinutes(5);

        return array_values(array_filter($slots, function ($time) use ($date, $cutoff) {
            try {
                $slotAt = Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $date.' '.substr((string) $time, 0, 5),
                    'Asia/Kolkata'
                );
            } catch (\Throwable) {
                return false;
            }

            // Allow booking up to exactly 5 minutes before the slot starts.
            return $slotAt->greaterThanOrEqualTo($cutoff);
        }));
    }

    /**
     * Occupied [start, end) intervals for a mentor on a date (bookings + payment holds).
     *
     * @return list<array{start:string,end:string,mentee_id:?int}>
     */
    private function occupiedIntervals(int $mentorId, string $date, ?int $exceptMenteeId = null): array
    {
        $intervals = [];

        $sessions = ConsultationSession::where('mentor_id', $mentorId)
            ->whereDate('scheduled_at', $date)
            ->occupyingSlot()
            ->get(['scheduled_at', 'duration_minutes', 'mentee_id']);

        foreach ($sessions as $session) {
            $start = Carbon::parse($session->scheduled_at)->timezone('Asia/Kolkata');
            $mins = max(15, (int) ($session->duration_minutes ?: 15));
            $intervals[] = [
                'start'     => $start->format('H:i'),
                'end'       => $start->copy()->addMinutes($mins)->format('H:i'),
                'mentee_id' => (int) $session->mentee_id,
            ];
        }

        foreach (ConsultationSession::heldIntervalsForDate($mentorId, $date) as $hold) {
            if ($exceptMenteeId !== null && (int) ($hold['mentee_id'] ?? 0) === (int) $exceptMenteeId) {
                continue;
            }
            $intervals[] = [
                'start'     => $hold['start'],
                'end'       => $hold['end'],
                'mentee_id' => isset($hold['mentee_id']) ? (int) $hold['mentee_id'] : null,
            ];
        }

        return $intervals;
    }

    /**
     * @param  list<array{start:string,end:string}>  $occupied
     */
    private function overlapsAny(string $start, string $end, array $occupied): bool
    {
        $a0 = $this->timeToMinutes($start);
        $a1 = $this->timeToMinutes($end);
        if ($a1 <= $a0) {
            return true;
        }

        foreach ($occupied as $block) {
            $b0 = $this->timeToMinutes($block['start']);
            $b1 = $this->timeToMinutes($block['end']);
            // Half-open intervals: [start, end)
            if ($a0 < $b1 && $a1 > $b0) {
                return true;
            }
        }

        return false;
    }

    private function timeToMinutes(string $time): int
    {
        $parts = explode(':', substr($time, 0, 5));

        return ((int) ($parts[0] ?? 0) * 60) + (int) ($parts[1] ?? 0);
    }

    private function addMinutes(string $time, int $minutes): string
    {
        try {
            return Carbon::createFromFormat('H:i', substr($time, 0, 5), 'Asia/Kolkata')
                ->addMinutes($minutes)
                ->format('H:i');
        } catch (\Throwable) {
            return $time;
        }
    }

    /**
     * @deprecated Prefer occupiedIntervals(); kept for any legacy callers.
     * @return list<string>
     */
    private function bookedTimes(int $mentorId, string $date): array
    {
        return array_values(array_unique(array_column(
            $this->occupiedIntervals($mentorId, $date),
            'start'
        )));
    }

    /**
     * @param  array{enabled?:mixed,from?:mixed,to?:mixed,ranges?:mixed}  $row
     * @return list<array{from:string,to:string,duration:int}>
     */
    private function extractRanges(array $row): array
    {
        $ranges = [];

        if (! empty($row['ranges']) && is_array($row['ranges'])) {
            foreach ($row['ranges'] as $range) {
                if (! is_array($range)) {
                    continue;
                }
                $from = isset($range['from']) ? substr((string) $range['from'], 0, 5) : null;
                $to = isset($range['to']) ? substr((string) $range['to'], 0, 5) : null;
                if ($from && $to && $from < $to) {
                    $ranges[] = $this->makeRange($from, $to);
                }
            }
        }

        if ($ranges === []) {
            $from = isset($row['from']) ? substr((string) $row['from'], 0, 5) : null;
            $to = isset($row['to']) ? substr((string) $row['to'], 0, 5) : null;
            if ($from && $to && $from < $to) {
                $ranges[] = $this->makeRange($from, $to);
            }
        }

        return $this->normalizeRanges($ranges);
    }

    /**
     * @return array{from:string,to:string,duration:int}
     */
    private function makeRange(string $from, string $to): array
    {
        return [
            'from'     => $from,
            'to'       => $to,
            'duration' => $this->minutesBetween($from, $to),
        ];
    }

    private function minutesBetween(string $from, string $to): int
    {
        try {
            $a = Carbon::createFromFormat('H:i', $from, 'Asia/Kolkata');
            $b = Carbon::createFromFormat('H:i', $to, 'Asia/Kolkata');
        } catch (\Throwable) {
            return 0;
        }

        return max(0, $a->diffInMinutes($b));
    }

    /**
     * @param  list<array{from:string,to:string,duration?:int}>  $ranges
     * @return list<array{from:string,to:string,duration:int}>
     */
    private function normalizeRanges(array $ranges): array
    {
        usort($ranges, fn ($a, $b) => strcmp($a['from'], $b['from']));

        $unique = [];
        foreach ($ranges as $range) {
            $from = $range['from'];
            $to = $range['to'];
            $key = $from.'-'.$to;
            $unique[$key] = $this->makeRange($from, $to);
        }

        return array_values($unique);
    }

    /**
     * All 15-min start times inside each window that still fit a minimum booking.
     *
     * @param  list<array{from:string,to:string,duration?:int}>  $ranges
     * @return list<string>
     */
    private function startsFromRanges(array $ranges): array
    {
        $slots = [];
        $step = min(ConsultationSession::BOOKING_DURATIONS);

        foreach ($ranges as $range) {
            $from = substr((string) ($range['from'] ?? ''), 0, 5);
            $to = substr((string) ($range['to'] ?? ''), 0, 5);
            if (! $from || ! $to || $from >= $to) {
                continue;
            }

            try {
                $cursor = Carbon::createFromFormat('H:i', $from, 'Asia/Kolkata');
                $windowEnd = Carbon::createFromFormat('H:i', $to, 'Asia/Kolkata');
            } catch (\Throwable) {
                continue;
            }

            while ($cursor->lt($windowEnd)) {
                $remaining = $cursor->diffInMinutes($windowEnd);
                if ($remaining < $step) {
                    break;
                }
                $slots[] = $cursor->format('H:i');
                $cursor->addMinutes($step);
            }
        }

        return array_values(array_unique($slots));
    }

    /**
     * @param  list<array{from:string,to:string,duration?:int}>  $ranges
     */
    private function labelForRanges(array $ranges): ?string
    {
        if ($ranges === []) {
            return null;
        }

        return implode(', ', array_map(function ($r) {
            $mins = (int) ($r['duration'] ?? $this->minutesBetween($r['from'], $r['to']));

            return $r['from'].'–'.$r['to'].' ('.$mins.'m)';
        }, $ranges));
    }
}
