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
        $encryption = strtolower((string) ($cfg['encryption'] ?? 'tls'));
        $scheme = in_array($encryption, ['ssl', 'smtps'], true) ? 'smtps' : null;
        $driver = in_array($cfg['driver'] ?? '', ['smtp', 'log', 'array', 'sendmail'], true)
            ? $cfg['driver']
            : 'smtp';

        Config::set('mail.default', $driver);
        Config::set('mail.mailers.smtp', [
            'transport'  => 'smtp',
            'scheme'     => $scheme,
            'host'       => $cfg['host'],
            'port'       => $cfg['port'],
            'encryption' => $encryption === 'none' ? null : $encryption,
            'username'   => $cfg['username'],
            'password'   => $cfg['password'],
            'timeout'    => 30,
        ]);
        Config::set('mail.from.address', $cfg['from_address']);
        Config::set('mail.from.name',    $cfg['from_name']);

        app()->forgetInstance('mail.manager');
        app()->forgetInstance('mailer');
    }

    public static function verify(): void
    {
        static::configure();

        $cfg = AppSetting::mail();
        if (($cfg['driver'] ?? 'smtp') === 'smtp') {
            if ($cfg['host'] === '' || $cfg['username'] === '' || $cfg['password'] === '') {
                throw new \RuntimeException('SMTP host, username, or password is missing. Save them in Settings → Email, then try again.');
            }

            if (
                filter_var($cfg['username'], FILTER_VALIDATE_EMAIL)
                && strcasecmp((string) $cfg['from_address'], (string) $cfg['username']) !== 0
            ) {
                throw new \RuntimeException(
                    'From Address must be the same mailbox as SMTP Username ('.$cfg['username'].'). Hostinger will reject any other sender with a relay error.'
                );
            }
        }

        $mailer = Mail::mailer();
        if (method_exists($mailer, 'getSymfonyTransport')) {
            $mailer->getSymfonyTransport()->start();
        }
    }

    public static function friendlyError(\Throwable $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, '535') || str_contains($message, 'BadCredentials') || str_contains($message, 'Username and Password not accepted')) {
            return 'Gmail rejected the SMTP login. Use a Google App Password (Google Account → Security → 2-Step Verification → App passwords), not your normal Gmail password. Paste it in Settings → Email, save, then send a test email.';
        }

        if (str_contains($message, '550') || str_contains($message, 'not allowed to relay') || str_contains($message, 'Relaying denied')) {
            return 'The mail server refused the sender. On Hostinger, From Address must be a real mailbox on vedrix.online and must match SMTP Username exactly (for example info@vedrix.online). hello@vedrix.com will be rejected.';
        }

        return 'Email could not be sent: '.$message;
    }

    /**
     * Send a Mailable using DB SMTP config.
     *
     * @param  \Illuminate\Mail\Mailable $mailable
     * @param  string|array|null         $to        recipient email, or legacy [email => name]
     * @param  string|null               $toName    display name when $to is an email string
     */
    public static function send(\Illuminate\Mail\Mailable $mailable, string|array $to = null, ?string $toName = null): void
    {
        static::configure();

        if ($to) {
            [$email, $name] = static::resolveRecipient($to, $toName);
            Mail::to($email, $name)->send($mailable);
        } else {
            Mail::send($mailable);
        }
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private static function resolveRecipient(string|array $to, ?string $toName = null): array
    {
        if (is_string($to)) {
            return [$to, $toName];
        }

        // Legacy [email => name] — do not pass this directly to Mail::to();
        // Laravel iterates array values and treats them as addresses.
        $email = array_key_first($to);
        $name = is_string($email) ? ($to[$email] ?? null) : null;

        if (! is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('Invalid recipient email address.');
        }

        return [$email, is_string($name) ? $name : null];
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