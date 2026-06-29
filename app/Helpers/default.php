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

if (!function_exists('activity')) {
    function activity(): ActivityLogger
    {
        return new ActivityLogger();
    }
}