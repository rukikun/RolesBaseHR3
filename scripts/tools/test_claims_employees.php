<?php
// Simple test to check employees for claims

try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3_hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== EMPLOYEES TEST ===\n";
    
    // Check if employees table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'employees'");
    if ($stmt->rowCount() == 0) {
        echo "❌ employees table does not exist!\n";
        exit;
    }
    
    // Count employees
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total employees: " . $count['count'] . "\n";
    
    // Count active employees
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'");
    $activeCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Active employees: " . $activeCount['count'] . "\n";
    
    // Show all employees
    $stmt = $pdo->query("SELECT id, first_name, last_name, status FROM employees ORDER BY id");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nAll employees:\n";
    foreach ($employees as $employee) {
        echo "ID: {$employee['id']}, Name: {$employee['first_name']} {$employee['last_name']}, Status: {$employee['status']}\n";
    }
    
    // Check claim_types
    echo "\n=== CLAIM TYPES TEST ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM claim_types");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total claim types: " . $count['count'] . "\n";
    
    $stmt = $pdo->query("SELECT id, name, code FROM claim_types ORDER BY id");
    $claimTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nAll claim types:\n";
    foreach ($claimTypes as $claimType) {
        echo "ID: {$claimType['id']}, Name: {$claimType['name']}, Code: {$claimType['code']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
