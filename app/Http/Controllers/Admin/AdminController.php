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

        $credentials = [
            'email'    => $request->email,
            'password' => $request->password,
        ];

        // If you want only admins allowed:
        // add role restriction if column exists
        /*
        $credentials['role'] = 'admin';
        */

        if (!Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {

            return response()->json([
                'status'  => 401,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Prevent session fixation
        $request->session()->regenerate();

        $user = Auth::guard('web')->user();

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
        Auth::guard('web')->logout();

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
