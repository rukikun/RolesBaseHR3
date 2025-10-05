<?php
/**
 * Run Seeders Safely
 * 
 * This script runs the seeders manually to populate the dual authentication system
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

echo "🌱 HR3 System - Safe Seeder Runner\n";
echo "==================================\n\n";

try {
    // Test database connection
    echo "📡 Testing database connection...\n";
    $pdo = $capsule->getConnection()->getPdo();
    echo "✅ Database connection successful!\n\n";

    // 1. Seed Admin Users (users table)
    echo "👨‍💼 Seeding Admin Users (users table)...\n";
    echo "========================================\n";
    
    $adminUsers = [
        [
            'name' => 'Super Administrator',
            'email' => 'admin@jetlouge.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'phone' => '+1234567890',
            'role' => 'admin',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'name' => 'HR Manager',
            'email' => 'hr.manager@jetlouge.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'phone' => '+1234567891',
            'role' => 'hr',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'name' => 'HR Scheduler',
            'email' => 'hr.scheduler@jetlouge.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'phone' => '+1234567892',
            'role' => 'hr',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'name' => 'Attendance Admin',
            'email' => 'attendance.admin@jetlouge.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'phone' => '+1234567893',
            'role' => 'hr',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ];

    foreach ($adminUsers as $user) {
        $existing = $capsule->table('users')->where('email', $user['email'])->first();
        if (!$existing) {
            $capsule->table('users')->insert($user);
            echo "✅ Created admin user: {$user['email']}\n";
        } else {
            echo "⚠️  Admin user already exists: {$user['email']}\n";
        }
    }

    // 2. Seed Employees (employees table)
    echo "\n👷‍♂️ Seeding Employees (employees table)...\n";
    echo "==========================================\n";
    
    $employees = [
        [
            'first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john.doe@jetlouge.com',
            'phone' => '+63 912 345 6789', 'position' => 'Customer Service Representative',
            'department' => 'Operations', 'hire_date' => '2024-01-15', 'salary' => 35000.00,
            'status' => 'active', 'online_status' => 'offline',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'first_name' => 'Jane', 'last_name' => 'Smith', 'email' => 'jane.smith@jetlouge.com',
            'phone' => '+63 917 234 5678', 'position' => 'Travel Consultant',
            'department' => 'Sales', 'hire_date' => '2024-02-01', 'salary' => 40000.00,
            'status' => 'active', 'online_status' => 'offline',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'first_name' => 'Mike', 'last_name' => 'Johnson', 'email' => 'mike.johnson@jetlouge.com',
            'phone' => '+63 918 345 6789', 'position' => 'Operations Manager',
            'department' => 'Operations', 'hire_date' => '2023-11-10', 'salary' => 55000.00,
            'status' => 'active', 'online_status' => 'offline',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'first_name' => 'Sarah', 'last_name' => 'Wilson', 'email' => 'sarah.wilson@jetlouge.com',
            'phone' => '+63 919 456 7890', 'position' => 'Marketing Specialist',
            'department' => 'Marketing', 'hire_date' => '2024-03-05', 'salary' => 42000.00,
            'status' => 'active', 'online_status' => 'offline',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'first_name' => 'David', 'last_name' => 'Brown', 'email' => 'david.brown@jetlouge.com',
            'phone' => '+63 920 567 8901', 'position' => 'IT Support',
            'department' => 'IT', 'hire_date' => '2024-01-20', 'salary' => 45000.00,
            'status' => 'active', 'online_status' => 'offline',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]
    ];

    foreach ($employees as $employee) {
        $existing = $capsule->table('employees')->where('email', $employee['email'])->first();
        if (!$existing) {
            $capsule->table('employees')->insert($employee);
            echo "✅ Created employee: {$employee['email']}\n";
        } else {
            echo "⚠️  Employee already exists: {$employee['email']}\n";
        }
    }

    // 3. Seed Shift Types
    echo "\n⏰ Seeding Shift Types...\n";
    echo "========================\n";
    
    $shiftTypes = [
        [
            'name' => 'Morning Shift', 'code' => 'MORNING',
            'description' => 'Standard morning shift for regular operations',
            'default_start_time' => '08:00:00', 'default_end_time' => '16:00:00',
            'break_duration' => 60, 'hourly_rate' => 25.00, 'color_code' => '#28a745',
            'type' => 'day', 'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'name' => 'Afternoon Shift', 'code' => 'AFTERNOON',
            'description' => 'Afternoon to evening coverage shift',
            'default_start_time' => '14:00:00', 'default_end_time' => '22:00:00',
            'break_duration' => 45, 'hourly_rate' => 27.50, 'color_code' => '#ffc107',
            'type' => 'swing', 'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'name' => 'Night Shift', 'code' => 'NIGHT',
            'description' => 'Overnight shift with premium pay',
            'default_start_time' => '22:00:00', 'default_end_time' => '06:00:00',
            'break_duration' => 60, 'hourly_rate' => 32.00, 'color_code' => '#6f42c1',
            'type' => 'night', 'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]
    ];

    foreach ($shiftTypes as $shiftType) {
        $existing = $capsule->table('shift_types')->where('code', $shiftType['code'])->first();
        if (!$existing) {
            $capsule->table('shift_types')->insert($shiftType);
            echo "✅ Created shift type: {$shiftType['name']}\n";
        } else {
            echo "⚠️  Shift type already exists: {$shiftType['name']}\n";
        }
    }

    // 4. Seed Leave Types
    echo "\n🏖️ Seeding Leave Types...\n";
    echo "=========================\n";
    
    $leaveTypes = [
        [
            'name' => 'Annual Leave', 'code' => 'AL',
            'description' => 'Annual vacation leave',
            'days_allowed' => 0, 'max_days_per_year' => 21,
            'carry_forward' => 1, 'requires_approval' => 1,
            'status' => 'active', 'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'name' => 'Sick Leave', 'code' => 'SL',
            'description' => 'Medical sick leave',
            'days_allowed' => 0, 'max_days_per_year' => 10,
            'carry_forward' => 0, 'requires_approval' => 0,
            'status' => 'active', 'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'name' => 'Emergency Leave', 'code' => 'EL',
            'description' => 'Emergency family leave',
            'days_allowed' => 0, 'max_days_per_year' => 5,
            'carry_forward' => 0, 'requires_approval' => 1,
            'status' => 'active', 'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]
    ];

    foreach ($leaveTypes as $leaveType) {
        $existing = $capsule->table('leave_types')->where('code', $leaveType['code'])->first();
        if (!$existing) {
            $capsule->table('leave_types')->insert($leaveType);
            echo "✅ Created leave type: {$leaveType['name']}\n";
        } else {
            echo "⚠️  Leave type already exists: {$leaveType['name']}\n";
        }
    }

    // 5. Seed Claim Types
    echo "\n💰 Seeding Claim Types...\n";
    echo "=========================\n";
    
    $claimTypes = [
        [
            'name' => 'Travel Expenses', 'code' => 'TRAVEL',
            'description' => 'Business travel related expenses',
            'max_amount' => 5000.00, 'requires_attachment' => 1,
            'auto_approve' => 0, 'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'name' => 'Office Supplies', 'code' => 'OFFICE',
            'description' => 'Office supplies and equipment',
            'max_amount' => 1000.00, 'requires_attachment' => 1,
            'auto_approve' => 0, 'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'name' => 'Meal Allowance', 'code' => 'MEAL',
            'description' => 'Business meal expenses',
            'max_amount' => 500.00, 'requires_attachment' => 1,
            'auto_approve' => 0, 'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]
    ];

    foreach ($claimTypes as $claimType) {
        $existing = $capsule->table('claim_types')->where('code', $claimType['code'])->first();
        if (!$existing) {
            $capsule->table('claim_types')->insert($claimType);
            echo "✅ Created claim type: {$claimType['name']}\n";
        } else {
            echo "⚠️  Claim type already exists: {$claimType['name']}\n";
        }
    }

    echo "\n📊 Final Summary:\n";
    echo "================\n";
    
    $userCount = $capsule->table('users')->count();
    $employeeCount = $capsule->table('employees')->count();
    $shiftTypeCount = $capsule->table('shift_types')->count();
    $leaveTypeCount = $capsule->table('leave_types')->count();
    $claimTypeCount = $capsule->table('claim_types')->count();
    
    echo "👨‍💼 Admin Users (users table): {$userCount}\n";
    echo "👷‍♂️ Employees (employees table): {$employeeCount}\n";
    echo "⏰ Shift Types: {$shiftTypeCount}\n";
    echo "🏖️ Leave Types: {$leaveTypeCount}\n";
    echo "💰 Claim Types: {$claimTypeCount}\n";

    echo "\n🎯 Login Credentials:\n";
    echo "====================\n";
    echo "ADMIN PORTAL (users table):\n";
    echo "   URL: http://localhost:8000/admin/login\n";
    echo "   Email: admin@jetlouge.com\n";
    echo "   Password: password123\n\n";
    
    echo "EMPLOYEE PORTAL (employees table):\n";
    echo "   URL: http://localhost:8000/employee/login\n";
    echo "   Email: john.doe@jetlouge.com\n";
    echo "   Password: password123\n\n";
    
    echo "✅ All seeders completed successfully!\n";
    echo "🎉 Your dual authentication system is ready with seeded data!\n\n";

} catch (Exception $e) {
    echo "❌ Seeding failed: " . $e->getMessage() . "\n";
    echo "🔧 Please check your database configuration.\n\n";
    exit(1);
}
?>
