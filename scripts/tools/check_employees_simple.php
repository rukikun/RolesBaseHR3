<?php
// Simple check of employees table

try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== EMPLOYEES CHECK ===\n";
    
    // Get all employees
    $stmt = $pdo->query("SELECT id, first_name, last_name, status FROM employees ORDER BY id");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total employees found: " . count($employees) . "\n\n";
    
    foreach ($employees as $employee) {
        echo "ID: {$employee['id']}, Name: {$employee['first_name']} {$employee['last_name']}, Status: {$employee['status']}\n";
    }
    
    // Check if David Brown exists
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE first_name = 'David' AND last_name = 'Brown'");
    $stmt->execute();
    $david = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\n=== DAVID BROWN CHECK ===\n";
    if ($david) {
        echo "David Brown found!\n";
        echo "ID: {$david['id']}\n";
        echo "Status: {$david['status']}\n";
        echo "Full data: " . json_encode($david) . "\n";
    } else {
        echo "David Brown NOT found in database!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
