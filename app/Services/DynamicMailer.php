<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

/**
 * DynamicMailer
 *
 * Reads SMTP config from app_settings table (not .env) and
 * reconfigures Laravel's mail driver at runtime before sending.
 *
 * Usage:
 *   DynamicMailer::send(new WelcomeMail($user));
 *   DynamicMailer::to('a@b.com')->send(new OtpMail('123456'));
 *   DynamicMailer::sendRaw('hello@b.com', 'Subject', '<p>Body</p>');
 */
class DynamicMailer
{
    /**
     * Reconfigure Laravel's SMTP driver from DB settings.
     * Call this once before any Mail:: call.
     */
    public static function configure(): void
    {
        $cfg = AppSetting::mail();

        // Override the mail config at runtime
        Config::set('mail.default', $cfg['driver']);
        Config::set('mail.mailers.smtp', [
            'transport'  => 'smtp',
            'host'       => $cfg['host'],
            'port'       => $cfg['port'],
            'encryption' => $cfg['encryption'],
            'username'   => $cfg['username'],
            'password'   => $cfg['password'],
            'timeout'    => 30,
        ]);
        Config::set('mail.from.address', $cfg['from_address']);
        Config::set('mail.from.name',    $cfg['from_name']);

        // Force Laravel to rebuild the mailer with new config
        app()->forgetInstance('mail.manager');
        app()->forgetInstance('mailer');
    }

    /**
     * Send a Mailable using DB SMTP config.
     *
     * @param  \Illuminate\Mail\Mailable $mailable
     * @param  string|array             $to        email or [email => name]
     */
    public static function send(\Illuminate\Mail\Mailable $mailable, string|array $to = null): void
    {
        static::configure();

        if ($to) {
            Mail::to($to)->send($mailable);
        } else {
            Mail::send($mailable);
        }
    }

    /**
     * Send a raw HTML email without creating a Mailable class.
     *
     * @param  string $to       Recipient email
     * @param  string $subject  Email subject
     * @param  string $htmlBody HTML content
     * @param  string|null $fromAddress Override from address
     */
    public static function sendRaw(
        string  $to,
        string  $subject,
        string  $htmlBody,
        ?string $fromAddress = null,
        ?string $fromName    = null,
    ): void {
        static::configure();

        $cfg = AppSetting::mail();

        Mail::html($htmlBody, function (Message $msg) use ($to, $subject, $cfg, $fromAddress, $fromName) {
            $msg->to($to)
                ->subject($subject)
                ->from(
                    $fromAddress ?? $cfg['from_address'],
                    $fromName    ?? $cfg['from_name']
                );
        });
    }

    /**
     * Send a Mailable to multiple recipients.
     */
    public static function sendBulk(\Illuminate\Mail\Mailable $mailable, array $recipients): void
    {
        static::configure();
        Mail::to($recipients)->send($mailable);
    }

    /**
     * Queue a mailable (uses DB config at dispatch time).
     */
    public static function queue(\Illuminate\Mail\Mailable $mailable, string $to): void
    {
        static::configure();
        Mail::to($to)->queue($mailable);
    }
}