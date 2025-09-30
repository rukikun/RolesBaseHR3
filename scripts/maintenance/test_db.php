<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create shift_requests table
    $pdo->exec("DROP TABLE IF EXISTS shift_requests");
    
    $createTable = "
    CREATE TABLE shift_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NOT NULL,
        request_type ENUM('shift_change', 'time_off', 'overtime', 'swap') NOT NULL DEFAULT 'shift_change',
        current_shift_type_id INT NULL,
        shift_type_id INT NULL,
        requested_date DATE NOT NULL,
        requested_start_time TIME NULL,
        requested_end_time TIME NULL,
        reason TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        approved_by INT NULL,
        approved_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
    ";
    
    $pdo->exec($createTable);
    
    // Insert sample data
    $pdo->exec("
    INSERT INTO shift_requests (employee_id, request_type, current_shift_type_id, shift_type_id, requested_date, reason, status) VALUES
    (1, 'shift_change', 1, 2, '2025-09-15', 'Need to switch to evening shift', 'pending'),
    (2, 'time_off', 2, NULL, '2025-09-18', 'Doctor appointment', 'pending'),
    (3, 'overtime', 3, 1, '2025-09-20', 'Project deadline coverage', 'pending'),
    (4, 'swap', 1, 3, '2025-09-22', 'Weekend shift swap', 'pending'),
    (5, 'shift_change', 2, 1, '2025-09-25', 'Better work-life balance', 'pending')
    ");
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM shift_requests");
    $count = $stmt->fetch()['count'];
    
    echo "SUCCESS: Created shift_requests table with {$count} records";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
