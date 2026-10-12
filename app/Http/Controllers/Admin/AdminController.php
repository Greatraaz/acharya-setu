<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /** GET /admin/login */
    public function showLogin()
    {
        if (session('admin_authenticated')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * POST /admin/login
     *
     * Returns JSON because the login form is submitted via fetch() in admin.js.
     * CSRF is enforced automatically by Laravel's VerifyCsrfToken middleware via
     * the X-CSRF-TOKEN header that admin.js attaches to every request.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim((string) $request->email));
        $ip = $request->ip() ?: 'local';

        if ($wait = \App\Support\LoginThrottle::availableIn($email, $ip)) {
            return response()->json([
                'status'  => 429,
                'message' => \App\Support\LoginThrottle::lockedMessage($wait),
            ], 429);
        }

        $credentials = [
            'email'    => $email,
            'password' => $request->password,
        ];

        if (!Auth::guard('web')->attempt($credentials, true)) {
            \App\Support\LoginThrottle::hit($email, $ip);

            return response()->json([
                'status'  => 401,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::guard('web')->user();

        if ($user->role !== 'admin' || \App\Support\LoginThrottle::isDisabled($user)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'status'  => 403,
                'message' => $user->role !== 'admin'
                    ? 'Unauthorized. You are not an admin.'
                    : 'This account has been disabled.',
            ], 403);
        }

        \App\Support\LoginThrottle::clear($email, $ip);
        $request->session()->regenerate();

        return response()->json([
            'status'   => 200,
            'message'  => 'Login successful',
            'user' => [
                'id'   => $user->id,
                'name' => $user->name,
                'email'=> $user->email
            ],
            'redirect' => route('admin.dashboard')
        ]);
    }

    /**
     * POST /admin/logout
     *
     * Also returns JSON — the layout logout form uses class="admin-form"
     * so it goes through fetch() too. data-redirect on the form handles
     * the client-side navigation to login page.
     */
    public function logout(Request $request)
    {
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        $request->session()->forget(['admin_authenticated', 'admin_email', 'admin_login_at']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status'   => 200,
                'message'  => 'Logged out successfully.',
                'redirect' => route('admin.login'),
            ], 200);
        }

        return redirect()->route('admin.login');
    }
}
