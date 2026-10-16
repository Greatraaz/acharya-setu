<?php

namespace App\Mail;

use App\Support\SensitiveLog;
use Illuminate\Mail\Transport\LogTransport;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\RawMessage;

class RedactingLogTransport extends LogTransport
{
    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        $string = Str::of($message->toString());

        if ($string->contains('Content-Type: multipart/')) {
            $boundary = $string
                ->after('boundary=')
                ->before("\r\n")
                ->prepend('--')
                ->append("\r\n");

            $string = $string
                ->explode($boundary)
                ->map(fn (string $part) => $this->decodePart($part))
                ->implode($boundary);
        } elseif ($string->contains('Content-Transfer-Encoding: quoted-printable')) {
            $string = $this->decodePart((string) $string);
        }

        $this->logger()->debug(SensitiveLog::redact((string) $string));

        return new SentMessage($message, $envelope ?? Envelope::create($message));
    }

    private function decodePart(string $part): string
    {
        if (! str_contains($part, 'Content-Transfer-Encoding: quoted-printable')) {
            return $part;
        }

        [$headers, $content] = explode("\r\n\r\n", $part, 2);

        return implode("\r\n\r\n", [
            $headers,
            quoted_printable_decode($content),
        ]);
    }
}
