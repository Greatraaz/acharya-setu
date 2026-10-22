<?php

namespace App\Jobs;

use App\Mail\AdminBulkMail;
use App\Models\User;
use App\Services\DynamicMailer;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAdminBulkEmailJob
{
    use Dispatchable;

    /**
     * @param  list<int>  $userIds
     */
    public function __construct(
        public array $userIds,
        public string $subject,
        public string $body,
    ) {}

    public function handle(): void
    {
        if ($this->userIds === []) {
            return;
        }

        DynamicMailer::configure();

        $users = User::query()
            ->whereIn('id', $this->userIds)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get(['id', 'name', 'email']);

        foreach ($users as $user) {
            if (filter_var($user->email, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }

            try {
                Mail::to($user->email, $user->name)->send(
                    new AdminBulkMail($user, $this->subject, $this->body)
                );
            } catch (\Throwable $e) {
                Log::error('Admin bulk email failed.', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }
}
