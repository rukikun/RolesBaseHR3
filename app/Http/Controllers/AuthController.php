<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\Employee;
use App\Models\OtpVerification;
use App\Models\BiometricCredential;
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

            // Store OTP session data before logging out
            $request->session()->put('otp_email', $employee->email);
            $request->session()->put('remember_me', $request->has('rememberMe'));
            $request->session()->put('employee_name', $employee->first_name . ' ' . $employee->last_name);

            // Log out until OTP verification is complete
            Auth::guard('employee')->logout();

            // Regenerate session and redirect to OTP verification
            $request->session()->regenerate();
            $request->session()->forget('url.intended');

            return redirect()->route('admin.otp.form')
                ->with('info', "Please click 'Send Verification Code' to receive your OTP.");
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
        $isAjax = $request->expectsJson() || $request->ajax();

        // Check if session email matches
        if (session('otp_email') !== $email) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Invalid session. Please login again.'], 400);
            }
            return back()->withErrors(['otp_code' => 'Invalid session. Please login again.']);
        }

        $otpValid = OtpVerification::verifyOtp($email, $otpCode);
        if (!$otpValid) {
            $message = OtpVerification::hasExceededAttempts($email)
                ? 'Too many failed attempts. Please request a new code.'
                : 'Invalid or expired verification code.';

            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }

            return back()->withErrors(['otp_code' => $message]);
        }

        // Find employee after OTP is verified
        $employee = Employee::where('email', $email)->first();
        
        if (!$employee) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
            }
            return back()->withErrors(['otp_code' => 'Employee not found.']);
        }

        // Log the employee in
        Auth::guard('employee')->login($employee, session('remember_me', false));
        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        // Update last activity and set online status
        try {
            $employee->update([
                'last_activity' => now(),
                'online_status' => 'online'
            ]);
            
            // Log login activity if the class exists
            if (class_exists('\App\Models\EmployeeActivity')) {
                \App\Models\EmployeeActivity::logLogin();
            }
        } catch (\Exception $e) {
            // Continue login even if activity logging fails
            \Log::error('Employee activity update failed: ' . $e->getMessage());
        }

        if ($isAjax) {
            $hasBiometric = $employee->hasBiometricAuth();

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully.',
                'requires_biometric' => true,
                'has_biometric' => $hasBiometric,
                'employee_id' => $employee->id,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'redirect_url' => route('dashboard')
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Login successful!');
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

    /**
     * Check if employee has biometric authentication enabled
     */
    public function checkBiometricStatus(Request $request)
    {
        $email = $request->input('email') ?? session('otp_email');
        
        if (!$email) {
            return response()->json(['error' => 'No email provided'], 400);
        }

        $employee = Employee::where('email', $email)->first();
        
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $hasBiometric = $employee->hasBiometricAuth();
        
        return response()->json([
            'has_biometric' => $hasBiometric,
            'employee_id' => $employee->id,
            'employee_name' => $employee->first_name . ' ' . $employee->last_name
        ]);
    }

    /**
     * Register biometric credential (Simplified version)
     */
    public function registerBiometric(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->email;
        $employee = Employee::where('email', $email)->first();

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        try {
            // Create a default biometric credential for the employee
            $existingCredential = BiometricCredential::where('employee_id', $employee->id)->first();
            
            if ($existingCredential) {
                return response()->json([
                    'success' => true,
                    'message' => 'Biometric authentication already registered',
                    'credential_id' => $existingCredential->id
                ]);
            }

            $credential = BiometricCredential::create([
                'employee_id' => $employee->id,
                'credential_id' => 'default_' . $employee->id . '_' . time(),
                'public_key' => 'default_fingerprint_key',
                'authenticator_type' => 'platform',
                'authenticator_data' => ['type' => 'default_fingerprint'],
                'device_name' => 'Default Fingerprint Device',
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Default biometric authentication registered successfully',
                'credential_id' => $credential->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Biometric registration failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to register biometric credential'], 500);
        }
    }

    /**
     * Verify biometric authentication (Simplified version)
     */
    public function verifyBiometric(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->email;
        $employee = Employee::where('email', $email)->first();

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Find any active biometric credential for the employee
        $credential = BiometricCredential::where('employee_id', $employee->id)
            ->where('is_active', true)
            ->first();

        if (!$credential) {
            return response()->json(['error' => 'Biometric credential not found'], 404);
        }

        // In a real implementation, you would verify the signature using the public key
        // For this demo, we'll simulate successful verification
        try {
            // Update last used timestamp
            $credential->updateLastUsed();

            // Complete the login process
            Auth::guard('employee')->login($employee, $request->session()->get('remember_me', false));
            $request->session()->regenerate();

            // Update employee activity
            $employee->update([
                'last_activity' => now(),
                'online_status' => 'online'
            ]);

            // Log login activity if the class exists
            if (class_exists('\App\Models\EmployeeActivity')) {
                \App\Models\EmployeeActivity::logLogin();
            }

            // Clear session data
            $request->session()->forget(['otp_email', 'remember_me']);

            return response()->json([
                'success' => true,
                'message' => 'Biometric authentication successful',
                'redirect_url' => route('dashboard')
            ]);

        } catch (\Exception $e) {
            \Log::error('Biometric verification failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Biometric verification failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
