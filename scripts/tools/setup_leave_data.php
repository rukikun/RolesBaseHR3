<?php
/**
 * Setup Leave Management Sample Data
 * This script creates the necessary tables and sample data for the leave management system
 */

// Database configuration
$host = '127.0.0.1';
$dbname = 'hr3_hr3systemdb';
$username = 'root';
$password = '';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Create leave_types table
    $createLeaveTypesTable = "
    CREATE TABLE IF NOT EXISTS leave_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        max_days_per_year INT DEFAULT 30,
        carry_forward BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($createLeaveTypesTable);
    echo "Leave types table created/verified.\n";
    
    // Create leave_requests table
    $createLeaveRequestsTable = "
    CREATE TABLE IF NOT EXISTS leave_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NOT NULL,
        leave_type_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        days_requested INT NOT NULL,
        reason TEXT,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        approved_by INT NULL,
        approved_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($createLeaveRequestsTable);
    echo "Leave requests table created/verified.\n";
    
    // Insert leave types
    $leaveTypes = [
        [1, 'Annual Leave', 'Annual vacation leave', 30, 1],
        [2, 'Sick Leave', 'Medical leave for illness', 15, 0],
        [3, 'Personal Leave', 'Personal time off', 10, 0],
        [4, 'Maternity Leave', 'Maternity/Paternity leave', 90, 0],
        [5, 'Emergency Leave', 'Emergency situations', 5, 0]
    ];
    
    $insertLeaveType = "INSERT IGNORE INTO leave_types (id, name, description, max_days_per_year, carry_forward) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertLeaveType);
    
    foreach ($leaveTypes as $type) {
        $stmt->execute($type);
    }
    echo "Leave types inserted.\n";
    
    // Ensure employees exist
    $employees = [
        [1, 'John', 'Doe', 'john.doe@jetlouge.com', '123-456-7890', 'Software Developer', 'IT', '2023-01-15', 75000.00, 'active'],
        [2, 'Jane', 'Smith', 'jane.smith@jetlouge.com', '123-456-7891', 'HR Manager', 'Human Resources', '2022-03-20', 80000.00, 'active'],
        [3, 'Mike', 'Johnson', 'mike.johnson@jetlouge.com', '123-456-7892', 'Marketing Specialist', 'Marketing', '2023-06-10', 65000.00, 'active'],
        [4, 'Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', '123-456-7893', 'Finance Analyst', 'Finance', '2022-11-05', 70000.00, 'active'],
        [5, 'David', 'Brown', 'david.brown@jetlouge.com', '123-456-7894', 'Operations Manager', 'Operations', '2021-08-12', 85000.00, 'active']
    ];
    
    $insertEmployee = "INSERT IGNORE INTO employees (id, first_name, last_name, email, phone, position, department, hire_date, salary, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertEmployee);
    
    foreach ($employees as $employee) {
        $stmt->execute($employee);
    }
    echo "Employees inserted/verified.\n";
    
    // Insert sample leave requests
    $leaveRequests = [
        [1, 1, 1, '2025-09-19', '2025-09-21', 3, 'Family vacation', 'rejected'],
        [2, 2, 1, '2025-09-10', '2025-09-11', 2, 'Personal time off', 'pending'],
        [3, 3, 1, '2025-09-10', '2025-09-10', 1, 'Doctor appointment', 'pending'],
        [4, 4, 2, '2025-09-04', '2025-09-06', 3, 'Medical treatment', 'approved'],
        [5, 5, 1, '2025-09-15', '2025-09-17', 3, 'Wedding anniversary', 'pending']
    ];
    
    $insertLeaveRequest = "INSERT IGNORE INTO leave_requests (id, employee_id, leave_type_id, start_date, end_date, days_requested, reason, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertLeaveRequest);
    
    foreach ($leaveRequests as $request) {
        $stmt->execute($request);
    }
    echo "Leave requests inserted.\n";
    
    // Verify the data with the same query used in the controller
    echo "\n=== Verification ===\n";
    $query = "
        SELECT 
            lr.*,
            CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as employee_name,
            lt.name as leave_type_name
        FROM leave_requests lr
        LEFT JOIN employees e ON lr.employee_id = e.id
        LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id
        ORDER BY lr.start_date DESC
    ";
    
    $result = $pdo->query($query);
    $leaveData = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo "Found " . count($leaveData) . " leave requests:\n";
    foreach ($leaveData as $leave) {
        echo "- {$leave->employee_name} | {$leave->leave_type_name} | {$leave->start_date} to {$leave->end_date} | {$leave->status}\n";
    }
    
    echo "\nSetup completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
