<?php
/**
 * Database Connection Diagnostic and Fix Script
 * This script helps diagnose and fix common database connection issues
 */

echo "=== HR3 System Database Connection Diagnostic ===\n\n";

// Check PHP version
echo "1. PHP Version: " . PHP_VERSION . "\n";

// Check if PDO is loaded
echo "2. PDO Extension: " . (extension_loaded('pdo') ? "✓ Loaded" : "✗ Not Loaded") . "\n";

// Check MySQL PDO driver
echo "3. PDO MySQL Driver: " . (extension_loaded('pdo_mysql') ? "✓ Loaded" : "✗ Not Loaded") . "\n";

// Check available PDO drivers
$drivers = PDO::getAvailableDrivers();
echo "4. Available PDO Drivers: " . implode(', ', $drivers) . "\n";

// Check if MySQL service is running (Windows)
echo "\n5. Checking MySQL Service Status:\n";
$mysqlServices = ['MySQL', 'MySQL80', 'MySQL57', 'MariaDB', 'WAMP_MySQL', 'XAMPP_MySQL'];
foreach ($mysqlServices as $service) {
    $output = shell_exec("sc query \"$service\" 2>nul");
    if ($output && strpos($output, 'RUNNING') !== false) {
        echo "   ✓ $service is running\n";
    }
}

// Test database connection with different configurations
echo "\n6. Testing Database Connections:\n";

// Test configurations
$testConfigs = [
    'MySQL (localhost)' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'hr3system',
        'username' => 'root',
        'password' => ''
    ],
    'MySQL (Herd)' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'hr3system',
        'username' => 'herd',
        'password' => ''
    ],
    'XAMPP MySQL' => [
        'host' => 'localhost',
        'port' => '3306',
        'database' => 'hr3system',
        'username' => 'root',
        'password' => ''
    ]
];

foreach ($testConfigs as $name => $config) {
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        // Test if database exists
        $stmt = $pdo->query("SHOW DATABASES LIKE '{$config['database']}'");
        $dbExists = $stmt->rowCount() > 0;
        
        echo "   ✓ $name: Connection successful" . ($dbExists ? " (Database exists)" : " (Database missing)") . "\n";
        
        if (!$dbExists) {
            echo "     → Creating database '{$config['database']}'...\n";
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "     ✓ Database created successfully\n";
        }
        
    } catch (PDOException $e) {
        echo "   ✗ $name: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Recommendations ===\n";

if (!extension_loaded('pdo_mysql')) {
    echo "1. CRITICAL: Install MySQL PDO extension\n";
    echo "   - For XAMPP: Enable extension=pdo_mysql in php.ini\n";
    echo "   - For Laravel Herd: Should be included by default\n";
    echo "   - For standalone PHP: Install php-mysql package\n\n";
}

echo "2. Ensure MySQL service is running\n";
echo "3. Verify database credentials in .env file\n";
echo "4. Create hr3system database if it doesn't exist\n";

echo "\n=== Next Steps ===\n";
echo "1. Run: php artisan config:clear\n";
echo "2. Run: php artisan config:cache\n";
echo "3. Run: php artisan migrate\n";

echo "\n=== Environment File Template ===\n";
echo "Add these lines to your .env file:\n\n";
echo "DB_CONNECTION=mysql\n";
echo "DB_HOST=127.0.0.1\n";
echo "DB_PORT=3306\n";
echo "DB_DATABASE=hr3system\n";
echo "DB_USERNAME=root\n";
echo "DB_PASSWORD=\n";

echo "\n=== Script Complete ===\n";
