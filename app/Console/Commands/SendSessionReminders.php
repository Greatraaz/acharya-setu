<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendSessionReminders extends Command
{
    protected $signature = 'notifications:session-reminders';

    protected $description = 'Push a reminder to mentor and mentee about 15 minutes before a session starts';

    public function handle(NotificationService $notifications): int
    {
        $sent = $notifications->sendDueSessionReminders();
        $this->info("Sent {$sent} session reminder(s).");

        return self::SUCCESS;
    }
}
