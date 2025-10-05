<?php
/**
 * Fix Employees Table - Direct SQL Creation
 * Access via: http://hr3system.test/fix_employees_table.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Employees Table - HR System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Fix Employees Table</h1>
    
    <?php
    try {
        // XAMPP MySQL connection settings
        $host = '127.0.0.1';
        $dbname = 'hr3_hr3systemdb';
        $username = 'root';
        $password = '';
        $charset = 'utf8mb4';
        
        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, $username, $password, $options);
        echo "<p class='success'>✅ Connected to hr3systemdb database</p>";
        
        // Check if employees table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'employees'");
        if ($stmt->rowCount() > 0) {
            echo "<p class='info'>ℹ️ Employees table already exists</p>";
        } else {
            echo "<p class='error'>❌ Employees table does not exist - creating it now...</p>";
            
            // Create employees table
            $pdo->exec("
                CREATE TABLE employees (
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
            echo "<p class='success'>✅ Employees table created successfully</p>";
        }
        
        // Check if employee with ID 1 exists
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM employees WHERE id = 1");
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result['count'] == 0) {
            echo "<p class='error'>❌ Employee with ID 1 does not exist - inserting sample data...</p>";
            
            // Insert sample employees
            $pdo->exec("
                INSERT INTO employees (employee_id, first_name, last_name, email, phone, position, department, hire_date, salary, status) VALUES
                ('EMP001', 'John', 'Doe', 'john.doe@company.com', '+1234567890', 'Software Developer', 'IT', '2023-01-15', 75000.00, 'active'),
                ('EMP002', 'Jane', 'Smith', 'jane.smith@company.com', '+1234567891', 'HR Manager', 'Human Resources', '2022-03-10', 65000.00, 'active'),
                ('EMP003', 'Mike', 'Johnson', 'mike.johnson@company.com', '+1234567892', 'Marketing Specialist', 'Marketing', '2023-06-01', 55000.00, 'active'),
                ('EMP004', 'Sarah', 'Wilson', 'sarah.wilson@company.com', '+1234567893', 'Accountant', 'Finance', '2022-11-20', 60000.00, 'active'),
                ('EMP005', 'Admin', 'User', 'admin@jetlouge.com', '+1234567894', 'System Administrator', 'IT', '2022-01-01', 80000.00, 'active')
            ");
            echo "<p class='success'>✅ Sample employees inserted</p>";
        } else {
            echo "<p class='success'>✅ Employee with ID 1 already exists</p>";
        }
        
        // Verify employee with ID 1
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = 1");
        $stmt->execute();
        $employee = $stmt->fetch();
        
        if ($employee) {
            echo "<h2 class='success'>✅ Employee ID 1 Found:</h2>";
            echo "<pre>";
            print_r($employee);
            echo "</pre>";
        } else {
            echo "<p class='error'>❌ Still cannot find employee with ID 1</p>";
        }
        
        // Show all tables in database
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<h2>Database Tables:</h2>";
        echo "<ul>";
        foreach ($tables as $table) {
            // Count records in each table
            $countStmt = $pdo->query("SELECT COUNT(*) as count FROM `{$table}`");
            $count = $countStmt->fetch()['count'];
            echo "<li><strong>{$table}</strong> ({$count} records)</li>";
        }
        echo "</ul>";
        
        echo "<h2 class='success'>🎉 Employees Table Fixed!</h2>";
        echo "<p>You can now access your <a href='/'>HR System Dashboard</a></p>";
        
    } catch (PDOException $e) {
        echo "<h2 class='error'>❌ Database Error</h2>";
        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
        echo "<h3>XAMPP Troubleshooting:</h3>";
        echo "<ul>";
        echo "<li>Make sure XAMPP MySQL service is running</li>";
        echo "<li>Check if hr3systemdb database exists in phpMyAdmin</li>";
        echo "<li>Verify MySQL is accessible on port 3306</li>";
        echo "</ul>";
    }
    ?>
</body>
</html>
