<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== QUICK 2FA TEST ===\n\n";

$testEmail = 'johnkaizer19.jh@gmail.com';

// Test 1: Check employee
echo "1. Employee Check: ";
$employee = \App\Models\Employee::where('email', $testEmail)->first();
if ($employee) {
    echo "✅ Found - " . $employee->first_name . " " . $employee->last_name . "\n";
} else {
    echo "❌ Not found\n";
    exit(1);
}

// Test 2: Generate OTP
echo "2. OTP Generation: ";
try {
    $otp = \App\Models\OtpVerification::generateOtp($testEmail);
    echo "✅ Generated - " . $otp->otp_code . "\n";
} catch (Exception $e) {
    echo "❌ Failed - " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: PHPMailer Service
echo "3. PHPMailer Service: ";
try {
    $phpMailer = new \App\Services\PHPMailerService();
    echo "✅ Service created\n";
} catch (Exception $e) {
    echo "❌ Failed - " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Send OTP Email
echo "4. Send OTP Email: ";
try {
    $result = $phpMailer->sendOtpEmail($testEmail, $otp->otp_code, $employee->first_name . ' ' . $employee->last_name);
    
    if ($result['success']) {
        echo "✅ Email sent successfully!\n";
        echo "   Message: " . $result['message'] . "\n";
        echo "\n🎉 SUCCESS: Check your email for OTP: " . $otp->otp_code . "\n";
    } else {
        echo "❌ Email failed to send\n";
        echo "   Error: " . $result['message'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ Exception - " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETED ===\n";

?>
