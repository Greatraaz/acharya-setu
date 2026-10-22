<?php

namespace App\Mail;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminBulkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $recipient,
        public string $subjectLine,
        public string $bodyText,
    ) {}

    public function build()
    {
        $mail = AppSetting::mail();

        return $this->from($mail['from_address'], $mail['from_name'])
            ->subject($this->personalize($this->subjectLine))
            ->view('emails.admin-bulk')
            ->with([
                'recipient' => $this->recipient,
                'headline'  => $this->personalize($this->subjectLine),
                'bodyHtml'  => $this->bodyHtml(),
            ]);
    }

    private function bodyHtml(): string
    {
        $html = $this->personalize($this->bodyText);

        if (trim(strip_tags($html)) === trim($html)) {
            return nl2br(e($html), false);
        }

        $html = preg_replace('#<(script|style|iframe|object|embed|form)[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $html = preg_replace('#</?(script|style|iframe|object|embed|form)[^>]*>#is', '', $html) ?? $html;
        $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace('/javascript\s*:/i', '', $html) ?? $html;

        return strip_tags($html, '<p><br><strong><b><em><i><u><s><a><ul><ol><li><h1><h2><h3><blockquote><span><div>');
    }

    private function personalize(string $text): string
    {
        $name = trim((string) ($this->recipient->name ?? ''));
        $first = $name !== '' ? explode(' ', $name)[0] : 'there';

        return strtr($text, [
            '{{name}}' => $name !== '' ? $name : 'there',
            '{{Name}}' => $name !== '' ? $name : 'there',
            '{{first_name}}' => $first,
            '{{email}}' => (string) $this->recipient->email,
        ]);
    }
}
