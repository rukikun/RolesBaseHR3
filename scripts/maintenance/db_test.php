<?php
/**
 * Database Test Script - Web Version
 * Access via: http://hr3system.test/db_test.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Test - HR System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>HR System Database Test</h1>
    
    <?php
    try {
        // XAMPP MySQL connection settings
        $host = '127.0.0.1';
        $port = '3306';
        $dbname = 'hr3_hr3systemdb';
        $username = 'root';
        $password = ''; // XAMPP default - no password
        $charset = 'utf8mb4';
        
        echo "<h2>Step 1: Testing Database Connection</h2>";
        
        // First try to connect without database to create it
        $dsn = "mysql:host={$host};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, $username, $password, $options);
        echo "<p class='success'>✅ Connected to MySQL server successfully!</p>";
        
        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS hr3systemdb");
        echo "<p class='success'>✅ Database 'hr3_hr3systemdb' created/verified</p>";
        
        // Now connect to the specific database
        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
        $pdo = new PDO($dsn, $username, $password, $options);
        echo "<p class='success'>✅ Connected to hr3systemdb database!</p>";
        
        echo "<h2>Step 2: Creating Tables</h2>";
        
        // Create employees table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS employees (
                id INT AUTO_INCREMENT PRIMARY KEY,
                employee_id VARCHAR(20) UNIQUE NOT NULL,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                phone VARCHAR(20),
                position VARCHAR(100),
                department VARCHAR(100),
                hire_date DATE,
                salary DECIMAL(10,2),
                status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        echo "<p class='success'>✅ Employees table created</p>";
        
        // Create other essential tables
        $tables = [
            'time_entries' => "
                CREATE TABLE IF NOT EXISTS time_entries (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    employee_id INT NOT NULL,
                    date DATE NOT NULL,
                    clock_in TIME,
                    clock_out TIME,
                    break_duration INT DEFAULT 0,
                    total_hours DECIMAL(4,2) DEFAULT NULL,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    notes TEXT,
                    approved_by INT,
                    approved_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
                    UNIQUE KEY unique_employee_date (employee_id, date)
                )
            ",
            'leave_types' => "
                CREATE TABLE IF NOT EXISTS leave_types (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    description TEXT,
                    max_days_per_year INT DEFAULT 0,
                    carry_forward BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ",
            'leave_requests' => "
                CREATE TABLE IF NOT EXISTS leave_requests (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    employee_id INT NOT NULL,
                    leave_type_id INT NOT NULL,
                    start_date DATE NOT NULL,
                    end_date DATE NOT NULL,
                    days_requested INT NOT NULL,
                    reason TEXT,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    approved_by INT,
                    approved_at TIMESTAMP NULL,
                    rejection_reason TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
                    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE
                )
            ",
            'claim_types' => "
                CREATE TABLE IF NOT EXISTS claim_types (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    description TEXT,
                    max_amount DECIMAL(10,2) DEFAULT 0,
                    requires_receipt BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ",
            'claims' => "
                CREATE TABLE IF NOT EXISTS claims (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    employee_id INT NOT NULL,
                    claim_type_id INT NOT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    description TEXT,
                    receipt_path VARCHAR(255),
                    claim_date DATE NOT NULL,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    approved_by INT,
                    approved_at TIMESTAMP NULL,
                    rejection_reason TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
                    FOREIGN KEY (claim_type_id) REFERENCES claim_types(id) ON DELETE CASCADE
                )
            "
        ];
        
        foreach ($tables as $tableName => $sql) {
            $pdo->exec($sql);
            echo "<p class='success'>✅ {$tableName} table created</p>";
        }
        
        echo "<h2>Step 3: Inserting Sample Data</h2>";
        
        // Insert sample employees
        $pdo->exec("
            INSERT IGNORE INTO employees (employee_id, first_name, last_name, email, phone, position, department, hire_date, salary, status) VALUES
            ('EMP001', 'John', 'Doe', 'john.doe@company.com', '+1234567890', 'Software Developer', 'IT', '2023-01-15', 75000.00, 'active'),
            ('EMP002', 'Jane', 'Smith', 'jane.smith@company.com', '+1234567891', 'HR Manager', 'Human Resources', '2022-03-10', 65000.00, 'active'),
            ('EMP003', 'Mike', 'Johnson', 'mike.johnson@company.com', '+1234567892', 'Marketing Specialist', 'Marketing', '2023-06-01', 55000.00, 'active'),
            ('EMP004', 'Sarah', 'Wilson', 'sarah.wilson@company.com', '+1234567893', 'Accountant', 'Finance', '2022-11-20', 60000.00, 'active'),
            ('EMP005', 'Admin', 'User', 'admin@jetlouge.com', '+1234567894', 'System Administrator', 'IT', '2022-01-01', 80000.00, 'active')
        ");
        echo "<p class='success'>✅ Sample employees inserted</p>";
        
        // Insert sample leave types
        $pdo->exec("
            INSERT IGNORE INTO leave_types (name, description, max_days_per_year, carry_forward) VALUES
            ('Annual Leave', 'Yearly vacation days', 25, TRUE),
            ('Sick Leave', 'Medical leave for illness', 10, FALSE),
            ('Personal Leave', 'Personal time off', 5, FALSE)
        ");
        echo "<p class='success'>✅ Sample leave types inserted</p>";
        
        // Insert sample claim types
        $pdo->exec("
            INSERT IGNORE INTO claim_types (name, description, max_amount, requires_receipt) VALUES
            ('Travel Expenses', 'Business travel related expenses', 1000.00, TRUE),
            ('Meal Allowance', 'Business meal expenses', 50.00, TRUE),
            ('Office Supplies', 'Work-related office supplies', 200.00, TRUE)
        ");
        echo "<p class='success'>✅ Sample claim types inserted</p>";
        
        echo "<h2>Step 4: Verification</h2>";
        
        // Test if employee with ID 1 exists (the one causing the error)
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = 1");
        $stmt->execute();
        $employee = $stmt->fetch();
        
        if ($employee) {
            echo "<p class='success'>✅ Employee with ID 1 found:</p>";
            echo "<pre>";
            print_r($employee);
            echo "</pre>";
        } else {
            echo "<p class='error'>❌ Employee with ID 1 not found</p>";
        }
        
        // Show all tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p class='info'>📊 Tables in database:</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>{$table}</li>";
        }
        echo "</ul>";
        
        echo "<h2 class='success'>🎉 Database Setup Complete!</h2>";
        echo "<p>The HR system database is now ready. You can access the main application at <a href='/'>HR System Dashboard</a></p>";
        
    } catch (PDOException $e) {
        echo "<h2 class='error'>❌ Database Error</h2>";
        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
        echo "<h3>XAMPP Troubleshooting:</h3>";
        echo "<ul>";
        echo "<li><strong>Start XAMPP Control Panel</strong> and make sure MySQL service is running (green status)</li>";
        echo "<li>Click 'Admin' next to MySQL in XAMPP to open phpMyAdmin</li>";
        echo "<li>Check if port 3306 is available (not blocked by other services)</li>";
        echo "<li>Verify XAMPP MySQL is using default root user with no password</li>";
        echo "<li>If using XAMPP portable, make sure it's running as administrator</li>";
        echo "</ul>";
        echo "<p><strong>XAMPP Status Check:</strong></p>";
        echo "<p>MySQL should be accessible at: <code>localhost:3306</code> or <code>127.0.0.1:3306</code></p>";
    }
    ?>
</body>
</html>
