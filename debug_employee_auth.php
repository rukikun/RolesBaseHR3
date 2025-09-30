<?php
// Comprehensive Employee Authentication Debug Script

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "🔍 COMPREHENSIVE EMPLOYEE AUTHENTICATION DEBUG\n";
echo "==============================================\n\n";

try {
    // 1. Test database connection and employees
    echo "1. Testing Database Connection & Employee Model...\n";
    
    $employees = Employee::all();
    echo "   ✅ Employee model works - Found {$employees->count()} employees\n";
    
    // 2. Test specific employee lookup
    echo "\n2. Testing Employee Lookup...\n";
    $testEmail = 'john.doe@jetlouge.com';
    
    // Try with Eloquent
    $employee = Employee::where('email', $testEmail)->first();
    if ($employee) {
        echo "   ✅ Found employee via Eloquent: {$employee->first_name} {$employee->last_name}\n";
        echo "   📧 Email: {$employee->email}\n";
        echo "   🔑 Password hash length: " . strlen($employee->password) . "\n";
        echo "   📊 Status: {$employee->status}\n";
    } else {
        echo "   ❌ Employee not found via Eloquent\n";
    }
    
    // Try with DB facade
    $employeeDB = DB::table('employees')->where('email', $testEmail)->first();
    if ($employeeDB) {
        echo "   ✅ Found employee via DB facade: {$employeeDB->first_name} {$employeeDB->last_name}\n";
    } else {
        echo "   ❌ Employee not found via DB facade\n";
    }
    
    // 3. Test password verification
    echo "\n3. Testing Password Verification...\n";
    $testPassword = 'password123';
    
    if ($employee) {
        // Test with Hash::check
        if (Hash::check($testPassword, $employee->password)) {
            echo "   ✅ Hash::check() works - Password matches\n";
        } else {
            echo "   ❌ Hash::check() failed - Password doesn't match\n";
            echo "   🔍 Testing with different password formats...\n";
            
            // Test if password is plain text
            if ($employee->password === $testPassword) {
                echo "   ⚠️  Password is stored as plain text!\n";
                
                // Fix by hashing it
                $hashedPassword = Hash::make($testPassword);
                Employee::where('id', $employee->id)->update(['password' => $hashedPassword]);
                echo "   ✅ Updated password to hashed version\n";
            }
        }
        
        // Test with password_verify (PHP native)
        if (password_verify($testPassword, $employee->password)) {
            echo "   ✅ password_verify() works - Password matches\n";
        } else {
            echo "   ❌ password_verify() failed - Password doesn't match\n";
        }
    }
    
    // 4. Test Auth Guard Configuration
    echo "\n4. Testing Auth Guard Configuration...\n";
    
    $guardConfig = config('auth.guards.employee');
    $providerConfig = config('auth.providers.employees');
    
    echo "   📋 Employee Guard Config:\n";
    echo "      Driver: {$guardConfig['driver']}\n";
    echo "      Provider: {$guardConfig['provider']}\n";
    
    echo "   📋 Employee Provider Config:\n";
    echo "      Driver: {$providerConfig['driver']}\n";
    echo "      Model: {$providerConfig['model']}\n";
    
    // 5. Test Authentication Attempt
    echo "\n5. Testing Authentication Attempt...\n";
    
    $credentials = [
        'email' => $testEmail,
        'password' => $testPassword
    ];
    
    // Test with employee guard
    if (Auth::guard('employee')->attempt($credentials)) {
        echo "   ✅ Auth::guard('employee')->attempt() SUCCESS!\n";
        $authUser = Auth::guard('employee')->user();
        echo "   👤 Authenticated user: {$authUser->first_name} {$authUser->last_name}\n";
        
        // Logout to clean up
        Auth::guard('employee')->logout();
    } else {
        echo "   ❌ Auth::guard('employee')->attempt() FAILED\n";
        
        // Try to debug why
        echo "   🔍 Debugging authentication failure...\n";
        
        // Check if user exists
        $user = Auth::guard('employee')->getProvider()->retrieveByCredentials($credentials);
        if ($user) {
            echo "   ✅ User found by credentials\n";
            
            // Check password validation
            if (Auth::guard('employee')->getProvider()->validateCredentials($user, $credentials)) {
                echo "   ✅ Password validation passed\n";
            } else {
                echo "   ❌ Password validation failed\n";
            }
        } else {
            echo "   ❌ User not found by credentials\n";
        }
    }
    
    // 6. List all employees with their authentication status
    echo "\n6. All Employees Authentication Test...\n";
    
    foreach ($employees as $emp) {
        echo "   👤 {$emp->first_name} {$emp->last_name} ({$emp->email})\n";
        
        $testCreds = ['email' => $emp->email, 'password' => $testPassword];
        
        if (Hash::check($testPassword, $emp->password)) {
            echo "      ✅ Password hash valid\n";
        } else {
            echo "      ❌ Password hash invalid\n";
            
            // Try to fix it
            $newHash = Hash::make($testPassword);
            Employee::where('id', $emp->id)->update(['password' => $newHash]);
            echo "      🔧 Updated password hash\n";
        }
        
        if (Auth::guard('employee')->attempt($testCreds)) {
            echo "      ✅ Authentication works\n";
            Auth::guard('employee')->logout();
        } else {
            echo "      ❌ Authentication fails\n";
        }
        echo "\n";
    }
    
    // 7. Create fresh test employee if needed
    echo "7. Creating Fresh Test Employee...\n";
    
    $freshEmail = 'test.employee@jetlouge.com';
    
    // Delete if exists
    Employee::where('email', $freshEmail)->delete();
    
    // Create new
    $freshEmployee = Employee::create([
        'first_name' => 'Test',
        'last_name' => 'Employee',
        'email' => $freshEmail,
        'phone' => '+63 999 999 9999',
        'position' => 'Test Position',
        'department' => 'Testing',
        'hire_date' => now()->toDateString(),
        'salary' => 50000.00,
        'status' => 'active',
        'password' => Hash::make($testPassword)
    ]);
    
    echo "   ✅ Created fresh test employee: {$freshEmployee->email}\n";
    
    // Test authentication with fresh employee
    $freshCreds = ['email' => $freshEmail, 'password' => $testPassword];
    
    if (Auth::guard('employee')->attempt($freshCreds)) {
        echo "   ✅ Fresh employee authentication SUCCESS!\n";
        Auth::guard('employee')->logout();
    } else {
        echo "   ❌ Fresh employee authentication FAILED\n";
    }
    
    echo "\n🎯 SUMMARY & RECOMMENDATIONS:\n";
    echo "=============================\n";
    echo "Use these credentials for testing:\n";
    echo "Email: {$freshEmail}\n";
    echo "Password: {$testPassword}\n\n";
    
    echo "If authentication still fails, check:\n";
    echo "1. Session configuration in config/session.php\n";
    echo "2. APP_KEY is set in .env file\n";
    echo "3. Clear browser cookies completely\n";
    echo "4. Check Laravel logs in storage/logs/\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
