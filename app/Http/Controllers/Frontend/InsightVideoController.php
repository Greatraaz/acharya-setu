<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\InsightVideo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InsightVideoController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));

        $videos = InsightVideo::query()
            ->active()
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            }))
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        return view('frontend.insights.videos.index', compact('videos', 'search'));
    }
}
