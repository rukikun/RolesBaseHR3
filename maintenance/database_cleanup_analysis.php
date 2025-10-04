<?php

/**
 * HR3 System Database Cleanup & Analysis Script
 * 
 * This script analyzes and cleans up the database structure inconsistencies
 * found in the HR3 system, including duplicate migrations and misaligned models.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class DatabaseCleanupAnalyzer
{
    private $issues = [];
    private $recommendations = [];
    
    public function __construct()
    {
        // Load Laravel configuration
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    }
    
    public function analyzeSystem()
    {
        echo "🔍 HR3 System Database Analysis & Cleanup\n";
        echo "==========================================\n\n";
        
        $this->checkDuplicateMigrations();
        $this->analyzeTableStructures();
        $this->validateModelRelationships();
        $this->checkControllerQueries();
        $this->generateCleanupRecommendations();
        
        $this->displayResults();
    }
    
    private function checkDuplicateMigrations()
    {
        echo "📋 Analyzing Migration Files...\n";
        
        $migrationPath = __DIR__ . '/../database/migrations';
        $files = glob($migrationPath . '/*.php');
        
        $duplicates = [
            'employees' => [],
            'time_entries' => [],
            'shifts' => [],
            'shift_types' => [],
            'shift_requests' => [],
            'claims' => [],
            'leave_requests' => []
        ];
        
        foreach ($files as $file) {
            $filename = basename($file);
            
            foreach ($duplicates as $table => &$tableFiles) {
                if (strpos($filename, $table) !== false) {
                    $tableFiles[] = $filename;
                }
            }
        }
        
        foreach ($duplicates as $table => $files) {
            if (count($files) > 1) {
                $this->issues[] = "❌ {$table} table has " . count($files) . " duplicate migrations";
                $this->recommendations[] = "🔧 Consolidate {$table} migrations into single authoritative migration";
            }
        }
        
        echo "   Found " . count($this->issues) . " migration duplication issues\n\n";
    }
    
    private function analyzeTableStructures()
    {
        echo "🗄️ Analyzing Database Table Structures...\n";
        
        try {
            $tables = [
                'employees' => [
                    'required_columns' => ['id', 'first_name', 'last_name', 'email', 'position', 'department', 'status'],
                    'optional_columns' => ['phone', 'hire_date', 'salary', 'online_status', 'last_activity', 'password', 'profile_picture']
                ],
                'time_entries' => [
                    'required_columns' => ['id', 'employee_id', 'work_date', 'clock_in_time', 'clock_out_time', 'status'],
                    'optional_columns' => ['hours_worked', 'overtime_hours', 'break_duration', 'notes', 'approved_by', 'approved_at']
                ],
                'attendances' => [
                    'required_columns' => ['id', 'employee_id', 'date', 'clock_in_time', 'status'],
                    'optional_columns' => ['clock_out_time', 'break_start_time', 'break_end_time', 'total_hours', 'overtime_hours', 'location', 'ip_address']
                ],
                'shifts' => [
                    'required_columns' => ['id', 'employee_id', 'shift_date', 'start_time', 'end_time'],
                    'optional_columns' => ['shift_type_id', 'location', 'break_duration', 'status', 'notes']
                ],
                'shift_types' => [
                    'required_columns' => ['id', 'name', 'start_time', 'end_time'],
                    'optional_columns' => ['description', 'is_active', 'break_duration']
                ]
            ];
            
            foreach ($tables as $tableName => $structure) {
                if (Schema::hasTable($tableName)) {
                    $columns = Schema::getColumnListing($tableName);
                    
                    // Check for missing required columns
                    foreach ($structure['required_columns'] as $requiredCol) {
                        if (!in_array($requiredCol, $columns)) {
                            $this->issues[] = "❌ Table '{$tableName}' missing required column: {$requiredCol}";
                        }
                    }
                    
                    echo "   ✅ {$tableName} table exists with " . count($columns) . " columns\n";
                } else {
                    $this->issues[] = "❌ Required table '{$tableName}' does not exist";
                }
            }
            
        } catch (\Exception $e) {
            $this->issues[] = "❌ Database connection error: " . $e->getMessage();
        }
        
        echo "\n";
    }
    
    private function validateModelRelationships()
    {
        echo "🔗 Validating Model Relationships...\n";
        
        $modelChecks = [
            'Employee' => [
                'file' => __DIR__ . '/../app/Models/Employee.php',
                'relationships' => ['timeEntries', 'shifts', 'leaveRequests', 'claims']
            ],
            'TimeEntry' => [
                'file' => __DIR__ . '/../app/Models/TimeEntry.php',
                'relationships' => ['employee', 'approver']
            ],
            'Attendance' => [
                'file' => __DIR__ . '/../app/Models/Attendance.php',
                'relationships' => ['employee']
            ]
        ];
        
        foreach ($modelChecks as $modelName => $config) {
            if (file_exists($config['file'])) {
                $content = file_get_contents($config['file']);
                
                foreach ($config['relationships'] as $relationship) {
                    if (strpos($content, "function {$relationship}()") !== false) {
                        echo "   ✅ {$modelName} -> {$relationship}() relationship exists\n";
                    } else {
                        $this->issues[] = "❌ {$modelName} missing {$relationship}() relationship";
                    }
                }
            } else {
                $this->issues[] = "❌ Model file not found: {$config['file']}";
            }
        }
        
        echo "\n";
    }
    
    private function checkControllerQueries()
    {
        echo "🎮 Analyzing Controller Database Queries...\n";
        
        $controllers = [
            'HRDashboardController.php',
            'TimesheetController.php',
            'EmployeeESSController.php',
            'AttendanceController.php'
        ];
        
        foreach ($controllers as $controller) {
            $path = __DIR__ . '/../app/Http/Controllers/' . $controller;
            
            if (file_exists($path)) {
                $content = file_get_contents($path);
                
                // Check for common query patterns that might fail
                $problematicPatterns = [
                    'attendances.attendance_date' => 'Should use attendances.date',
                    'time_entries.entry_date' => 'Should use time_entries.work_date',
                    'employees.name' => 'Should use first_name and last_name',
                ];
                
                foreach ($problematicPatterns as $pattern => $suggestion) {
                    if (strpos($content, $pattern) !== false) {
                        $this->issues[] = "❌ {$controller} uses deprecated column: {$pattern}";
                        $this->recommendations[] = "🔧 Update {$controller}: {$suggestion}";
                    }
                }
                
                echo "   ✅ {$controller} analyzed\n";
            }
        }
        
        echo "\n";
    }
    
    private function generateCleanupRecommendations()
    {
        echo "💡 Generating Cleanup Recommendations...\n";
        
        // Add specific cleanup recommendations
        $this->recommendations[] = "🗑️ Remove duplicate migration files (keep only the latest for each table)";
        $this->recommendations[] = "📋 Create single authoritative migration for each core table";
        $this->recommendations[] = "🔄 Run fresh migration to ensure consistent database structure";
        $this->recommendations[] = "🧹 Update controllers to use correct column names";
        $this->recommendations[] = "📊 Add missing database indexes for performance";
        $this->recommendations[] = "🔗 Validate all foreign key constraints";
        
        echo "   Generated " . count($this->recommendations) . " recommendations\n\n";
    }
    
    private function displayResults()
    {
        echo "📊 ANALYSIS RESULTS\n";
        echo "==================\n\n";
        
        echo "🚨 ISSUES FOUND (" . count($this->issues) . "):\n";
        foreach ($this->issues as $issue) {
            echo "   {$issue}\n";
        }
        
        echo "\n💡 RECOMMENDATIONS (" . count($this->recommendations) . "):\n";
        foreach ($this->recommendations as $recommendation) {
            echo "   {$recommendation}\n";
        }
        
        echo "\n🎯 PRIORITY ACTIONS:\n";
        echo "   1. Backup current database\n";
        echo "   2. Remove duplicate migrations\n";
        echo "   3. Create clean migration structure\n";
        echo "   4. Update controller queries\n";
        echo "   5. Test all functionality\n";
        
        echo "\n✅ Analysis complete! Review recommendations above.\n";
    }
    
    public function createCleanupScript()
    {
        echo "\n🛠️ Creating automated cleanup script...\n";
        
        $cleanupScript = $this->generateCleanupScript();
        file_put_contents(__DIR__ . '/database_cleanup_script.php', $cleanupScript);
        
        echo "   ✅ Cleanup script created: maintenance/database_cleanup_script.php\n";
    }
    
    private function generateCleanupScript()
    {
        return '<?php
/**
 * Automated Database Cleanup Script
 * Generated by Database Analysis Tool
 */

// This script will be implemented based on analysis results
echo "Database cleanup script - Implementation pending based on analysis results\n";
';
    }
}

// Run the analysis
try {
    $analyzer = new DatabaseCleanupAnalyzer();
    $analyzer->analyzeSystem();
    $analyzer->createCleanupScript();
} catch (Exception $e) {
    echo "❌ Error running analysis: " . $e->getMessage() . "\n";
    echo "💡 Make sure you're running this from the project root directory\n";
}
