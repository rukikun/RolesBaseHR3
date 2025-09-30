<?php

$pdo = new PDO("mysql:host=localhost;dbname=hr3systemdb", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Delete existing test employees
$pdo->exec("DELETE FROM employees WHERE email IN ('john.doe@jetlouge.com', 'jane.smith@jetlouge.com')");

// Create new employee with proper hash
$hash = password_hash('password123', PASSWORD_DEFAULT);
$pdo->prepare("INSERT INTO employees (first_name, last_name, email, password, online_status, created_at, updated_at) VALUES (?, ?, ?, ?, 'offline', NOW(), NOW())")
    ->execute(['John', 'Doe', 'john.doe@jetlouge.com', $hash]);

echo "Employee created: john.doe@jetlouge.com / password123\n";

// Verify it works
$stmt = $pdo->prepare("SELECT password FROM employees WHERE email = ?");
$stmt->execute(['john.doe@jetlouge.com']);
$stored = $stmt->fetchColumn();

if (password_verify('password123', $stored)) {
    echo "Password verification: SUCCESS\n";
} else {
    echo "Password verification: FAILED\n";
}

// Create admin user
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$adminHash = password_hash('password123', PASSWORD_DEFAULT);
$pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE password = VALUES(password)")
    ->execute(['Admin', 'admin@jetlouge.com', $adminHash]);

echo "Admin created: admin@jetlouge.com / password123\n";
echo "Ready to test login!\n";

?>
