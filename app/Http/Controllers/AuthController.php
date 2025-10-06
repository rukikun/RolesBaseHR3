<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
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
     * Handle admin login (uses 'employees' table) - redirects to HR dashboard
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Use the default 'web' guard which now uses 'employees' table
        if (Auth::guard('web')->attempt($credentials, $request->has('rememberMe'))) {
            $request->session()->regenerate();
            // Get the authenticated employee
            $employee = Auth::guard('web')->user();
            
            // Update last activity timestamp
            try {
                $employee->update(['last_activity' => now()]);
                
                // Log login activity (if UserActivity model exists)
                if (class_exists('App\Models\UserActivity')) {
                    UserActivity::log('login', 'Employee logged in successfully', [
                        'user_agent' => $request->userAgent(),
                        'ip_address' => $request->ip(),
                        'login_method' => 'web_form'
                    ]);
                }
            } catch (\Exception $e) {
                // Continue login even if activity logging fails
                \Log::error('Login activity logging failed: ' . $e->getMessage());
            }
            
            // Redirect based on employee role
            if ($employee->hasAnyRole(['admin', 'hr', 'manager'])) {
                return redirect()->intended(route('dashboard'));
            } else {
                // Regular employees go to employee dashboard
                return redirect()->intended(route('employee.dashboard'));
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
     * Get current authenticated employee
     */
    public function getCurrentUser()
    {
        $employee = Auth::guard('web')->user();
        
        if (!$employee) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        return response()->json([
            'id' => $employee->id,
            'name' => $employee->full_name,
            'email' => $employee->email,
            'role' => $employee->role,
            'phone' => $employee->phone,
            'profile_picture' => $employee->profile_picture,
            'position' => $employee->position,
            'department' => $employee->department,
        ]);
    }
}
