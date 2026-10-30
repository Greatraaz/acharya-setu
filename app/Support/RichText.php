<?php

namespace App\Support;

final class RichText
{
    public const HTML_MAX = 12000;

    public const PLAIN_MAX = 2000;

    public static function allowedTags(): string
    {
        return '<p><br><strong><b><em><i><u><ul><ol><li><a><h2><h3><blockquote>';
    }

    public static function plain(?string $html): string
    {
        $html = (string) $html;
        $html = preg_replace('/<\s*br\s*\/?>/i', ' ', $html) ?? $html;
        $html = preg_replace('/<\/(p|div|li|h[1-6]|blockquote)>/i', ' ', $html) ?? $html;
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\xc2\xa0", ' ', $text);

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    public static function sanitize(?string $html): string
    {
        $html = (string) $html;
        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? $html;
        $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html) ?? $html;
        $html = strip_tags($html, self::allowedTags());
        $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace_callback('/<a\s+([^>]*?)>/i', function (array $m) {
            if (! preg_match('/href\s*=\s*("|\')([^\'"]+)\1/i', $m[1], $href)) {
                return '<a>';
            }

            $url = html_entity_decode($href[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (! preg_match('#^(https?:|mailto:)#i', $url)) {
                return '<a>';
            }

            return '<a href="'.e($url).'" target="_blank" rel="noopener noreferrer">';
        }, $html) ?? $html;

        if (self::plain($html) === '') {
            return '';
        }

        return trim($html);
    }

    /**
     * @return list<mixed>
     */
    public static function rules(int $minPlain, bool $required = true, int $maxPlain = self::PLAIN_MAX): array
    {
        $rules = [$required ? 'required' : 'nullable', 'string', 'max:'.self::HTML_MAX];
        $rules[] = function (string $attribute, mixed $value, \Closure $fail) use ($minPlain, $maxPlain, $required): void {
            $plain = self::plain(is_string($value) ? $value : '');
            if ($plain === '') {
                if ($required) {
                    $fail('Please write a bio.');
                }

                return;
            }

            $len = mb_strlen($plain);
            if ($len < $minPlain) {
                $fail("The bio must be at least {$minPlain} characters.");
            }
            if ($len > $maxPlain) {
                $fail("The bio must not exceed {$maxPlain} characters.");
            }
        };

        return $rules;
    }
}
