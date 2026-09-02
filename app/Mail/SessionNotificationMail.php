<?php

namespace App\Mail;

use App\Models\ConsultationSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SessionNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ConsultationSession $session,
        public string $event,
        public string $recipientRole,
        public User $recipient,
    ) {}

    public function build()
    {
        $subjects = [
            'booked' => [
                'mentee' => 'Session confirmed — '.$this->sessionTitle(),
                'mentor' => 'New session booked — '.$this->sessionTitle(),
            ],
            'cancelled' => [
                'mentee' => 'Session cancelled — '.$this->sessionTitle(),
                'mentor' => 'Session cancelled — '.$this->sessionTitle(),
            ],
            'completed' => [
                'mentee' => 'Session completed — '.$this->sessionTitle(),
                'mentor' => 'Session marked complete — '.$this->sessionTitle(),
            ],
        ];

        $subject = $subjects[$this->event][$this->recipientRole]
            ?? 'Session update — Vedrix';

        return $this->subject($subject)
            ->view('emails.session-notification')
            ->with([
                'session'        => $this->session,
                'event'          => $this->event,
                'recipientRole'  => $this->recipientRole,
                'recipient'      => $this->recipient,
                'sessionDetails' => $this->sessionDetails(),
                'headline'       => $this->headline(),
                'intro'          => $this->intro(),
                'cta'            => $this->cta(),
                'footerNote'     => $this->footerNote(),
            ]);
    }

    private function sessionTitle(): string
    {
        return $this->session->title ?: 'Mentorship Session';
    }

    private function headline(): string
    {
        return match ($this->event) {
            'booked' => match ($this->recipientRole) {
                'mentee' => 'Your session is confirmed',
                default  => 'You have a new booking',
            },
            'cancelled' => 'Session cancelled',
            'completed' => match ($this->recipientRole) {
                'mentee' => 'Session completed',
                default  => 'Session marked complete',
            },
            default => 'Session update',
        };
    }

    private function intro(): string
    {
        $mentorName = $this->session->mentor?->name ?? 'your mentor';
        $menteeName = $this->session->mentee?->name ?? 'your mentee';
        $when = $this->formattedSchedule();

        return match ($this->event) {
            'booked' => match ($this->recipientRole) {
                'mentee' => 'Hi '.$this->recipientFirstName().", your mentorship session with {$mentorName} is booked for {$when}.",
                default  => 'Hi '.$this->recipientFirstName().", {$menteeName} booked a session with you for {$when}.",
            },
            'cancelled' => match ($this->recipientRole) {
                'mentee' => 'Hi '.$this->recipientFirstName().", your session with {$mentorName} scheduled for {$when} has been cancelled.",
                default  => 'Hi '.$this->recipientFirstName().", your session with {$menteeName} scheduled for {$when} has been cancelled.",
            },
            'completed' => match ($this->recipientRole) {
                'mentee' => 'Hi '.$this->recipientFirstName().", your session with {$mentorName} on {$when} is now marked complete.",
                default  => 'Hi '.$this->recipientFirstName().", your session with {$menteeName} on {$when} is now marked complete.",
            },
            default => 'There is an update on your Vedrix session.',
        };
    }

    private function footerNote(): ?string
    {
        if ($this->event === 'cancelled') {
            $parts = [];
            $cancelledBy = $this->session->cancelledBy;
            if ($cancelledBy) {
                $role = (int) $cancelledBy->id === (int) $this->session->mentor_id
                    ? 'mentor'
                    : ((int) $cancelledBy->id === (int) $this->session->mentee_id ? 'mentee' : 'user');
                $parts[] = 'Cancelled by '.$cancelledBy->name.' ('.$role.')';
            }
            if (filled($this->session->cancellation_reason)) {
                $parts[] = 'Reason: '.$this->session->cancellation_reason;
            }

            return $parts !== [] ? implode(' · ', $parts) : null;
        }

        if ($this->event === 'completed' && $this->recipientRole === 'mentor' && $this->session->payment_status === 'paid') {
            return 'Session earnings will reflect in your wallet if applicable.';
        }

        if ($this->event === 'completed' && $this->recipientRole === 'mentee') {
            return 'We hope the session was valuable. You can leave a review from your sessions page.';
        }

        if ($this->event === 'booked') {
            return 'Please join a few minutes early and keep your notes ready.';
        }

        return null;
    }

    private function cta(): ?array
    {
        if ($this->event === 'booked' && $this->session->status === ConsultationSession::STATUS_UPCOMING) {
            return [
                'label' => 'View session details',
                'url'   => $this->recipientRole === 'mentee'
                    ? route('mentee.sessions.show', $this->session->id)
                    : route('mentor.sessions.show', $this->session->id),
            ];
        }

        if ($this->event === 'completed' && $this->recipientRole === 'mentee') {
            return [
                'label' => 'View session & review',
                'url'   => route('mentee.sessions.show', $this->session->id),
            ];
        }

        return [
            'label' => 'Open sessions',
            'url'   => $this->recipientRole === 'mentee'
                ? route('mentee.sessions')
                : route('mentor.sessions'),
        ];
    }

    private function sessionDetails(): array
    {
        $session = $this->session;

        return [
            'Booking ID' => $session->booking_ref ?: ('#'.$session->id),
            'Session'    => $this->sessionTitle(),
            'Mentor'     => $session->mentor?->name ?? '—',
            'Mentee'     => $session->mentee?->name ?? '—',
            'Date & time'=> $this->formattedSchedule(),
            'Duration'   => ($session->duration_minutes ?? 0).' minutes',
            'Amount'     => $this->formattedAmount(),
            'Payment'    => $session->paymentMethodLabel(),
        ];
    }

    private function formattedSchedule(): string
    {
        if (! $this->session->scheduled_at) {
            return '—';
        }

        return $this->session->scheduled_at
            ->copy()
            ->timezone(ConsultationSession::SCHEDULE_TIMEZONE)
            ->format('D, d M Y · g:i A').' IST';
    }

    private function formattedAmount(): string
    {
        $amount = (float) ($this->session->amount ?? 0);

        if ($amount <= 0) {
            return match ($this->session->payment_method) {
                'plan' => 'Included in plan',
                'free' => 'Free',
                default => $this->session->payment_status === 'waived' ? 'Waived' : '₹0',
            };
        }

        return '₹'.number_format($amount, 0);
    }

    private function recipientFirstName(): string
    {
        $name = trim((string) ($this->recipient->name ?? ''));

        return $name !== '' ? explode(' ', $name)[0] : 'there';
    }
}
