<?php

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== COMPREHENSIVE 2FA DIAGNOSTIC TEST ===\n";
echo "Time: " . now() . "\n\n";

$testEmail = 'johnkaizer19.jh@gmail.com';
$testPassword = 'password123';

// Step 1: Database Connection Test
echo "=== STEP 1: DATABASE CONNECTION ===\n";
try {
    $connection = \Illuminate\Support\Facades\DB::connection();
    $dbName = $connection->getDatabaseName();
    echo "✅ Database connected: {$dbName}\n";
    
    // Check if otp_verifications table exists
    $tables = \Illuminate\Support\Facades\DB::select("SHOW TABLES LIKE 'otp_verifications'");
    if (count($tables) > 0) {
        echo "✅ otp_verifications table exists\n";
        
        // Check table structure
        $columns = \Illuminate\Support\Facades\DB::select("DESCRIBE otp_verifications");
        echo "Table columns: " . implode(', ', array_column($columns, 'Field')) . "\n";
    } else {
        echo "❌ otp_verifications table missing\n";
        echo "Run: php artisan migrate\n";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

// Step 2: Employee Check
echo "\n=== STEP 2: EMPLOYEE VERIFICATION ===\n";
try {
    $employee = \App\Models\Employee::where('email', $testEmail)->first();
    if ($employee) {
        echo "✅ Employee found: {$employee->first_name} {$employee->last_name}\n";
        echo "  Position: {$employee->position}\n";
        echo "  Role: {$employee->role}\n";
        echo "  Status: {$employee->status}\n";
        echo "  Can Access Dashboard: " . ($employee->canAccessDashboard() ? 'YES' : 'NO') . "\n";
        
        // Test password
        if (\Illuminate\Support\Facades\Hash::check($testPassword, $employee->password)) {
            echo "✅ Password verification successful\n";
        } else {
            echo "❌ Password verification failed\n";
        }
    } else {
        echo "❌ Employee not found\n";
    }
} catch (Exception $e) {
    echo "❌ Employee check error: " . $e->getMessage() . "\n";
}

// Step 3: Environment Configuration
echo "\n=== STEP 3: EMAIL CONFIGURATION ===\n";
$requiredEnvVars = [
    'MAIL_HOST' => env('MAIL_HOST'),
    'MAIL_PORT' => env('MAIL_PORT'),
    'MAIL_USERNAME' => env('MAIL_USERNAME'),
    'MAIL_PASSWORD' => env('MAIL_PASSWORD'),
    'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
    'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
    'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
];

$missingConfig = [];
foreach ($requiredEnvVars as $key => $value) {
    if ($value) {
        $displayValue = ($key === 'MAIL_PASSWORD') ? '***CONFIGURED***' : $value;
        echo "✅ {$key}: {$displayValue}\n";
    } else {
        echo "❌ {$key}: NOT SET\n";
        $missingConfig[] = $key;
    }
}

if (!empty($missingConfig)) {
    echo "\n⚠️  Missing configuration variables:\n";
    foreach ($missingConfig as $var) {
        echo "  - {$var}\n";
    }
}

// Step 4: OTP Generation Test
echo "\n=== STEP 4: OTP GENERATION TEST ===\n";
try {
    // Clean up any existing OTPs first
    \App\Models\OtpVerification::where('email', $testEmail)->delete();
    
    $otpRecord = \App\Models\OtpVerification::generateOtp($testEmail);
    echo "✅ OTP generated successfully\n";
    echo "  Email: {$otpRecord->email}\n";
    echo "  OTP Code: {$otpRecord->otp_code}\n";
    echo "  Expires At: {$otpRecord->expires_at}\n";
    echo "  Max Attempts: {$otpRecord->max_attempts}\n";
    
    // Verify the OTP was saved to database
    $savedOtp = \App\Models\OtpVerification::where('email', $testEmail)->where('is_used', false)->first();
    if ($savedOtp) {
        echo "✅ OTP saved to database successfully\n";
    } else {
        echo "❌ OTP not found in database\n";
    }
    
} catch (Exception $e) {
    echo "❌ OTP generation error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Step 5: PHPMailer Service Test
echo "\n=== STEP 5: PHPMAILER SERVICE TEST ===\n";
try {
    if (!isset($otpRecord)) {
        echo "❌ Cannot test email - OTP generation failed\n";
    } else {
        $phpMailer = new \App\Services\PHPMailerService();
        echo "✅ PHPMailer service instantiated\n";
        
        // Test basic email sending
        echo "Testing OTP email to: {$testEmail}\n";
        $result = $phpMailer->sendOtpEmail($testEmail, $otpRecord->otp_code, $employee->first_name . ' ' . $employee->last_name);
        
        if ($result['success']) {
            echo "✅ OTP email sent successfully!\n";
            echo "  Message: {$result['message']}\n";
        } else {
            echo "❌ OTP email failed to send\n";
            echo "  Error: {$result['message']}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ PHPMailer error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Step 6: Complete AuthController Flow Simulation
echo "\n=== STEP 6: AUTHCONTROLLER FLOW SIMULATION ===\n";
try {
    echo "Simulating complete login flow...\n";
    
    $credentials = ['email' => $testEmail, 'password' => $testPassword];
    
    // Step 6a: Credential verification
    if (\Illuminate\Support\Facades\Auth::guard('employee')->attempt($credentials)) {
        echo "✅ Credentials validated\n";
        
        $employee = \Illuminate\Support\Facades\Auth::guard('employee')->user();
        
        // Step 6b: Dashboard access check
        if (!$employee->canAccessDashboard()) {
            echo "❌ Employee cannot access dashboard\n";
        } else {
            echo "✅ Dashboard access confirmed\n";
            
            // Step 6c: Logout (as done in AuthController)
            \Illuminate\Support\Facades\Auth::guard('employee')->logout();
            echo "✅ User logged out after validation\n";
            
            // Step 6d: Generate OTP
            try {
                $otpRecord = \App\Models\OtpVerification::generateOtp($testEmail);
                echo "✅ OTP generated: {$otpRecord->otp_code}\n";
                
                // Step 6e: Send email
                $phpMailer = new \App\Services\PHPMailerService();
                $result = $phpMailer->sendOtpEmail($testEmail, $otpRecord->otp_code, $employee->first_name . ' ' . $employee->last_name);
                
                if ($result['success']) {
                    echo "✅ Complete flow successful - OTP email sent!\n";
                    echo "\n🎉 SUCCESS: The 2FA system is working correctly!\n";
                    echo "Check your email for OTP: {$otpRecord->otp_code}\n";
                } else {
                    echo "❌ Email sending failed in complete flow\n";
                    echo "Error: {$result['message']}\n";
                }
                
            } catch (Exception $e) {
                echo "❌ OTP generation failed in complete flow: " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "❌ Credential validation failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ AuthController flow error: " . $e->getMessage() . "\n";
}

// Step 7: Recommendations
echo "\n=== STEP 7: RECOMMENDATIONS ===\n";

if (!empty($missingConfig)) {
    echo "❌ Fix missing email configuration in .env file\n";
}

echo "✅ Test URLs available:\n";
echo "  - Main login: https://hr3.jetlougetravels-ph.com/admin/login\n";
echo "  - This test: https://hr3.jetlougetravels-ph.com/test-comprehensive-2fa\n";
echo "  - Email config: https://hr3.jetlougetravels-ph.com/test-email-config\n";
echo "  - Employee check: https://hr3.jetlougetravels-ph.com/check-employee-credentials\n";

echo "\n✅ If emails are not received:\n";
echo "  1. Check spam/junk folder\n";
echo "  2. Verify Gmail App Password is correct\n";
echo "  3. Check Gmail account security settings\n";
echo "  4. Try sending test email manually\n";

echo "\n=== TEST COMPLETED ===\n";

?>
