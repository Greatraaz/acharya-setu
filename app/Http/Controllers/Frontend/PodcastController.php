<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PodcastController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->input('type', '');
        if (! in_array($type, ['', Podcast::TYPE_AUDIO, Podcast::TYPE_YOUTUBE], true)) {
            $type = '';
        }

        $query = Podcast::query()
            ->active()
            ->latest('id');

        if ($type !== '') {
            $query->where('podcast_type', $type);
        }

        $podcasts = $query->paginate(9)->withQueryString();

        return view('frontend.insights.podcasts.index', compact('podcasts', 'type'));
    }
}
