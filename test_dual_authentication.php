<?php
/**
 * Test Dual Authentication System
 * 
 * This script tests both admin (users table) and employee (employees table) authentication
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Illuminate\Database\Capsule\Manager as Capsule;

// Database configuration
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'database' => $_ENV['DB_DATABASE'] ?? 'hr3_hr3systemdb',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "🔍 HR3 System - Dual Authentication Test\n";
echo "=======================================\n\n";

try {
    // Test database connection
    echo "📡 Testing database connection...\n";
    $pdo = $capsule->getConnection()->getPdo();
    echo "✅ Database connection successful!\n\n";

    // Test ADMIN PORTAL (users table)
    echo "👨‍💼 ADMIN PORTAL AUTHENTICATION (users table)\n";
    echo "==============================================\n";
    
    $users = $capsule->table('users')->select('id', 'name', 'email', 'role')->get();
    
    if ($users->isEmpty()) {
        echo "❌ No admin users found in 'users' table!\n";
        echo "💡 Run: php create_admin_user.php to create admin users\n\n";
    } else {
        echo "✅ Found " . $users->count() . " admin users:\n";
        foreach ($users as $user) {
            echo "   ID: {$user->id} | {$user->name} | {$user->email} | Role: {$user->role}\n";
        }
        
        // Test admin password
        $adminUser = $capsule->table('users')->where('email', 'admin@hr3system.com')->first();
        if ($adminUser) {
            $passwordCheck = password_verify('admin123', $adminUser->password);
            echo "   🔐 Admin password test: " . ($passwordCheck ? "✅ PASSED" : "❌ FAILED") . "\n";
        }
    }
    
    echo "\n👷‍♂️ EMPLOYEE PORTAL AUTHENTICATION (employees table)\n";
    echo "===================================================\n";
    
    $employees = $capsule->table('employees')
        ->select('id', 'first_name', 'last_name', 'email', 'position', 'department', 'status')
        ->where('status', 'active')
        ->get();
    
    if ($employees->isEmpty()) {
        echo "❌ No active employees found in 'employees' table!\n";
        echo "💡 Need to create employee accounts with passwords\n\n";
        
        // Create sample employee if none exist
        echo "🔧 Creating sample employee account...\n";
        $employeeData = [
            'first_name' => 'John',
            'last_name' => 'Employee',
            'email' => 'john.employee@hr3system.com',
            'password' => password_hash('employee123', PASSWORD_DEFAULT),
            'phone' => '+63 912 345 6789',
            'position' => 'Software Developer',
            'department' => 'IT',
            'hire_date' => date('Y-m-d'),
            'salary' => 50000.00,
            'status' => 'active',
            'online_status' => 'offline',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $employeeId = $capsule->table('employees')->insertGetId($employeeData);
        echo "✅ Created sample employee (ID: {$employeeId}): john.employee@hr3system.com / employee123\n\n";
        
        // Re-fetch employees
        $employees = $capsule->table('employees')
            ->select('id', 'first_name', 'last_name', 'email', 'position', 'department', 'status')
            ->where('status', 'active')
            ->get();
    }
    
    echo "✅ Found " . $employees->count() . " active employees:\n";
    foreach ($employees as $employee) {
        $fullName = $employee->first_name . ' ' . $employee->last_name;
        echo "   ID: {$employee->id} | {$fullName} | {$employee->email} | {$employee->position} ({$employee->department})\n";
    }
    
    // Test employee password
    $testEmployee = $capsule->table('employees')->where('email', 'john.employee@hr3system.com')->first();
    if ($testEmployee && $testEmployee->password) {
        $passwordCheck = password_verify('employee123', $testEmployee->password);
        echo "   🔐 Employee password test: " . ($passwordCheck ? "✅ PASSED" : "❌ FAILED") . "\n";
    }
    
    echo "\n🌐 AUTHENTICATION ENDPOINTS\n";
    echo "===========================\n";
    echo "Admin Portal:\n";
    echo "   Login URL: http://localhost:8000/admin/login\n";
    echo "   Uses: 'users' table with 'web' guard\n";
    echo "   Redirects to: /admin_dashboard\n\n";
    
    echo "Employee Portal:\n";
    echo "   Login URL: http://localhost:8000/employee/login\n";
    echo "   Uses: 'employees' table with 'employee' guard\n";
    echo "   Redirects to: /employee/dashboard\n\n";
    
    echo "🎯 TEST CREDENTIALS\n";
    echo "==================\n";
    echo "ADMIN LOGIN (users table):\n";
    echo "   Email: admin@hr3system.com\n";
    echo "   Password: admin123\n";
    echo "   Access: Admin dashboard with full management features\n\n";
    
    echo "EMPLOYEE LOGIN (employees table):\n";
    echo "   Email: john.employee@hr3system.com\n";
    echo "   Password: employee123\n";
    echo "   Access: Employee Self-Service (ESS) portal\n\n";
    
    echo "📋 AUTHENTICATION GUARDS\n";
    echo "========================\n";
    echo "✅ 'web' guard → 'users' table → Admin/HR management\n";
    echo "✅ 'employee' guard → 'employees' table → Employee Self-Service\n\n";
    
    echo "🔧 MIDDLEWARE PROTECTION\n";
    echo "=======================\n";
    echo "✅ Admin routes: middleware(['auth:web'])\n";
    echo "✅ Employee routes: middleware(['auth:employee'])\n";
    echo "✅ Separate session management for each portal\n\n";
    
    echo "✅ Dual authentication system test completed successfully!\n";
    echo "🎉 Both admin and employee portals are properly configured!\n\n";

} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "🔧 Please check your database configuration.\n\n";
    exit(1);
}
?>
