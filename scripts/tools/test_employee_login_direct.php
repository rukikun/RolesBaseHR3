<?php

// Direct database test for employee login
$host = 'localhost';
$dbname = 'hr3_hr3systemdb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connected\n";
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage() . "\n");
}

// Clear existing test employees
try {
    $pdo->exec("DELETE FROM employees WHERE email IN ('john.doe@jetlouge.com', 'jane.smith@jetlouge.com')");
    echo "🧹 Cleared existing test employees\n";
} catch (PDOException $e) {
    echo "⚠️  Clear warning: " . $e->getMessage() . "\n";
}

// Create fresh test employees with proper password hashing
$testEmployees = [
    ['John', 'Doe', 'john.doe@jetlouge.com'],
    ['Jane', 'Smith', 'jane.smith@jetlouge.com']
];

foreach ($testEmployees as $emp) {
    $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO employees (first_name, last_name, email, password, online_status, created_at, updated_at) VALUES (?, ?, ?, ?, 'offline', NOW(), NOW())");
        $stmt->execute([$emp[0], $emp[1], $emp[2], $hashedPassword]);
        echo "✅ Created employee: {$emp[2]}\n";
        
        // Verify password immediately
        $verify = password_verify('password123', $hashedPassword);
        echo "   Password verification: " . ($verify ? "✅ Valid" : "❌ Invalid") . "\n";
        
    } catch (PDOException $e) {
        echo "❌ Error creating {$emp[2]}: " . $e->getMessage() . "\n";
    }
}

// Test authentication simulation
echo "\n🔐 Testing authentication simulation:\n";
try {
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password FROM employees WHERE email = ?");
    $stmt->execute(['john.doe@jetlouge.com']);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($employee) {
        echo "📧 Found employee: {$employee['email']}\n";
        echo "👤 Name: {$employee['first_name']} {$employee['last_name']}\n";
        
        $passwordCheck = password_verify('password123', $employee['password']);
        echo "🔑 Password check: " . ($passwordCheck ? "✅ PASS" : "❌ FAIL") . "\n";
        
        if ($passwordCheck) {
            echo "🎉 Authentication would succeed!\n";
        } else {
            echo "❌ Authentication would fail!\n";
        }
    } else {
        echo "❌ Employee not found!\n";
    }
} catch (PDOException $e) {
    echo "❌ Auth test error: " . $e->getMessage() . "\n";
}

// Check admin user too
echo "\n👤 Checking admin user:\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute(['admin@jetlouge.com']);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin) {
            $adminPassword = password_hash('password123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
            $stmt->execute(['Admin User', 'admin@jetlouge.com', $adminPassword]);
            echo "✅ Created admin user\n";
        } else {
            echo "✅ Admin user exists\n";
        }
    } else {
        echo "⚠️  Users table doesn't exist\n";
    }
} catch (PDOException $e) {
    echo "⚠️  Admin check: " . $e->getMessage() . "\n";
}

echo "\n📋 FINAL TEST CREDENTIALS:\n";
echo "Employee Login (http://localhost:8000/employee/login):\n";
echo "  Email: john.doe@jetlouge.com\n";
echo "  Password: password123\n";
echo "\nAdmin Login (http://localhost:8000/admin/login):\n";
echo "  Email: admin@jetlouge.com\n";
echo "  Password: password123\n";

echo "\n✅ Setup complete! Try logging in now.\n";

?>
