<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PRODUCTION OTP TEST ===\n";
echo "Domain: " . request()->getHost() . "\n";
echo "Environment: " . app()->environment() . "\n";
echo "Time: " . now() . "\n\n";

$testEmail = 'johnkaizer19.jh@gmail.com';

// Test 1: Check environment variables
echo "1. EMAIL CONFIGURATION:\n";
$emailConfig = [
    'MAIL_HOST' => env('MAIL_HOST'),
    'MAIL_PORT' => env('MAIL_PORT'),
    'MAIL_USERNAME' => env('MAIL_USERNAME'),
    'MAIL_PASSWORD' => env('MAIL_PASSWORD') ? 'SET (' . strlen(env('MAIL_PASSWORD')) . ' chars)' : 'NOT SET',
    'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
    'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
    'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
];

foreach ($emailConfig as $key => $value) {
    $status = $value ? '✅' : '❌';
    echo "  {$status} {$key}: {$value}\n";
}

// Test 2: Check session
echo "\n2. SESSION CHECK:\n";
session_start();
if (session('otp_email')) {
    echo "✅ Session email: " . session('otp_email') . "\n";
} else {
    echo "❌ No session email found\n";
    // Set test session for testing
    session(['otp_email' => $testEmail]);
    echo "✅ Test session set: {$testEmail}\n";
}

// Test 3: Check employee
echo "\n3. EMPLOYEE CHECK:\n";
try {
    $employee = \App\Models\Employee::where('email', $testEmail)->first();
    if ($employee) {
        echo "✅ Employee found: " . $employee->first_name . " " . $employee->last_name . "\n";
    } else {
        echo "❌ Employee not found\n";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

// Test 4: Test OTP generation
echo "\n4. OTP GENERATION TEST:\n";
try {
    $otp = \App\Models\OtpVerification::generateOtp($testEmail);
    echo "✅ OTP generated: " . $otp->otp_code . "\n";
    echo "  Expires: " . $otp->expires_at . "\n";
} catch (Exception $e) {
    echo "❌ OTP generation failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 5: Test PHPMailer service
echo "\n5. PHPMAILER SERVICE TEST:\n";
try {
    $phpMailer = new \App\Services\PHPMailerService();
    echo "✅ PHPMailer service created\n";
    
    // Test email sending
    echo "  Testing email send...\n";
    $result = $phpMailer->sendOtpEmail($testEmail, $otp->otp_code, $employee ? $employee->first_name . ' ' . $employee->last_name : 'Test User');
    
    if ($result['success']) {
        echo "✅ Email sent successfully!\n";
        echo "  Message: " . $result['message'] . "\n";
    } else {
        echo "❌ Email failed to send\n";
        echo "  Error: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ PHPMailer error: " . $e->getMessage() . "\n";
    echo "  Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 6: Test resend endpoint simulation
echo "\n6. RESEND ENDPOINT SIMULATION:\n";
try {
    // Simulate the resend request
    $request = new \Illuminate\Http\Request();
    $request->merge(['email' => $testEmail]);
    
    $authController = new \App\Http\Controllers\AuthController();
    $response = $authController->resendOtp($request);
    
    $responseData = $response->getData(true);
    
    if ($responseData['success'] ?? false) {
        echo "✅ Resend endpoint working\n";
        echo "  Message: " . ($responseData['message'] ?? 'Success') . "\n";
    } else {
        echo "❌ Resend endpoint failed\n";
        echo "  Message: " . ($responseData['message'] ?? 'Unknown error') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Resend endpoint error: " . $e->getMessage() . "\n";
}

echo "\n=== PRODUCTION DIAGNOSTICS COMPLETE ===\n";
echo "If email sending failed, check:\n";
echo "1. Production .env file has correct email settings\n";
echo "2. Gmail App Password is valid and not expired\n";
echo "3. Server can connect to smtp.gmail.com:587\n";
echo "4. No firewall blocking SMTP connections\n";

?>
