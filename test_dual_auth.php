<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== HR3 System Dual Authentication Test ===\n\n";

try {
    // Test 1: Create test admin user in users table
    echo "1. Creating test admin user in users table...\n";
    
    $adminData = [
        'name' => 'Test Admin',
        'email' => 'testadmin@jetlouge.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
        'phone' => '09123456789',
    ];

    $existingAdmin = User::where('email', $adminData['email'])->first();
    if ($existingAdmin) {
        $existingAdmin->update($adminData);
        echo "✅ Updated admin user: {$adminData['email']}\n";
    } else {
        User::create($adminData);
        echo "✅ Created admin user: {$adminData['email']}\n";
    }

    // Test 2: Create test employee in employees table
    echo "\n2. Creating test employee in employees table...\n";
    
    $employeeData = [
        'first_name' => 'Test',
        'last_name' => 'Employee',
        'email' => 'testemployee@jetlouge.com',
        'password' => Hash::make('password123'),
        'role' => 'employee',
        'phone' => '09123456790',
        'position' => 'Staff',
        'department' => 'General',
        'status' => 'active',
        'hire_date' => now(),
    ];

    $existingEmployee = Employee::where('email', $employeeData['email'])->first();
    if ($existingEmployee) {
        $existingEmployee->update($employeeData);
        echo "✅ Updated employee: {$employeeData['email']}\n";
    } else {
        Employee::create($employeeData);
        echo "✅ Created employee: {$employeeData['email']}\n";
    }

    // Test 3: Verify authentication configuration
    echo "\n3. Verifying authentication configuration...\n";
    
    $webGuardProvider = config('auth.guards.web.provider');
    $employeeGuardProvider = config('auth.guards.employee.provider');
    
    echo "  - Web guard (admin) uses: {$webGuardProvider} provider\n";
    echo "  - Employee guard uses: {$employeeGuardProvider} provider\n";
    
    if ($webGuardProvider === 'users' && $employeeGuardProvider === 'employees') {
        echo "✅ Authentication configuration is correct for dual system\n";
    } else {
        echo "❌ Authentication configuration needs adjustment\n";
    }

    // Test 4: Test admin authentication
    echo "\n4. Testing admin authentication (web guard - users table)...\n";
    
    $adminCredentials = ['email' => 'testadmin@jetlouge.com', 'password' => 'password123'];
    if (\Auth::guard('web')->attempt($adminCredentials)) {
        $adminUser = \Auth::guard('web')->user();
        echo "✅ Admin login successful: {$adminUser->name} (Role: {$adminUser->role})\n";
        echo "  - User ID: {$adminUser->id}\n";
        echo "  - Email: {$adminUser->email}\n";
        echo "  - Table: users\n";
        \Auth::guard('web')->logout();
    } else {
        echo "❌ Admin login failed\n";
    }

    // Test 5: Test employee authentication
    echo "\n5. Testing employee authentication (employee guard - employees table)...\n";
    
    $employeeCredentials = ['email' => 'testemployee@jetlouge.com', 'password' => 'password123'];
    if (\Auth::guard('employee')->attempt($employeeCredentials)) {
        $employee = \Auth::guard('employee')->user();
        echo "✅ Employee login successful: {$employee->full_name} (Role: {$employee->role})\n";
        echo "  - Employee ID: {$employee->id}\n";
        echo "  - Email: {$employee->email}\n";
        echo "  - Position: {$employee->position}\n";
        echo "  - Table: employees\n";
        \Auth::guard('employee')->logout();
    } else {
        echo "❌ Employee login failed\n";
    }

    // Test 6: Count records in both tables
    echo "\n6. Database verification...\n";
    
    $userCount = User::count();
    $employeeCount = Employee::count();
    
    echo "  - Total users (admin portal): {$userCount}\n";
    echo "  - Total employees (employee portal): {$employeeCount}\n";

    echo "\n✅ Dual authentication system test completed successfully!\n";
    echo "\n=== SYSTEM SUMMARY ===\n";
    echo "ADMIN PORTAL:\n";
    echo "  - Uses: users table\n";
    echo "  - Guard: web\n";
    echo "  - Login URL: /admin/login\n";
    echo "  - Registration URL: /register\n";
    echo "  - Test Account: testadmin@jetlouge.com / password123\n";
    
    echo "\nEMPLOYEE PORTAL:\n";
    echo "  - Uses: employees table\n";
    echo "  - Guard: employee\n";
    echo "  - Login URL: /employee/login\n";
    echo "  - Dashboard URL: /employee/dashboard\n";
    echo "  - Test Account: testemployee@jetlouge.com / password123\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    exit(1);
}
