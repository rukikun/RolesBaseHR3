<?php
// Check shifts table structure

try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== SHIFTS TABLE STRUCTURE ===\n";
    
    $stmt = $pdo->query("DESCRIBE shifts");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current columns:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']}) {$col['Null']} {$col['Key']} {$col['Default']}\n";
    }
    
    // Check if we need to add missing columns
    $currentColumns = array_column($columns, 'Field');
    $requiredColumns = ['shift_date', 'location'];
    $missingColumns = array_diff($requiredColumns, $currentColumns);
    
    if (!empty($missingColumns)) {
        echo "\n=== ADDING MISSING COLUMNS ===\n";
        foreach ($missingColumns as $col) {
            if ($col == 'shift_date') {
                $pdo->exec("ALTER TABLE shifts ADD COLUMN shift_date DATE NOT NULL DEFAULT '2025-01-01'");
                echo "✓ Added shift_date column\n";
            }
            if ($col == 'location') {
                $pdo->exec("ALTER TABLE shifts ADD COLUMN location VARCHAR(255) DEFAULT 'Main Office'");
                echo "✓ Added location column\n";
            }
        }
        
        echo "\n=== UPDATED TABLE STRUCTURE ===\n";
        $stmt = $pdo->query("DESCRIBE shifts");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $col) {
            echo "  - {$col['Field']} ({$col['Type']})\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
