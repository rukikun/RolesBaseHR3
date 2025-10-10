<?php

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== 2FA LOGIN DEBUG TOOL ===\n\n";

// Test credentials from the screenshot
$testEmail = 'johnkaizer19.jh@gmail.com';
$testPassword = 'password123';

echo "Testing login flow for: {$testEmail}\n\n";

echo "=== STEP 1: Check Employee Exists ===\n";
try {
    $employee = \App\Models\Employee::where('email', $testEmail)->first();
    if ($employee) {
        echo "✅ Employee found:\n";
        echo "  - ID: {$employee->id}\n";
        echo "  - Name: {$employee->first_name} {$employee->last_name}\n";
        echo "  - Email: {$employee->email}\n";
        echo "  - Position: {$employee->position}\n";
        echo "  - Role: {$employee->role}\n";
        echo "  - Status: {$employee->status}\n";
        echo "  - Can Access Dashboard: " . ($employee->canAccessDashboard() ? 'YES' : 'NO') . "\n";
    } else {
        echo "❌ Employee not found with email: {$testEmail}\n";
        echo "Available employees:\n";
        $employees = \App\Models\Employee::select('email', 'first_name', 'last_name')->limit(5)->get();
        foreach ($employees as $emp) {
            echo "  - {$emp->email} ({$emp->first_name} {$emp->last_name})\n";
        }
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== STEP 2: Test Password Verification ===\n";
try {
    // Test Laravel's Auth attempt
    $credentials = ['email' => $testEmail, 'password' => $testPassword];
    
    if (\Illuminate\Support\Facades\Auth::guard('employee')->attempt($credentials)) {
        echo "✅ Password verification successful\n";
        \Illuminate\Support\Facades\Auth::guard('employee')->logout(); // Logout immediately
    } else {
        echo "❌ Password verification failed\n";
        echo "  - Check if password is correct\n";
        echo "  - Check if password is properly hashed in database\n";
        
        // Show password hash for debugging
        if ($employee && $employee->password) {
            echo "  - Current password hash: " . substr($employee->password, 0, 20) . "...\n";
            echo "  - Hash starts with \$2y\$: " . (str_starts_with($employee->password, '$2y$') ? 'YES' : 'NO') . "\n";
        }
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Auth error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== STEP 3: Test OTP Generation ===\n";
try {
    $otpRecord = \App\Models\OtpVerification::generateOtp($testEmail);
    echo "✅ OTP generated successfully:\n";
    echo "  - OTP Code: {$otpRecord->otp_code}\n";
    echo "  - Email: {$otpRecord->email}\n";
    echo "  - Expires At: {$otpRecord->expires_at}\n";
    echo "  - Attempts: {$otpRecord->attempts}\n";
} catch (Exception $e) {
    echo "❌ OTP generation error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== STEP 4: Test Email Configuration ===\n";
$emailConfig = [
    'MAIL_HOST' => env('MAIL_HOST'),
    'MAIL_PORT' => env('MAIL_PORT'),
    'MAIL_USERNAME' => env('MAIL_USERNAME'),
    'MAIL_PASSWORD' => env('MAIL_PASSWORD') ? 'CONFIGURED' : 'NOT SET',
    'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
    'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
    'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
];

foreach ($emailConfig as $key => $value) {
    $status = $value ? '✅' : '❌';
    echo "{$status} {$key}: " . ($value ?: 'NOT SET') . "\n";
}

echo "\n=== STEP 5: Test PHPMailer Service ===\n";
try {
    $phpMailer = new \App\Services\PHPMailerService();
    echo "✅ PHPMailer service instantiated successfully\n";
    
    // Test OTP email sending
    $result = $phpMailer->sendOtpEmail($testEmail, $otpRecord->otp_code, $employee->first_name . ' ' . $employee->last_name);
    
    if ($result['success']) {
        echo "✅ OTP email sent successfully!\n";
        echo "  - Message: {$result['message']}\n";
        echo "  - Check your email inbox for the OTP code\n";
    } else {
        echo "❌ OTP email failed to send!\n";
        echo "  - Error: {$result['message']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ PHPMailer error: " . $e->getMessage() . "\n";
}

echo "\n=== STEP 6: Test Complete Login Flow ===\n";
try {
    // Simulate the complete login process
    echo "Simulating AuthController login process...\n";
    
    $credentials = ['email' => $testEmail, 'password' => $testPassword];
    
    if (\Illuminate\Support\Facades\Auth::guard('employee')->attempt($credentials)) {
        $employee = \Illuminate\Support\Facades\Auth::guard('employee')->user();
        
        if (!$employee->canAccessDashboard()) {
            echo "❌ Employee cannot access dashboard\n";
            exit(1);
        }
        
        // Logout immediately (as done in AuthController)
        \Illuminate\Support\Facades\Auth::guard('employee')->logout();
        
        // Generate OTP
        $otpRecord = \App\Models\OtpVerification::generateOtp($testEmail);
        
        // Send email
        $phpMailer = new \App\Services\PHPMailerService();
        $result = $phpMailer->sendOtpEmail($testEmail, $otpRecord->otp_code, $employee->first_name . ' ' . $employee->last_name);
        
        if ($result['success']) {
            echo "✅ Complete login flow successful!\n";
            echo "  - Credentials validated ✅\n";
            echo "  - Dashboard access confirmed ✅\n";
            echo "  - OTP generated ✅\n";
            echo "  - Email sent ✅\n";
            echo "\nNext steps:\n";
            echo "1. Check your email for OTP code: {$otpRecord->otp_code}\n";
            echo "2. Go to: /admin/otp-verification\n";
            echo "3. Enter the OTP code to complete login\n";
        } else {
            echo "❌ Email sending failed in complete flow\n";
            echo "  - Error: {$result['message']}\n";
        }
        
    } else {
        echo "❌ Credential validation failed in complete flow\n";
    }
    
} catch (Exception $e) {
    echo "❌ Complete flow error: " . $e->getMessage() . "\n";
}

echo "\n=== RECOMMENDATIONS ===\n";

if (!env('MAIL_USERNAME') || !env('MAIL_PASSWORD')) {
    echo "❌ Check .env file for missing email credentials\n";
}

if (!env('MAIL_HOST')) {
    echo "❌ Check .env file for missing MAIL_HOST\n";
}

echo "✅ If emails are not received, check spam/junk folder\n";
echo "✅ For Gmail, ensure you're using App Password, not regular password\n";
echo "✅ Test URL: https://hr3.jetlougetravels-ph.com/test-2fa-debug\n";

?>
