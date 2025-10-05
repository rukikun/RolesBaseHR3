<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserActivity;

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
            
            // Update last login timestamp
            try {
                $user->update(['last_login' => now()]);
                
                // Log login activity
                UserActivity::log('login', 'User logged in successfully', [
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'login_method' => 'web_form'
                ]);
            } catch (\Exception $e) {
                // Continue login even if activity logging fails
                \Log::error('Login activity logging failed: ' . $e->getMessage());
            }
            
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
        // Log logout activity before logging out
        if (Auth::check()) {
            UserActivity::log('logout', 'User logged out', [
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip()
            ]);
        }
        
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
