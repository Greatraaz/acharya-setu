<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    /**
     * Handle an incoming request.
     * Supports one or more allowed roles, e.g. 'admin', or 'admin,manager'
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  mixed ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Normalize roles: if passed as comma-separated single argument, explode it
        if (count($roles) === 1 && is_string($roles[0]) && str_contains($roles[0], ',')) {
            $roles = array_map('trim', explode(',', $roles[0]));
        }

        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            if ($request->is('admin') || $request->is('admin/*') || in_array('admin', $roles, true)) {
                return redirect()->route('admin.login');
            }

            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            abort(403, 'Unauthorized Access');
        }

        return $next($request);
    }
}
