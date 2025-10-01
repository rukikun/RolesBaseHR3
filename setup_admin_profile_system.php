<?php

/**
 * Admin Profile System Setup Script
 * 
 * This script sets up the admin profile management system with roles
 * Run this after creating the files to initialize the database
 */

echo "=== Admin Profile System Setup ===\n\n";

// Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Database connection established\n";
} catch (PDOException $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// 1. Add profile_picture column to users table if it doesn't exist
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) NULL AFTER password");
        echo "✓ Added profile_picture column to users table\n";
    } else {
        echo "✓ profile_picture column already exists\n";
    }
} catch (PDOException $e) {
    echo "✗ Error adding profile_picture column: " . $e->getMessage() . "\n";
}

// 2. Update existing admin user with super_admin role
try {
    $stmt = $pdo->prepare("UPDATE users SET role = 'super_admin' WHERE email = 'admin@jetlouge.com'");
    $stmt->execute();
    echo "✓ Updated admin user role to super_admin\n";
} catch (PDOException $e) {
    echo "✗ Error updating admin role: " . $e->getMessage() . "\n";
}

// 3. Create additional admin users for testing
$adminUsers = [
    [
        'name' => 'HR Manager',
        'email' => 'hr.manager@jetlouge.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'phone' => '+1234567891',
        'role' => 'hr_manager',
        'is_active' => 1
    ],
    [
        'name' => 'HR Scheduler',
        'email' => 'hr.scheduler@jetlouge.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'phone' => '+1234567892',
        'role' => 'hr_scheduler',
        'is_active' => 1
    ],
    [
        'name' => 'Attendance Admin',
        'email' => 'attendance.admin@jetlouge.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'phone' => '+1234567893',
        'role' => 'attendance_admin',
        'is_active' => 1
    ]
];

foreach ($adminUsers as $user) {
    try {
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$user['email']]);
        
        if ($stmt->rowCount() == 0) {
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, phone, role, is_active, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $user['name'],
                $user['email'],
                $user['password'],
                $user['phone'],
                $user['role'],
                $user['is_active']
            ]);
            echo "✓ Created {$user['role']}: {$user['email']}\n";
        } else {
            echo "✓ User {$user['email']} already exists\n";
        }
    } catch (PDOException $e) {
        echo "✗ Error creating user {$user['email']}: " . $e->getMessage() . "\n";
    }
}

// 4. Create profile_pictures directory
$profilePicturesDir = __DIR__ . '/storage/app/public/profile_pictures';
if (!is_dir($profilePicturesDir)) {
    if (mkdir($profilePicturesDir, 0755, true)) {
        echo "✓ Created profile_pictures directory\n";
    } else {
        echo "✗ Failed to create profile_pictures directory\n";
    }
} else {
    echo "✓ profile_pictures directory already exists\n";
}

// 5. Create symbolic link for storage (if not exists)
$publicStorageLink = __DIR__ . '/public/storage';
if (!is_link($publicStorageLink) && !is_dir($publicStorageLink)) {
    $storageAppPublic = __DIR__ . '/storage/app/public';
    if (symlink($storageAppPublic, $publicStorageLink)) {
        echo "✓ Created storage symbolic link\n";
    } else {
        echo "✗ Failed to create storage symbolic link\n";
    }
} else {
    echo "✓ Storage symbolic link already exists\n";
}

// 6. Display admin accounts summary
echo "\n=== Admin Accounts Summary ===\n";
try {
    $stmt = $pdo->query("SELECT name, email, role, is_active FROM users WHERE role IN ('super_admin', 'admin', 'hr_manager', 'hr_scheduler', 'attendance_admin') ORDER BY role");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($admins as $admin) {
        $status = $admin['is_active'] ? 'Active' : 'Inactive';
        $roleDisplay = ucfirst(str_replace('_', ' ', $admin['role']));
        echo "• {$admin['name']} ({$admin['email']}) - {$roleDisplay} - {$status}\n";
    }
} catch (PDOException $e) {
    echo "✗ Error fetching admin accounts: " . $e->getMessage() . "\n";
}

echo "\n=== Role Permissions ===\n";
$rolePermissions = [
    'super_admin' => 'Full system access with all permissions',
    'admin' => 'Administrative access to most system features',
    'hr_manager' => 'Human Resources management capabilities',
    'hr_scheduler' => 'Scheduling and timesheet management',
    'attendance_admin' => 'Time and attendance administration'
];

foreach ($rolePermissions as $role => $description) {
    $roleDisplay = ucfirst(str_replace('_', ' ', $role));
    echo "• {$roleDisplay}: {$description}\n";
}

echo "\n=== Setup Complete ===\n";
echo "You can now access the admin profile system at:\n";
echo "• Profile Management: /admin/profile\n";
echo "• Change Password: /admin/profile/change-password\n";
echo "• Manage Admins (Super Admin only): /admin/profile/manage-admins\n\n";

echo "Test Login Credentials:\n";
echo "• Super Admin: admin@jetlouge.com / password123\n";
echo "• HR Manager: hr.manager@jetlouge.com / password123\n";
echo "• HR Scheduler: hr.scheduler@jetlouge.com / password123\n";
echo "• Attendance Admin: attendance.admin@jetlouge.com / password123\n\n";

echo "Next Steps:\n";
echo "1. Test the profile management functionality\n";
echo "2. Upload profile pictures\n";
echo "3. Create additional admin accounts as needed\n";
echo "4. Configure role-based access control for HR modules\n";

?>
