<?php

// Simple script to set up shift requests functionality
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

try {
    // Get database connection
    $host = env('DB_HOST', '127.0.0.1');
    $port = env('DB_PORT', '3306');
    $database = env('DB_DATABASE', 'hr3_hr3systemdb');
    $username = env('DB_USERNAME', 'root');
    $password = env('DB_PASSWORD', '');
    
    $dsn = "mysql:host={$host};port={$port};dbname={$database}";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully!\n";
    
    // Drop and recreate shift_requests table
    $pdo->exec("DROP TABLE IF EXISTS shift_requests");
    echo "Dropped existing shift_requests table\n";
    
    $pdo->exec("
        CREATE TABLE shift_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            shift_type_id INT NOT NULL,
            shift_date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            hours DECIMAL(4,2) DEFAULT 8.00,
            location VARCHAR(255) DEFAULT 'Main Office',
            notes TEXT,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            approved_by INT NULL,
            approved_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_employee_id (employee_id),
            INDEX idx_shift_type_id (shift_type_id),
            INDEX idx_status (status),
            INDEX idx_shift_date (shift_date)
        )
    ");
    echo "Created shift_requests table\n";
    
    // Insert sample data
    $sampleRequests = [
        [1, 1, '2024-10-05', '09:00:00', '17:00:00', 8.00, 'Main Office', 'Regular morning shift request', 'pending'],
        [2, 2, '2024-10-06', '14:00:00', '22:00:00', 8.00, 'Branch Office', 'Evening shift request', 'pending'],
        [3, 3, '2024-10-07', '22:00:00', '06:00:00', 8.00, 'Main Office', 'Night shift request', 'approved'],
        [1, 1, '2024-10-08', '08:00:00', '16:00:00', 8.00, 'Remote', 'Work from home request', 'rejected'],
        [2, 2, '2024-10-09', '10:00:00', '18:00:00', 8.00, 'Main Office', 'Flexible hours request', 'pending']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO shift_requests (employee_id, shift_type_id, shift_date, start_time, end_time, hours, location, notes, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    foreach ($sampleRequests as $request) {
        $stmt->execute($request);
    }
    
    echo "Inserted " . count($sampleRequests) . " sample shift requests\n";
    
    // Check the data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM shift_requests");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total shift requests in database: " . $count['count'] . "\n";
    
    echo "Setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
