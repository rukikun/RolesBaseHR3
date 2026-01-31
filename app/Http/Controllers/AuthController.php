<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\Employee;
use App\Models\OtpVerification;
use App\Models\BiometricCredential;
use App\Mail\OtpMail;
use App\Services\PHPMailerService;

class AuthController extends Controller
{
    private ?string $systemAccountAuthError = null;
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

        $systemAccount = $this->authenticateSystemAccount($credentials['email'], $credentials['password']);

        if (!$systemAccount) {
            $errorMessage = match ($this->systemAccountAuthError) {
                'api_unreachable' => 'Unable to reach the system account service. Please try again.',
                'password_mismatch' => 'Incorrect password for the system account.',
                'email_not_found' => 'No system account was found for this email.',
                default => 'The provided credentials do not match our system accounts.'
            };
            return back()->withErrors([
                'email' => $errorMessage,
            ])->withInput($request->only('email'));
        }

        if (!empty($systemAccount['blocked'])) {
            return back()->withErrors([
                'email' => 'This system account is currently blocked.',
            ]);
        }

        $employee = $this->syncSystemAccountEmployee($systemAccount, $credentials['password']);

        if (!$employee) {
            return back()->withErrors([
                'email' => 'System account details are incomplete. Please contact support.',
            ]);
        }

        if (!$employee->canAccessDashboard()) {
            return back()->withErrors([
                'email' => 'Your account does not have permission to access this system.',
            ]);
        }

        // Store OTP session data before completing login
        $request->session()->put('otp_email', $employee->email);
        $request->session()->put('remember_me', $request->has('rememberMe'));
        $request->session()->put('employee_name', $employee->first_name . ' ' . $employee->last_name);

        // Regenerate session and redirect to OTP verification
        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return redirect()->route('admin.otp.form')
            ->with('info', "Please click 'Send Verification Code' to receive your OTP.");
    }

    /**
     * Validate credentials against HR4 system accounts API.
     */
    private function authenticateSystemAccount(string $email, string $password): ?array
    {
        $this->systemAccountAuthError = null;
        $normalizedEmail = strtolower(trim($email));
        $apiUrls = [
            'https://hr4.jetlougetravels-ph.com/api/accounts',
            'http://hr4.jetlougetravels-ph.com/api/accounts'
        ];
        $apiErrors = [];
        $emailMatched = false;
        $apiReachable = false;

        foreach ($apiUrls as $apiUrl) {
            try {
                $response = Http::timeout(15)
                    ->acceptJson()
                    ->withoutVerifying()
                    ->get($apiUrl);

                if (!$response->successful()) {
                    $apiErrors[] = $apiUrl . ' responded with status ' . $response->status();
                    continue;
                }

                $apiReachable = true;

                $payload = $response->json();
                $accounts = $this->extractSystemAccounts($payload);

                $matchedAccount = $this->matchSystemAccount($accounts, $normalizedEmail, $password, $emailMatched);
                if ($matchedAccount) {
                    return $matchedAccount;
                }
            } catch (\Exception $e) {
                $apiErrors[] = $apiUrl . ' error: ' . $e->getMessage();
            }
        }

        $fallbackAccount = $this->authenticateSystemAccountViaStream(
            $normalizedEmail,
            $password,
            $apiUrls,
            $emailMatched,
            $apiReachable,
            $apiErrors
        );
        if ($fallbackAccount) {
            return $fallbackAccount;
        }

        if (!empty($apiErrors)) {
            \Log::warning('HR4 system account authentication failed: ' . implode(' | ', $apiErrors));
        }

        $this->systemAccountAuthError = match (true) {
            !$apiReachable => 'api_unreachable',
            $emailMatched => 'password_mismatch',
            default => 'email_not_found',
        };

        \Log::info('System account auth failure', [
            'email' => $normalizedEmail,
            'reason' => $this->systemAccountAuthError,
        ]);

        return null;
    }

    /**
     * Extract system accounts from the API payload.
     */
    private function extractSystemAccounts($payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        if (isset($payload['system_accounts']) && is_array($payload['system_accounts'])) {
            return $payload['system_accounts'];
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            if (isset($payload['data']['system_accounts']) && is_array($payload['data']['system_accounts'])) {
                return $payload['data']['system_accounts'];
            }

            if (isset($payload['data']['accounts']) && is_array($payload['data']['accounts'])) {
                return $payload['data']['accounts'];
            }
        }

        return [];
    }

    /**
     * Match a system account from the API list.
     */
    private function matchSystemAccount(array $accounts, string $normalizedEmail, string $password, bool &$emailMatched): ?array
    {
        foreach ($accounts as $account) {
            if (!is_array($account)) {
                continue;
            }

            $accountType = isset($account['account_type']) ? strtolower(trim((string) $account['account_type'])) : null;
            if ($accountType && $accountType !== 'system') {
                continue;
            }

            $employee = $account['employee'] ?? [];
            $accountEmail = $employee['email'] ?? $account['email'] ?? null;
            $normalizedAccountEmail = $accountEmail ? strtolower(trim($accountEmail)) : null;

            if (!$normalizedAccountEmail || $normalizedAccountEmail !== $normalizedEmail) {
                continue;
            }

            $emailMatched = true;

            $accountPassword = isset($account['password']) ? trim((string) $account['password']) : '';
            if ($this->passwordMatches($password, $accountPassword)) {
                return $account;
            }
        }

        return null;
    }

    /**
     * Fallback to file_get_contents if the HTTP client cannot reach the API.
     */
    private function authenticateSystemAccountViaStream(
        string $normalizedEmail,
        string $password,
        array $apiUrls,
        bool &$emailMatched,
        bool &$apiReachable,
        array &$apiErrors
    ): ?array {
        $allowUrlFopen = filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN);

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
            'http' => [
                'timeout' => 15,
            ],
        ]);

        foreach ($apiUrls as $apiUrl) {
            $response = null;
            if ($allowUrlFopen) {
                $response = @file_get_contents($apiUrl, false, $context);
            }

            if ($response === false || $response === null) {
                $curlPayload = $this->fetchApiPayloadViaCurl($apiUrl, $apiErrors);
                if ($curlPayload !== null) {
                    $apiReachable = true;
                    $accounts = $this->extractSystemAccounts($curlPayload);
                    $matchedAccount = $this->matchSystemAccount($accounts, $normalizedEmail, $password, $emailMatched);
                    if ($matchedAccount) {
                        return $matchedAccount;
                    }
                } else {
                    if ($allowUrlFopen) {
                        $error = error_get_last();
                        $apiErrors[] = $apiUrl . ' stream error: ' . ($error['message'] ?? 'unknown error');
                    }
                }
                continue;
            }

            $apiReachable = true;

            $payload = json_decode($response, true);
            $accounts = $this->extractSystemAccounts($payload);
            $matchedAccount = $this->matchSystemAccount($accounts, $normalizedEmail, $password, $emailMatched);
            if ($matchedAccount) {
                return $matchedAccount;
            }
        }

        return null;
    }

    /**
     * Fetch API payload using cURL for environments without allow_url_fopen.
     */
    private function fetchApiPayloadViaCurl(string $apiUrl, array &$apiErrors): ?array
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $statusCode >= 400) {
            $apiErrors[] = $apiUrl . ' curl error: ' . ($curlError ?: 'HTTP ' . $statusCode);
            return null;
        }

        $payload = json_decode($response, true);
        return is_array($payload) ? $payload : null;
    }

    /**
     * Sync HR4 system account employee data into local employees table.
     */
    private function syncSystemAccountEmployee(array $systemAccount, string $password): ?Employee
    {
        $employeePayload = $systemAccount['employee'] ?? null;

        if (!is_array($employeePayload)) {
            return null;
        }

        $email = $employeePayload['email'] ?? null;
        if (!$email) {
            return null;
        }

        $departmentPayload = $employeePayload['department'] ?? null;
        $departmentName = null;
        if (is_array($departmentPayload)) {
            $departmentName = $departmentPayload['name'] ?? null;
        } elseif (is_string($departmentPayload)) {
            $departmentName = $departmentPayload;
        }

        $position = $employeePayload['position'] ?? $employeePayload['role'] ?? 'System Account';
        $role = $this->mapSystemRole($position);

        $status = $this->mapEmployeeStatus($employeePayload['employee_status'] ?? null);

        $updateData = [
            'first_name' => $employeePayload['first_name'] ?? 'System',
            'last_name' => $employeePayload['last_name'] ?? 'Account',
            'position' => $position,
            'department' => $departmentName,
            'role' => $role,
            'phone' => $employeePayload['phone'] ?? null,
            'address' => $employeePayload['address'] ?? null,
            'hire_date' => $employeePayload['date_hired'] ?? $employeePayload['start_date'] ?? null,
            'status' => $status,
            'password' => Hash::make($password)
        ];

        if (Schema::hasColumn('employees', 'profile_picture_url')) {
            $updateData['profile_picture_url'] = $systemAccount['profile_picture'] ?? null;
        } elseif (Schema::hasColumn('employees', 'profile_picture')) {
            $updateData['profile_picture'] = $systemAccount['profile_picture'] ?? null;
        }

        return Employee::updateOrCreate(
            ['email' => $email],
            $updateData
        );
    }

    /**
     * Match system account password against plain or hashed API values.
     */
    private function passwordMatches(string $plain, ?string $stored): bool
    {
        if (!$stored) {
            return false;
        }

        $stored = trim((string) $stored);
        $isBcrypt = preg_match('/^\$2[aby]\$/', $stored) === 1;

        if ($isBcrypt) {
            return Hash::check($plain, $stored);
        }

        return hash_equals($stored, $plain);
    }

    /**
     * Map HR4 employee_status values into local employees.status enum.
     */
    private function mapEmployeeStatus(?string $status): string
    {
        $normalized = strtolower(trim((string) $status));

        $map = [
            'regular' => 'active',
            'new_hire' => 'active',
            'active' => 'active',
            'inactive' => 'inactive',
            'terminated' => 'terminated',
            'resigned' => 'terminated',
            'separated' => 'terminated',
        ];

        return $map[$normalized] ?? 'active';
    }

    /**
     * Map HR4 positions into local role values.
     */
    private function mapSystemRole(?string $position): string
    {
        $position = strtolower((string) $position);

        if (str_contains($position, 'system administrator') || str_contains($position, 'super admin')) {
            return 'super_admin';
        }

        if (str_contains($position, 'hr manager')) {
            return 'hr_manager';
        }

        if (str_contains($position, 'hr scheduler')) {
            return 'hr_scheduler';
        }

        if (str_contains($position, 'admin')) {
            return 'admin';
        }

        return 'employee';
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
