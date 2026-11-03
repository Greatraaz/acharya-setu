<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MentorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Checks that the user is authenticated and has a mentor role.
     * Redirects unauthenticated users to the login page.
     * Blocks users who are not mentors.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['status'=> false, 'message' => 'Unauthenticated.'], 401);
        }

        if (! $user->isSessionHost()) {
            return response()->json(['status'=> false, 'message' => 'Unauthorized. Session host access required.'], 403);
        }

        $path = $request->path();
        $onboardingPath = str_contains($path, '/onboarding') || str_contains($path, '/account');
        if (! $onboardingPath && ! $user->canUsePlatform()) {
            return response()->json([
                'status'               => false,
                'statuscode'           => 403,
                'message'              => $user->platformGateMessage(),
                'onboarding_completed' => (bool) $user->onboarding_completed,
                'mentor_status'        => $user->mentor_status,
                'can_use_platform'     => false,
                'can_skip_onboarding'  => $user->canSkipOnboarding(),
            ], 403);
        }

        return $next($request);
    }
}