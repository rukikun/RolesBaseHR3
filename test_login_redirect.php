<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== LOGIN REDIRECT TEST ===\n\n";

$testEmail = 'johnkaizer19.jh@gmail.com';
$testPassword = 'password123';

// Test the exact login flow
echo "1. Testing credential validation...\n";
$credentials = ['email' => $testEmail, 'password' => $testPassword];

if (\Illuminate\Support\Facades\Auth::guard('employee')->attempt($credentials)) {
    echo "✅ Credentials valid\n";
    
    $employee = \Illuminate\Support\Facades\Auth::guard('employee')->user();
    echo "   Employee: " . $employee->first_name . " " . $employee->last_name . "\n";
    
    // Check dashboard access
    if (!$employee->canAccessDashboard()) {
        echo "❌ Cannot access dashboard\n";
        exit(1);
    }
    echo "✅ Dashboard access confirmed\n";
    
    // Logout (as done in AuthController)
    \Illuminate\Support\Facades\Auth::guard('employee')->logout();
    echo "✅ User logged out after validation\n";
    
    // Test OTP generation
    echo "\n2. Testing OTP generation...\n";
    try {
        $otpRecord = \App\Models\OtpVerification::generateOtp($testEmail);
        echo "✅ OTP generated: " . $otpRecord->otp_code . "\n";
        
        // Test email sending
        echo "\n3. Testing email sending...\n";
        $phpMailer = new \App\Services\PHPMailerService();
        $result = $phpMailer->sendOtpEmail($testEmail, $otpRecord->otp_code, $employee->first_name . ' ' . $employee->last_name);
        
        if ($result['success']) {
            echo "✅ Email sent successfully\n";
            
            // Test session setup (this is what should happen in AuthController)
            echo "\n4. Testing session setup...\n";
            session(['otp_email' => $testEmail]);
            session(['remember_me' => false]);
            
            echo "✅ Session variables set:\n";
            echo "   otp_email: " . session('otp_email') . "\n";
            echo "   remember_me: " . (session('remember_me') ? 'true' : 'false') . "\n";
            
            // Test route generation
            echo "\n5. Testing route generation...\n";
            try {
                $otpUrl = route('admin.otp.form');
                echo "✅ OTP form URL: " . $otpUrl . "\n";
                
                // Test if we can access the OTP form
                echo "\n6. Testing OTP form access...\n";
                if (session('otp_email')) {
                    echo "✅ Session exists - OTP form should be accessible\n";
                    echo "   Go to: " . $otpUrl . "\n";
                    echo "   Use OTP: " . $otpRecord->otp_code . "\n";
                } else {
                    echo "❌ Session missing - would redirect to login\n";
                }
                
            } catch (Exception $e) {
                echo "❌ Route error: " . $e->getMessage() . "\n";
            }
            
        } else {
            echo "❌ Email failed: " . $result['message'] . "\n";
        }
        
    } catch (Exception $e) {
        echo "❌ OTP generation failed: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "❌ Credential validation failed\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Try logging in again at: https://hr3.jetlougetravels-ph.com/admin/login\n";
echo "2. If redirected to login instead of OTP page, check browser console for errors\n";
echo "3. Try accessing OTP page directly: https://hr3.jetlougetravels-ph.com/admin/otp-verification\n";
echo "4. Check if session cookies are being set properly\n";

?>
