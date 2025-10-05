<?php
// Direct database fix for phone column
$host = '127.0.0.1';
$username = 'root';
$password = '';

// Try both possible database names
$databases = ['hr3_hr3systemdb', 'hr_system'];

foreach ($databases as $dbname) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "✅ Connected to database: $dbname<br>";
        
        // Check if phone column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone'");
        $hasPhone = $stmt->rowCount() > 0;
        
        if (!$hasPhone) {
            echo "🔧 Adding phone column to users table...<br>";
            $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email");
            echo "✅ Phone column added successfully!<br>";
        } else {
            echo "✅ Phone column already exists!<br>";
        }
        
        // Show final structure
        echo "<h3>Users table structure in $dbname:</h3>";
        $stmt = $pdo->query("DESCRIBE users");
        echo "<ul>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<li>{$row['Field']} ({$row['Type']})</li>";
        }
        echo "</ul>";
        
        break; // Exit loop if successful
        
    } catch (PDOException $e) {
        echo "❌ Failed to connect to $dbname: " . $e->getMessage() . "<br>";
        continue;
    }
}
?>
