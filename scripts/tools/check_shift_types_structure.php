<?php
// Check shift_types table structure

try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== SHIFT TYPES TABLE STRUCTURE ===\n";
    
    $stmt = $pdo->query("DESCRIBE shift_types");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current columns:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']}) {$col['Null']} {$col['Key']} {$col['Default']}\n";
    }
    
    echo "\n=== SAMPLE DATA ===\n";
    $stmt = $pdo->query("SELECT * FROM shift_types LIMIT 3");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, Name: {$row['name']}\n";
        foreach ($row as $key => $value) {
            if ($key != 'id' && $key != 'name') {
                echo "  $key: $value\n";
            }
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
