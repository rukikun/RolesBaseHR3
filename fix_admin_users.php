<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

// Load Laravel configuration
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== HR3 System: Admin Users Setup ===\n\n";

try {
    // Update existing users to have proper admin roles
    echo "1. Updating existing users to admin roles...\n";
    
    DB::table('users')->where('id', 1)->update([
        'role' => 'admin',
        'updated_at' => now()
    ]);
    
    DB::table('users')->where('id', 2)->update([
        'role' => 'hr', 
        'updated_at' => now()
    ]);
    
    echo "   ✅ Updated existing users with proper roles\n";
    
    // Create additional admin users
    echo "\n2. Creating additional admin users...\n";
    
    $adminUsers = [
        [
            'name' => 'System Administrator',
            'email' => 'admin@jetlouge.com',
            'password' => Hash::make('admin123'),
            'phone' => '+63 900 000 0001',
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'name' => 'HR Administrator', 
            'email' => 'hr@jetlouge.com',
            'password' => Hash::make('hr123'),
            'phone' => '+63 900 000 0002',
            'role' => 'hr',
            'created_at' => now(),
            'updated_at' => now()
        ]
    ];
    
    foreach ($adminUsers as $user) {
        // Check if user already exists
        $existing = DB::table('users')->where('email', $user['email'])->first();
        if (!$existing) {
            DB::table('users')->insert($user);
            echo "   ✅ Created admin user: {$user['name']} ({$user['email']})\n";
        } else {
            echo "   ⚠️  Admin user already exists: {$user['email']}\n";
        }
    }
    
    // Display final user summary
    echo "\n3. Final Admin Users Summary:\n";
    $users = DB::table('users')->select('id', 'name', 'email', 'role')->get();
    
    foreach ($users as $user) {
        $roleIcon = $user->role === 'admin' ? '👑' : ($user->role === 'hr' ? '👥' : '👤');
        echo "   {$roleIcon} {$user->name} ({$user->email}) - Role: {$user->role}\n";
    }
    
    echo "\n=== Admin Portal Login Credentials ===\n";
    echo "🔐 System Admin: admin@jetlouge.com / admin123\n";
    echo "🔐 HR Admin: hr@jetlouge.com / hr123\n";
    echo "🔐 Jonnylito (Admin): johnkaizer19.jh@gmail.com / [existing password]\n";
    echo "🔐 Brylle (HR): Brylle.Cil@gmai.com / [existing password]\n";
    
    echo "\n=== Employee Portal (Separate) ===\n";
    $employees = DB::table('employees')->select('first_name', 'last_name', 'email', 'position')->limit(3)->get();
    foreach ($employees as $emp) {
        echo "👤 {$emp->first_name} {$emp->last_name} ({$emp->email}) - {$emp->position}\n";
    }
    
    echo "\n✅ Admin users setup completed successfully!\n";
    echo "\nAccess URLs:\n";
    echo "🌐 Admin Portal: http://localhost:8000/admin/login\n";
    echo "🌐 Employee Portal: http://localhost:8000/employee/login\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
