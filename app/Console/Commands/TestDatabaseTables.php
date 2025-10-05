<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Employee;
use App\Models\TimeEntry;
use App\Models\Attendance;
use App\Models\ShiftType;
use App\Models\Shift;
use App\Models\ShiftRequest;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\ClaimType;
use App\Models\Claim;
use App\Models\AIGeneratedTimesheet;

class TestDatabaseTables extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'db:test-tables {--quick : Run quick verification only}';

    /**
     * The console command description.
     */
    protected $description = 'Test all database tables to verify they are working correctly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Starting Database Table Tests...');
        $this->newLine();

        if ($this->option('quick')) {
            $this->runQuickTests();
        } else {
            $this->runComprehensiveTests();
        }

        $this->newLine();
        $this->info('✅ Database table tests completed successfully!');
    }

    /**
     * Run quick verification tests
     */
    private function runQuickTests()
    {
        $this->info('📋 Running Quick Table Verification Tests');
        $this->line('==========================================');

        $tables = [
            'Users' => User::class,
            'Employees' => Employee::class,
            'Time Entries' => TimeEntry::class,
            'Attendances' => Attendance::class,
            'Shift Types' => ShiftType::class,
            'Shifts' => Shift::class,
            'Shift Requests' => ShiftRequest::class,
            'Leave Types' => LeaveType::class,
            'Leave Requests' => LeaveRequest::class,
            'Claim Types' => ClaimType::class,
            'Claims' => Claim::class,
            'AI Generated Timesheets' => AIGeneratedTimesheet::class,
        ];

        foreach ($tables as $tableName => $modelClass) {
            try {
                $count = $modelClass::count();
                $this->line("✅ {$tableName}: {$count} records");
                
                // Show sample data for tables with records
                if ($count > 0) {
                    $sample = $modelClass::take(1)->first();
                    if ($sample) {
                        $this->line("   Sample ID: {$sample->id}");
                    }
                }
            } catch (\Exception $e) {
                $this->error("❌ {$tableName}: Error - " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->runSummaryStats();
    }

    /**
     * Run comprehensive tests
     */
    private function runComprehensiveTests()
    {
        $this->info('📊 Running Comprehensive Table Tests');
        $this->line('====================================');

        // Test 1: Basic table functionality
        $this->testBasicTableFunctionality();

        // Test 2: Relationship integrity
        $this->testRelationshipIntegrity();

        // Test 3: Data consistency
        $this->testDataConsistency();

        // Test 4: Performance indicators
        $this->testPerformanceIndicators();

        // Test 5: Summary statistics
        $this->runSummaryStats();
    }

    /**
     * Test basic table functionality
     */
    private function testBasicTableFunctionality()
    {
        $this->info('🔧 Testing Basic Table Functionality');
        $this->line('-----------------------------------');

        try {
            // Test Users table
            $userCount = User::count();
            $this->line("Users: {$userCount} records");

            // Test Employees table with relationships
            $activeEmployees = Employee::where('status', 'active')->count();
            $this->line("Active Employees: {$activeEmployees} records");

            // Test Time Entries with employee relationship
            $timeEntries = TimeEntry::with('employee')->count();
            $this->line("Time Entries: {$timeEntries} records");

            // Test Attendances with employee relationship
            $attendances = Attendance::with('employee')->count();
            $this->line("Attendances: {$attendances} records");

            // Test Shift Types
            $activeShiftTypes = ShiftType::where('is_active', true)->count();
            $this->line("Active Shift Types: {$activeShiftTypes} records");

            // Test Shifts with relationships
            $shifts = Shift::with(['employee', 'shiftType'])->count();
            $this->line("Shifts: {$shifts} records");

            // Test Leave Types
            $activeLeaveTypes = LeaveType::where('is_active', true)->count();
            $this->line("Active Leave Types: {$activeLeaveTypes} records");

            // Test Leave Requests with relationships
            $leaveRequests = LeaveRequest::with(['employee', 'leaveType'])->count();
            $this->line("Leave Requests: {$leaveRequests} records");

            // Test Claim Types
            $activeClaimTypes = ClaimType::where('is_active', true)->count();
            $this->line("Active Claim Types: {$activeClaimTypes} records");

            // Test Claims with relationships
            $claims = Claim::with(['employee', 'claimType'])->count();
            $this->line("Claims: {$claims} records");

            // Test AI Generated Timesheets
            $aiTimesheets = AIGeneratedTimesheet::count();
            $this->line("AI Generated Timesheets: {$aiTimesheets} records");

            $this->info('✅ Basic functionality tests passed');

        } catch (\Exception $e) {
            $this->error('❌ Basic functionality test failed: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Test relationship integrity
     */
    private function testRelationshipIntegrity()
    {
        $this->info('🔗 Testing Relationship Integrity');
        $this->line('--------------------------------');

        try {
            // Test Employee relationships
            $employeeWithTimeEntries = Employee::has('timeEntries')->count();
            $this->line("Employees with Time Entries: {$employeeWithTimeEntries}");

            $employeeWithAttendances = Employee::has('attendances')->count();
            $this->line("Employees with Attendances: {$employeeWithAttendances}");

            $employeeWithShifts = Employee::has('shifts')->count();
            $this->line("Employees with Shifts: {$employeeWithShifts}");

            $employeeWithLeaveRequests = Employee::has('leaveRequests')->count();
            $this->line("Employees with Leave Requests: {$employeeWithLeaveRequests}");

            $employeeWithClaims = Employee::has('claims')->count();
            $this->line("Employees with Claims: {$employeeWithClaims}");

            // Test orphaned records
            $orphanedTimeEntries = TimeEntry::whereDoesntHave('employee')->count();
            $orphanedAttendances = Attendance::whereDoesntHave('employee')->count();
            $orphanedShifts = Shift::whereDoesntHave('employee')->count();

            if ($orphanedTimeEntries > 0 || $orphanedAttendances > 0 || $orphanedShifts > 0) {
                $this->warn("⚠️  Found orphaned records:");
                $this->line("   Time Entries: {$orphanedTimeEntries}");
                $this->line("   Attendances: {$orphanedAttendances}");
                $this->line("   Shifts: {$orphanedShifts}");
            } else {
                $this->info('✅ No orphaned records found');
            }

        } catch (\Exception $e) {
            $this->error('❌ Relationship integrity test failed: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Test data consistency
     */
    private function testDataConsistency()
    {
        $this->info('📏 Testing Data Consistency');
        $this->line('-------------------------');

        try {
            // Check for negative hours
            $negativeTimeEntries = TimeEntry::where('hours_worked', '<', 0)
                ->orWhere('overtime_hours', '<', 0)
                ->count();

            $negativeAttendances = Attendance::where('total_hours', '<', 0)
                ->orWhere('overtime_hours', '<', 0)
                ->count();

            if ($negativeTimeEntries > 0 || $negativeAttendances > 0) {
                $this->warn("⚠️  Found records with negative hours:");
                $this->line("   Time Entries: {$negativeTimeEntries}");
                $this->line("   Attendances: {$negativeAttendances}");
            } else {
                $this->info('✅ No negative hours found');
            }

            // Check for invalid date ranges in leave requests
            $invalidLeaveRequests = LeaveRequest::whereColumn('start_date', '>', 'end_date')->count();
            if ($invalidLeaveRequests > 0) {
                $this->warn("⚠️  Found {$invalidLeaveRequests} leave requests with invalid date ranges");
            } else {
                $this->info('✅ All leave request date ranges are valid');
            }

            // Check for duplicate attendance records (should be unique per employee per day)
            $duplicateAttendances = DB::table('attendances')
                ->select('employee_id', 'date')
                ->groupBy('employee_id', 'date')
                ->havingRaw('COUNT(*) > 1')
                ->count();

            if ($duplicateAttendances > 0) {
                $this->warn("⚠️  Found {$duplicateAttendances} duplicate attendance records");
            } else {
                $this->info('✅ No duplicate attendance records found');
            }

        } catch (\Exception $e) {
            $this->error('❌ Data consistency test failed: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Test performance indicators
     */
    private function testPerformanceIndicators()
    {
        $this->info('⚡ Testing Performance Indicators');
        $this->line('-------------------------------');

        try {
            // Test index usage by running common queries
            $start = microtime(true);
            
            // Employee lookup by email (should use index)
            Employee::where('email', 'john.doe@jetlouge.com')->first();
            
            // Attendance lookup by employee and date (should use composite index)
            Attendance::where('employee_id', 1)->where('date', '2025-10-04')->first();
            
            // Active shift types (should use index)
            ShiftType::where('is_active', true)->get();
            
            // Time entries by status (should use index)
            TimeEntry::where('status', 'pending')->get();

            $end = microtime(true);
            $executionTime = round(($end - $start) * 1000, 2);

            $this->info("✅ Common queries executed in {$executionTime}ms");

        } catch (\Exception $e) {
            $this->error('❌ Performance test failed: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Run summary statistics
     */
    private function runSummaryStats()
    {
        $this->info('📈 System Summary Statistics');
        $this->line('===========================');

        try {
            $stats = [
                'Total Users' => User::count(),
                'Active Employees' => Employee::where('status', 'active')->count(),
                'Online Employees' => Employee::where('online_status', 'online')->count(),
                'Total Time Entries' => TimeEntry::count(),
                'Pending Time Entries' => TimeEntry::where('status', 'pending')->count(),
                'Total Attendances' => Attendance::count(),
                'Today\'s Attendances' => Attendance::whereDate('date', today())->count(),
                'Active Shift Types' => ShiftType::where('is_active', true)->count(),
                'Scheduled Shifts' => Shift::count(),
                'Pending Leave Requests' => LeaveRequest::where('status', 'pending')->count(),
                'Pending Claims' => Claim::where('status', 'pending')->count(),
                'AI Timesheets Generated' => AIGeneratedTimesheet::count(),
            ];

            foreach ($stats as $label => $value) {
                $this->line("📊 {$label}: {$value}");
            }

            // Department breakdown
            $this->newLine();
            $this->info('👥 Department Breakdown');
            $departments = Employee::where('status', 'active')
                ->select('department', DB::raw('COUNT(*) as count'))
                ->groupBy('department')
                ->orderBy('count', 'desc')
                ->get();

            foreach ($departments as $dept) {
                $this->line("   {$dept->department}: {$dept->count} employees");
            }

        } catch (\Exception $e) {
            $this->error('❌ Summary statistics failed: ' . $e->getMessage());
        }
    }
}
