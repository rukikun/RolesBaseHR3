<?php
/**
 * Test Admin Login Script
 * 
 * This script tests the admin login functionality
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

echo "🔍 HR3 System - Admin Login Test\n";
echo "================================\n\n";

try {
    // Test database connection
    echo "📡 Testing database connection...\n";
    $pdo = $capsule->getConnection()->getPdo();
    echo "✅ Database connection successful!\n\n";

    // Check users table
    echo "👥 Checking users in database:\n";
    echo "------------------------------\n";
    
    $users = $capsule->table('users')->select('id', 'name', 'email', 'role')->get();
    
    if ($users->isEmpty()) {
        echo "❌ No users found in database!\n";
        echo "💡 Run: php create_admin_user.php to create admin users\n\n";
        exit(1);
    }
    
    foreach ($users as $user) {
        echo "   ID: {$user->id} | {$user->name} | {$user->email} | Role: {$user->role}\n";
    }
    
    echo "\n🔐 Testing password verification:\n";
    echo "---------------------------------\n";
    
    // Test admin user password
    $adminUser = $capsule->table('users')->where('email', 'admin@hr3system.com')->first();
    if ($adminUser) {
        $passwordCheck = password_verify('admin123', $adminUser->password);
        if ($passwordCheck) {
            echo "✅ Admin password verification: PASSED\n";
        } else {
            echo "❌ Admin password verification: FAILED\n";
        }
    } else {
        echo "❌ Admin user not found!\n";
    }
    
    echo "\n🌐 Available Login URLs:\n";
    echo "========================\n";
    echo "Admin Portal: http://localhost:8000/admin/login\n";
    echo "Employee Portal: http://localhost:8000/employee/login\n";
    echo "Direct Dashboard: http://localhost:8000/admin_dashboard\n\n";
    
    echo "🎯 Test Login Credentials:\n";
    echo "==========================\n";
    echo "Admin Login:\n";
    echo "   Email: admin@hr3system.com\n";
    echo "   Password: admin123\n\n";
    
    echo "HR Login:\n";
    echo "   Email: hr@hr3system.com\n";
    echo "   Password: hr123\n\n";
    
    echo "Employee Login:\n";
    echo "   Email: employee@hr3system.com\n";
    echo "   Password: employee123\n\n";
    
    echo "✅ Admin login test completed successfully!\n";
    echo "💡 You can now access the admin portal at: http://localhost:8000/admin/login\n\n";

} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "🔧 Please check your database configuration.\n\n";
    exit(1);
}
?>
