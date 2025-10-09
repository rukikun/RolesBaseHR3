<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

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
     * Handle employee login (uses 'employees' table) - redirects to HR dashboard
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Use the 'employee' guard which uses 'employees' table
        if (Auth::guard('employee')->attempt($credentials, $request->has('rememberMe'))) {
            $request->session()->regenerate();
            // Get the authenticated employee from employees table
            $employee = Auth::guard('employee')->user();
            
            // Update last activity and set online status
            try {
                $employee->update([
                    'last_activity' => now(),
                    'online_status' => 'online'
                ]);
                
                // Log login activity
                \App\Models\EmployeeActivity::logLogin();
            } catch (\Exception $e) {
                // Continue login even if activity logging fails
                \Log::error('Employee activity update failed: ' . $e->getMessage());
            }
            
            // Redirect based on employee role - all roles go to dashboard
            if ($employee->canAccessDashboard()) {
                return redirect()->intended(route('dashboard'));
            } else {
                // If role doesn't have dashboard access, logout and show error
                Auth::guard('employee')->logout();
                return back()->withErrors([
                    'email' => 'Your account does not have permission to access this system.',
                ]);
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Handle employee logout
     */
    public function logout(Request $request)
    {
        // Update employee status before logging out
        if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
            try {
                $employee->update([
                    'online_status' => 'offline',
                    'last_activity' => now()
                ]);
                
                // Log logout activity
                \App\Models\EmployeeActivity::logLogout();
            } catch (\Exception $e) {
                \Log::error('Employee logout status update failed: ' . $e->getMessage());
            }
        }
        
        Auth::guard('employee')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }

    /**
     * Get current authenticated employee
     */
    public function getCurrentUser()
    {
        $employee = Auth::guard('employee')->user();
        
        if (!$employee) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        return response()->json([
            'id' => $employee->id,
            'name' => $employee->full_name,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'email' => $employee->email,
            'role' => $employee->role,
            'phone' => $employee->phone,
            'profile_picture' => $employee->profile_picture,
            'position' => $employee->position,
            'department' => $employee->department,
        ]);
    }
}
