<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show admin login form
     */
    public function showLoginForm()
    {
        return view('admin_login');
    }

    /**
     * Handle admin login (uses 'users' table) - redirects to HR dashboard
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Use the default 'web' guard which uses 'users' table
        if (Auth::guard('web')->attempt($credentials, $request->has('rememberMe'))) {
            $request->session()->regenerate();
            // Get the authenticated user from users table
            $user = Auth::guard('web')->user();
            
            // Redirect based on user role - all admin users go to HR dashboard
            if ($user->role === 'admin' || $user->role === 'hr') {
                return redirect()->intended(route('dashboard'));
            } else {
                // Regular users from users table also go to HR dashboard
                return redirect()->intended(route('dashboard'));
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }

    /**
     * Get current authenticated admin user
     */
    public function getCurrentUser()
    {
        $user = Auth::guard('web')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
            'profile_picture' => $user->profile_picture,
        ]);
    }
}
