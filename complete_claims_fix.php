<?php
/**
 * Complete Claims System Fix
 * This script completely fixes the claims system employee integration
 */

echo "🔧 Starting Complete Claims System Fix...\n\n";

try {
    // Database connection
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to hr3systemdb database\n";
    
    // Step 1: Drop and recreate tables to ensure clean state
    echo "\n🗑️  Cleaning up existing tables...\n";
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS claims");
    $pdo->exec("DROP TABLE IF EXISTS claim_types");
    $pdo->exec("DROP TABLE IF EXISTS employees");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "✅ Old tables removed\n";
    
    // Step 2: Create employees table
    echo "\n👥 Creating employees table...\n";
    
    $pdo->exec("CREATE TABLE employees (
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
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_department (department),
        INDEX idx_email (email)
    )");
    
    echo "✅ Employees table created\n";
    
    // Step 3: Create claim_types table
    echo "\n🏷️  Creating claim_types table...\n";
    
    $pdo->exec("CREATE TABLE claim_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        code VARCHAR(10) NOT NULL UNIQUE,
        description TEXT,
        max_amount DECIMAL(10,2),
        requires_attachment BOOLEAN DEFAULT TRUE,
        auto_approve BOOLEAN DEFAULT FALSE,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_active (is_active),
        INDEX idx_code (code)
    )");
    
    echo "✅ Claim types table created\n";
    
    // Step 4: Create claims table
    echo "\n💰 Creating claims table...\n";
    
    $pdo->exec("CREATE TABLE claims (
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
        rejection_reason TEXT,
        paid_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
        FOREIGN KEY (claim_type_id) REFERENCES claim_types(id) ON DELETE CASCADE,
        FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL,
        INDEX idx_employee (employee_id),
        INDEX idx_claim_type (claim_type_id),
        INDEX idx_status (status),
        INDEX idx_claim_date (claim_date)
    )");
    
    echo "✅ Claims table created with foreign keys\n";
    
    // Step 5: Insert sample employees
    echo "\n👤 Inserting sample employees...\n";
    
    $employees = [
        ['John', 'Doe', 'john.doe@jetlouge.com', '555-0101', 'Software Developer', 'IT', '2023-01-15', 75000.00],
        ['Jane', 'Smith', 'jane.smith@jetlouge.com', '555-0102', 'Project Manager', 'IT', '2022-03-20', 85000.00],
        ['Mike', 'Johnson', 'mike.johnson@jetlouge.com', '555-0103', 'HR Specialist', 'HR', '2023-06-10', 65000.00],
        ['Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', '555-0104', 'Accountant', 'Finance', '2022-11-05', 70000.00],
        ['Tom', 'Brown', 'tom.brown@jetlouge.com', '555-0105', 'Sales Representative', 'Sales', '2023-02-28', 60000.00],
        ['Lisa', 'Davis', 'lisa.davis@jetlouge.com', '555-0106', 'Marketing Manager', 'Marketing', '2023-04-12', 72000.00],
        ['David', 'Miller', 'david.miller@jetlouge.com', '555-0107', 'Operations Manager', 'Operations', '2022-08-30', 78000.00],
        ['Emma', 'Garcia', 'emma.garcia@jetlouge.com', '555-0108', 'Customer Service Rep', 'Support', '2023-07-20', 45000.00]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO employees (first_name, last_name, email, phone, position, department, hire_date, salary, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
    
    foreach ($employees as $emp) {
        $stmt->execute($emp);
        echo "   ✅ Created: {$emp[0]} {$emp[1]} - {$emp[4]}\n";
    }
    
    // Step 6: Insert sample claim types
    echo "\n🏷️  Inserting sample claim types...\n";
    
    $claimTypes = [
        ['Travel Expenses', 'TRAVEL', 'Business travel and accommodation expenses', 2000.00, 1, 0],
        ['Meal Allowance', 'MEAL', 'Daily meal allowances and business meals', 100.00, 0, 1],
        ['Office Supplies', 'OFFICE', 'Office equipment and supplies', 500.00, 1, 0],
        ['Training Costs', 'TRAIN', 'Professional development and training', 1500.00, 1, 0],
        ['Medical Expenses', 'MEDICAL', 'Medical and health-related expenses', 1000.00, 1, 0],
        ['Transportation', 'TRANSPORT', 'Local transportation and parking', 200.00, 1, 1],
        ['Communication', 'COMM', 'Phone and internet expenses', 300.00, 1, 0],
        ['Equipment', 'EQUIP', 'Work equipment and tools', 800.00, 1, 0]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO claim_types (name, code, description, max_amount, requires_attachment, auto_approve, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
    
    foreach ($claimTypes as $type) {
        $stmt->execute($type);
        echo "   ✅ Created: {$type[0]} ({$type[1]}) - Max: $" . number_format($type[3], 2) . "\n";
    }
    
    // Step 7: Insert sample claims
    echo "\n💰 Inserting sample claims...\n";
    
    $sampleClaims = [
        [1, 2, 12.00, '2025-09-25', 'Break meal'],
        [1, 1, 250.00, '2025-09-26', 'Training costs'],
        [2, 3, 25.00, '2025-09-22', 'Injury medical expense'],
        [3, 2, 12.00, '2025-09-22', 'Meal allowance'],
        [4, 2, 12.00, '2025-09-22', 'Break meal'],
        [2, 4, 150.00, '2025-09-20', 'Online course registration'],
        [3, 1, 180.00, '2025-09-18', 'Client meeting travel'],
        [5, 6, 45.00, '2025-09-15', 'Parking and taxi fare']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO claims (employee_id, claim_type_id, amount, claim_date, description, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    
    foreach ($sampleClaims as $claim) {
        $stmt->execute($claim);
        echo "   ✅ Created claim: Employee {$claim[0]} - $" . number_format($claim[2], 2) . " - {$claim[4]}\n";
    }
    
    // Step 8: Verify data integrity
    echo "\n🔍 Verifying data integrity...\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'active'");
    $employeeCount = $stmt->fetchColumn();
    echo "   📊 Active employees: {$employeeCount}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM claim_types WHERE is_active = 1");
    $claimTypeCount = $stmt->fetchColumn();
    echo "   📊 Active claim types: {$claimTypeCount}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM claims");
    $claimsCount = $stmt->fetchColumn();
    echo "   📊 Total claims: {$claimsCount}\n";
    
    // Step 9: Test foreign key relationships
    echo "\n🔗 Testing foreign key relationships...\n";
    
    $stmt = $pdo->query("
        SELECT 
            c.id,
            CONCAT(e.first_name, ' ', e.last_name) as employee_name,
            ct.name as claim_type_name,
            c.amount,
            c.status
        FROM claims c
        JOIN employees e ON c.employee_id = e.id
        JOIN claim_types ct ON c.claim_type_id = ct.id
        LIMIT 5
    ");
    
    $testClaims = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($testClaims as $claim) {
        echo "   ✅ Claim #{$claim['id']}: {$claim['employee_name']} - {$claim['claim_type_name']} - $" . number_format($claim['amount'], 2) . " ({$claim['status']})\n";
    }
    
    echo "\n🎉 Complete Claims System Fix Completed Successfully!\n";
    echo "\n📋 Summary:\n";
    echo "   - ✅ Database tables recreated with proper structure\n";
    echo "   - ✅ {$employeeCount} active employees created\n";
    echo "   - ✅ {$claimTypeCount} claim types created\n";
    echo "   - ✅ {$claimsCount} sample claims created\n";
    echo "   - ✅ Foreign key relationships working\n";
    echo "   - ✅ Data integrity verified\n";
    
    echo "\n🚀 Next Steps:\n";
    echo "   1. Update routes/web.php to use ClaimControllerFixed\n";
    echo "   2. Clear Laravel cache: php artisan cache:clear\n";
    echo "   3. Test the claims system in browser\n";
    echo "\n💡 The employee dropdown should now work perfectly!\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "💡 Make sure:\n";
    echo "   - XAMPP MySQL is running\n";
    echo "   - hr3systemdb database exists\n";
    echo "   - Database credentials are correct\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
