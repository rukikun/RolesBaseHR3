<?php
/**
 * Fix Claims Employee Integration
 * This script ensures the employees table exists and is populated for the claims system
 */

try {
    // Database connection
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to hr3systemdb database\n";
    
    // Create employees table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS employees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE,
        phone VARCHAR(20),
        position VARCHAR(100),
        department VARCHAR(100),
        hire_date DATE,
        salary DECIMAL(10,2),
        status ENUM('active', 'inactive') DEFAULT 'active',
        online_status ENUM('online', 'offline') DEFAULT 'offline',
        last_activity TIMESTAMP NULL,
        password VARCHAR(255),
        profile_picture VARCHAR(255),
        remember_token VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    echo "✅ Employees table created/verified\n";
    
    // Check if employees exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'active'");
    $employeeCount = $stmt->fetchColumn();
    
    echo "📊 Found {$employeeCount} active employees\n";
    
    if ($employeeCount == 0) {
        echo "🔧 No employees found, creating sample employees...\n";
        
        // Insert sample employees
        $employees = [
            ['John', 'Doe', 'john.doe@jetlouge.com', '555-0101', 'Software Developer', 'IT', '2023-01-15', 75000],
            ['Jane', 'Smith', 'jane.smith@jetlouge.com', '555-0102', 'Project Manager', 'IT', '2022-03-20', 85000],
            ['Mike', 'Johnson', 'mike.johnson@jetlouge.com', '555-0103', 'HR Specialist', 'HR', '2023-06-10', 65000],
            ['Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', '555-0104', 'Accountant', 'Finance', '2022-11-05', 70000],
            ['Tom', 'Brown', 'tom.brown@jetlouge.com', '555-0105', 'Sales Representative', 'Sales', '2023-02-28', 60000]
        ];
        
        $insertStmt = $pdo->prepare("INSERT INTO employees (first_name, last_name, email, phone, position, department, hire_date, salary, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        
        foreach ($employees as $emp) {
            try {
                $insertStmt->execute($emp);
                echo "✅ Created employee: {$emp[0]} {$emp[1]}\n";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    echo "⚠️  Employee {$emp[0]} {$emp[1]} already exists\n";
                } else {
                    echo "❌ Error creating employee {$emp[0]} {$emp[1]}: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    // Verify final employee count
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'active'");
    $finalCount = $stmt->fetchColumn();
    
    echo "✅ Final employee count: {$finalCount} active employees\n";
    
    // Show sample employees
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, position, department FROM employees WHERE status = 'active' ORDER BY first_name LIMIT 10");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n📋 Sample Employees:\n";
    echo "ID | Name                | Email                     | Position\n";
    echo "---|---------------------|---------------------------|------------------\n";
    foreach ($employees as $emp) {
        printf("%-2d | %-19s | %-25s | %s\n", 
            $emp['id'], 
            $emp['first_name'] . ' ' . $emp['last_name'],
            $emp['email'],
            $emp['position']
        );
    }
    
    // Test claims table integration
    echo "\n🔗 Testing Claims Integration:\n";
    
    // Create claims table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS claims (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NOT NULL,
        claim_type_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        claim_date DATE NOT NULL,
        description TEXT,
        receipt_path VARCHAR(255),
        attachment_path VARCHAR(255),
        status ENUM('pending', 'approved', 'rejected', 'paid') DEFAULT 'pending',
        approved_by INT NULL,
        approved_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
    )");
    
    echo "✅ Claims table created/verified with employee foreign key\n";
    
    // Create claim_types table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS claim_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        code VARCHAR(10) NOT NULL UNIQUE,
        description TEXT,
        max_amount DECIMAL(10,2),
        requires_attachment BOOLEAN DEFAULT TRUE,
        auto_approve BOOLEAN DEFAULT FALSE,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    echo "✅ Claim types table created/verified\n";
    
    // Check if claim types exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM claim_types WHERE is_active = 1");
    $claimTypeCount = $stmt->fetchColumn();
    
    if ($claimTypeCount == 0) {
        echo "🔧 No claim types found, creating sample claim types...\n";
        
        $claimTypes = [
            ['Travel Expenses', 'TRAVEL', 'Business travel and accommodation expenses', 2000.00, 1, 0],
            ['Meal Allowance', 'MEAL', 'Daily meal allowances and business meals', 100.00, 0, 1],
            ['Office Supplies', 'OFFICE', 'Office equipment and supplies', 500.00, 1, 0],
            ['Training Costs', 'TRAIN', 'Professional development and training', 1500.00, 1, 0],
            ['Medical Expenses', 'MEDICAL', 'Medical and health-related expenses', 1000.00, 1, 0]
        ];
        
        $insertStmt = $pdo->prepare("INSERT INTO claim_types (name, code, description, max_amount, requires_attachment, auto_approve, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
        
        foreach ($claimTypes as $type) {
            try {
                $insertStmt->execute($type);
                echo "✅ Created claim type: {$type[0]} ({$type[1]})\n";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    echo "⚠️  Claim type {$type[0]} already exists\n";
                } else {
                    echo "❌ Error creating claim type {$type[0]}: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "\n🎉 Claims Employee Integration Fixed Successfully!\n";
    echo "📝 The claims system now has:\n";
    echo "   - {$finalCount} active employees\n";
    echo "   - Proper database tables with foreign key relationships\n";
    echo "   - Sample claim types for testing\n";
    echo "\n🔗 You can now use the claims system with the employee dropdown working properly.\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "💡 Make sure:\n";
    echo "   - XAMPP MySQL is running\n";
    echo "   - hr3systemdb database exists\n";
    echo "   - Database credentials are correct\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
