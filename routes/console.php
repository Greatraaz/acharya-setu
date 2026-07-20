<?php

use App\Services\PublicFileStorage;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('storage:ensure', function () {
    PublicFileStorage::ensureStorageReady(force: true);

    $root = storage_path('app/public');
    $link = public_path('storage');

    $this->info('Public storage directories ready.');
    $this->line('  storage/app/public writable: ' . (is_writable($root) ? 'yes' : 'NO — fix permissions'));
    $this->line('  public/storage link: ' . ((file_exists($link) || is_link($link)) ? 'yes' : 'no (files served via /storage route)'));

    return 0;
})->purpose('Create upload directories and public/storage symlink for production');
