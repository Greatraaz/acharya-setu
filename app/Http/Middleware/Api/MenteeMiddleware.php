<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MenteeMiddleware
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

        if ($user->role !== 'mentee') {
            return response()->json(['status'=> false, 'message' => 'Unauthorized. You are not a mentee.'], 403);
        }

        return $next($request);
    }
}