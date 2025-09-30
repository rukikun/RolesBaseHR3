<?php
/**
 * Debug Login Issue - Step by Step Analysis
 */

// Database configuration
$host = '127.0.0.1';
$dbname = 'hr3systemdb';
$username = 'root';
$password = '';

echo "=== DEBUGGING LOGIN ISSUE ===\n\n";

try {
    // Step 1: Database Connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful\n";

    // Step 2: Check if employee exists
    $testEmail = 'john.doe@jetlouge.com';
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
    $stmt->execute([$testEmail]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        echo "❌ Employee not found. Creating new employee...\n";
        
        // Create employee with working hash
        $hash = '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxkUBww2BDv6SvnpEOeKHF0H0ni'; // password123
        
        $stmt = $pdo->prepare("
            INSERT INTO employees (first_name, last_name, email, position, department, hire_date, salary, status, password, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $result = $stmt->execute([
            'John', 'Doe', $testEmail, 'Developer', 'IT', '2024-01-15', 55000, 'active', $hash
        ]);
        
        if ($result) {
            echo "✅ Employee created successfully\n";
            
            // Fetch the created employee
            $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
            $stmt->execute([$testEmail]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            echo "❌ Failed to create employee\n";
            exit(1);
        }
    } else {
        echo "✅ Employee found: {$employee['first_name']} {$employee['last_name']}\n";
    }

    // Step 3: Check employee status
    echo "Employee Status: {$employee['status']}\n";
    if ($employee['status'] !== 'active') {
        echo "❌ Employee is not active. Fixing...\n";
        $stmt = $pdo->prepare("UPDATE employees SET status = 'active' WHERE email = ?");
        $stmt->execute([$testEmail]);
        echo "✅ Employee status updated to active\n";
    }

    // Step 4: Test password verification
    $testPassword = 'password123';
    echo "\nTesting password verification:\n";
    
    if (password_verify($testPassword, $employee['password'])) {
        echo "✅ Password verification works\n";
    } else {
        echo "❌ Password verification failed. Updating password...\n";
        
        $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE employees SET password = ? WHERE email = ?");
        $stmt->execute([$newHash, $testEmail]);
        
        echo "✅ Password updated with new hash\n";
    }

    // Step 5: Check table structure
    echo "\nChecking table structure:\n";
    $stmt = $pdo->query("DESCRIBE employees");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = ['id', 'email', 'password', 'status', 'first_name', 'last_name'];
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columns)) {
            echo "✅ Column exists: $col\n";
        } else {
            echo "❌ Missing column: $col\n";
        }
    }

    // Step 6: Final verification
    echo "\n=== FINAL VERIFICATION ===\n";
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, status, password FROM employees WHERE email = ?");
    $stmt->execute([$testEmail]);
    $finalEmployee = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Employee ID: {$finalEmployee['id']}\n";
    echo "Name: {$finalEmployee['first_name']} {$finalEmployee['last_name']}\n";
    echo "Email: {$finalEmployee['email']}\n";
    echo "Status: {$finalEmployee['status']}\n";
    echo "Password Hash Length: " . strlen($finalEmployee['password']) . "\n";
    
    if (password_verify('password123', $finalEmployee['password'])) {
        echo "✅ Password verification: SUCCESS\n";
        echo "\n🎉 EMPLOYEE RECORD IS READY FOR LOGIN!\n";
        echo "Email: {$finalEmployee['email']}\n";
        echo "Password: password123\n";
    } else {
        echo "❌ Password verification: FAILED\n";
    }

} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
