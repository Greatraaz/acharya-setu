<?php

namespace App\Observers;

use App\Models\ConsultationSession;
use App\Services\SessionMailService;
use Illuminate\Support\Facades\DB;

class ConsultationSessionObserver
{
    public function __construct(
        private readonly SessionMailService $mail
    ) {}

    public function created(ConsultationSession $session): void
    {
        if ($session->status !== ConsultationSession::STATUS_UPCOMING) {
            return;
        }

        DB::afterCommit(function () use ($session) {
            $this->mail->notifyBooked($session);
        });
    }

    public function updated(ConsultationSession $session): void
    {
        if (! $session->wasChanged('status')) {
            return;
        }

        DB::afterCommit(function () use ($session) {
            match ($session->status) {
                ConsultationSession::STATUS_CANCELLED => $this->mail->notifyCancelled($session),
                ConsultationSession::STATUS_COMPLETED => $this->mail->notifyCompleted($session),
                default => null,
            };
        });
    }
}
