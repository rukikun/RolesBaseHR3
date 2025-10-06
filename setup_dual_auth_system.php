<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Employee;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== HR3 System Dual Authentication Setup ===\n\n";

try {
    // Test 1: Check and setup users table
    echo "1. Setting up users table for admin authentication...\n";
    
    // Check if users table exists
    if (!Schema::hasTable('users')) {
        echo "❌ Users table does not exist. Please run migrations first.\n";
        exit(1);
    }
    
    // Check if role column exists in users table
    if (!Schema::hasColumn('users', 'role')) {
        echo "  - Adding role column to users table...\n";
        Schema::table('users', function ($table) {
            $table->string('role')->default('admin')->after('email');
        });
        echo "✅ Role column added to users table\n";
    } else {
        echo "✅ Role column already exists in users table\n";
    }

    // Test 2: Create admin users
    echo "\n2. Creating admin users in users table...\n";
    
    $adminUsers = [
        [
            'name' => 'Super Admin',
            'email' => 'superadmin@jetlouge.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'phone' => '09123456789',
        ],
        [
            'name' => 'Admin User',
            'email' => 'admin@jetlouge.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '09123456790',
        ],
        [
            'name' => 'HR Manager',
            'email' => 'hrmanager@jetlouge.com',
            'password' => Hash::make('password123'),
            'role' => 'hr_manager',
            'phone' => '09123456791',
        ],
    ];

    foreach ($adminUsers as $userData) {
        $existingUser = User::where('email', $userData['email'])->first();
        if ($existingUser) {
            $existingUser->update($userData);
            echo "✅ Updated admin user: {$userData['email']} (Role: {$userData['role']})\n";
        } else {
            User::create($userData);
            echo "✅ Created admin user: {$userData['email']} (Role: {$userData['role']})\n";
        }
    }

    // Test 3: Setup employees table for employee portal
    echo "\n3. Setting up employees table for employee portal...\n";
    
    if (!Schema::hasTable('employees')) {
        echo "❌ Employees table does not exist. Please run migrations first.\n";
        exit(1);
    }
    
    // Check if role column exists in employees table
    if (!Schema::hasColumn('employees', 'role')) {
        echo "  - Adding role column to employees table...\n";
        Schema::table('employees', function ($table) {
            $table->enum('role', ['employee', 'supervisor', 'team_lead'])->default('employee')->after('status');
        });
        echo "✅ Role column added to employees table\n";
    } else {
        echo "✅ Role column already exists in employees table\n";
    }

    // Test 4: Create employee users
    echo "\n4. Creating employees in employees table...\n";
    
    $employees = [
        [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@jetlouge.com',
            'password' => Hash::make('password123'),
            'role' => 'employee',
            'phone' => '09123456792',
            'position' => 'Software Developer',
            'department' => 'IT',
            'status' => 'active',
            'hire_date' => now(),
        ],
        [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@jetlouge.com',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'phone' => '09123456793',
            'position' => 'Team Supervisor',
            'department' => 'Operations',
            'status' => 'active',
            'hire_date' => now(),
        ],
        [
            'first_name' => 'Mike',
            'last_name' => 'Johnson',
            'email' => 'mike.johnson@jetlouge.com',
            'password' => Hash::make('password123'),
            'role' => 'team_lead',
            'phone' => '09123456794',
            'position' => 'Team Lead',
            'department' => 'Sales',
            'status' => 'active',
            'hire_date' => now(),
        ],
    ];

    foreach ($employees as $employeeData) {
        $existingEmployee = Employee::where('email', $employeeData['email'])->first();
        if ($existingEmployee) {
            $existingEmployee->update($employeeData);
            echo "✅ Updated employee: {$employeeData['email']} (Role: {$employeeData['role']})\n";
        } else {
            Employee::create($employeeData);
            echo "✅ Created employee: {$employeeData['email']} (Role: {$employeeData['role']})\n";
        }
    }

    // Test 5: Verify authentication configuration
    echo "\n5. Verifying authentication configuration...\n";
    
    $defaultGuard = config('auth.defaults.guard');
    $webGuardProvider = config('auth.guards.web.provider');
    $employeeGuardProvider = config('auth.guards.employee.provider');
    $usersProvider = config('auth.providers.users.model');
    $employeesProvider = config('auth.providers.employees.model');
    
    echo "  - Default guard: {$defaultGuard}\n";
    echo "  - Web guard provider: {$webGuardProvider}\n";
    echo "  - Employee guard provider: {$employeeGuardProvider}\n";
    echo "  - Users provider model: {$usersProvider}\n";
    echo "  - Employees provider model: {$employeesProvider}\n";
    
    if ($webGuardProvider === 'users' && $employeeGuardProvider === 'employees') {
        echo "✅ Authentication configuration is correct\n";
    } else {
        echo "❌ Authentication configuration needs adjustment\n";
    }

    // Test 6: Test authentication for both systems
    echo "\n6. Testing authentication for both systems...\n";
    
    // Test admin authentication
    echo "  Testing admin authentication (users table):\n";
    $adminCredentials = ['email' => 'admin@jetlouge.com', 'password' => 'password123'];
    if (\Auth::guard('web')->attempt($adminCredentials)) {
        $adminUser = \Auth::guard('web')->user();
        echo "    ✅ Admin login successful: {$adminUser->name} (Role: {$adminUser->role})\n";
        \Auth::guard('web')->logout();
    } else {
        echo "    ❌ Admin login failed\n";
    }
    
    // Test employee authentication
    echo "  Testing employee authentication (employees table):\n";
    $employeeCredentials = ['email' => 'john.doe@jetlouge.com', 'password' => 'password123'];
    if (\Auth::guard('employee')->attempt($employeeCredentials)) {
        $employee = \Auth::guard('employee')->user();
        echo "    ✅ Employee login successful: {$employee->full_name} (Role: {$employee->role})\n";
        \Auth::guard('employee')->logout();
    } else {
        echo "    ❌ Employee login failed\n";
    }

    echo "\n7. Summary of created accounts:\n";
    echo "\n  ADMIN PORTAL (uses users table):\n";
    echo "  - superadmin@jetlouge.com / password123 (Super Admin)\n";
    echo "  - admin@jetlouge.com / password123 (Admin)\n";
    echo "  - hrmanager@jetlouge.com / password123 (HR Manager)\n";
    
    echo "\n  EMPLOYEE PORTAL (uses employees table):\n";
    echo "  - john.doe@jetlouge.com / password123 (Employee)\n";
    echo "  - jane.smith@jetlouge.com / password123 (Supervisor)\n";
    echo "  - mike.johnson@jetlouge.com / password123 (Team Lead)\n";

    echo "\n✅ Dual authentication system setup completed successfully!\n";
    echo "\nAccess URLs:\n";
    echo "- Admin Portal: http://localhost:8000/admin/login (uses users table)\n";
    echo "- Employee Portal: http://localhost:8000/employee/login (uses employees table)\n";
    echo "- Registration: http://localhost:8000/register (creates admin users in users table)\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
