<?php
/**
 * Fix Production Database Name Issue
 * This script fixes the hardcoded database name in Employee model
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING PRODUCTION DATABASE NAME ISSUE ===\n\n";

try {
    // Get current database configuration
    $actualDbName = DB::connection()->getDatabaseName();
    echo "1. Current Production Database: $actualDbName\n";
    
    // Path to Employee model
    $modelPath = __DIR__ . '/app/Models/Employee.php';
    
    if (!file_exists($modelPath)) {
        throw new Exception("Employee model not found at: $modelPath");
    }
    
    echo "2. Reading Employee model...\n";
    $modelContent = file_get_contents($modelPath);
    
    // Check if hardcoded database exists
    if (strpos($modelContent, "hr3systemdb") !== false) {
        echo "3. Found hardcoded database name 'hr3systemdb'\n";
        
        // Replace hardcoded database name with dynamic one
        $updatedContent = str_replace(
            "'hr3systemdb'", 
            "env('DB_DATABASE', 'hr3systemdb')", 
            $modelContent
        );
        
        // Create backup
        $backupPath = $modelPath . '.backup.' . date('Y-m-d-H-i-s');
        file_put_contents($backupPath, $modelContent);
        echo "4. Created backup: " . basename($backupPath) . "\n";
        
        // Write updated content
        file_put_contents($modelPath, $updatedContent);
        echo "5. ✅ Updated Employee model to use dynamic database name\n";
        
        // Clear Laravel caches
        echo "6. Clearing Laravel caches...\n";
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        echo "   ✅ Caches cleared\n";
        
        // Test the fix
        echo "\n7. Testing the fix...\n";
        $employeeCount = \App\Models\Employee::count();
        echo "   Employee count after fix: $employeeCount\n";
        
        if ($employeeCount > 0) {
            echo "   ✅ SUCCESS! Employees are now accessible\n";
            
            // Test other models that might have similar issues
            echo "\n8. Testing other potential issues...\n";
            
            // Check if shift_types table exists and has data
            try {
                $shiftTypesCount = DB::table('shift_types')->count();
                echo "   Shift Types: $shiftTypesCount\n";
                if ($shiftTypesCount == 0) {
                    echo "   ⚠️  No shift types found - need to seed default data\n";
                }
            } catch (Exception $e) {
                echo "   ❌ shift_types table missing\n";
            }
            
            // Check claim_types
            try {
                $claimTypesCount = DB::table('claim_types')->count();
                echo "   Claim Types: $claimTypesCount\n";
                if ($claimTypesCount == 0) {
                    echo "   ⚠️  No claim types found - need to seed default data\n";
                }
            } catch (Exception $e) {
                echo "   ❌ claim_types table missing\n";
            }
            
        } else {
            echo "   ❌ Still no employees found - there may be other issues\n";
        }
        
    } else {
        echo "3. No hardcoded database name found in Employee model\n";
        echo "   The issue might be elsewhere...\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIX COMPLETE ===\n";
echo "Next steps:\n";
echo "1. Test the application in browser\n";
echo "2. If still no data, run: php artisan migrate\n";
echo "3. Seed default data if needed\n";
