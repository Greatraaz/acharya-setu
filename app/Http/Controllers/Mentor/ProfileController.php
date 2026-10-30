<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Support\IndianPhone;
use App\Support\MentorIndustry;
use App\Support\ProfileUpdate;
use App\Support\RichText;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();

        return view('frontend.mentors.profile-edit', [
            'user' => $user,
            'pendingChange' => $user->latestPendingChange,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'gender' => 'nullable|in:male,female,other',
            'phone' => IndianPhone::rules(),
            'bio' => RichText::rules(80),
            'designation' => 'required|string|max:100',
            'company' => 'nullable|string|max:100',
            'experience_years' => 'required|integer|min:0|max:50',
            'field' => ['required', 'string', Rule::in(array_values(array_unique(array_filter([
                ...MentorIndustry::all(),
                auth()->user()->field,
            ]))))],
            'rate_per_minute' => 'required|numeric|min:5|max:500',
            'linkedin' => 'nullable|url|max:255',
            'expertise' => 'required|array|min:1',
            'expertise.*' => 'string|max:60',
            'avatar' => 'nullable|file|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $avatar = $request->file('avatar');
        unset($data['avatar']);

        $result = ProfileUpdate::save(auth()->user(), $data);
        if ($avatar) {
            ProfileUpdate::storeAvatar(auth()->user(), $avatar);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => $result['message'],
                'pending' => $result['pending'],
            ]);
        }

        return back()->with('success', $result['message']);
    }

    public function avatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|file|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);
        $url = ProfileUpdate::storeAvatar(auth()->user(), $request->file('avatar'));

        return response()->json(['message' => 'Photo updated.', 'url' => $url]);
    }
}
