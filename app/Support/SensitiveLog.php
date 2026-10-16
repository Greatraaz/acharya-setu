<?php

namespace App\Support;

class SensitiveLog
{
    public static function redact(?string $text): string
    {
        $text = (string) $text;

        if ($text === '' || ! preg_match('/otp|one-time password|verification code/i', $text)) {
            return $text;
        }

        return preg_replace('/\b\d{6}\b/', '******', $text) ?? $text;
    }
}
