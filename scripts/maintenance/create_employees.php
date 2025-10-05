<?php
/**
 * Create Employees Table - Direct Fix
 * Access via: http://hr3system.test/create_employees.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Employees Table - HR System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Create Employees Table</h1>
    
    <?php
    try {
        // XAMPP MySQL connection
        $host = '127.0.0.1';
        $dbname = 'hr3_hr3systemdb';
        $username = 'root';
        $password = '';
        $charset = 'utf8mb4';
        
        // Connect to MySQL server first
        $dsn = "mysql:host={$host};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        
        $pdo = new PDO($dsn, $username, $password, $options);
        echo "<p class='success'>✅ Connected to MySQL</p>";
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS hr3systemdb");
        echo "<p class='success'>✅ Database hr3systemdb ready</p>";
        
        // Connect to specific database
        $pdo->exec("USE hr3systemdb");
        
        // Drop employees table if exists to recreate it fresh
        $pdo->exec("DROP TABLE IF EXISTS employees");
        echo "<p class='info'>🔄 Dropped existing employees table</p>";
        
        // Create employees table
        $pdo->exec("
            CREATE TABLE employees (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
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
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "<p class='success'>✅ Employees table created</p>";
        
        // Insert sample employees including ID 1
        $pdo->exec("
            INSERT INTO employees (id, employee_id, first_name, last_name, email, phone, position, department, hire_date, salary, status, created_at, updated_at) VALUES
            (1, 'EMP001', 'John', 'Doe', 'john.doe@company.com', '+1234567890', 'Software Developer', 'IT', '2023-01-15', 75000.00, 'active', NOW(), NOW()),
            (2, 'EMP002', 'Jane', 'Smith', 'jane.smith@company.com', '+1234567891', 'HR Manager', 'Human Resources', '2022-03-10', 65000.00, 'active', NOW(), NOW()),
            (3, 'EMP003', 'Mike', 'Johnson', 'mike.johnson@company.com', '+1234567892', 'Marketing Specialist', 'Marketing', '2023-06-01', 55000.00, 'active', NOW(), NOW()),
            (4, 'EMP004', 'Sarah', 'Wilson', 'sarah.wilson@company.com', '+1234567893', 'Accountant', 'Finance', '2022-11-20', 60000.00, 'active', NOW(), NOW()),
            (5, 'EMP005', 'Admin', 'User', 'admin@jetlouge.com', '+1234567894', 'System Administrator', 'IT', '2022-01-01', 80000.00, 'active', NOW(), NOW())
        ");
        echo "<p class='success'>✅ Sample employees inserted</p>";
        
        // Verify employee ID 1 exists
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = 1");
        $stmt->execute();
        $employee = $stmt->fetch();
        
        if ($employee) {
            echo "<h2 class='success'>✅ Employee ID 1 Verified:</h2>";
            echo "<pre>";
            echo "ID: " . $employee['id'] . "\n";
            echo "Name: " . $employee['first_name'] . " " . $employee['last_name'] . "\n";
            echo "Email: " . $employee['email'] . "\n";
            echo "Position: " . $employee['position'] . "\n";
            echo "</pre>";
        }
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE employees");
        $columns = $stmt->fetchAll();
        echo "<h2>Table Structure:</h2>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . $col['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Count total employees
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees");
        $count = $stmt->fetch()['count'];
        echo "<p class='success'>✅ Total employees in database: {$count}</p>";
        
        echo "<h2 class='success'>🎉 Employees Table Created Successfully!</h2>";
        echo "<p>The employees table now exists with ID 1. Your <a href='/'>HR System</a> should work now.</p>";
        
    } catch (PDOException $e) {
        echo "<h2 class='error'>❌ Database Error</h2>";
        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
        echo "<h3>XAMPP Troubleshooting:</h3>";
        echo "<ul>";
        echo "<li>Make sure XAMPP Control Panel shows MySQL as 'Running'</li>";
        echo "<li>Try restarting MySQL service in XAMPP</li>";
        echo "<li>Check if port 3306 is available</li>";
        echo "</ul>";
    }
    ?>
</body>
</html>
