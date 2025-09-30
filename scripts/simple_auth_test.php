<?php

// Simple authentication test without Laravel bootstrap
$host = '127.0.0.1';
$dbname = 'hr3systemdb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== SIMPLE AUTH TEST ===\n";
    
    // Check employee exists
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
    $stmt->execute(['john.doe@jetlouge.com']);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        echo "Employee not found. Creating...\n";
        
        // Create employee with known working hash
        $hash = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO employees (first_name, last_name, email, position, department, hire_date, salary, status, password, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute(['John', 'Doe', 'john.doe@jetlouge.com', 'Developer', 'IT', '2024-01-15', 55000, 'active', $hash]);
        
        echo "Employee created with hash: " . substr($hash, 0, 20) . "...\n";
        
        // Fetch the created employee
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
        $stmt->execute(['john.doe@jetlouge.com']);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    echo "Employee found: {$employee['first_name']} {$employee['last_name']}\n";
    echo "Password hash: " . substr($employee['password'], 0, 20) . "...\n";
    
    // Test password verification
    if (password_verify('password123', $employee['password'])) {
        echo "✅ Password verification WORKS\n";
    } else {
        echo "❌ Password verification FAILED\n";
        
        // Fix the password
        $newHash = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE employees SET password = ? WHERE email = ?");
        $stmt->execute([$newHash, 'john.doe@jetlouge.com']);
        
        echo "Password updated. New hash: " . substr($newHash, 0, 20) . "...\n";
    }
    
    echo "\nTest credentials:\n";
    echo "Email: john.doe@jetlouge.com\n";
    echo "Password: password123\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
