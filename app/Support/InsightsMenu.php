<?php

namespace App\Support;

class InsightsMenu
{
    /**
     * Utilities / Insights topics shown in admin sidebar and website navbar.
     *
     * @return list<array{key:string,label:string,icon:string,route:?string}>
     */
    public static function items(): array
    {
        return [
            ['key' => 'blogs', 'label' => 'Blogs', 'icon' => '📰', 'route' => 'insights.blogs.index'],
            ['key' => 'white-papers', 'label' => 'White Papers', 'icon' => '📄', 'route' => 'insights.white-papers.index'],
            ['key' => 'case-studies', 'label' => 'Case Studies', 'icon' => '📁', 'route' => 'insights.case-studies.index'],
            ['key' => 'testimonials', 'label' => 'Testimonials', 'icon' => '⭐', 'route' => 'insights.testimonials.index'],
            ['key' => 'webinars', 'label' => 'Webinars', 'icon' => '🎥', 'route' => 'insights.webinars.index'],
            ['key' => 'events', 'label' => 'Events', 'icon' => '📅', 'route' => 'insights.events.index'],
            ['key' => 'podcasts', 'label' => 'Podcasts', 'icon' => '🎙️', 'route' => 'insights.podcasts.index'],
            ['key' => 'videos', 'label' => 'Videos', 'icon' => '🎬', 'route' => 'insights.videos.index'],
            ['key' => 'download-centre', 'label' => 'Download Centre', 'icon' => '⬇️', 'route' => 'insights.download-centre.index'],
        ];
    }

    /**
     * @return list<list<array{key:string,label:string,icon:string,route:?string}>>
     */
    public static function columns(): array
    {
        $items = self::items();

        return [
            array_slice($items, 0, 3),
            array_slice($items, 3, 3),
            array_slice($items, 6, 3),
        ];
    }

    public static function href(array $item): string
    {
        if (! empty($item['route'])) {
            try {
                return route($item['route']);
            } catch (\Throwable) {
                // Route may be missing on a stale deploy/cache — use path fallback below.
            }
        }

        return match ($item['key'] ?? '') {
            'blogs' => url('/insights/blogs'),
            'white-papers' => url('/insights/white-papers'),
            'case-studies' => url('/insights/case-studies'),
            'testimonials' => url('/insights/testimonials'),
            'webinars' => url('/insights/webinars'),
            'events' => url('/insights/events'),
            'podcasts' => url('/insights/podcasts'),
            'videos' => url('/insights/videos'),
            'download-centre' => url('/insights/download-centre'),
            default => '#',
        };
    }
}
