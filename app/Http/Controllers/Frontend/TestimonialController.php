<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));

        $testimonials = Testimonial::query()
            ->active()
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('name', 'like', '%'.$search.'%')
                    ->orWhere('designation', 'like', '%'.$search.'%')
                    ->orWhere('message', 'like', '%'.$search.'%');
            }))
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        return view('frontend.insights.testimonials.index', compact('testimonials', 'search'));
    }
}
