<?php
// Browser-accessible setup for shift requests table
$host = 'localhost';
$dbname = 'hr3_hr3systemdb';
$username = 'root';
$password = '';

echo "<h2>Setting up Shift Requests Table</h2>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Drop existing table if exists
    $pdo->exec("DROP TABLE IF EXISTS shift_requests");
    echo "<p>🗑️ Dropped existing shift_requests table</p>";
    
    // Create shift_requests table
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
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_employee_id (employee_id),
        INDEX idx_status (status),
        INDEX idx_requested_date (requested_date)
    ) ENGINE=InnoDB;
    ";
    
    $pdo->exec($createTable);
    echo "<p style='color: green;'>✅ Created shift_requests table</p>";
    
    // Insert sample data
    $insertData = "
    INSERT INTO shift_requests (employee_id, request_type, current_shift_type_id, shift_type_id, requested_date, reason, status, created_at, updated_at) VALUES
    (1, 'shift_change', 1, 2, '2025-09-15', 'Need to switch to evening shift due to childcare arrangements', 'pending', NOW() - INTERVAL 4 DAY, NOW() - INTERVAL 4 DAY),
    (2, 'time_off', 2, NULL, '2025-09-18', 'Doctor appointment - need the day off', 'pending', NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 3 DAY),
    (3, 'overtime', 3, 1, '2025-09-20', 'Willing to work extended hours for project deadline', 'pending', NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY),
    (4, 'swap', 1, 3, '2025-09-22', 'Want to swap weekend shift with weekday shift', 'pending', NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY),
    (5, 'shift_change', 2, 1, '2025-09-25', 'Prefer early morning shift for better work-life balance', 'pending', NOW(), NOW()),
    (1, 'time_off', 1, NULL, '2025-09-12', 'Family vacation - pre-approved', 'approved', NOW() - INTERVAL 11 DAY, NOW() - INTERVAL 10 DAY),
    (2, 'shift_change', 3, 2, '2025-09-16', 'Requested evening shift for school schedule', 'approved', NOW() - INTERVAL 9 DAY, NOW() - INTERVAL 8 DAY),
    (3, 'overtime', 1, 1, '2025-09-19', 'Extra coverage needed for busy period', 'approved', NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 4 DAY);
    ";
    
    $pdo->exec($insertData);
    echo "<p style='color: green;'>✅ Inserted 8 sample shift requests</p>";
    
    // Verify data with joins
    $stmt = $pdo->query("
        SELECT 
            sr.*,
            CONCAT(COALESCE(e.first_name, 'Unknown'), ' ', COALESCE(e.last_name, 'Employee')) as employee_name,
            COALESCE(current_shift.name, 'N/A') as current_shift_name,
            COALESCE(requested_shift.name, 'N/A') as requested_shift_name
        FROM shift_requests sr
        LEFT JOIN employees e ON sr.employee_id = e.id
        LEFT JOIN shift_types current_shift ON sr.current_shift_type_id = current_shift.id
        LEFT JOIN shift_types requested_shift ON sr.shift_type_id = requested_shift.id
        ORDER BY sr.created_at DESC
    ");
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>✅ Verification: " . count($results) . " shift requests created</h3>";
    
    if (count($results) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 15px;'>";
        echo "<tr style='background-color: #f8f9fa; font-weight: bold;'>";
        echo "<th style='padding: 12px; border: 1px solid #ddd;'>ID</th>";
        echo "<th style='padding: 12px; border: 1px solid #ddd;'>Employee</th>";
        echo "<th style='padding: 12px; border: 1px solid #ddd;'>Type</th>";
        echo "<th style='padding: 12px; border: 1px solid #ddd;'>Current Shift</th>";
        echo "<th style='padding: 12px; border: 1px solid #ddd;'>Requested Shift</th>";
        echo "<th style='padding: 12px; border: 1px solid #ddd;'>Date</th>";
        echo "<th style='padding: 12px; border: 1px solid #ddd;'>Status</th>";
        echo "</tr>";
        
        foreach ($results as $row) {
            $statusColor = $row['status'] === 'approved' ? 'green' : ($row['status'] === 'rejected' ? 'red' : 'orange');
            echo "<tr>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . $row['id'] . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . $row['employee_name'] . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . ucfirst(str_replace('_', ' ', $row['request_type'])) . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . $row['current_shift_name'] . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . $row['requested_shift_name'] . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . $row['requested_date'] . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd; color: {$statusColor}; font-weight: bold;'>" . ucfirst($row['status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test the exact query from blade template
    echo "<h3>🔍 Testing Blade Template Query</h3>";
    $stmt = $pdo->query("
        SELECT 
            sr.*,
            COALESCE(e.first_name, 'Unknown') as first_name,
            COALESCE(e.last_name, 'Employee') as last_name,
            COALESCE(current_shift.name, 'N/A') as current_shift_name,
            COALESCE(requested_shift.name, 'N/A') as requested_shift_name
        FROM shift_requests sr
        LEFT JOIN employees e ON sr.employee_id = e.id
        LEFT JOIN shift_types current_shift ON sr.current_shift_type_id = current_shift.id
        LEFT JOIN shift_types requested_shift ON sr.shift_type_id = requested_shift.id
        ORDER BY sr.created_at DESC
    ");
    
    $bladeResults = $stmt->fetchAll(PDO::FETCH_OBJ);
    echo "<p style='color: green;'>✅ Blade template query returns: <strong>" . count($bladeResults) . " records</strong></p>";
    
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin-top: 20px; border-radius: 5px;'>";
    echo "<h3 style='color: #155724; margin-top: 0;'>🎉 Setup Complete!</h3>";
    echo "<p style='color: #155724; margin-bottom: 0;'>The shift_requests table has been created with sample data. Refresh your shift management page to see the data.</p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<p style='color: red; background-color: #f8d7da; padding: 10px; border-radius: 5px;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
