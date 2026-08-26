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
            ['key' => 'white-papers', 'label' => 'White Papers', 'icon' => '📄', 'route' => null],
            ['key' => 'case-studies', 'label' => 'Case Studies', 'icon' => '📁', 'route' => null],
            ['key' => 'customer-stories', 'label' => 'Customer Stories', 'icon' => '💬', 'route' => null],
            ['key' => 'testimonials', 'label' => 'Testimonials', 'icon' => '⭐', 'route' => null],
            ['key' => 'webinars', 'label' => 'Webinars', 'icon' => '🎥', 'route' => null],
            ['key' => 'events', 'label' => 'Events', 'icon' => '📅', 'route' => null],
            ['key' => 'podcasts', 'label' => 'Podcasts', 'icon' => '🎙️', 'route' => null],
            ['key' => 'videos', 'label' => 'Videos', 'icon' => '🎬', 'route' => null],
            ['key' => 'download-centre', 'label' => 'Download Centre', 'icon' => '⬇️', 'route' => null],
            ['key' => 'assessment-tools', 'label' => 'Assessment Tools', 'icon' => '🧰', 'route' => null],
        ];
    }

    /**
     * @return list<list<array{key:string,label:string,icon:string,route:?string}>>
     */
    public static function columns(): array
    {
        $items = self::items();

        return [
            array_slice($items, 0, 4),
            array_slice($items, 4, 4),
            array_slice($items, 8, 3),
        ];
    }

    public static function href(array $item): string
    {
        if (! empty($item['route'])) {
            try {
                return route($item['route']);
            } catch (\Throwable) {
                return '#';
            }
        }

        return '#';
    }
}
