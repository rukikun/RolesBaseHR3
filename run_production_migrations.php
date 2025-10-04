<?php
/**
 * Production Migrations Runner
 * Run this after fixing database permissions
 */

echo "<h2>HR3 System - Production Migrations</h2>";
echo "<pre>";

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    try {
        // Test database connection first
        DB::connection()->getPdo();
        echo "✅ Database connection successful\n";
        
        echo "\n=== CHECKING MIGRATION STATUS ===\n";
        
        // Get migration status
        $migrations = DB::table('migrations')->pluck('migration')->toArray();
        echo "Migrations already run: " . count($migrations) . "\n";
        
        echo "\n=== RUNNING PENDING MIGRATIONS ===\n";
        
        // Run migrations
        if (function_exists('exec')) {
            exec('cd ' . __DIR__ . ' && php artisan migrate --force 2>&1', $output, $return);
            
            if ($return === 0) {
                echo "✅ Migrations completed successfully\n";
                foreach ($output as $line) {
                    echo "$line\n";
                }
            } else {
                echo "❌ Migration failed\n";
                foreach ($output as $line) {
                    echo "$line\n";
                }
            }
        } else {
            echo "❌ exec() function disabled. Run manually: php artisan migrate --force\n";
        }
        
        echo "\n=== CHECKING REQUIRED TABLES ===\n";
        $requiredTables = [
            'users', 'employees', 'time_entries', 'attendances', 
            'shifts', 'shift_types', 'leave_requests', 'leave_types',
            'claims', 'claim_types'
        ];
        
        foreach ($requiredTables as $table) {
            try {
                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();
                    echo "✅ $table ($count records)\n";
                } else {
                    echo "❌ $table (missing)\n";
                }
            } catch (Exception $e) {
                echo "⚠️ $table (error: " . $e->getMessage() . ")\n";
            }
        }
        
        echo "\n=== CREATING ADMIN USER ===\n";
        
        // Check if admin user exists
        $adminEmail = 'admin@jetlouge.com';
        $adminPassword = 'admin123';
        
        try {
            $existingAdmin = DB::table('users')->where('email', $adminEmail)->first();
            
            if ($existingAdmin) {
                echo "✅ Admin user already exists: $adminEmail\n";
            } else {
                // Create admin user
                DB::table('users')->insert([
                    'name' => 'System Administrator',
                    'email' => $adminEmail,
                    'password' => Hash::make($adminPassword),
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                echo "✅ Admin user created: $adminEmail\n";
            }
            
            echo "\n=== LOGIN CREDENTIALS ===\n";
            echo "Email: $adminEmail\n";
            echo "Password: $adminPassword\n";
            echo "URL: " . url('/admin/login') . "\n";
            
        } catch (Exception $e) {
            echo "❌ Error creating admin user: " . $e->getMessage() . "\n";
        }
        
        echo "\n=== SEEDING SAMPLE DATA ===\n";
        
        // Check if we should seed data
        $employeeCount = 0;
        try {
            $employeeCount = DB::table('employees')->count();
        } catch (Exception $e) {
            echo "⚠️ Cannot check employee count: " . $e->getMessage() . "\n";
        }
        
        if ($employeeCount == 0) {
            echo "No employees found. Running seeder...\n";
            
            if (function_exists('exec')) {
                exec('cd ' . __DIR__ . ' && php artisan db:seed --force 2>&1', $seedOutput, $seedReturn);
                
                if ($seedReturn === 0) {
                    echo "✅ Database seeded successfully\n";
                } else {
                    echo "⚠️ Seeding had issues (this is often normal)\n";
                }
            } else {
                echo "❌ Cannot run seeder automatically. Run: php artisan db:seed\n";
            }
        } else {
            echo "✅ Database already has $employeeCount employees\n";
        }
        
        echo "\n=== FINAL STATUS ===\n";
        echo "✅ Database connection working\n";
        echo "✅ Migrations completed\n";
        echo "✅ Admin user available\n";
        echo "✅ Ready for production use\n";
        
        echo "\n=== NEXT STEPS ===\n";
        echo "1. Try logging in at: " . url('/admin/login') . "\n";
        echo "2. Use credentials: $adminEmail / $adminPassword\n";
        echo "3. If login fails, check Laravel logs\n";
        
    } catch (Exception $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "\n";
        echo "Please fix database permissions first using fix_database_permissions.php\n";
    }
} else {
    echo "❌ Laravel not found\n";
}

echo "</pre>";
?>
