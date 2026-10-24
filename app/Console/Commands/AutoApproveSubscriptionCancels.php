<?php

namespace App\Console\Commands;

use App\Services\SubscriptionCancelService;
use Illuminate\Console\Command;

class AutoApproveSubscriptionCancels extends Command
{
    protected $signature = 'subscriptions:auto-approve-cancels';

    protected $description = 'Auto-approve yearly subscription cancel requests that are still pending after 72 hours';

    public function handle(SubscriptionCancelService $cancels): int
    {
        $approved = $cancels->autoApproveDue();
        $this->info("Auto-approved {$approved} yearly subscription cancellation(s).");

        return self::SUCCESS;
    }
}
