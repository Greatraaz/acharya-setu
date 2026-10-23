<?php

namespace App\Console\Commands;

use App\Models\ConsultationSession;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendSessionReminders extends Command
{
    protected $signature = 'notifications:session-reminders';

    protected $description = 'Push a reminder to mentor and mentee about 15 minutes before a session starts';

    public function handle(NotificationService $notifications): int
    {
        $from = now()->addMinutes(10);
        $to   = now()->addMinutes(20);

        $sessions = ConsultationSession::query()
            ->with(['mentor', 'mentee'])
            ->where('status', ConsultationSession::STATUS_UPCOMING)
            ->whereBetween('scheduled_at', [$from, $to])
            ->get();

        $sent = 0;

        foreach ($sessions as $session) {
            $notifications->sessionEvent($session, 'reminder');
            $sent++;
        }

        $this->info("Sent {$sent} session reminder(s).");

        return self::SUCCESS;
    }
}
