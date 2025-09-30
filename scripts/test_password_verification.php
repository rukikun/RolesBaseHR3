<?php
/**
 * Test Password Verification Script
 * This script tests if the password hash in the database works with Laravel's verification
 */

// Database configuration
$host = '127.0.0.1';
$dbname = 'hr3systemdb';
$username = 'root';
$password = '';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== PASSWORD VERIFICATION TEST ===\n";
    echo "Database: $dbname\n";
    echo "Testing password: password123\n\n";
    
    // Get employee record
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password FROM employees WHERE email = ?");
    $stmt->execute(['john.doe@jetlouge.com']);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        echo "❌ Employee john.doe@jetlouge.com not found!\n";
        
        // Show all employees
        echo "\nAvailable employees:\n";
        $stmt = $pdo->query("SELECT id, first_name, last_name, email FROM employees WHERE status = 'active'");
        while ($emp = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- {$emp['first_name']} {$emp['last_name']} ({$emp['email']})\n";
        }
        exit(1);
    }
    
    echo "✅ Employee found: {$employee['first_name']} {$employee['last_name']}\n";
    echo "Email: {$employee['email']}\n";
    echo "Password hash length: " . strlen($employee['password']) . "\n";
    echo "Password hash: " . substr($employee['password'], 0, 20) . "...\n\n";
    
    // Test password verification
    $testPassword = 'password123';
    $isValid = password_verify($testPassword, $employee['password']);
    
    if ($isValid) {
        echo "✅ PASSWORD VERIFICATION SUCCESSFUL!\n";
        echo "The password 'password123' matches the hash in database.\n";
        echo "Laravel authentication should work.\n\n";
    } else {
        echo "❌ PASSWORD VERIFICATION FAILED!\n";
        echo "The password 'password123' does not match the hash.\n";
        echo "Need to update the password hash.\n\n";
        
        // Generate correct hash
        $correctHash = password_hash($testPassword, PASSWORD_DEFAULT);
        echo "Correct hash for 'password123':\n";
        echo "$correctHash\n\n";
        
        // Update the database
        echo "Updating password hash in database...\n";
        $updateStmt = $pdo->prepare("UPDATE employees SET password = ?, updated_at = NOW() WHERE email = ?");
        $updateStmt->execute([$correctHash, $employee['email']]);
        echo "✅ Password hash updated!\n";
    }
    
    // Test all employees
    echo "\n=== TESTING ALL EMPLOYEES ===\n";
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, password FROM employees WHERE status = 'active' ORDER BY id");
    
    while ($emp = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $isValid = password_verify('password123', $emp['password']);
        $status = $isValid ? '✅' : '❌';
        echo "$status {$emp['first_name']} {$emp['last_name']} ({$emp['email']})\n";
        
        if (!$isValid) {
            // Fix this employee's password
            $correctHash = password_hash('password123', PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE employees SET password = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->execute([$correctHash, $emp['id']]);
            echo "   🔧 Fixed password hash for {$emp['email']}\n";
        }
    }
    
    echo "\n=== FINAL TEST ===\n";
    echo "All employees should now be able to log in with 'password123'\n";
    echo "Try logging in at: http://localhost/employee/login\n";
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
