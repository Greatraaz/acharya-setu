<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $featuredMentors = User::where('role', 'mentor')
            ->where('mentor_status', 'approved')
            ->where('is_active', true)
            ->orderByDesc('rating')
            ->limit(4)
            ->get();

        return view('frontend.new', compact('featuredMentors'));
    }
}