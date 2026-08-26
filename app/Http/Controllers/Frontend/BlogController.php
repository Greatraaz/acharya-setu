<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $category = trim((string) $request->query('category', ''));

        $query = Blog::query()
            ->where('status', Blog::STATUS_ACTIVE)
            ->latest('blog_date')
            ->latest('id');

        if ($category !== '') {
            $query->where('category', $category);
        }

        $blogs = $query->paginate(9)->withQueryString();

        $categories = Blog::query()
            ->where('status', Blog::STATUS_ACTIVE)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('frontend.insights.blogs.index', compact('blogs', 'categories', 'category'));
    }

    public function show(string $slug): View
    {
        $blog = Blog::query()
            ->where('slug', $slug)
            ->where('status', Blog::STATUS_ACTIVE)
            ->firstOrFail();

        $recent = Blog::query()
            ->where('status', Blog::STATUS_ACTIVE)
            ->where('id', '!=', $blog->id)
            ->latest('blog_date')
            ->latest('id')
            ->limit(5)
            ->get();

        return view('frontend.insights.blogs.show', compact('blog', 'recent'));
    }
}
