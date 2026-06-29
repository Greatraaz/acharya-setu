<?php
// ── app/Http/Middleware/MentorApproved.php ────────────────────
// Redirects unapproved mentors to the pending page
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MentorApproved
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) return redirect()->route('login');

        // If onboarding not done yet, send to onboarding
        if (! $user->onboarding_completed) {
            $step = max(1, $user->onboarding_step + 1);
            return redirect()->route('mentor.onboarding', ['step' => min($step, 5)]);
        }

        // If rejected or pending, show pending page
        if (in_array($user->mentor_status, ['pending', 'rejected'])) {
            return redirect()->route('mentor.onboarding.pending');
        }

        // If suspended
        if ($user->mentor_status === 'suspended') {
            abort(403, 'Your mentor account has been suspended. Please contact support.');
        }

        return $next($request);
    }
}