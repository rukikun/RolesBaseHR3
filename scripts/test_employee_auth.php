<?php
/**
 * Test Employee Authentication with Laravel
 * This script tests authentication step by step using Laravel components
 */

// Load Laravel
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== EMPLOYEE AUTHENTICATION TEST ===\n\n";

try {
    // Test 1: Check if Employee model works
    echo "1. Testing Employee Model...\n";
    $employeeCount = App\Models\Employee::count();
    echo "   ✅ Employee model works - Found $employeeCount employees\n";
    
    // Test 2: Find specific employee
    echo "\n2. Finding test employee...\n";
    $testEmail = 'john.doe@jetlouge.com';
    $employee = App\Models\Employee::where('email', $testEmail)->first();
    
    if (!$employee) {
        echo "   ❌ Employee not found: $testEmail\n";
        echo "   Creating employee...\n";
        
        $employee = App\Models\Employee::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $testEmail,
            'position' => 'Software Developer',
            'department' => 'IT',
            'hire_date' => '2024-01-15',
            'salary' => 55000.00,
            'status' => 'active',
            'password' => Hash::make('password123')
        ]);
        
        echo "   ✅ Employee created with ID: {$employee->id}\n";
    } else {
        echo "   ✅ Employee found: {$employee->first_name} {$employee->last_name} (ID: {$employee->id})\n";
    }
    
    // Test 3: Check password hash
    echo "\n3. Testing password hash...\n";
    $testPassword = 'password123';
    
    if (Hash::check($testPassword, $employee->password)) {
        echo "   ✅ Password hash verification works\n";
    } else {
        echo "   ❌ Password hash verification failed\n";
        echo "   Updating password hash...\n";
        
        $employee->password = Hash::make($testPassword);
        $employee->save();
        
        echo "   ✅ Password hash updated\n";
    }
    
    // Test 4: Test authentication guard
    echo "\n4. Testing authentication guard...\n";
    
    $credentials = ['email' => $testEmail, 'password' => $testPassword];
    
    if (Auth::guard('employee')->attempt($credentials)) {
        echo "   ✅ Authentication successful!\n";
        
        $authenticatedEmployee = Auth::guard('employee')->user();
        echo "   Authenticated as: {$authenticatedEmployee->first_name} {$authenticatedEmployee->last_name}\n";
        
        // Logout to clean up
        Auth::guard('employee')->logout();
        echo "   ✅ Logged out successfully\n";
    } else {
        echo "   ❌ Authentication failed\n";
        
        // Debug authentication failure
        echo "\n   DEBUGGING AUTHENTICATION FAILURE:\n";
        
        // Check if employee exists with email
        $debugEmployee = App\Models\Employee::where('email', $testEmail)->first();
        if ($debugEmployee) {
            echo "   - Employee exists in database\n";
            echo "   - Employee status: {$debugEmployee->status}\n";
            echo "   - Password length: " . strlen($debugEmployee->password) . "\n";
            
            // Test direct password check
            if (Hash::check($testPassword, $debugEmployee->password)) {
                echo "   - Direct password check: ✅ WORKS\n";
                echo "   - Issue is with guard configuration or model methods\n";
            } else {
                echo "   - Direct password check: ❌ FAILS\n";
                echo "   - Issue is with password hash\n";
            }
        } else {
            echo "   - Employee does not exist in database\n";
        }
    }
    
    // Test 5: Check authentication methods
    echo "\n5. Testing authentication methods...\n";
    
    $methods = [
        'getAuthIdentifierName',
        'getAuthIdentifier', 
        'getAuthPassword',
        'getRememberToken',
        'getRememberTokenName'
    ];
    
    foreach ($methods as $method) {
        if (method_exists($employee, $method)) {
            echo "   ✅ Method exists: $method\n";
        } else {
            echo "   ❌ Method missing: $method\n";
        }
    }
    
    // Test 6: Manual authentication test
    echo "\n6. Manual authentication test...\n";
    
    $provider = Auth::guard('employee')->getProvider();
    $user = $provider->retrieveByCredentials($credentials);
    
    if ($user) {
        echo "   ✅ User retrieved by credentials\n";
        
        if ($provider->validateCredentials($user, $credentials)) {
            echo "   ✅ Credentials validation successful\n";
            echo "   🎉 AUTHENTICATION SHOULD WORK!\n";
        } else {
            echo "   ❌ Credentials validation failed\n";
        }
    } else {
        echo "   ❌ User not retrieved by credentials\n";
    }
    
    echo "\n=== FINAL RESULT ===\n";
    echo "Test login at: http://127.0.0.1:8000/employee/login\n";
    echo "Email: $testEmail\n";
    echo "Password: $testPassword\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
