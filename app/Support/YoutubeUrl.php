<?php

namespace App\Support;

class YoutubeUrl
{
    public static function extractId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $url = trim($url);

        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $url)) {
            return $url;
        }

        if (preg_match('/(?:v=|\/embed\/|youtu\.be\/|\/shorts\/|\/live\/|\/v\/)([A-Za-z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public static function embedUrl(?string $url, bool $autoplay = false): ?string
    {
        $id = self::extractId($url);

        if (! $id) {
            return null;
        }

        $params = http_build_query(array_filter([
            'autoplay' => $autoplay ? '1' : null,
            'rel' => '0',
            'modestbranding' => '1',
            'playsinline' => '1',
        ]));

        return 'https://www.youtube.com/embed/'.$id.($params ? '?'.$params : '');
    }

    public static function watchUrl(?string $url): ?string
    {
        $id = self::extractId($url);

        return $id ? 'https://www.youtube.com/watch?v='.$id : null;
    }

    public static function thumbnailUrl(?string $url): ?string
    {
        $id = self::extractId($url);

        return $id ? 'https://img.youtube.com/vi/'.$id.'/hqdefault.jpg' : null;
    }
}
