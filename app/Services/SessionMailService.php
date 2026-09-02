<?php

namespace App\Services;

use App\Mail\SessionNotificationMail;
use App\Models\ConsultationSession;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SessionMailService
{
    public function notifyBooked(ConsultationSession $session): void
    {
        if ($session->status !== ConsultationSession::STATUS_UPCOMING) {
            return;
        }

        $this->sendToBoth($session, 'booked');
    }

    public function notifyCancelled(ConsultationSession $session): void
    {
        if ($session->status !== ConsultationSession::STATUS_CANCELLED) {
            return;
        }

        $this->sendToBoth($session, 'cancelled');
    }

    public function notifyCompleted(ConsultationSession $session): void
    {
        if ($session->status !== ConsultationSession::STATUS_COMPLETED) {
            return;
        }

        $this->sendToBoth($session, 'completed');
    }

    private function sendToBoth(ConsultationSession $session, string $event): void
    {
        $session = $session->fresh(['mentor', 'mentee', 'cancelledBy']);

        if (! $session) {
            return;
        }

        if ($session->mentee) {
            $this->send($session, $event, 'mentee', $session->mentee);
        }

        if ($session->mentor) {
            $this->send($session, $event, 'mentor', $session->mentor);
        }
    }

    private function send(
        ConsultationSession $session,
        string $event,
        string $role,
        User $recipient
    ): void {
        if (! $this->canEmail($recipient)) {
            return;
        }

        try {
            DynamicMailer::send(
                new SessionNotificationMail($session, $event, $role, $recipient),
                [$recipient->email => $recipient->name]
            );
        } catch (\Throwable $e) {
            Log::warning('Session notification email failed.', [
                'session_id' => $session->id,
                'event'      => $event,
                'role'       => $role,
                'email'      => $recipient->email,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    private function canEmail(User $user): bool
    {
        if (! filled($user->email)) {
            return false;
        }

        if (method_exists($user, 'isReleasedEmail') && $user->isReleasedEmail($user->email)) {
            return false;
        }

        return filter_var($user->email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
