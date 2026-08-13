<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Services\MenteeOnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function __construct(private readonly MenteeOnboardingService $onboarding)
    {
    }

    public function show(int $step)
    {
        $user = auth()->user();
        $streams = collect();
        try {
            $streams = \DB::table('education_streams')
                ->where('is_active', true)
                ->whereNull('mentee_id')
                ->orderBy('sort_order')
                ->get();
        } catch (\Throwable) {
            $streams = collect();
        }
        $trackSuggestions = $this->onboarding->catalogStreams();
        $tracks = old('tracks', $this->onboarding->menteeTracks($user->id) ?: ($user->career_goals ?? []));
        $preferences = $user->preferences ?? [];

        return view('frontend.mentee.steps', compact('step', 'streams', 'trackSuggestions', 'tracks', 'preferences'));
    }

    public function saveStep1(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'gender' => 'nullable|in:male,female,other',
            'phone'  => 'nullable|string|max:20',
            'address'=> 'nullable|string|max:200',
            'avatar' => 'nullable|image|mimes:jpeg,png,webp,jpg|max:2048',
        ]);

        $user = auth()->user();

        if ($request->hasFile('avatar')) {
            if ($user->avatar_url && str_starts_with($user->avatar_url, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar_url));
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar_url'] = '/storage/' . $path;
        }

        $data['location'] = $data['address'] ?? $user->location;
        unset($data['address'], $data['avatar']);

        $user->update(array_merge($data, ['onboarding_step' => 1]));

        return $this->next($request, 2);
    }

    public function saveStep2(Request $request)
    {
        $data = $request->validate([
            'education_stream' => 'nullable|string|max:100',
            'field'            => 'nullable|string|max:100',
            'college'          => 'nullable|string|max:200',
            'year'             => 'nullable|string|max:50',
        ]);

        $user = auth()->user();
        $user->update(array_merge($data, ['onboarding_step' => 2]));

        if (! empty($data['education_stream'])) {
            $this->onboarding->assignCatalogStream($user->fresh(), $data['education_stream']);
        }

        return $this->next($request, 3);
    }

    public function saveStep3(Request $request)
    {
        $data = $request->validate([
            'tracks'   => 'required|array|min:1',
            'tracks.*' => 'string|max:100',
        ]);

        $tracks = collect($data['tracks'])
            ->map(fn ($track) => trim((string) $track))
            ->filter()
            ->unique(fn ($track) => Str::lower($track))
            ->values();

        if ($tracks->isEmpty()) {
            $msg = 'Please provide at least one valid track.';
            if ($request->ajax()) {
                return response()->json(['message' => $msg], 422);
            }
            return back()->withErrors(['tracks' => $msg])->withInput();
        }

        $this->onboarding->syncMenteeTracks(auth()->id(), $tracks->all());
        auth()->user()->update(['onboarding_step' => 3]);

        return $this->next($request, 4);
    }

    public function saveStep4(Request $request)
    {
        $data = $request->validate([
            'weekly_time_commitment' => 'required|string',
            'monthly_budget'         => 'nullable|string',
            'preferred_language'     => 'required|string',
            'mentoring_format'       => 'required|string',
        ]);

        $user = auth()->user();
        $preferences = array_merge($user->preferences ?? [], [
            'weekly_time_commitment' => $data['weekly_time_commitment'],
            'monthly_budget'         => $data['monthly_budget'] ?? null,
            'preferred_language'     => $data['preferred_language'],
            'mentoring_format'       => $data['mentoring_format'],
        ]);

        $user->update([
            'preferences'     => $preferences,
            'onboarding_step' => 4,
        ]);

        return $this->complete($request);
    }

    public function complete(Request $request)
    {
        $user = auth()->user();
        $result = $this->onboarding->complete($user);

        if (! $result['completed']) {
            $msg = 'Please complete all onboarding steps before finishing.';
            if ($request->ajax()) {
                return response()->json([
                    'message'        => $msg,
                    'missing_fields' => $result['missing'],
                ], 422);
            }
            return back()->with('error', $msg);
        }

        $redirect = route('mentee.dashboard');
        if ($request->ajax()) {
            return response()->json([
                'message'  => $result['assigned']
                    ? 'Onboarding complete! A mentor has been assigned to you.'
                    : 'Onboarding complete!',
                'redirect' => $redirect,
            ]);
        }

        return redirect($redirect)->with('success', $result['assigned']
            ? 'Onboarding complete! A mentor has been assigned to you.'
            : 'Onboarding complete!');
    }

    private function next(Request $request, int $step)
    {
        $redirect = route('mentee.onboarding', ['step' => $step]);
        if ($request->ajax()) {
            return response()->json(['message' => 'Saved!', 'redirect' => $redirect]);
        }
        return redirect($redirect);
    }
}
