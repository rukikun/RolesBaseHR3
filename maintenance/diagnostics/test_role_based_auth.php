<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== HR3 System Role-Based Authentication Test ===\n\n";

try {
    // Test 1: Check if role column exists
    echo "1. Checking if 'role' column exists in employees table...\n";
    $columns = \Schema::getColumnListing('employees');
    if (in_array('role', $columns)) {
        echo "✅ Role column exists in employees table\n\n";
    } else {
        echo "❌ Role column does not exist in employees table\n\n";
        exit(1);
    }

    // Test 2: Create test employees with different roles
    echo "2. Creating test employees with different roles...\n";
    
    $testEmployees = [
        [
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@jetlouge.com',
            'phone' => '09123456789',
            'role' => 'admin',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'hire_date' => now(),
            'position' => 'System Administrator',
            'department' => 'Administration',
        ],
        [
            'first_name' => 'HR',
            'last_name' => 'Manager',
            'email' => 'hr@jetlouge.com',
            'phone' => '09123456790',
            'role' => 'hr',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'hire_date' => now(),
            'position' => 'HR Manager',
            'department' => 'Human Resources',
        ],
        [
            'first_name' => 'Department',
            'last_name' => 'Manager',
            'email' => 'manager@jetlouge.com',
            'phone' => '09123456791',
            'role' => 'manager',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'hire_date' => now(),
            'position' => 'Department Manager',
            'department' => 'Management',
        ],
        [
            'first_name' => 'Regular',
            'last_name' => 'Employee',
            'email' => 'employee@jetlouge.com',
            'phone' => '09123456792',
            'role' => 'employee',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'hire_date' => now(),
            'position' => 'Staff',
            'department' => 'General',
        ],
    ];

    foreach ($testEmployees as $employeeData) {
        // Check if employee already exists
        $existingEmployee = Employee::where('email', $employeeData['email'])->first();
        if ($existingEmployee) {
            // Update existing employee with role
            $existingEmployee->update(['role' => $employeeData['role']]);
            echo "✅ Updated existing employee: {$employeeData['email']} (Role: {$employeeData['role']})\n";
        } else {
            // Create new employee
            $employee = Employee::create($employeeData);
            echo "✅ Created new employee: {$employeeData['email']} (Role: {$employeeData['role']})\n";
        }
    }

    echo "\n3. Testing role-based methods...\n";
    
    // Test role methods
    $adminEmployee = Employee::where('email', 'admin@jetlouge.com')->first();
    $hrEmployee = Employee::where('email', 'hr@jetlouge.com')->first();
    $managerEmployee = Employee::where('email', 'manager@jetlouge.com')->first();
    $regularEmployee = Employee::where('email', 'employee@jetlouge.com')->first();

    if ($adminEmployee) {
        echo "Admin Employee Tests:\n";
        echo "  - isAdmin(): " . ($adminEmployee->isAdmin() ? "✅ True" : "❌ False") . "\n";
        echo "  - isHR(): " . ($adminEmployee->isHR() ? "❌ True" : "✅ False") . "\n";
        echo "  - hasRole('admin'): " . ($adminEmployee->hasRole('admin') ? "✅ True" : "❌ False") . "\n";
        echo "  - hasAnyRole(['admin', 'hr']): " . ($adminEmployee->hasAnyRole(['admin', 'hr']) ? "✅ True" : "❌ False") . "\n";
    }

    if ($hrEmployee) {
        echo "\nHR Employee Tests:\n";
        echo "  - isHR(): " . ($hrEmployee->isHR() ? "✅ True" : "❌ False") . "\n";
        echo "  - isAdmin(): " . ($hrEmployee->isAdmin() ? "❌ True" : "✅ False") . "\n";
        echo "  - hasRole('hr'): " . ($hrEmployee->hasRole('hr') ? "✅ True" : "❌ False") . "\n";
        echo "  - hasAnyRole(['admin', 'hr']): " . ($hrEmployee->hasAnyRole(['admin', 'hr']) ? "✅ True" : "❌ False") . "\n";
    }

    if ($regularEmployee) {
        echo "\nRegular Employee Tests:\n";
        echo "  - isEmployee(): " . ($regularEmployee->isEmployee() ? "✅ True" : "❌ False") . "\n";
        echo "  - isAdmin(): " . ($regularEmployee->isAdmin() ? "❌ True" : "✅ False") . "\n";
        echo "  - hasRole('employee'): " . ($regularEmployee->hasRole('employee') ? "✅ True" : "❌ False") . "\n";
        echo "  - hasAnyRole(['admin', 'hr']): " . ($regularEmployee->hasAnyRole(['admin', 'hr']) ? "❌ True" : "✅ False") . "\n";
    }

    echo "\n4. Testing authentication configuration...\n";
    
    // Check auth configuration
    $defaultGuard = config('auth.defaults.guard');
    $webGuardProvider = config('auth.guards.web.provider');
    $employeeProvider = config('auth.providers.employees.model');
    
    echo "  - Default guard: {$defaultGuard}\n";
    echo "  - Web guard provider: {$webGuardProvider}\n";
    echo "  - Employee provider model: {$employeeProvider}\n";
    
    if ($webGuardProvider === 'employees' && $employeeProvider === 'App\Models\Employee') {
        echo "✅ Authentication configuration is correct\n";
    } else {
        echo "❌ Authentication configuration needs adjustment\n";
    }

    echo "\n5. Summary of created test accounts:\n";
    echo "  - admin@jetlouge.com / password123 (Admin)\n";
    echo "  - hr@jetlouge.com / password123 (HR)\n";
    echo "  - manager@jetlouge.com / password123 (Manager)\n";
    echo "  - employee@jetlouge.com / password123 (Employee)\n";

    echo "\n✅ Role-based authentication system test completed successfully!\n";
    echo "\nYou can now:\n";
    echo "1. Visit http://localhost:8000/register to register new employees with roles\n";
    echo "2. Visit http://localhost:8000/admin/login to login with any of the test accounts\n";
    echo "3. Test role-based access control throughout the system\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
