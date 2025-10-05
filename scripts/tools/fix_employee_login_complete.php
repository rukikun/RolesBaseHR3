<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Hash;

// Database connection
$host = 'localhost';
$dbname = 'hr3_hr3systemdb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful\n";
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage() . "\n");
}

// Check if employees table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'employees'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Employees table does not exist\n";
        exit;
    }
    echo "✅ Employees table exists\n";
} catch (PDOException $e) {
    echo "❌ Error checking employees table: " . $e->getMessage() . "\n";
    exit;
}

// Check employees table structure
try {
    $stmt = $pdo->query("DESCRIBE employees");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasPassword = false;
    $hasEmail = false;
    
    echo "\n📋 Employees table structure:\n";
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']})\n";
        if ($column['Field'] === 'password') $hasPassword = true;
        if ($column['Field'] === 'email') $hasEmail = true;
    }
    
    if (!$hasPassword) {
        echo "\n⚠️  Adding password column to employees table...\n";
        $pdo->exec("ALTER TABLE employees ADD COLUMN password VARCHAR(255) NULL");
        echo "✅ Password column added\n";
    }
    
    if (!$hasEmail) {
        echo "\n⚠️  Adding email column to employees table...\n";
        $pdo->exec("ALTER TABLE employees ADD COLUMN email VARCHAR(255) UNIQUE NULL");
        echo "✅ Email column added\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error checking table structure: " . $e->getMessage() . "\n";
    exit;
}

// Create/Update test employee
try {
    // Check if test employee exists
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
    $stmt->execute(['john.doe@jetlouge.com']);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
    
    if ($employee) {
        // Update existing employee
        $stmt = $pdo->prepare("UPDATE employees SET password = ?, online_status = 'offline' WHERE email = ?");
        $stmt->execute([$hashedPassword, 'john.doe@jetlouge.com']);
        echo "✅ Updated existing employee: john.doe@jetlouge.com\n";
    } else {
        // Create new employee
        $stmt = $pdo->prepare("INSERT INTO employees (first_name, last_name, email, password, online_status, created_at, updated_at) VALUES (?, ?, ?, ?, 'offline', NOW(), NOW())");
        $stmt->execute(['John', 'Doe', 'john.doe@jetlouge.com', $hashedPassword]);
        echo "✅ Created new employee: john.doe@jetlouge.com\n";
    }
    
    // Create additional test employee
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
    $stmt->execute(['jane.smith@jetlouge.com']);
    $employee2 = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee2) {
        $stmt = $pdo->prepare("INSERT INTO employees (first_name, last_name, email, password, online_status, created_at, updated_at) VALUES (?, ?, ?, ?, 'offline', NOW(), NOW())");
        $stmt->execute(['Jane', 'Smith', 'jane.smith@jetlouge.com', $hashedPassword]);
        echo "✅ Created additional employee: jane.smith@jetlouge.com\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error creating/updating employees: " . $e->getMessage() . "\n";
    exit;
}

// Verify password hashing
try {
    $stmt = $pdo->prepare("SELECT email, password FROM employees WHERE email IN (?, ?)");
    $stmt->execute(['john.doe@jetlouge.com', 'jane.smith@jetlouge.com']);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n🔐 Password verification:\n";
    foreach ($employees as $emp) {
        $isValid = password_verify('password123', $emp['password']);
        echo "  - {$emp['email']}: " . ($isValid ? "✅ Valid" : "❌ Invalid") . "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error verifying passwords: " . $e->getMessage() . "\n";
}

// Check users table for admin
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "\n👤 Checking admin user...\n";
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute(['admin@jetlouge.com']);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $adminPassword = password_hash('password123', PASSWORD_DEFAULT);
        
        if ($admin) {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$adminPassword, 'admin@jetlouge.com']);
            echo "✅ Updated admin user: admin@jetlouge.com\n";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
            $stmt->execute(['Admin User', 'admin@jetlouge.com', $adminPassword]);
            echo "✅ Created admin user: admin@jetlouge.com\n";
        }
    }
} catch (PDOException $e) {
    echo "⚠️  Admin user setup: " . $e->getMessage() . "\n";
}

echo "\n🎉 Employee login setup completed!\n";
echo "\n📝 Test Credentials:\n";
echo "Employee Portal:\n";
echo "  - Email: john.doe@jetlouge.com\n";
echo "  - Password: password123\n";
echo "  - Email: jane.smith@jetlouge.com\n";
echo "  - Password: password123\n";
echo "\nAdmin Portal:\n";
echo "  - Email: admin@jetlouge.com\n";
echo "  - Password: password123\n";
echo "\n🔗 Login URLs:\n";
echo "  - Employee: http://localhost:8000/employee/login\n";
echo "  - Admin: http://localhost:8000/admin/login\n";

?>
