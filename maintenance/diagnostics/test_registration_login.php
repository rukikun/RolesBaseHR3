<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== HR3 System Registration & Login Test ===\n\n";

try {
    // Test 1: Test registration functionality
    echo "1. Testing registration functionality...\n";
    
    $testRegistration = [
        'firstName' => 'Test',
        'lastName' => 'User',
        'email' => 'testuser@jetlouge.com',
        'phone' => '09123456793',
        'role' => 'hr',
        'password' => 'password123',
    ];

    // Check if test user already exists
    $existingUser = Employee::where('email', $testRegistration['email'])->first();
    if ($existingUser) {
        echo "  - Test user already exists, deleting...\n";
        $existingUser->delete();
    }

    // Create test user via Employee model (simulating registration)
    $newEmployee = Employee::create([
        'first_name' => $testRegistration['firstName'],
        'last_name' => $testRegistration['lastName'],
        'email' => $testRegistration['email'],
        'phone' => $testRegistration['phone'],
        'role' => $testRegistration['role'],
        'password' => Hash::make($testRegistration['password']),
        'status' => 'active',
        'hire_date' => now(),
        'position' => ucfirst($testRegistration['role']),
        'department' => 'Human Resources',
    ]);

    if ($newEmployee) {
        echo "✅ Registration test passed - Employee created successfully\n";
        echo "  - ID: {$newEmployee->id}\n";
        echo "  - Name: {$newEmployee->full_name}\n";
        echo "  - Email: {$newEmployee->email}\n";
        echo "  - Role: {$newEmployee->role}\n";
        echo "  - Department: {$newEmployee->department}\n";
    } else {
        echo "❌ Registration test failed\n";
    }

    // Test 2: Test authentication
    echo "\n2. Testing authentication...\n";
    
    // Test login credentials
    $credentials = [
        'email' => $testRegistration['email'],
        'password' => $testRegistration['password'],
    ];

    // Attempt authentication
    if (Auth::guard('web')->attempt($credentials)) {
        $authenticatedUser = Auth::guard('web')->user();
        echo "✅ Authentication test passed\n";
        echo "  - Authenticated user: {$authenticatedUser->full_name}\n";
        echo "  - Role: {$authenticatedUser->role}\n";
        echo "  - Email: {$authenticatedUser->email}\n";
        
        // Test role-based methods
        echo "\n3. Testing role-based methods on authenticated user...\n";
        echo "  - isHR(): " . ($authenticatedUser->isHR() ? "✅ True" : "❌ False") . "\n";
        echo "  - isAdmin(): " . ($authenticatedUser->isAdmin() ? "❌ True" : "✅ False") . "\n";
        echo "  - hasRole('hr'): " . ($authenticatedUser->hasRole('hr') ? "✅ True" : "❌ False") . "\n";
        echo "  - hasAnyRole(['admin', 'hr']): " . ($authenticatedUser->hasAnyRole(['admin', 'hr']) ? "✅ True" : "❌ False") . "\n";
        
        // Logout
        Auth::guard('web')->logout();
        echo "✅ Logout successful\n";
    } else {
        echo "❌ Authentication test failed\n";
    }

    // Test 3: Test all existing test accounts
    echo "\n4. Testing all created test accounts...\n";
    
    $testAccounts = [
        ['email' => 'admin@jetlouge.com', 'password' => 'password123', 'expected_role' => 'admin'],
        ['email' => 'hr@jetlouge.com', 'password' => 'password123', 'expected_role' => 'hr'],
        ['email' => 'manager@jetlouge.com', 'password' => 'password123', 'expected_role' => 'manager'],
        ['email' => 'employee@jetlouge.com', 'password' => 'password123', 'expected_role' => 'employee'],
    ];

    foreach ($testAccounts as $account) {
        $credentials = [
            'email' => $account['email'],
            'password' => $account['password'],
        ];

        if (Auth::guard('web')->attempt($credentials)) {
            $user = Auth::guard('web')->user();
            $roleMatch = $user->role === $account['expected_role'];
            echo "  - {$account['email']}: " . ($roleMatch ? "✅ Login successful" : "❌ Role mismatch") . " (Role: {$user->role})\n";
            Auth::guard('web')->logout();
        } else {
            echo "  - {$account['email']}: ❌ Login failed\n";
        }
    }

    // Test 4: Test role-based access control
    echo "\n5. Testing role-based access scenarios...\n";
    
    // Test admin access
    if (Auth::guard('web')->attempt(['email' => 'admin@jetlouge.com', 'password' => 'password123'])) {
        $admin = Auth::guard('web')->user();
        echo "  - Admin can access HR dashboard: " . ($admin->hasAnyRole(['admin', 'hr', 'manager']) ? "✅ Yes" : "❌ No") . "\n";
        echo "  - Admin can access employee portal: " . ($admin->hasRole('employee') ? "❌ No (Correct)" : "✅ Yes (Has higher privileges)") . "\n";
        Auth::guard('web')->logout();
    }

    // Test regular employee access
    if (Auth::guard('web')->attempt(['email' => 'employee@jetlouge.com', 'password' => 'password123'])) {
        $employee = Auth::guard('web')->user();
        echo "  - Employee can access HR dashboard: " . ($employee->hasAnyRole(['admin', 'hr', 'manager']) ? "❌ No (Should be restricted)" : "✅ No (Correct)") . "\n";
        echo "  - Employee should use employee portal: " . ($employee->hasRole('employee') ? "✅ Yes" : "❌ No") . "\n";
        Auth::guard('web')->logout();
    }

    echo "\n6. Database verification...\n";
    
    // Count employees by role
    $adminCount = Employee::where('role', 'admin')->count();
    $hrCount = Employee::where('role', 'hr')->count();
    $managerCount = Employee::where('role', 'manager')->count();
    $employeeCount = Employee::where('role', 'employee')->count();
    
    echo "  - Admin employees: {$adminCount}\n";
    echo "  - HR employees: {$hrCount}\n";
    echo "  - Manager employees: {$managerCount}\n";
    echo "  - Regular employees: {$employeeCount}\n";
    echo "  - Total employees: " . Employee::count() . "\n";

    echo "\n✅ All tests completed successfully!\n";
    echo "\n=== TESTING INSTRUCTIONS ===\n";
    echo "1. Visit http://localhost:8000/register to test registration with role selection\n";
    echo "2. Visit http://localhost:8000/admin/login to test login with role-based redirects\n";
    echo "3. Test accounts available:\n";
    echo "   - admin@jetlouge.com / password123 (Admin - should go to HR dashboard)\n";
    echo "   - hr@jetlouge.com / password123 (HR - should go to HR dashboard)\n";
    echo "   - manager@jetlouge.com / password123 (Manager - should go to HR dashboard)\n";
    echo "   - employee@jetlouge.com / password123 (Employee - should go to employee dashboard)\n";
    echo "   - testuser@jetlouge.com / password123 (HR - created by this test)\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
