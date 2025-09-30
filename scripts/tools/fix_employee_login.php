<?php
// Fix employee login issues

try {
    // Connect to database
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database\n";
    
    // Check if employees table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'employees'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Employees table doesn't exist. Creating it...\n";
        
        // Create employees table
        $createTable = "
        CREATE TABLE employees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            phone VARCHAR(20),
            position VARCHAR(255),
            department VARCHAR(255),
            hire_date DATE,
            salary DECIMAL(10,2),
            status ENUM('active', 'inactive') DEFAULT 'active',
            online_status ENUM('online', 'offline') DEFAULT 'offline',
            last_activity TIMESTAMP NULL,
            password VARCHAR(255) NOT NULL,
            profile_picture VARCHAR(255) NULL,
            remember_token VARCHAR(100) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($createTable);
        echo "✅ Employees table created\n";
    }
    
    // Check if we have any employees
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "📝 No employees found. Creating test employees...\n";
        
        // Create test employees with proper password hashes
        $employees = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@jetlouge.com',
                'phone' => '+63 912 345 6789',
                'position' => 'Software Developer',
                'department' => 'IT',
                'hire_date' => '2023-01-15',
                'salary' => 50000.00,
                'status' => 'active',
                'password' => password_hash('password123', PASSWORD_DEFAULT)
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@jetlouge.com',
                'phone' => '+63 917 234 5678',
                'position' => 'HR Manager',
                'department' => 'Human Resources',
                'hire_date' => '2022-06-10',
                'salary' => 60000.00,
                'status' => 'active',
                'password' => password_hash('password123', PASSWORD_DEFAULT)
            ],
            [
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'email' => 'mike.johnson@jetlouge.com',
                'phone' => '+63 918 345 6789',
                'position' => 'Accountant',
                'department' => 'Finance',
                'hire_date' => '2023-03-20',
                'salary' => 45000.00,
                'status' => 'active',
                'password' => password_hash('password123', PASSWORD_DEFAULT)
            ]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO employees (first_name, last_name, email, phone, position, department, hire_date, salary, status, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($employees as $employee) {
            $stmt->execute([
                $employee['first_name'],
                $employee['last_name'],
                $employee['email'],
                $employee['phone'],
                $employee['position'],
                $employee['department'],
                $employee['hire_date'],
                $employee['salary'],
                $employee['status'],
                $employee['password']
            ]);
            echo "✅ Created employee: {$employee['email']}\n";
        }
    } else {
        echo "📊 Found $count employees. Checking passwords...\n";
        
        // Check if passwords are properly hashed
        $stmt = $pdo->query("SELECT id, email, password FROM employees LIMIT 5");
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($employees as $employee) {
            if (strlen($employee['password']) < 50) {
                echo "❌ Password for {$employee['email']} needs to be hashed\n";
                
                // Update with proper hash
                $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE employees SET password = ? WHERE id = ?");
                $updateStmt->execute([$hashedPassword, $employee['id']]);
                echo "✅ Updated password for {$employee['email']}\n";
            } else {
                echo "✅ Password for {$employee['email']} is properly hashed\n";
            }
        }
    }
    
    echo "\n🎉 Employee login setup complete!\n";
    echo "Test credentials:\n";
    echo "  Email: john.doe@jetlouge.com\n";
    echo "  Password: password123\n";
    echo "\nOr try:\n";
    echo "  Email: jane.smith@jetlouge.com\n";
    echo "  Password: password123\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    echo "\nPlease ensure:\n";
    echo "1. XAMPP MySQL is running\n";
    echo "2. Database 'hr3systemdb' exists\n";
    echo "3. You have proper database permissions\n";
}
?>
