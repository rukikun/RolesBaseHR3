<?php
/**
 * Fix Shift Calendar Data Issues
 * 
 * This script fixes issues with the shift schedule calendar showing
 * duplicate or incorrect data by cleaning up the database and
 * ensuring data integrity.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load Laravel environment
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "🔧 Fixing Shift Calendar Data Issues...\n\n";

try {
    // 1. Check current database state
    echo "📊 Current Database State:\n";
    $totalShifts = DB::table('shifts')->count();
    $validShifts = DB::table('shifts')->where('id', '>', 0)->count();
    $invalidShifts = DB::table('shifts')->where('id', '<=', 0)->count();
    $employees = DB::table('employees')->where('status', 'active')->count();
    $shiftTypes = DB::table('shift_types')->where('is_active', 1)->count();
    
    echo "   Total Shifts: {$totalShifts}\n";
    echo "   Valid Shifts: {$validShifts}\n";
    echo "   Invalid Shifts: {$invalidShifts}\n";
    echo "   Active Employees: {$employees}\n";
    echo "   Active Shift Types: {$shiftTypes}\n\n";
    
    // 2. Remove any shifts with invalid IDs
    if ($invalidShifts > 0) {
        echo "🗑️ Removing {$invalidShifts} invalid shifts...\n";
        $deleted = DB::table('shifts')->where('id', '<=', 0)->delete();
        echo "   Deleted {$deleted} invalid shifts\n\n";
    }
    
    // 3. Check for duplicate shifts (same employee, same date, same shift type)
    echo "🔍 Checking for duplicate shifts...\n";
    $duplicates = DB::table('shifts')
        ->select('employee_id', 'shift_date', 'shift_type_id', DB::raw('COUNT(*) as count'))
        ->groupBy('employee_id', 'shift_date', 'shift_type_id')
        ->having('count', '>', 1)
        ->get();
    
    if ($duplicates->count() > 0) {
        echo "   Found {$duplicates->count()} sets of duplicate shifts\n";
        
        foreach ($duplicates as $duplicate) {
            echo "   - Employee {$duplicate->employee_id}, Date {$duplicate->shift_date}, Type {$duplicate->shift_type_id}: {$duplicate->count} duplicates\n";
            
            // Keep only the first shift, delete the rest
            $shifts = DB::table('shifts')
                ->where('employee_id', $duplicate->employee_id)
                ->where('shift_date', $duplicate->shift_date)
                ->where('shift_type_id', $duplicate->shift_type_id)
                ->orderBy('id')
                ->get();
            
            $keepFirst = true;
            foreach ($shifts as $shift) {
                if ($keepFirst) {
                    $keepFirst = false;
                    continue;
                }
                
                DB::table('shifts')->where('id', $shift->id)->delete();
                echo "     Deleted duplicate shift ID {$shift->id}\n";
            }
        }
        echo "\n";
    } else {
        echo "   No duplicate shifts found\n\n";
    }
    
    // 4. Verify shift data integrity
    echo "🔧 Verifying shift data integrity...\n";
    $shiftsWithoutEmployees = DB::table('shifts')
        ->leftJoin('employees', 'shifts.employee_id', '=', 'employees.id')
        ->whereNull('employees.id')
        ->count();
    
    $shiftsWithoutShiftTypes = DB::table('shifts')
        ->leftJoin('shift_types', 'shifts.shift_type_id', '=', 'shift_types.id')
        ->whereNull('shift_types.id')
        ->count();
    
    echo "   Shifts without valid employees: {$shiftsWithoutEmployees}\n";
    echo "   Shifts without valid shift types: {$shiftsWithoutShiftTypes}\n";
    
    // Remove orphaned shifts
    if ($shiftsWithoutEmployees > 0) {
        $deleted = DB::table('shifts')
            ->leftJoin('employees', 'shifts.employee_id', '=', 'employees.id')
            ->whereNull('employees.id')
            ->delete();
        echo "   Removed {$deleted} shifts with invalid employees\n";
    }
    
    if ($shiftsWithoutShiftTypes > 0) {
        $deleted = DB::table('shifts')
            ->leftJoin('shift_types', 'shifts.shift_type_id', '=', 'shift_types.id')
            ->whereNull('shift_types.id')
            ->delete();
        echo "   Removed {$deleted} shifts with invalid shift types\n";
    }
    
    echo "\n";
    
    // 5. Show final state
    echo "📊 Final Database State:\n";
    $finalShifts = DB::table('shifts')->count();
    $currentMonthShifts = DB::table('shifts')
        ->whereMonth('shift_date', Carbon::now()->month)
        ->whereYear('shift_date', Carbon::now()->year)
        ->count();
    
    echo "   Total Shifts: {$finalShifts}\n";
    echo "   Current Month Shifts: {$currentMonthShifts}\n";
    
    // Show sample of current shifts
    echo "\n📋 Sample Current Shifts:\n";
    $sampleShifts = DB::table('shifts')
        ->join('employees', 'shifts.employee_id', '=', 'employees.id')
        ->join('shift_types', 'shifts.shift_type_id', '=', 'shift_types.id')
        ->select(
            'shifts.id',
            'shifts.shift_date',
            'employees.first_name',
            'employees.last_name',
            'shift_types.name as shift_type',
            'shifts.start_time',
            'shifts.end_time'
        )
        ->orderBy('shifts.shift_date', 'desc')
        ->limit(10)
        ->get();
    
    foreach ($sampleShifts as $shift) {
        echo "   ID {$shift->id}: {$shift->first_name} {$shift->last_name} - {$shift->shift_type} on {$shift->shift_date} ({$shift->start_time}-{$shift->end_time})\n";
    }
    
    echo "\n✅ Shift calendar data cleanup completed successfully!\n";
    echo "🔄 Please refresh the shift schedule page to see the corrected data.\n";
    
} catch (Exception $e) {
    echo "❌ Error during cleanup: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    exit(1);
}
