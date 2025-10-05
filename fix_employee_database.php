<?php
/**
 * Fix Employee Database Structure Issues
 * This script fixes the "Unknown column 'employees_id'" error
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "🔧 Fixing Employee Database Structure Issues...\n\n";

    // Connect to database
    $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    // 1. Check if employees table exists and has correct structure
    echo "📋 Checking employees table structure...\n";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'employees'");
    if ($stmt->rowCount() == 0) {
        echo "  ❌ Employees table doesn't exist. Creating...\n";
        
        $createEmployeesTable = "
        CREATE TABLE `employees` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `employee_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
            `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
            `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `position` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `department` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `hire_date` date DEFAULT NULL,
            `salary` decimal(10,2) DEFAULT 0.00,
            `status` enum('active','inactive','terminated') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
            `online_status` enum('online','offline') COLLATE utf8mb4_unicode_ci DEFAULT 'offline',
            `last_activity` timestamp NULL DEFAULT NULL,
            `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `date_of_birth` date DEFAULT NULL,
            `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `emergency_contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `emergency_contact_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `bank_account_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `tax_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `employees_email_unique` (`email`),
            KEY `employees_employee_id_index` (`employee_id`),
            KEY `employees_status_index` (`status`),
            KEY `employees_department_index` (`department`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($createEmployeesTable);
        echo "  ✅ Employees table created successfully\n";
    } else {
        echo "  ✅ Employees table exists\n";
    }

    // 2. Check for tables with incorrect foreign key references
    echo "\n🔍 Checking for incorrect foreign key references...\n";
    
    $tables = ['time_entries', 'attendances', 'leave_requests', 'claims', 'shifts', 'employee_shifts'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "  📋 Checking table: $table\n";
                
                // Check columns in this table
                $columns = $pdo->query("SHOW COLUMNS FROM $table")->fetchAll(\PDO::FETCH_ASSOC);
                
                foreach ($columns as $column) {
                    if ($column['Field'] === 'employees_id') {
                        echo "    ❌ Found incorrect column 'employees_id' in $table\n";
                        echo "    🔧 Renaming to 'employee_id'...\n";
                        
                        // Drop foreign key constraint if exists
                        try {
                            $pdo->exec("ALTER TABLE $table DROP FOREIGN KEY {$table}_employees_id_foreign");
                        } catch (\Exception $e) {
                            // Constraint might not exist
                        }
                        
                        // Rename column
                        $pdo->exec("ALTER TABLE $table CHANGE employees_id employee_id INT(11)");
                        
                        // Add proper foreign key constraint
                        try {
                            $pdo->exec("ALTER TABLE $table ADD CONSTRAINT {$table}_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE");
                        } catch (\Exception $e) {
                            echo "    ⚠️  Could not add foreign key constraint: " . $e->getMessage() . "\n";
                        }
                        
                        echo "    ✅ Fixed column name in $table\n";
                    }
                }
            }
        } catch (\Exception $e) {
            echo "    ⚠️  Could not check table $table: " . $e->getMessage() . "\n";
        }
    }

    // 3. Ensure time_entries table exists with correct structure
    echo "\n📋 Ensuring time_entries table exists...\n";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'time_entries'");
    if ($stmt->rowCount() == 0) {
        echo "  ❌ Time entries table doesn't exist. Creating...\n";
        
        $createTimeEntriesTable = "
        CREATE TABLE `time_entries` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `employee_id` bigint(20) UNSIGNED NOT NULL,
            `work_date` date NOT NULL,
            `clock_in_time` time DEFAULT NULL,
            `clock_out_time` time DEFAULT NULL,
            `break_duration` int(11) DEFAULT 60,
            `hours_worked` decimal(4,2) DEFAULT 0.00,
            `overtime_hours` decimal(4,2) DEFAULT 0.00,
            `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
            `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
            `approved_at` timestamp NULL DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `time_entries_employee_id_foreign` (`employee_id`),
            KEY `time_entries_work_date_index` (`work_date`),
            KEY `time_entries_status_index` (`status`),
            CONSTRAINT `time_entries_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($createTimeEntriesTable);
        echo "  ✅ Time entries table created successfully\n";
    } else {
        echo "  ✅ Time entries table exists\n";
    }

    // 4. Test employee creation
    echo "\n🧪 Testing employee creation...\n";
    
    try {
        // Try to insert a test employee
        $stmt = $pdo->prepare("
            INSERT INTO employees (employee_id, first_name, last_name, email, position, department, hire_date, salary, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $testEmail = 'test_' . time() . '@jetlouge.com';
        $stmt->execute([
            'TEST001',
            'Test',
            'Employee',
            $testEmail,
            'Test Position',
            'Test Department',
            date('Y-m-d'),
            50000.00,
            'active'
        ]);
        
        $testId = $pdo->lastInsertId();
        echo "  ✅ Test employee created successfully (ID: $testId)\n";
        
        // Clean up test employee
        $pdo->prepare("DELETE FROM employees WHERE id = ?")->execute([$testId]);
        echo "  🧹 Test employee cleaned up\n";
        
    } catch (\Exception $e) {
        echo "  ❌ Employee creation test failed: " . $e->getMessage() . "\n";
    }

    // 5. Display current table structure
    echo "\n📊 Current employees table structure:\n";
    $columns = $pdo->query("SHOW COLUMNS FROM employees")->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']}) " . ($column['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }

    echo "\n✅ Database structure fix completed!\n";
    echo "🌐 Try adding an employee again at: http://localhost:8000/employees\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n\n";
}
