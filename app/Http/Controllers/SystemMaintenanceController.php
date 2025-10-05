<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Console\Commands\SetupEmployeesTable;
use Exception;

class SystemMaintenanceController extends Controller
{
    /**
     * Quick fix for negative attendance hours
     */
    public function quickFixNegativeHours()
    {
        try {
            // Simple approach: convert all negative total_hours to positive
            $negativeRecords = DB::table('attendances')->where('total_hours', '<', 0)->get();
            $fixedCount = 0;
            
            foreach ($negativeRecords as $record) {
                $positiveHours = abs($record->total_hours);
                DB::table('attendances')
                    ->where('id', $record->id)
                    ->update([
                        'total_hours' => $positiveHours,
                        'updated_at' => now()
                    ]);
                $fixedCount++;
            }
            
            return response()->json([
                'success' => true,
                'message' => "Quick fix completed: converted {$fixedCount} negative hours to positive",
                'fixed_count' => $fixedCount
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * Fix invalid shift IDs in database
     */
    public function fixInvalidShifts()
    {
        try {
            $results = [];
            
            // Find shifts with invalid IDs
            $invalidShifts = DB::table('shifts')->where('id', '<=', 0)->get();
            $results['invalid_shifts_found'] = $invalidShifts->count();
            
            if ($invalidShifts->count() > 0) {
                $results['invalid_shifts'] = $invalidShifts->toArray();
                
                // Delete shifts with invalid IDs
                $deleted = DB::table('shifts')->where('id', '<=', 0)->delete();
                $results['deleted_invalid_shifts'] = $deleted;
            }
            
            // Check for any shifts that might have been created incorrectly
            $duplicateShifts = DB::table('shifts')
                ->select('employee_id', 'shift_date', DB::raw('COUNT(*) as count'))
                ->groupBy('employee_id', 'shift_date')
                ->having('count', '>', 1)
                ->get();
                
            $results['duplicate_shifts'] = $duplicateShifts->count();
            
            // Get current shift statistics
            $results['current_stats'] = [
                'total_shifts' => DB::table('shifts')->count(),
                'valid_shifts' => DB::table('shifts')->where('id', '>', 0)->count(),
                'min_id' => DB::table('shifts')->min('id'),
                'max_id' => DB::table('shifts')->max('id')
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Invalid shifts cleanup completed',
                'results' => $results
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Verify shifts fix
     */
    public function verifyShiftsFix()
    {
        try {
            $stats = [
                'total_shifts' => DB::table('shifts')->count(),
                'valid_shifts' => DB::table('shifts')->where('id', '>', 0)->count(),
                'invalid_shifts' => DB::table('shifts')->where('id', '<=', 0)->count(),
                'id_range' => [
                    'min' => DB::table('shifts')->min('id'),
                    'max' => DB::table('shifts')->max('id')
                ],
                'auto_increment_info' => DB::select("SHOW TABLE STATUS LIKE 'shifts'")[0] ?? null
            ];
            
            $message = $stats['invalid_shifts'] > 0 ? 
                'WARNING: Still have invalid shifts!' : 
                'SUCCESS: All shifts have valid IDs!';
                
            return response()->json([
                'status' => $stats['invalid_shifts'] > 0 ? 'warning' : 'success',
                'message' => $message,
                'statistics' => $stats
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Setup employees table
     */
    public function setupEmployees()
    {
        try {
            $command = new SetupEmployeesTable();
            $result = $command->handle();
            
            return response()->json([
                'success' => $result === 0,
                'message' => 'Employees table setup completed',
                'employees_count' => DB::table('employees')->count()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Setup failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Fix shift calendar data issues
     */
    public function fixShiftCalendarData()
    {
        try {
            $results = [];
            
            // Check current state
            $totalShifts = DB::table('shifts')->count();
            $validShifts = DB::table('shifts')->where('id', '>', 0)->count();
            $invalidShifts = DB::table('shifts')->where('id', '<=', 0)->count();
            
            $results[] = "📊 Current State: {$totalShifts} total shifts, {$validShifts} valid, {$invalidShifts} invalid";
            
            // Remove invalid shifts
            if ($invalidShifts > 0) {
                $deleted = DB::table('shifts')->where('id', '<=', 0)->delete();
                $results[] = "🗑️ Removed {$deleted} invalid shifts";
            }
            
            // Check for duplicates
            $duplicates = DB::table('shifts')
                ->select('employee_id', 'shift_date', 'shift_type_id', DB::raw('COUNT(*) as count'))
                ->groupBy('employee_id', 'shift_date', 'shift_type_id')
                ->having('count', '>', 1)
                ->get();
            
            if ($duplicates->count() > 0) {
                $results[] = "🔍 Found {$duplicates->count()} sets of duplicate shifts";
                
                $totalDuplicatesRemoved = 0;
                foreach ($duplicates as $duplicate) {
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
                        $totalDuplicatesRemoved++;
                    }
                }
                
                $results[] = "✅ Removed {$totalDuplicatesRemoved} duplicate shifts";
            } else {
                $results[] = "✅ No duplicate shifts found";
            }
            
            // Remove orphaned shifts
            $orphanedEmployees = DB::table('shifts')
                ->leftJoin('employees', 'shifts.employee_id', '=', 'employees.id')
                ->whereNull('employees.id')
                ->delete();
            
            $orphanedShiftTypes = DB::table('shifts')
                ->leftJoin('shift_types', 'shifts.shift_type_id', '=', 'shift_types.id')
                ->whereNull('shift_types.id')
                ->delete();
            
            if ($orphanedEmployees > 0) {
                $results[] = "🧹 Removed {$orphanedEmployees} shifts with invalid employees";
            }
            
            if ($orphanedShiftTypes > 0) {
                $results[] = "🧹 Removed {$orphanedShiftTypes} shifts with invalid shift types";
            }
            
            // Final state
            $finalShifts = DB::table('shifts')->count();
            $results[] = "📊 Final State: {$finalShifts} clean shifts remaining";
            
            return response()->json([
                'success' => true,
                'message' => 'Shift calendar data cleanup completed successfully!',
                'details' => $results
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cleanup failed: ' . $e->getMessage(),
                'error' => $e->getTraceAsString()
            ]);
        }
    }
}
