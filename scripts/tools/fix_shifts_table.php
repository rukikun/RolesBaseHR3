<?php
// Fix shifts table structure

try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== FIXING SHIFTS TABLE ===\n";
    
    // Check current table structure
    $stmt = $pdo->query("SHOW CREATE TABLE shifts");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Current table structure:\n";
    echo $result['Create Table'] . "\n\n";
    
    // Drop and recreate the table with proper structure
    echo "Recreating shifts table with proper structure...\n";
    
    $pdo->exec("DROP TABLE IF EXISTS shifts");
    
    $createTable = "
        CREATE TABLE shifts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            shift_type_id INT NOT NULL,
            shift_date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            location VARCHAR(255) DEFAULT 'Main Office',
            notes TEXT,
            status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_employee_date (employee_id, shift_date),
            INDEX idx_shift_type_date (shift_type_id, shift_date),
            INDEX idx_shift_date (shift_date),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($createTable);
    echo "✓ Shifts table recreated successfully!\n";
    
    // Verify the new structure
    $stmt = $pdo->query("DESCRIBE shifts");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nNew table structure:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']}) {$col['Null']} {$col['Key']} {$col['Default']} {$col['Extra']}\n";
    }
    
    // Test insertion
    echo "\nTesting shift insertion...\n";
    
    // Get first employee and shift type
    $stmt = $pdo->query("SELECT id FROM employees WHERE status = 'active' LIMIT 1");
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT id FROM shift_types WHERE is_active = 1 LIMIT 1");
    $shiftType = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($employee && $shiftType) {
        $testDate = date('Y-m-d', strtotime('+1 day'));
        
        $stmt = $pdo->prepare("
            INSERT INTO shifts (employee_id, shift_type_id, shift_date, start_time, end_time, location, status)
            VALUES (?, ?, ?, '09:00:00', '17:00:00', 'Main Office', 'scheduled')
        ");
        
        $stmt->execute([$employee['id'], $shiftType['id'], $testDate]);
        
        $insertedId = $pdo->lastInsertId();
        echo "✓ Test shift inserted successfully with ID: $insertedId\n";
        
        // Clean up test data
        $pdo->exec("DELETE FROM shifts WHERE id = $insertedId");
        echo "✓ Test data cleaned up\n";
    }
    
    echo "\n=== SHIFTS TABLE FIXED ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
