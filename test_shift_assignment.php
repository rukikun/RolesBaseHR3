<?php
// Test script for shift assignment functionality

require_once 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== SHIFT ASSIGNMENT TEST ===\n";
    
    // Check if shifts table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'shifts'");
    $shiftsTableExists = $stmt->rowCount() > 0;
    echo "Shifts table exists: " . ($shiftsTableExists ? "YES" : "NO") . "\n";
    
    if (!$shiftsTableExists) {
        echo "Creating shifts table...\n";
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS shifts (
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
            )
        ");
        echo "Shifts table created successfully!\n";
    }
    
    // Check employees
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'");
    $employeeCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Active employees: $employeeCount\n";
    
    // Check shift types
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM shift_types WHERE is_active = 1");
    $shiftTypeCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Active shift types: $shiftTypeCount\n";
    
    // Check existing shifts
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM shifts");
    $shiftCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Total shifts: $shiftCount\n";
    
    // Show recent shifts
    if ($shiftCount > 0) {
        echo "\n=== RECENT SHIFTS ===\n";
        $stmt = $pdo->query("
            SELECT s.*, 
                   CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                   st.name as shift_type_name
            FROM shifts s
            LEFT JOIN employees e ON s.employee_id = e.id
            LEFT JOIN shift_types st ON s.shift_type_id = st.id
            ORDER BY s.created_at DESC
            LIMIT 5
        ");
        
        while ($shift = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "ID: {$shift['id']}, Employee: {$shift['employee_name']}, Type: {$shift['shift_type_name']}, Date: {$shift['shift_date']}, Time: {$shift['start_time']}-{$shift['end_time']}\n";
        }
    }
    
    echo "\n=== TEST COMPLETED SUCCESSFULLY ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
