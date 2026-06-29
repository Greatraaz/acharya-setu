<?php
// ── app/Http/Middleware/OnboardingComplete.php ────────────────
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OnboardingComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) return redirect()->route('login');

        if (! $user->onboarding_completed) {
            $step = max(1, $user->onboarding_step + 1);
            return redirect()->route('mentee.onboarding', ['step' => min($step, 4)]);
        }

        return $next($request);
    }
}