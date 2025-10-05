<?php
/**
 * Create Admin User Script
 * 
 * This script creates an admin user in the users table for admin portal login
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\Hash;

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

echo "🔧 HR3 System - Create Admin User\n";
echo "=================================\n\n";

try {
    // Check if admin user already exists
    $existingAdmin = $capsule->table('users')->where('email', 'admin@hr3system.com')->first();
    
    if ($existingAdmin) {
        echo "⚠️  Admin user already exists with email: admin@hr3system.com\n";
        echo "   User ID: {$existingAdmin->id}\n";
        echo "   Name: {$existingAdmin->name}\n";
        echo "   Role: {$existingAdmin->role}\n\n";
        
        // Update password if needed
        $newPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $capsule->table('users')
            ->where('id', $existingAdmin->id)
            ->update([
                'password' => $newPassword,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        
        echo "✅ Password updated to: admin123\n";
    } else {
        // Create new admin user
        $adminData = [
            'name' => 'System Administrator',
            'email' => 'admin@hr3system.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'phone' => '+63 912 345 6789',
            'profile_picture' => null,
            'role' => 'admin',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $userId = $capsule->table('users')->insertGetId($adminData);
        
        echo "✅ Admin user created successfully!\n";
        echo "   User ID: {$userId}\n";
        echo "   Name: System Administrator\n";
        echo "   Email: admin@hr3system.com\n";
        echo "   Password: admin123\n";
        echo "   Role: admin\n\n";
    }
    
    // Create additional test users if needed
    $testUsers = [
        [
            'name' => 'HR Manager',
            'email' => 'hr@hr3system.com',
            'password' => password_hash('hr123', PASSWORD_DEFAULT),
            'role' => 'hr',
            'phone' => '+63 917 234 5678'
        ],
        [
            'name' => 'Employee User',
            'email' => 'employee@hr3system.com',
            'password' => password_hash('employee123', PASSWORD_DEFAULT),
            'role' => 'employee',
            'phone' => '+63 918 345 6789'
        ]
    ];
    
    foreach ($testUsers as $userData) {
        $existing = $capsule->table('users')->where('email', $userData['email'])->first();
        
        if (!$existing) {
            $userData['email_verified_at'] = date('Y-m-d H:i:s');
            $userData['created_at'] = date('Y-m-d H:i:s');
            $userData['updated_at'] = date('Y-m-d H:i:s');
            
            $userId = $capsule->table('users')->insertGetId($userData);
            echo "✅ Created {$userData['role']} user: {$userData['email']}\n";
        }
    }
    
    echo "\n📊 Current users in database:\n";
    echo "============================\n";
    
    $allUsers = $capsule->table('users')->select('id', 'name', 'email', 'role')->get();
    foreach ($allUsers as $user) {
        echo "   ID: {$user->id} | {$user->name} | {$user->email} | Role: {$user->role}\n";
    }
    
    echo "\n🎯 Login Credentials:\n";
    echo "====================\n";
    echo "Admin Portal: http://localhost:8000/admin/login\n";
    echo "   Email: admin@hr3system.com\n";
    echo "   Password: admin123\n\n";
    echo "HR Portal: http://localhost:8000/admin/login\n";
    echo "   Email: hr@hr3system.com\n";
    echo "   Password: hr123\n\n";
    echo "Employee Portal: http://localhost:8000/admin/login\n";
    echo "   Email: employee@hr3system.com\n";
    echo "   Password: employee123\n\n";
    
    echo "✅ Admin user setup completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error creating admin user: " . $e->getMessage() . "\n";
    echo "🔧 Please check your database configuration and ensure the database is running.\n\n";
    exit(1);
}
?>
