<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\Employee;
use App\Models\OtpVerification;
use App\Mail\OtpMail;
use App\Services\PHPMailerService;

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
     * Handle employee login (uses 'employees' table) - WITH 2FA ENABLED
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Use the 'employee' guard which uses 'employees' table
        if (Auth::guard('employee')->attempt($credentials, $request->has('rememberMe'))) {
            $employee = Auth::guard('employee')->user();
            
            // Check if employee can access dashboard
            if (!$employee->canAccessDashboard()) {
                Auth::guard('employee')->logout();
                return back()->withErrors([
                    'email' => 'Your account does not have permission to access this system.',
                ]);
            }

            // Store email and remember me preference in session BEFORE logout
            $request->session()->put('otp_email', $credentials['email']);
            $request->session()->put('remember_me', $request->has('rememberMe'));
            $request->session()->put('employee_name', $employee->first_name . ' ' . $employee->last_name);
            
            // Logout immediately after credential verification (2FA required)
            Auth::guard('employee')->logout();

            // Regenerate session to ensure it persists
            $request->session()->regenerate();
            
            // Redirect to OTP form without sending email yet
            return redirect()->route('admin.otp.form')->with('info', 'Please click "Send Verification Code" to receive your OTP.');
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
     * Show OTP verification form
     */
    public function showOtpForm()
    {
        if (!session('otp_email')) {
            return redirect()->route('admin.login')->withErrors(['email' => 'Session expired. Please login again.']);
        }
        
        return view('otp_verification');
    }

    /**
     * Verify OTP and complete login
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        $email = $request->email;
        $otpCode = $request->otp_code;

        // Check if session email matches
        if (session('otp_email') !== $email) {
            return back()->withErrors(['otp_code' => 'Invalid session. Please login again.']);
        }

        // Check for too many attempts
        if (OtpVerification::hasExceededAttempts($email)) {
            return back()->withErrors(['otp_code' => 'Too many failed attempts. Please request a new code.']);
        }

        // Verify OTP
        if (!OtpVerification::verifyOtp($email, $otpCode)) {
            return back()->withErrors(['otp_code' => 'Invalid or expired verification code.']);
        }

        // Find employee and log them in
        $employee = Employee::where('email', $email)->first();
        
        if (!$employee) {
            return back()->withErrors(['otp_code' => 'Employee not found.']);
        }

        // Log the employee in
        Auth::guard('employee')->login($employee, session('remember_me', false));
        $request->session()->regenerate();

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

        // Clear OTP session data
        $request->session()->forget(['otp_email', 'remember_me']);

        return redirect()->intended(route('dashboard'))->with('success', 'Login successful!');
    }

    /**
     * Resend OTP code
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->email;

        // Check if session email matches
        if (session('otp_email') !== $email) {
            return response()->json(['success' => false, 'message' => 'Invalid session.'], 400);
        }

        // Find employee
        $employee = Employee::where('email', $email)->first();
        
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        try {
            // Generate new OTP
            $otpRecord = OtpVerification::generateOtp($email);
            
            // Get employee name for email
            $userName = $employee->first_name . ' ' . $employee->last_name;
            
            // Send OTP email using PHPMailer
            $phpMailer = new PHPMailerService();
            $result = $phpMailer->sendOtpEmail($email, $otpRecord->otp_code, $userName);
            
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            
            return response()->json(['success' => true, 'message' => 'New verification code sent successfully.']);
            
        } catch (\Exception $e) {
            \Log::error('OTP resend failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send verification code.'], 500);
        }
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
