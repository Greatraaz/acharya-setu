<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PublicFileStorage
{
    public static function store(UploadedFile $file, string $directory): string
    {
        self::ensureStorageReady();

        $root = storage_path('app/public');

        if (! is_writable($root)) {
            throw new RuntimeException("storage/app/public is not writable by the web server. Fix permissions on production (chmod 775 or chown www-data).");
        }

        Storage::disk('public')->makeDirectory($directory);

        $path = $file->store($directory, 'public');

        if (! $path || ! Storage::disk('public')->exists($path)) {
            throw new RuntimeException("Failed to store uploaded file in [{$directory}]. Check storage/app/public permissions.");
        }

        return $path;
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return $path;
        }

        return '/storage/' . ltrim($path, '/');
    }

    public static function pathFromUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (str_starts_with($url, '/storage/')) {
            return ltrim(substr($url, strlen('/storage/')), '/');
        }

        $publicBase = rtrim((string) config('filesystems.disks.public.url'), '/');

        if ($publicBase && str_starts_with($url, $publicBase . '/')) {
            return ltrim(substr($url, strlen($publicBase) + 1), '/');
        }

        if (! str_contains($url, '://') && ! str_starts_with($url, '/')) {
            return ltrim($url, '/');
        }

        return null;
    }

    public static function deleteByUrl(?string $url): void
    {
        $path = self::pathFromUrl($url);

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function ensureStorageReady(bool $force = false): void
    {
        $root = storage_path('app/public');

        if (! is_dir($root) && ! mkdir($root, 0755, true) && ! is_dir($root)) {
            throw new RuntimeException("Unable to create storage directory at [{$root}].");
        }

        foreach (['avatars', 'settings/app_logo', 'settings/app_favicon', 'submissions', 'videos'] as $dir) {
            Storage::disk('public')->makeDirectory($dir);
        }

        self::ensurePublicSymlink($force);
    }

    private static function ensurePublicSymlink(bool $force = false): void
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if ($force && is_link($link)) {
            $resolved = realpath($link);
            if ($resolved === false || $resolved !== realpath($target)) {
                @unlink($link);
            }
        }

        if (file_exists($link) || is_link($link)) {
            return;
        }

        try {
            Artisan::call('storage:link');
        } catch (\Throwable $e) {
            Log::warning('artisan storage:link failed, trying native symlink', ['error' => $e->getMessage()]);
        }

        if (! file_exists($link) && ! is_link($link)) {
            if (! @symlink($target, $link)) {
                Log::warning('Could not create public/storage symlink. Files will be served via /storage/{path} route.');
            }
        }
    }
}
