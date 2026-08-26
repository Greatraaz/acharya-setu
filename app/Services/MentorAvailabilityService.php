<?php

namespace App\Services;

use App\Models\ConsultationSession;
use App\Models\MentorAvailability;
use App\Models\User;
use Carbon\Carbon;

class MentorAvailabilityService
{
    private const DEFAULT_SLOTS = ['09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00'];

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
            $slots = $this->openSlotsForDate($mentor, $date->toDateString(), $schedule);
            $meta = $schedule['days'][$dayKey] ?? null;
            $enabled = $meta['enabled'] ?? (! $hasSchedule);
            $label = null;
            if ($enabled) {
                $label = $meta['label'] ?? null;
                if (! $label && count($slots)) {
                    $label = $slots[0].'–'.end($slots);
                }
            }

            $daysOut[] = [
                'date'       => $date->toDateString(),
                'day'        => $dayKey,
                'available'  => count($slots) > 0,
                'slot_count' => count($slots),
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

        if (! $hasSchedule) {
            foreach ($summary as &$row) {
                $row['enabled'] = true;
                $row['from'] = '09:00';
                $row['to'] = '19:00';
                $row['ranges'] = [['from' => '09:00', 'to' => '19:00']];
                $row['windows'] = '09:00–19:00';
            }
            unset($row);
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
        $booked = $this->bookedTimes($mentor->id, $date);
        $meta = $schedule['days'][$dayKey] ?? null;

        $options = $this->slotOptionsForDate($mentor, $date, $schedule);
        // Mentee may book any duration that fits inside the mentor window
        // e.g. 09:00–09:30 (30m) allows 15 or 30, but not 60.
        if ($duration !== null && $duration > 0) {
            $options = array_values(array_filter(
                $options,
                fn ($opt) => (int) $opt['duration'] >= (int) $duration
            ));
        }

        $options = array_values(array_filter(
            $options,
            fn ($opt) => ! in_array($opt['start_time'], $booked, true)
        ));
        $starts = array_column($options, 'start_time');
        $starts = $this->excludePastSlots($date, $starts);
        $options = array_values(array_filter(
            $options,
            fn ($opt) => in_array($opt['start_time'], $starts, true)
        ));

        return [
            'date'         => $date,
            'day'          => $dayKey,
            'slots'        => array_values(array_unique($starts)),
            'slot_options' => $options,
            'booked'       => $booked,
            'available'    => count($starts) > 0,
            'label'        => ($meta['enabled'] ?? false) ? ($meta['label'] ?? null) : null,
            'ranges'       => ($meta['enabled'] ?? false) ? ($meta['ranges'] ?? []) : [],
        ];
    }

    public function isSlotOpen(User $mentor, string $date, string $time, ?int $duration = null): bool
    {
        $time = substr($time, 0, 5);
        $payload = $this->slotsForDate($mentor, $date, $duration);

        if ($duration !== null && $duration > 0) {
            foreach ($payload['slot_options'] as $opt) {
                if ($opt['start_time'] === $time && (int) $opt['duration'] >= (int) $duration) {
                    return true;
                }
            }

            return false;
        }

        return in_array($time, $payload['slots'], true);
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
     * @param  array{has_schedule:bool,days:array,blocked_dates?:array}  $schedule
     * @return list<array{start_time:string,end_time:string,duration:int}>
     */
    private function slotOptionsForDate(User $mentor, string $date, array $schedule): array
    {
        if (in_array($date, $schedule['blocked_dates'] ?? [], true)) {
            return [];
        }

        $dayKey = strtolower(Carbon::parse($date, 'Asia/Kolkata')->format('l'));
        $day = $schedule['days'][$dayKey] ?? null;

        if ($schedule['has_schedule']) {
            if (! ($day['enabled'] ?? false)) {
                return [];
            }

            $options = [];
            foreach ($day['ranges'] ?? [] as $range) {
                $duration = (int) ($range['duration'] ?? $this->minutesBetween($range['from'], $range['to']));
                if ($duration < 15) {
                    continue;
                }
                $options[] = [
                    'start_time' => $range['from'],
                    'end_time'   => $range['to'],
                    'duration'   => $duration,
                ];
            }

            return $options;
        }

        // No schedule configured → default hourly starts as 60-min windows
        return array_map(function ($time) {
            $end = Carbon::createFromFormat('H:i', $time, 'Asia/Kolkata')->addMinutes(60)->format('H:i');

            return [
                'start_time' => $time,
                'end_time'   => $end,
                'duration'   => 60,
            ];
        }, self::DEFAULT_SLOTS);
    }

    /**
     * @param  array{has_schedule:bool,days:array,blocked_dates?:array}  $schedule
     * @return list<string>
     */
    private function openSlotsForDate(User $mentor, string $date, array $schedule): array
    {
        $options = $this->slotOptionsForDate($mentor, $date, $schedule);
        $open = array_column($options, 'start_time');
        $booked = $this->bookedTimes($mentor->id, $date);

        return $this->excludePastSlots($date, array_values(array_diff($open, $booked)));
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

            return $slotAt->greaterThan($cutoff);
        }));
    }

    /**
     * @return list<string>
     */
    private function bookedTimes(int $mentorId, string $date): array
    {
        ConsultationSession::expireAbandonedUnpaidPayments();

        return ConsultationSession::where('mentor_id', $mentorId)
            ->whereDate('scheduled_at', $date)
            ->occupyingSlot()
            ->pluck('scheduled_at')
            ->map(fn ($dt) => Carbon::parse($dt)->format('H:i'))
            ->unique()
            ->values()
            ->all();
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
     * Each window is one bookable start (duration = window length).
     *
     * @param  list<array{from:string,to:string,duration?:int}>  $ranges
     * @return list<string>
     */
    private function startsFromRanges(array $ranges): array
    {
        $slots = [];
        foreach ($ranges as $range) {
            $duration = (int) ($range['duration'] ?? $this->minutesBetween($range['from'], $range['to']));
            if ($duration >= 15) {
                $slots[] = $range['from'];
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
