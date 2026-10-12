<?php

namespace App\Http\Middleware;

use App\Support\LoginThrottle;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $token = method_exists($user, 'currentAccessToken') ? $user->currentAccessToken() : null;
            if ($token instanceof \Laravel\Sanctum\PersonalAccessToken && $this->tokenExpired($token)) {
                $token->delete();

                return response()->json([
                    'status'  => false,
                    'message' => 'Your session has expired. Please sign in again.',
                ], 401);
            }

            if (LoginThrottle::isDisabled($user)) {
                if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
                    $user->tokens()->delete();
                }

                if ($request->is('api/*') || $request->expectsJson()) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'This account has been disabled.',
                    ], 403);
                }

                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'This account has been disabled.',
                ]);
            }
        }

        $response = $next($request);

        if ($user) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }

    private function tokenExpired(object $token): bool
    {
        if (isset($token->expires_at) && $token->expires_at && $token->expires_at->isPast()) {
            return true;
        }

        $minutes = (int) config('sanctum.expiration');
        if ($minutes > 0 && isset($token->created_at) && $token->created_at) {
            return $token->created_at->lte(now()->subMinutes($minutes));
        }

        return false;
    }
}
