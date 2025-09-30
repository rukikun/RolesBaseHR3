<?php
/**
 * Fix Laravel Authentication Issues
 * This script identifies and fixes Laravel-specific authentication problems
 */

echo "=== LARAVEL AUTHENTICATION ANALYSIS ===\n\n";

// Check if we're in Laravel environment
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "❌ Laravel vendor directory not found\n";
    echo "Run this from Laravel project root or install dependencies\n";
    exit(1);
}

// Load Laravel
require_once __DIR__ . '/../vendor/autoload.php';

try {
    // Bootstrap Laravel application
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "✅ Laravel application loaded\n";
    
    // Test database connection
    try {
        $pdo = DB::connection()->getPdo();
        echo "✅ Database connection successful\n";
        echo "Database: " . DB::connection()->getDatabaseName() . "\n";
    } catch (Exception $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // Test Employee model
    try {
        $employeeCount = App\Models\Employee::count();
        echo "✅ Employee model works - Found $employeeCount employees\n";
    } catch (Exception $e) {
        echo "❌ Employee model error: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // Test specific employee
    $testEmail = 'john.doe@jetlouge.com';
    $employee = App\Models\Employee::where('email', $testEmail)->first();
    
    if (!$employee) {
        echo "❌ Test employee not found: $testEmail\n";
        
        // Show available employees
        $employees = App\Models\Employee::where('status', 'active')->limit(5)->get();
        echo "Available employees:\n";
        foreach ($employees as $emp) {
            echo "- {$emp->email}\n";
        }
        exit(1);
    }
    
    echo "✅ Test employee found: {$employee->first_name} {$employee->last_name}\n";
    
    // Test password verification
    $testPassword = 'password123';
    if (Hash::check($testPassword, $employee->password)) {
        echo "✅ Password verification works with Laravel Hash::check()\n";
    } else {
        echo "❌ Password verification fails with Laravel Hash::check()\n";
        echo "Fixing password hash...\n";
        
        $employee->password = Hash::make($testPassword);
        $employee->save();
        
        echo "✅ Password hash updated using Laravel Hash::make()\n";
    }
    
    // Test authentication guard
    try {
        $guard = Auth::guard('employee');
        echo "✅ Employee guard configured\n";
        
        // Test manual authentication
        $credentials = ['email' => $testEmail, 'password' => $testPassword];
        
        if ($guard->attempt($credentials)) {
            echo "✅ Authentication attempt successful!\n";
            $guard->logout(); // Clean up
        } else {
            echo "❌ Authentication attempt failed\n";
            
            // Debug the attempt
            $employee = App\Models\Employee::where('email', $testEmail)->first();
            if ($employee) {
                echo "Employee found but credentials don't match\n";
                echo "Email: {$employee->email}\n";
                echo "Password hash length: " . strlen($employee->password) . "\n";
                
                // Try direct password check
                if (Hash::check($testPassword, $employee->password)) {
                    echo "Direct Hash::check() works - issue might be with guard configuration\n";
                } else {
                    echo "Direct Hash::check() fails - password hash issue\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "❌ Guard error: " . $e->getMessage() . "\n";
    }
    
    // Final recommendations
    echo "\n=== RECOMMENDATIONS ===\n";
    echo "1. Clear Laravel cache: php artisan config:clear\n";
    echo "2. Check .env DB_DATABASE matches your database\n";
    echo "3. Verify Employee model connection setting\n";
    echo "4. Test login at: http://localhost/employee/login\n";
    
} catch (Exception $e) {
    echo "❌ Laravel error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
