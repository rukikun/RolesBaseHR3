<?php
/**
 * Debug Authentication Flow
 * This script tests the complete authentication process step by step
 */

// Database configuration
$host = '127.0.0.1';
$dbname = 'hr3_hr3systemdb';
$username = 'root';
$password = '';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== AUTHENTICATION DEBUG ANALYSIS ===\n\n";
    
    // Step 1: Check database connection
    echo "1. Database Connection: ✅ Connected to $dbname\n";
    
    // Step 2: Check if employees table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'employees'");
    if ($stmt->rowCount() > 0) {
        echo "2. Employees Table: ✅ Exists\n";
    } else {
        echo "2. Employees Table: ❌ Missing\n";
        exit(1);
    }
    
    // Step 3: Check table structure
    echo "3. Table Structure:\n";
    $stmt = $pdo->query("DESCRIBE employees");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $hasPassword = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'password') {
            $hasPassword = true;
            echo "   ✅ Password column exists (Type: {$column['Type']})\n";
        }
        if ($column['Field'] === 'email') {
            echo "   ✅ Email column exists (Type: {$column['Type']})\n";
        }
    }
    
    if (!$hasPassword) {
        echo "   ❌ Password column missing!\n";
        exit(1);
    }
    
    // Step 4: Check for test employee
    $testEmail = 'john.doe@jetlouge.com';
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password, status FROM employees WHERE email = ?");
    $stmt->execute([$testEmail]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        echo "4. Test Employee: ❌ $testEmail not found\n";
        
        // Show available employees
        echo "   Available employees:\n";
        $stmt = $pdo->query("SELECT email FROM employees WHERE status = 'active' LIMIT 5");
        while ($emp = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "   - {$emp['email']}\n";
        }
        exit(1);
    } else {
        echo "4. Test Employee: ✅ Found {$employee['first_name']} {$employee['last_name']}\n";
        echo "   Status: {$employee['status']}\n";
        echo "   Password Length: " . strlen($employee['password']) . "\n";
    }
    
    // Step 5: Test password verification
    $testPassword = 'password123';
    $isValid = password_verify($testPassword, $employee['password']);
    
    if ($isValid) {
        echo "5. Password Verification: ✅ Password 'password123' matches hash\n";
    } else {
        echo "5. Password Verification: ❌ Password 'password123' does NOT match hash\n";
        echo "   Current hash: " . substr($employee['password'], 0, 30) . "...\n";
        
        // Generate correct hash
        $correctHash = password_hash($testPassword, PASSWORD_DEFAULT);
        echo "   Correct hash should be: " . substr($correctHash, 0, 30) . "...\n";
        
        // Update with correct hash
        echo "   Fixing password hash...\n";
        $updateStmt = $pdo->prepare("UPDATE employees SET password = ? WHERE email = ?");
        $updateStmt->execute([$correctHash, $testEmail]);
        echo "   ✅ Password hash updated!\n";
    }
    
    // Step 6: Test Laravel authentication requirements
    echo "6. Laravel Authentication Requirements:\n";
    
    // Check if password is hashed (bcrypt format)
    if (preg_match('/^\$2[ayb]\$.{56}$/', $employee['password'])) {
        echo "   ✅ Password is in bcrypt format\n";
    } else {
        echo "   ❌ Password is NOT in bcrypt format\n";
    }
    
    // Check if employee is active
    if ($employee['status'] === 'active') {
        echo "   ✅ Employee status is active\n";
    } else {
        echo "   ❌ Employee status is: {$employee['status']}\n";
    }
    
    // Step 7: Final verification
    echo "\n=== FINAL TEST ===\n";
    $stmt = $pdo->prepare("SELECT id, email, password FROM employees WHERE email = ?");
    $stmt->execute([$testEmail]);
    $finalEmployee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $finalTest = password_verify('password123', $finalEmployee['password']);
    
    if ($finalTest) {
        echo "✅ AUTHENTICATION SHOULD WORK!\n";
        echo "Login with:\n";
        echo "Email: {$finalEmployee['email']}\n";
        echo "Password: password123\n";
        echo "URL: http://localhost/employee/login\n";
    } else {
        echo "❌ AUTHENTICATION WILL FAIL\n";
        echo "Password verification still not working.\n";
    }
    
    // Step 8: Laravel-specific checks
    echo "\n=== LARAVEL CONFIGURATION CHECKS ===\n";
    echo "Check these in your Laravel application:\n";
    echo "1. .env file: DB_DATABASE=hr3systemdb\n";
    echo "2. Employee model: protected \$connection = 'mysql'\n";
    echo "3. Auth config: 'employee' guard uses Employee model\n";
    echo "4. Clear cache: php artisan config:clear\n";
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
