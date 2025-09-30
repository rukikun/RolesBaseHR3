<?php
// Direct shift types setup - run this in browser

try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Shift Types Setup</h2>";
    echo "<p>✅ Connected to database</p>";
    
    // Check if shift_types table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'shift_types'");
    if ($stmt->rowCount() == 0) {
        echo "<p>❌ shift_types table does not exist. Creating...</p>";
        
        // Create shift_types table
        $createTable = "
        CREATE TABLE shift_types (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            default_start_time time NOT NULL,
            default_end_time time NOT NULL,
            color_code varchar(7) DEFAULT '#007bff',
            type enum('regular','overtime','overnight') DEFAULT 'regular',
            break_duration int(11) DEFAULT 30,
            hourly_rate decimal(8,2) DEFAULT 0.00,
            is_active tinyint(1) DEFAULT 1,
            created_at timestamp NULL DEFAULT NULL,
            updated_at timestamp NULL DEFAULT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($createTable);
        echo "<p>✅ Created shift_types table</p>";
    } else {
        echo "<p>✅ shift_types table exists</p>";
    }
    
    // Clear existing data
    $pdo->exec("DELETE FROM shift_types");
    $pdo->exec("ALTER TABLE shift_types AUTO_INCREMENT = 1");
    echo "<p>✅ Cleared existing data</p>";
    
    // Insert fresh data
    $insertData = "
    INSERT INTO shift_types (name, description, default_start_time, default_end_time, color_code, type, break_duration, hourly_rate, is_active, created_at, updated_at) VALUES
    ('Morning Shift', 'Standard morning work shift', '08:00:00', '16:00:00', '#3B82F6', 'regular', 30, 25.00, 1, NOW(), NOW()),
    ('Evening Shift', 'Standard evening work shift', '16:00:00', '00:00:00', '#F59E0B', 'regular', 30, 27.50, 1, NOW(), NOW()),
    ('Night Shift', 'Overnight work shift', '00:00:00', '08:00:00', '#8B5CF6', 'overnight', 30, 30.00, 1, NOW(), NOW()),
    ('Weekend Day', 'Weekend day shift', '09:00:00', '17:00:00', '#10B981', 'regular', 30, 28.00, 1, NOW(), NOW()),
    ('Weekend Night', 'Weekend night shift', '22:00:00', '06:00:00', '#EF4444', 'overnight', 30, 32.00, 1, NOW(), NOW());
    ";
    
    $pdo->exec($insertData);
    echo "<p>✅ Inserted 5 shift types</p>";
    
    // Verify data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM shift_types");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>✅ Verified: {$count} shift types in database</p>";
    
    // Show data
    $stmt = $pdo->query("SELECT id, name, type, default_start_time, default_end_time FROM shift_types ORDER BY name");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Shift Types Data:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Start Time</th><th>End Time</th></tr>";
    
    foreach ($results as $row) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['type']}</td>";
        echo "<td>{$row['default_start_time']}</td>";
        echo "<td>{$row['default_end_time']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>🎉 Migration completed successfully!</h2>";
    echo "<p><a href='/shift-schedule-management'>Go to Shift Schedule Management</a></p>";
    
} catch (PDOException $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
