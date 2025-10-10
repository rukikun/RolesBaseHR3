<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== EMAIL CONFIGURATION TEST ===\n\n";

// Check environment variables
$emailConfig = [
    'MAIL_HOST' => env('MAIL_HOST'),
    'MAIL_PORT' => env('MAIL_PORT'),
    'MAIL_USERNAME' => env('MAIL_USERNAME'),
    'MAIL_PASSWORD' => env('MAIL_PASSWORD') ? '***CONFIGURED***' : 'NOT SET',
    'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
    'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
    'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
];

echo "Current Email Configuration:\n";
foreach ($emailConfig as $key => $value) {
    echo "  {$key}: " . ($value ?: 'NOT SET') . "\n";
}

echo "\n=== TESTING EMAIL FUNCTIONALITY ===\n\n";

// Test PHPMailer service
try {
    
    $phpMailer = new \App\Services\PHPMailerService();
    $testEmail = env('MAIL_USERNAME'); // Send test to same email
    
    if (!$testEmail) {
        echo "❌ Cannot test: MAIL_USERNAME not configured\n";
        exit(1);
    }
    
    echo "Sending test email to: {$testEmail}\n";
    $result = $phpMailer->sendTestEmail($testEmail, 'HR3 System Email Test', 'This is a test email to verify email configuration.');
    
    if ($result['success']) {
        echo "✅ Email sent successfully!\n";
        echo "Message: " . $result['message'] . "\n";
    } else {
        echo "❌ Email failed to send!\n";
        echo "Error: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ PHPMailer Error: " . $e->getMessage() . "\n";
}

echo "\n=== TESTING OTP EMAIL SPECIFICALLY ===\n\n";

try {
    $phpMailer = new \App\Services\PHPMailerService();
    $testEmail = env('MAIL_USERNAME');
    $testOtp = '123456';
    $testUser = 'Test User';
    
    echo "Sending OTP test email to: {$testEmail}\n";
    $result = $phpMailer->sendOtpEmail($testEmail, $testOtp, $testUser);
    
    if ($result['success']) {
        echo "✅ OTP Email sent successfully!\n";
        echo "Message: " . $result['message'] . "\n";
    } else {
        echo "❌ OTP Email failed to send!\n";
        echo "Error: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ OTP Email Error: " . $e->getMessage() . "\n";
}

echo "\n=== RECOMMENDATIONS ===\n\n";

if (!env('MAIL_USERNAME') || !env('MAIL_PASSWORD')) {
    echo "❌ Missing email credentials in .env file\n";
    echo "   Please set MAIL_USERNAME and MAIL_PASSWORD\n\n";
}

if (!env('MAIL_HOST')) {
    echo "❌ Missing MAIL_HOST in .env file\n";
    echo "   Please set MAIL_HOST (e.g., smtp.gmail.com)\n\n";
}

echo "✅ Test completed. Check your email inbox for test messages.\n";
echo "✅ If emails are not received, check spam/junk folder.\n";
echo "✅ For Gmail, ensure you're using an App Password, not your regular password.\n";

?>
