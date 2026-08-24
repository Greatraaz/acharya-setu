<?php

namespace App\Services;

use App\Models\ConsultationSession;
use App\Models\MentorAvailability;
use App\Models\User;
use Carbon\Carbon;

class MentorAvailabilityService
{
    private const DEFAULT_SLOTS = ['09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00'];

    /**
     * @return array{
     *   has_schedule: bool,
     *   weekly_summary: list<array{day:string,label:string,enabled:bool,from:?string,to:?string}>,
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
            if ($enabled && ($meta['from'] ?? null) && ($meta['to'] ?? null)) {
                $label = $meta['from'].'–'.$meta['to'];
            } elseif ($enabled && count($slots)) {
                $label = $slots[0].'–'.end($slots);
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
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $row = $schedule['days'][$day] ?? ['enabled' => false, 'from' => null, 'to' => null];
            $summary[] = [
                'day'     => $day,
                'label'   => ucfirst(substr($day, 0, 3)),
                'enabled' => (bool) ($row['enabled'] ?? false),
                'from'    => $row['from'] ?? null,
                'to'      => $row['to'] ?? null,
            ];
        }

        // If mentor never set a schedule, show all days as typically available (defaults).
        if (! $hasSchedule) {
            foreach ($summary as &$row) {
                $row['enabled'] = true;
                $row['from'] = '09:00';
                $row['to'] = '19:00';
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
     * @return array{date:string,slots:list<string>,booked:list<string>,available:bool,day:string,label:?string}
     */
    public function slotsForDate(User $mentor, string $date): array
    {
        $schedule = $this->resolvedSchedule($mentor);
        $dayKey = strtolower(Carbon::parse($date, 'Asia/Kolkata')->format('l'));
        $open = $this->rawOpenSlots($mentor, $date, $schedule);
        $booked = $this->bookedTimes($mentor->id, $date);
        $available = $this->excludePastSlots($date, array_values(array_diff($open, $booked)));
        $meta = $schedule['days'][$dayKey] ?? null;
        $label = null;
        if (($meta['enabled'] ?? false) && ($meta['from'] ?? null) && ($meta['to'] ?? null)) {
            $label = $meta['from'].'–'.$meta['to'];
        }

        return [
            'date'      => $date,
            'day'       => $dayKey,
            'slots'     => $available,
            'booked'    => $booked,
            'available' => count($available) > 0,
            'label'     => $label,
        ];
    }

    /**
     * @return array{
     *   has_schedule: bool,
     *   days: array<string, array{enabled:bool,from:?string,to:?string,slots:list<string>}>
     * }
     */
    private function resolvedSchedule(User $mentor): array
    {
        $prefs = is_array($mentor->preferences) ? $mentor->preferences : [];
        $weeklySlots = $prefs['weekly_slots'] ?? null;
        $weeklySchedule = $prefs['weekly_schedule'] ?? null;
        $blocked = collect($prefs['blocked_dates'] ?? [])->map(fn ($d) => (string) $d)->all();

        $days = [];
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $days[$day] = [
                'enabled' => false,
                'from'    => null,
                'to'      => null,
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
                $from = isset($row['from']) ? substr((string) $row['from'], 0, 5) : null;
                $to = isset($row['to']) ? substr((string) $row['to'], 0, 5) : null;
                $slots = [];
                if ($enabled && is_array($weeklySlots[$day] ?? null) && count($weeklySlots[$day])) {
                    $slots = array_values(array_map(fn ($t) => substr((string) $t, 0, 5), $weeklySlots[$day]));
                } elseif ($enabled && $from && $to) {
                    $duration = (int) ($row['slot_duration'] ?? 30);
                    $slots = $this->generateSlots($from, $to, max(30, $duration));
                }
                $days[$day] = [
                    'enabled' => $enabled,
                    'from'    => $from,
                    'to'      => $to,
                    'slots'   => $slots,
                ];
                if ($enabled) {
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
                    ? array_values(array_map(fn ($t) => substr((string) $t, 0, 5), $slots))
                    : [];
                $enabled = count($list) > 0;
                $days[$day] = [
                    'enabled' => $enabled,
                    'from'    => $list[0] ?? null,
                    'to'      => $list ? end($list) : null,
                    'slots'   => $list,
                ];
                if ($enabled) {
                    $hasSchedule = true;
                }
            }
        }

        // Fallback: mentor_availabilities table (API/mobile)
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
                    $slots = $this->generateSlots($from, $to, 30);
                    $existing = $days[$day]['slots'];
                    $merged = array_values(array_unique(array_merge($existing, $slots)));
                    sort($merged);
                    $days[$day] = [
                        'enabled' => true,
                        'from'    => $days[$day]['from'] ?: $from,
                        'to'      => $to,
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
     * @return list<string>
     */
    private function openSlotsForDate(User $mentor, string $date, array $schedule): array
    {
        $open = $this->rawOpenSlots($mentor, $date, $schedule);
        $booked = $this->bookedTimes($mentor->id, $date);

        return $this->excludePastSlots($date, array_values(array_diff($open, $booked)));
    }

    /**
     * Drop times that have already started (or are about to) when the date is today (IST).
     *
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
     * @param  array{has_schedule:bool,days:array,blocked_dates?:array}  $schedule
     * @return list<string>
     */
    private function rawOpenSlots(User $mentor, string $date, array $schedule): array
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

            return array_values($day['slots'] ?? []);
        }

        // No schedule configured → default hours every day
        return self::DEFAULT_SLOTS;
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
     * @return list<string>
     */
    private function generateSlots(string $from, string $to, int $durationMinutes): array
    {
        $slots = [];
        try {
            $cursor = Carbon::createFromFormat('H:i', $from, 'Asia/Kolkata');
            $end = Carbon::createFromFormat('H:i', $to, 'Asia/Kolkata');
        } catch (\Throwable) {
            return [];
        }

        while ($cursor->copy()->addMinutes($durationMinutes)->lte($end)) {
            $slots[] = $cursor->format('H:i');
            $cursor->addMinutes($durationMinutes);
        }

        return $slots;
    }
}
