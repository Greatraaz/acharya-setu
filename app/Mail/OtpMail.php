<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $type;

    /**
     * Create a new message instance.
     *
     * @param string $otp
     * @param string $type
     */
    public function __construct(string $otp, string $type = 'registration') // registration | login | reset
    {
        $this->otp = $otp;
        $this->type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subjects = [
            'registration' => 'Verify your AcharyaSetu account',
            'login'        => 'Your AcharyaSetu login OTP',
            'reset'        => 'Reset your AcharyaSetu password',
        ];

        $subject = $subjects[$this->type] ?? 'Your AcharyaSetu OTP';

        return $this->subject($subject)
                    ->view('emails.otp')
                    ->with([
                        'otp' => $this->otp,
                        'type' => $this->type,
                    ]);
    }
}