<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;

class EmployeeAuthController extends Controller
{
    /**
     * Show employee login form
     */
    public function showLoginForm()
    {
        return view('employee_login');
    }

    /**
     * Handle employee login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Use the employee guard for authentication
        if (Auth::guard('employee')->attempt($credentials, $request->has('rememberMe'))) {
            $request->session()->regenerate();
            
            // Update employee online status
            $employee = Auth::guard('employee')->user();
            if ($employee) {
                $employee->update([
                    'online_status' => 'online',
                    'last_activity' => now()
                ]);
            }
            
            // Redirect to employee dashboard/ESS portal
            return redirect()->intended('/employee/dashboard');
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
        // Set employee offline status before logout
        $employee = Auth::guard('employee')->user();
        if ($employee) {
            $employee->update([
                'online_status' => 'offline',
                'last_activity' => now()
            ]);
        }
        
        Auth::guard('employee')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/employee/login');
    }

    /**
     * Show employee registration form (if needed)
     */
    public function showRegistrationForm()
    {
        return view('employee_register');
    }

    /**
     * Handle employee registration (if needed)
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:employees'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'hire_date' => ['required', 'date'],
        ]);

        $employee = Employee::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'position' => $request->position,
            'department' => $request->department,
            'hire_date' => $request->hire_date,
            'status' => 'active',
            'online_status' => 'offline',
        ]);

        // Automatically login the employee after registration
        Auth::guard('employee')->login($employee);

        return redirect('/employee/dashboard');
    }

    /**
     * Get current authenticated employee
     */
    public function getCurrentEmployee()
    {
        $employee = Auth::guard('employee')->user();
        
        if (!$employee) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        return response()->json([
            'id' => $employee->id,
            'name' => $employee->first_name . ' ' . $employee->last_name,
            'email' => $employee->email,
            'position' => $employee->position,
            'department' => $employee->department,
            'online_status' => $employee->online_status,
            'profile_picture' => $employee->profile_picture,
        ]);
    }
}
