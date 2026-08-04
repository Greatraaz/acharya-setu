<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\WaitlistLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('frontend.index');
    }

    public function waitlist(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|max:191',
            'phone' => 'required|string|max:20',
            'who'   => 'required|in:student,professional,mentor,institution',
        ]);

        WaitlistLead::create($data);

        return response()->json([
            'status'  => 1,
            'message' => 'Successfully joined the waitlist',
        ]);
    }
}
