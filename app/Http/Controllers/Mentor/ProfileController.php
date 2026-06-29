<?php
namespace App\Http\Controllers\Mentor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('mentor.profile-edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'bio'            => 'nullable|string|max:2000',
            'designation'    => 'nullable|string|max:100',
            'company'        => 'nullable|string|max:100',
            'experience_years' => 'nullable|integer|min:0',
            'rate_per_minute'  => 'nullable|numeric|min:1',
            'linkedin'       => 'nullable|url',
            'expertise'      => 'nullable|array',
            'expertise.*'    => 'string|max:60',
            'preferences'    => 'nullable|array',
            'strengths'      => 'nullable|array',
        ]);

        auth()->user()->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Profile updated successfully.']);
        }
        return back()->with('success', 'Profile updated.');
    }

    public function avatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|max:2048']);
        $path = $request->file('avatar')->store('avatars', 'public');
        auth()->user()->update(['avatar_url' => '/storage/' . $path]);
        return response()->json(['message' => 'Photo updated.', 'url' => '/storage/' . $path]);
    }
}