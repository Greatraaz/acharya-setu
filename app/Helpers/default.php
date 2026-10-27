<?php
use App\Models\AppSetting;
use App\Services\ActivityLogger;

if (!function_exists('config_val')) {
    /**
     * Retrieve an application configuration value.
     *
     * Usage in Blade:  {{ config_val('app_name') }}
     *                  {{ config_val('maintenance_mode') ? 'Yes' : 'No' }}
     */
    function config_val(string $key, mixed $default = null): mixed
    {
        return AppSetting::get($key, $default);
    }
}

if (!function_exists('storage_url')) {
    function storage_url(?string $path): ?string
    {
        return \App\Services\PublicFileStorage::url($path);
    }
}

if (!function_exists('html_excerpt')) {
    function html_excerpt(?string $html, int $words = 28): string
    {
        $html = (string) $html;
        $html = preg_replace('/<\s*br\s*\/?>/i', ' ', $html) ?? $html;
        $html = preg_replace('/<\/(p|div|li|h[1-6])>/i', ' ', $html) ?? $html;
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\xc2\xa0", ' ', $text);
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        if ($text === '') {
            return '';
        }

        return \Illuminate\Support\Str::words($text, $words, '…');
    }
}

if (!function_exists('activity')) {
    function activity(): ActivityLogger
    {
        return new ActivityLogger();
    }
}