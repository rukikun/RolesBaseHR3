<?php
/**
 * Database Permissions Fix Script
 * This script helps diagnose and fix database permission issues
 */

echo "<h2>HR3 System - Database Permissions Fix</h2>";
echo "<pre>";

echo "=== CURRENT DATABASE CONFIGURATION ===\n";
echo "Database User: hr3_johnkaizer\n";
echo "Database Name: hr3systemdb\n";
echo "Error: Access denied - user lacks permissions\n\n";

echo "=== SOLUTION STEPS ===\n";
echo "You need to grant permissions to the database user. Here are the SQL commands:\n\n";

echo "1. LOGIN TO YOUR HOSTING CONTROL PANEL (cPanel/phpMyAdmin)\n";
echo "2. Go to MySQL Databases or Database section\n";
echo "3. Run these SQL commands:\n\n";

echo "-- Grant all privileges to the user for the specific database\n";
echo "GRANT ALL PRIVILEGES ON hr3systemdb.* TO 'hr3_johnkaizer'@'localhost';\n";
echo "FLUSH PRIVILEGES;\n\n";

echo "-- Alternative: If user doesn't exist, create it with permissions\n";
echo "CREATE USER IF NOT EXISTS 'hr3_johnkaizer'@'localhost' IDENTIFIED BY 'your_password';\n";
echo "GRANT ALL PRIVILEGES ON hr3systemdb.* TO 'hr3_johnkaizer'@'localhost';\n";
echo "FLUSH PRIVILEGES;\n\n";

echo "=== HOSTING PROVIDER SPECIFIC INSTRUCTIONS ===\n\n";

echo "**For cPanel Hosting:**\n";
echo "1. Login to cPanel\n";
echo "2. Go to 'MySQL Databases'\n";
echo "3. Scroll down to 'Add User To Database'\n";
echo "4. Select user: hr3_johnkaizer\n";
echo "5. Select database: hr3systemdb\n";
echo "6. Click 'Add'\n";
echo "7. Check 'ALL PRIVILEGES' and click 'Make Changes'\n\n";

echo "**For phpMyAdmin:**\n";
echo "1. Login to phpMyAdmin\n";
echo "2. Click 'User accounts' tab\n";
echo "3. Find user 'hr3_johnkaizer'\n";
echo "4. Click 'Edit privileges'\n";
echo "5. Go to 'Database-specific privileges'\n";
echo "6. Select database 'hr3systemdb'\n";
echo "7. Check 'Select All' privileges\n";
echo "8. Click 'Go'\n\n";

echo "**For Shared Hosting:**\n";
echo "1. Contact your hosting provider support\n";
echo "2. Ask them to grant full privileges to user 'hr3_johnkaizer' on database 'hr3systemdb'\n";
echo "3. Or ask them to create the user with proper permissions\n\n";

echo "=== VERIFICATION ===\n";
echo "After fixing permissions, test the connection by:\n";
echo "1. Visiting your admin login page again\n";
echo "2. Or running this verification script:\n\n";

// Test connection if we can load Laravel
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "=== TESTING CONNECTION ===\n";
    try {
        require_once __DIR__ . '/vendor/autoload.php';
        $app = require_once __DIR__ . '/bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        // Test database connection
        $pdo = DB::connection()->getPdo();
        echo "✅ Database connection successful!\n";
        echo "✅ User has proper access to the database\n";
        
        // Test table access
        try {
            $tables = DB::select("SHOW TABLES");
            echo "✅ Can list tables (" . count($tables) . " tables found)\n";
            
            // Test users table specifically
            $userCount = DB::table('users')->count();
            echo "✅ Can access users table ($userCount records)\n";
            
        } catch (Exception $e) {
            echo "⚠️ Limited table access: " . $e->getMessage() . "\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Still having connection issues: " . $e->getMessage() . "\n";
        echo "Please follow the permission fix steps above.\n";
    }
} else {
    echo "Laravel environment not available for testing.\n";
}

echo "\n=== ALTERNATIVE SOLUTIONS ===\n";
echo "If you can't fix permissions, you can:\n";
echo "1. Create a new database user with full privileges\n";
echo "2. Update your .env file with the new credentials\n";
echo "3. Contact your hosting provider for assistance\n\n";

echo "=== NEXT STEPS AFTER FIXING PERMISSIONS ===\n";
echo "1. Run migrations: php artisan migrate\n";
echo "2. Seed database: php artisan db:seed\n";
echo "3. Test admin login\n";

echo "</pre>";
?>
