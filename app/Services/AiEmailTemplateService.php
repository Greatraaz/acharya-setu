<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class AiEmailTemplateService
{
    /**
     * @return array{subject: string, body: string, provider: string}
     */
    public function generateBulkEmail(string $topic, string $audience = 'all', string $tone = 'professional'): array
    {
        $topic = trim($topic);
        if ($topic === '') {
            throw new RuntimeException('Enter a subject or topic first.');
        }

        $audience = in_array($audience, ['all', 'mentors', 'experts', 'mentees', 'specific'], true)
            ? $audience
            : 'all';
        $tone = in_array($tone, ['professional', 'friendly', 'urgent'], true)
            ? $tone
            : 'professional';

        if ($this->isConfigured()) {
            return $this->generateWithOpenAi($topic, $audience, $tone);
        }

        return $this->generateFallback($topic, $audience, $tone);
    }

    public function isConfigured(): bool
    {
        return filled(config('ai.api_key'));
    }

    /**
     * @return array{subject: string, body: string, provider: string}
     */
    private function generateWithOpenAi(string $topic, string $audience, string $tone): array
    {
        $appName = (string) (\App\Models\AppSetting::get('app_name') ?: config('app.name', 'Vedrix'));
        $audienceLabel = match ($audience) {
            'mentors' => 'mentors',
            'experts' => 'experts',
            'mentees' => 'mentees / learners',
            'specific' => 'selected users',
            default => 'the Vedrix community (mentors and mentees)',
        };
        $toneGuide = match ($tone) {
            'friendly' => 'Warm, encouraging, conversational but still polished.',
            'urgent' => 'Clear, time-sensitive, action-oriented without sounding alarming.',
            default => 'Professional, clear, concise, and trustworthy.',
        };

        $system = <<<PROMPT
You write HTML email body copy for {$appName}, a mentorship platform.
Return ONLY valid JSON with keys "subject" and "body". No markdown fences.
Rules for body:
- Use simple HTML only: <p>, <ul>, <li>, <strong>, <em>, <br>
- Start with a greeting using {{first_name}} (exact token)
- Keep it under 180 words
- Include one clear call to action
- You may use {{name}}, {{first_name}}, or {{email}} personalization tokens where natural
- Do not invent fake URLs; if linking is needed, say "log in to {$appName}"
PROMPT;

        $userPrompt = <<<PROMPT
Topic / draft subject: {$topic}
Audience: {$audienceLabel}
Tone: {$toneGuide}

Write a polished bulk email. Improve the subject if needed (max 100 chars).
PROMPT;

        $models = $this->modelCandidates();
        $lastError = 'OpenAI is busy right now. Please try again in a moment.';

        foreach ($models as $model) {
            try {
                return $this->requestTemplateFromModel($model, $system, $userPrompt, $topic);
            } catch (RuntimeException $e) {
                $lastError = $e->getMessage();
                if (! $this->isRetryableError($lastError)) {
                    throw $e;
                }
            }
        }

        throw new RuntimeException($this->friendlyCapacityMessage($lastError));
    }

    /**
     * @return list<string>
     */
    private function modelCandidates(): array
    {
        $primary = (string) config('ai.model', 'gpt-4o-mini');
        $fallbacks = config('ai.fallback_models', []);
        if (! is_array($fallbacks)) {
            $fallbacks = [];
        }

        $models = array_values(array_unique(array_filter(array_map(
            'strval',
            array_merge([$primary], $fallbacks)
        ))));

        return $models !== [] ? $models : ['gpt-4o-mini'];
    }

    /**
     * @return array{subject: string, body: string, provider: string}
     */
    private function requestTemplateFromModel(string $model, string $system, string $userPrompt, string $topic): array
    {
        $retries = max(0, (int) config('ai.retries', 2));
        $lastError = 'OpenAI returned an error.';

        for ($attempt = 0; $attempt <= $retries; $attempt++) {
            if ($attempt > 0) {
                usleep(400000 * $attempt);
            }

            try {
                $response = $this->callOpenAi($model, $system, $userPrompt);
            } catch (\Throwable $e) {
                $lastError = $e->getMessage() ?: 'Could not reach OpenAI.';
                if ($this->isRetryableError($lastError) && $attempt < $retries) {
                    continue;
                }
                throw new RuntimeException($this->friendlyCapacityMessage($lastError));
            }

            if (! $response->successful()) {
                $lastError = $response->json('error.message')
                    ?: $response->json('message')
                    ?: 'OpenAI returned an error.';

                if ($this->isRetryableError($lastError) && $attempt < $retries) {
                    continue;
                }

                throw new RuntimeException($this->friendlyCapacityMessage($lastError));
            }

            $content = (string) data_get($response->json(), 'choices.0.message.content', '');
            $parsed = $this->decodeJsonPayload($content);

            if (! is_array($parsed)) {
                $lastError = 'AI returned an invalid template. Try again.';
                if ($attempt < $retries) {
                    continue;
                }
                throw new RuntimeException($lastError);
            }

            $subject = trim((string) ($parsed['subject'] ?? $topic));
            $body = trim((string) ($parsed['body'] ?? ''));

            if ($subject === '' || $body === '') {
                $lastError = 'AI returned an empty template. Try again.';
                if ($attempt < $retries) {
                    continue;
                }
                throw new RuntimeException($lastError);
            }

            return [
                'subject' => Str::limit($subject, 180, ''),
                'body' => $this->sanitizeHtml($body),
                'provider' => 'openai',
            ];
        }

        throw new RuntimeException($this->friendlyCapacityMessage($lastError));
    }

    private function callOpenAi(string $model, string $system, string $userPrompt): Response
    {
        return Http::baseUrl((string) config('ai.base_url'))
            ->withToken((string) config('ai.api_key'))
            ->timeout((int) config('ai.timeout', 60))
            ->acceptJson()
            ->post('/chat/completions', [
                'model' => $model,
                'temperature' => 0.7,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);
    }

    private function isRetryableError(string $message): bool
    {
        $message = strtolower($message);

        return str_contains($message, 'high demand')
            || str_contains($message, 'try again')
            || str_contains($message, 'unavailable')
            || str_contains($message, 'overloaded')
            || str_contains($message, 'resource exhausted')
            || str_contains($message, 'rate limit')
            || str_contains($message, 'timeout')
            || str_contains($message, 'timed out')
            || str_contains($message, 'server error')
            || str_contains($message, '503')
            || str_contains($message, '429')
            || str_contains($message, '502');
    }

    private function friendlyCapacityMessage(string $message): string
    {
        $lower = strtolower($message);

        if (str_contains($lower, 'insufficient_quota')
            || str_contains($lower, 'exceeded your current quota')
            || str_contains($lower, 'billing')) {
            return 'OpenAI quota/billing issue. Add credits at platform.openai.com, then try again.';
        }

        if (str_contains($lower, 'invalid api key')
            || str_contains($lower, 'incorrect api key')
            || str_contains($lower, 'authentication')) {
            return 'OpenAI API key is invalid. Check OPENAI_API_KEY in .env.';
        }

        if (str_contains($lower, 'rate limit')
            || str_contains($lower, 'overloaded')
            || str_contains($lower, 'unavailable')
            || str_contains($lower, '429')
            || str_contains($lower, '503')) {
            return 'OpenAI is busy right now. Wait a few seconds and click Generate again.';
        }

        return $message !== '' ? $message : 'Could not generate a template right now. Try again.';
    }

    /**
     * Local draft when OPENAI_API_KEY is not set — still usable for admins.
     *
     * @return array{subject: string, body: string, provider: string}
     */
    private function generateFallback(string $topic, string $audience, string $tone): array
    {
        $appName = (string) (\App\Models\AppSetting::get('app_name') ?: config('app.name', 'Vedrix'));
        $audiencePhrase = match ($audience) {
            'mentors' => 'our mentor community',
            'experts' => 'our expert community',
            'mentees' => 'our mentees',
            'specific' => 'you',
            default => 'the Vedrix community',
        };

        $opening = match ($tone) {
            'friendly' => "We hope you're doing well!",
            'urgent' => 'Please take a moment to review this important update.',
            default => 'We have a quick update for you.',
        };

        $subject = Str::limit($topic, 180, '');
        $safeTopic = e($topic);

        $body = <<<HTML
<p>Hi {{first_name}},</p>
<p>{$opening}</p>
<p>This note is about <strong>{$safeTopic}</strong> for {$audiencePhrase}.</p>
<ul>
<li>Read the details carefully and keep this email for reference.</li>
<li>If action is needed on your side, please complete it at your earliest convenience.</li>
<li>Reply to this email or contact support if you have questions.</li>
</ul>
<p>Thank you for being part of <strong>{$appName}</strong>.</p>
<p>Warm regards,<br>The {$appName} Team</p>
HTML;

        return [
            'subject' => $subject,
            'body' => $body,
            'provider' => 'local',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJsonPayload(string $content): ?array
    {
        $content = trim($content);
        if ($content === '') {
            return null;
        }

        $parsed = json_decode($content, true);
        if (is_array($parsed)) {
            return $parsed;
        }

        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $parsed = json_decode($matches[0], true);
            if (is_array($parsed)) {
                return $parsed;
            }
        }

        return null;
    }

    private function sanitizeHtml(string $html): string
    {
        $allowed = '<p><br><ul><ol><li><strong><b><em><i><a><h2><h3>';
        $clean = strip_tags($html, $allowed);

        return trim($clean) !== '' ? $clean : '<p>'.e(strip_tags($html)).'</p>';
    }
}
