<?php
// Test script to check employees for dropdown

require_once 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== EMPLOYEES DROPDOWN TEST ===\n";
    
    // Check employees table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees");
    $totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Total employees in database: $totalEmployees\n";
    
    // Check active employees
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'");
    $activeEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Active employees: $activeEmployees\n";
    
    // List all active employees
    if ($activeEmployees > 0) {
        echo "\n=== ACTIVE EMPLOYEES LIST ===\n";
        $stmt = $pdo->query("SELECT id, first_name, last_name, status FROM employees WHERE status = 'active' ORDER BY first_name");
        
        while ($employee = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "ID: {$employee['id']}, Name: {$employee['first_name']} {$employee['last_name']}, Status: {$employee['status']}\n";
        }
    } else {
        echo "\nNo active employees found. Creating sample employees...\n";
        
        // Create sample employees
        $sampleEmployees = [
            ['John', 'Doe', 'john.doe@jetlouge.com'],
            ['Jane', 'Smith', 'jane.smith@jetlouge.com'],
            ['Mike', 'Johnson', 'mike.johnson@jetlouge.com'],
            ['Sarah', 'Wilson', 'sarah.wilson@jetlouge.com'],
            ['David', 'Brown', 'david.brown@jetlouge.com']
        ];
        
        foreach ($sampleEmployees as $emp) {
            $stmt = $pdo->prepare("
                INSERT INTO employees (first_name, last_name, email, status, hire_date, created_at, updated_at) 
                VALUES (?, ?, ?, 'active', CURDATE(), NOW(), NOW())
                ON DUPLICATE KEY UPDATE updated_at = NOW()
            ");
            $stmt->execute([$emp[0], $emp[1], $emp[2]]);
        }
        
        echo "Sample employees created!\n";
        
        // Re-check active employees
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'");
        $activeEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "Active employees after creation: $activeEmployees\n";
    }
    
    echo "\n=== TEST COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
