<?php

/**
 * Database Cleanup Verification Script
 * 
 * This script verifies that the database cleanup was successful
 * and all HR3 system functionality is working correctly
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseCleanupVerifier
{
    private $results = [];
    
    public function __construct()
    {
        // Load Laravel configuration
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    }
    
    public function runVerification()
    {
        echo "✅ HR3 System Database Cleanup Verification\n";
        echo "==========================================\n\n";
        
        $this->verifyTables();
        $this->verifyColumns();
        $this->verifyIndexes();
        $this->verifyRelationships();
        $this->testBasicQueries();
        
        $this->displayResults();
    }
    
    private function verifyTables()
    {
        echo "🗄️ Verifying Database Tables...\n";
        
        $requiredTables = [
            'users',
            'employees', 
            'time_entries',
            'attendances',
            'shift_types',
            'shifts',
            'shift_requests',
            'leave_types',
            'leave_requests',
            'claim_types',
            'claims',
            'ai_generated_timesheets'
        ];
        
        foreach ($requiredTables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                echo "   ✅ {$table} (exists, {$count} records)\n";
                $this->results['tables'][$table] = 'EXISTS';
            } else {
                echo "   ❌ {$table} (missing)\n";
                $this->results['tables'][$table] = 'MISSING';
            }
        }
        echo "\n";
    }
    
    private function verifyColumns()
    {
        echo "📋 Verifying Key Columns...\n";
        
        $columnChecks = [
            'employees' => ['first_name', 'last_name', 'email', 'position', 'department', 'status', 'online_status'],
            'time_entries' => ['employee_id', 'work_date', 'clock_in_time', 'clock_out_time', 'status'],
            'attendances' => ['employee_id', 'date', 'clock_in_time', 'status'],
            'shift_requests' => ['employee_id', 'requested_date', 'status', 'approved_by']
        ];
        
        foreach ($columnChecks as $table => $columns) {
            if (Schema::hasTable($table)) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        echo "   ✅ {$table}.{$column}\n";
                    } else {
                        echo "   ❌ {$table}.{$column} (missing)\n";
                        $this->results['columns'][] = "{$table}.{$column} MISSING";
                    }
                }
            }
        }
        echo "\n";
    }
    
    private function verifyIndexes()
    {
        echo "⚡ Verifying Database Indexes...\n";
        
        try {
            $indexes = DB::select("
                SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME 
                FROM information_schema.statistics 
                WHERE table_schema = DATABASE() 
                AND table_name IN ('employees', 'time_entries', 'attendances', 'shifts')
                ORDER BY TABLE_NAME, INDEX_NAME
            ");
            
            $indexCount = count($indexes);
            echo "   ✅ Found {$indexCount} database indexes\n";
            
            // Check for specific important indexes
            $importantIndexes = [
                'employees' => ['email', 'status'],
                'time_entries' => ['employee_id', 'work_date'],
                'attendances' => ['employee_id', 'date']
            ];
            
            foreach ($importantIndexes as $table => $columns) {
                foreach ($columns as $column) {
                    $hasIndex = collect($indexes)->where('TABLE_NAME', $table)
                                                ->where('COLUMN_NAME', $column)
                                                ->count() > 0;
                    if ($hasIndex) {
                        echo "   ✅ Index on {$table}.{$column}\n";
                    } else {
                        echo "   ⚠️ Missing index on {$table}.{$column}\n";
                    }
                }
            }
            
        } catch (Exception $e) {
            echo "   ⚠️ Could not verify indexes: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    private function verifyRelationships()
    {
        echo "🔗 Verifying Foreign Key Relationships...\n";
        
        try {
            $foreignKeys = DB::select("
                SELECT 
                    TABLE_NAME,
                    COLUMN_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            $fkCount = count($foreignKeys);
            echo "   ✅ Found {$fkCount} foreign key relationships\n";
            
            foreach ($foreignKeys as $fk) {
                echo "   🔗 {$fk->TABLE_NAME}.{$fk->COLUMN_NAME} → {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
            }
            
        } catch (Exception $e) {
            echo "   ⚠️ Could not verify foreign keys: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    private function testBasicQueries()
    {
        echo "🧪 Testing Basic Database Queries...\n";
        
        $queries = [
            'Employee Count' => "SELECT COUNT(*) as count FROM employees",
            'Time Entries Today' => "SELECT COUNT(*) as count FROM time_entries WHERE work_date = CURDATE()",
            'Attendance Records' => "SELECT COUNT(*) as count FROM attendances",
            'Active Shift Types' => "SELECT COUNT(*) as count FROM shift_types WHERE is_active = 1",
            'Pending Requests' => "SELECT COUNT(*) as count FROM shift_requests WHERE status = 'pending'"
        ];
        
        foreach ($queries as $name => $sql) {
            try {
                $result = DB::select($sql);
                $count = $result[0]->count ?? 0;
                echo "   ✅ {$name}: {$count} records\n";
                $this->results['queries'][$name] = 'SUCCESS';
            } catch (Exception $e) {
                echo "   ❌ {$name}: " . $e->getMessage() . "\n";
                $this->results['queries'][$name] = 'FAILED';
            }
        }
        echo "\n";
    }
    
    private function displayResults()
    {
        echo "📊 VERIFICATION SUMMARY\n";
        echo "======================\n\n";
        
        // Count successes and failures
        $tableCount = count($this->results['tables'] ?? []);
        $tableSuccess = count(array_filter($this->results['tables'] ?? [], fn($v) => $v === 'EXISTS'));
        
        $queryCount = count($this->results['queries'] ?? []);
        $querySuccess = count(array_filter($this->results['queries'] ?? [], fn($v) => $v === 'SUCCESS'));
        
        echo "🗄️ Tables: {$tableSuccess}/{$tableCount} verified\n";
        echo "🧪 Queries: {$querySuccess}/{$queryCount} successful\n";
        
        if (isset($this->results['columns']) && count($this->results['columns']) > 0) {
            echo "⚠️ Missing Columns: " . count($this->results['columns']) . "\n";
            foreach ($this->results['columns'] as $missing) {
                echo "   - {$missing}\n";
            }
        }
        
        echo "\n🎯 CLEANUP STATUS: ";
        if ($tableSuccess === $tableCount && $querySuccess === $queryCount) {
            echo "✅ SUCCESSFUL\n";
            echo "\n🚀 Your HR3 system database is now clean and optimized!\n";
            echo "📋 Next steps:\n";
            echo "   1. Test HR Dashboard functionality\n";
            echo "   2. Test ESS clock-in/out features\n";
            echo "   3. Verify timesheet management\n";
            echo "   4. Check AI timesheet generation\n";
        } else {
            echo "⚠️ ISSUES DETECTED\n";
            echo "\n🔧 Please review the issues above and fix any missing components.\n";
        }
    }
}

// Run the verification
try {
    $verifier = new DatabaseCleanupVerifier();
    $verifier->runVerification();
} catch (Exception $e) {
    echo "❌ Verification failed: " . $e->getMessage() . "\n";
}
