<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use Exception;

class SystemDebugController extends Controller
{
    /**
     * Debug employee data
     */
    public function debugEmployees()
    {
        try {
            $employees = Employee::where('status', 'active')->get();
            return response()->json([
                'count' => $employees->count(),
                'employees' => $employees->toArray()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'fallback' => 'Using PDO...'
            ]);
        }
    }

    /**
     * Debug attendance table structure and data
     */
    public function debugAttendanceStructure()
    {
        try {
            $columns = DB::select("SHOW COLUMNS FROM attendances");
            $sampleData = DB::table('attendances')->limit(5)->get();
            $negativeHours = DB::table('attendances')->where('total_hours', '<', 0)->get();
            
            return response()->json([
                'table_columns' => $columns,
                'sample_data' => $sampleData,
                'negative_hours_count' => $negativeHours->count(),
                'negative_hours_data' => $negativeHours
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * Debug shift data
     */
    public function debugShifts()
    {
        $shiftTypes = DB::table('shift_types')->get();
        $shifts = DB::table('shifts')
            ->join('employees', 'shifts.employee_id', '=', 'employees.id')
            ->select('shifts.*', 'employees.first_name', 'employees.last_name', 'employees.status')
            ->orderBy('shifts.shift_date', 'desc')
            ->limit(20)
            ->get();
        $employees = DB::table('employees')->where('status', 'active')->count();
        
        return response()->json([
            'shift_types_count' => $shiftTypes->count(),
            'shift_types' => $shiftTypes,
            'recent_shifts_count' => $shifts->count(),
            'recent_shifts' => $shifts,
            'active_employees_count' => $employees,
            'today_date' => today()->format('Y-m-d')
        ]);
    }

    /**
     * Debug calendar shifts data structure
     */
    public function debugCalendarShifts()
    {
        try {
            $controller = new ShiftController();
            $request = request();
            $request->merge(['month' => now()->format('Y-m')]);
            
            // Call the controller method to get the data
            $response = $controller->index();
            $data = $response->getData();
            
            return response()->json([
                'calendar_shifts_structure' => $data['calendarShifts'] ?? 'Not found',
                'sample_shift' => isset($data['calendarShifts']) && !empty($data['calendarShifts']) ? 
                    array_values($data['calendarShifts'])[0][0] ?? 'No shifts in first date' : 'No calendar shifts',
                'shifts_count' => isset($data['calendarShifts']) ? count($data['calendarShifts']) : 0
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Debug actual database shifts
     */
    public function debugDbShifts()
    {
        try {
            $shifts = DB::table('shifts')
                ->leftJoin('employees', 'shifts.employee_id', '=', 'employees.id')
                ->leftJoin('shift_types', 'shifts.shift_type_id', '=', 'shift_types.id')
                ->select(
                    'shifts.*',
                    'employees.first_name',
                    'employees.last_name',
                    'shift_types.name as shift_type_name'
                )
                ->orderBy('shifts.shift_date', 'desc')
                ->limit(10)
                ->get();
                
            return response()->json([
                'total_shifts' => DB::table('shifts')->count(),
                'shifts_with_zero_id' => DB::table('shifts')->where('id', 0)->count(),
                'shifts_with_null_id' => DB::table('shifts')->whereNull('id')->count(),
                'recent_shifts' => $shifts,
                'id_range' => [
                    'min' => DB::table('shifts')->min('id'),
                    'max' => DB::table('shifts')->max('id')
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Debug attendance data
     */
    public function debugAttendance()
    {
        $attendances = DB::table('attendances')
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->select('attendances.*', 'employees.first_name', 'employees.last_name')
            ->orderBy('attendances.attendance_date', 'desc')
            ->limit(10)
            ->get();
        
        $timeEntries = DB::table('time_entries')
            ->join('employees', 'time_entries.employee_id', '=', 'employees.id')
            ->select('time_entries.*', 'employees.first_name', 'employees.last_name')
            ->orderBy('time_entries.created_at', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json([
            'attendances_count' => $attendances->count(),
            'attendances' => $attendances,
            'time_entries_count' => $timeEntries->count(),
            'time_entries' => $timeEntries,
            'today_date' => today()->format('Y-m-d')
        ]);
    }

    /**
     * Test HR Dashboard data
     */
    public function testHrData()
    {
        try {
            // Test attendances table
            $attendanceCount = DB::table('attendances')->count();
            $recentAttendances = DB::table('attendances')
                ->join('employees', 'attendances.employee_id', '=', 'employees.id')
                ->select('attendances.*', 'employees.first_name', 'employees.last_name')
                ->limit(5)
                ->get();
            
            // Test time_entries table
            $timeEntriesCount = DB::table('time_entries')->count();
            
            // Test employees table
            $employeesCount = DB::table('employees')->count();
            
            return [
                'attendances_count' => $attendanceCount,
                'time_entries_count' => $timeEntriesCount,
                'employees_count' => $employeesCount,
                'sample_attendances' => $recentAttendances->take(3),
                'tables_exist' => [
                    'attendances' => Schema::hasTable('attendances'),
                    'time_entries' => Schema::hasTable('time_entries'),
                    'employees' => Schema::hasTable('employees')
                ]
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Check table structure
     */
    public function checkTableStructure()
    {
        try {
            $results = [];
            
            // Check if tables exist
            $tables = ['employees', 'attendances', 'time_entries'];
            foreach ($tables as $table) {
                try {
                    $count = DB::table($table)->count();
                    $results[] = "Table '{$table}': {$count} records";
                } catch (Exception $e) {
                    $results[] = "Table '{$table}': ERROR - " . $e->getMessage();
                }
            }
            
            // Check attendances table structure
            try {
                $columns = DB::select("DESCRIBE attendances");
                $results[] = "<br><strong>Attendances table structure:</strong>";
                foreach ($columns as $column) {
                    $results[] = "- {$column->Field}: {$column->Type} " . 
                               ($column->Null === 'NO' ? 'NOT NULL' : 'NULL') . 
                               ($column->Key ? " ({$column->Key})" : '') .
                               ($column->Default ? " DEFAULT {$column->Default}" : '') .
                               ($column->Extra ? " {$column->Extra}" : '');
                }
            } catch (Exception $e) {
                $results[] = "Error checking table structure: " . $e->getMessage();
            }
            
            return implode('<br>', $results);
            
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
