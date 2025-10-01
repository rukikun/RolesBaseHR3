<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HRDashboardController extends Controller
{
    public function index()
    {
        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        
        // Get today's schedule with employee assignments
        $todayShifts = $this->getTodayShifts();
        
        // Get recent time entries
        $recentTimeEntries = $this->getRecentTimeEntries();
        
        // Debug: Let's see what we're getting
        \Log::info('Recent Time Entries Count: ' . $recentTimeEntries->count());
        \Log::info('Recent Time Entries Data: ' . json_encode($recentTimeEntries->toArray()));
        
        // Get employee statistics
        $employeeStats = $this->getEmployeeStats();
        
        return view('dashboard', compact(
            'stats',
            'todayShifts', 
            'recentTimeEntries',
            'employeeStats'
        ));
    }
    
    private function getDashboardStats()
    {
        try {
            return [
                'total_employees' => DB::table('employees')->where('status', 'active')->count(),
                'pending_timesheets' => DB::table('time_entries')
                    ->where('status', 'pending')
                    ->count(),
                'leave_requests' => DB::table('leave_requests')
                    ->where('status', 'pending')
                    ->count(),
                'active_claims' => DB::table('claims')
                    ->whereIn('status', ['pending', 'approved'])
                    ->count()
            ];
        } catch (\Exception $e) {
            return [
                'total_employees' => 0,
                'pending_timesheets' => 0,
                'leave_requests' => 0,
                'active_claims' => 0
            ];
        }
    }
    
    private function getEmployeeStats()
    {
        try {
            // Try to get present count from attendance logs first
            $presentToday = 0;
            try {
                $presentToday = DB::table('attendances')
                    ->join('employees', 'attendances.employee_id', '=', 'employees.id')
                    ->where('employees.status', 'active')
                    ->whereDate('attendances.date', today())  // Use 'date' column
                    ->whereNotNull('attendances.clock_in_time')
                    ->distinct('employees.id')
                    ->count();
            } catch (\Exception $e) {
                // Fallback to time_entries if attendances doesn't exist
                $presentToday = DB::table('time_entries')
                    ->join('employees', 'time_entries.employee_id', '=', 'employees.id')
                    ->where('employees.status', 'active')
                    ->whereDate('time_entries.work_date', today())
                    ->whereNotNull('time_entries.clock_in_time')
                    ->distinct('employees.id')
                    ->count();
            }
            
            return [
                'active_employees' => DB::table('employees')
                    ->where('status', 'active')
                    ->count(),
                'present_today' => $presentToday,
                'on_leave_today' => DB::table('leave_requests')
                    ->join('employees', 'leave_requests.employee_id', '=', 'employees.id')
                    ->where('employees.status', 'active')
                    ->where('leave_requests.status', 'approved')
                    ->whereDate('leave_requests.start_date', '<=', today())
                    ->whereDate('leave_requests.end_date', '>=', today())
                    ->count()
            ];
        } catch (\Exception $e) {
            return [
                'active_employees' => 0,
                'present_today' => 0,
                'on_leave_today' => 0
            ];
        }
    }
    
    private function getTodayShifts()
    {
        try {
            // Get shift types with employee assignments
            $shiftTypes = DB::table('shift_types')
                ->where('is_active', 1)
                ->orderBy('name')
                ->get();

            $shifts = collect();
            
            foreach ($shiftTypes as $shiftType) {
                // First try today's assignments - DO NOT remove duplicates to show all assignments
                $employees = DB::table('shifts')
                    ->join('employees', 'shifts.employee_id', '=', 'employees.id')
                    ->where('shifts.shift_type_id', $shiftType->id)
                    ->whereDate('shifts.shift_date', today())
                    ->where('employees.status', 'active')
                    ->select(
                        'employees.id',
                        'employees.first_name',
                        'employees.last_name',
                        'employees.position',
                        'employees.profile_picture',
                        'shifts.id as shift_id',
                        'shifts.shift_date',
                        'shifts.start_time',
                        'shifts.end_time'
                    )
                    ->orderBy('shifts.start_time')
                    ->get();
                
                // If no employees found for today, get the most recent assignments for this specific shift type
                if ($employees->isEmpty()) {
                    $employees = DB::table('shifts')
                        ->join('employees', 'shifts.employee_id', '=', 'employees.id')
                        ->where('shifts.shift_type_id', $shiftType->id)
                        ->where('employees.status', 'active')
                        ->select(
                            'employees.id',
                            'employees.first_name',
                            'employees.last_name',
                            'employees.position',
                            'employees.profile_picture',
                            'shifts.id as shift_id',
                            'shifts.shift_date',
                            'shifts.start_time',
                            'shifts.end_time'
                        )
                        ->orderBy('shifts.shift_date', 'desc')
                        ->orderBy('shifts.start_time')
                        ->limit(20) // Increased limit to show more assignments
                        ->get();
                        // DO NOT use unique('id') here - we want to show all shift assignments
                }

                try {
                    $startTime = Carbon::createFromFormat('H:i:s', $shiftType->start_time)->format('g:i A');
                    $endTime = Carbon::createFromFormat('H:i:s', $shiftType->end_time)->format('g:i A');
                } catch (\Exception $e) {
                    $startTime = $shiftType->start_time;
                    $endTime = $shiftType->end_time;
                }
                
                $shifts->push([
                    'id' => $shiftType->id,
                    'name' => $shiftType->name,
                    'time_range' => $startTime . ' - ' . $endTime,
                    'employee_count' => $employees->count(),
                    'employees' => $employees->map(function ($emp) {
                        return [
                            'id' => $emp->id,
                            'shift_id' => $emp->shift_id ?? null,
                            'name' => $emp->first_name . ' ' . $emp->last_name,
                            'position' => $emp->position,
                            'avatar' => $emp->profile_picture ?? null,
                            'shift_date' => $emp->shift_date ?? null,
                            'specific_time' => isset($emp->start_time, $emp->end_time) ? 
                                date('g:i A', strtotime($emp->start_time)) . ' - ' . date('g:i A', strtotime($emp->end_time)) : null
                        ];
                    })->toArray()
                ]);
            }

            // Only create default shifts if NO shift types exist in database
            if ($shifts->isEmpty()) {
                // Get ALL shift assignments without removing duplicates
                $allShiftAssignments = DB::table('shifts')
                    ->join('employees', 'shifts.employee_id', '=', 'employees.id')
                    ->where('employees.status', 'active')
                    ->select(
                        'employees.id',
                        'employees.first_name', 
                        'employees.last_name',
                        'employees.position',
                        'employees.profile_picture',
                        'shifts.shift_type_id',
                        'shifts.id as shift_id',
                        'shifts.shift_date',
                        'shifts.start_time',
                        'shifts.end_time'
                    )
                    ->orderBy('shifts.shift_date', 'desc')
                    ->orderBy('shifts.start_time')
                    ->get();
                
                // Group by shift_type_id but keep all assignments (no unique filtering)
                $employeesByShift = $allShiftAssignments->groupBy('shift_type_id');
                
                // Create shifts based on actual assignments
                $defaultShifts = collect();
                
                // Morning Shift (shift_type_id = 1)
                $morningEmployees = $employeesByShift->get(1, collect());
                $defaultShifts->push([
                    'id' => 1,
                    'name' => 'Morning Shift',
                    'time_range' => '8:00 AM - 4:00 PM',
                    'employee_count' => $morningEmployees->count(),
                    'employees' => $morningEmployees->map(function ($emp) {
                        return [
                            'id' => $emp->id,
                            'shift_id' => $emp->shift_id,
                            'name' => $emp->first_name . ' ' . $emp->last_name,
                            'position' => $emp->position,
                            'avatar' => $emp->profile_picture ?? null,
                            'shift_date' => $emp->shift_date,
                            'specific_time' => isset($emp->start_time, $emp->end_time) ? 
                                date('g:i A', strtotime($emp->start_time)) . ' - ' . date('g:i A', strtotime($emp->end_time)) : null
                        ];
                    })->toArray()
                ]);
                
                // Afternoon Shift (shift_type_id = 2)
                $afternoonEmployees = $employeesByShift->get(2, collect());
                $defaultShifts->push([
                    'id' => 2,
                    'name' => 'Afternoon Shift',
                    'time_range' => '2:00 PM - 10:00 PM',
                    'employee_count' => $afternoonEmployees->count(),
                    'employees' => $afternoonEmployees->map(function ($emp) {
                        return [
                            'id' => $emp->id,
                            'shift_id' => $emp->shift_id,
                            'name' => $emp->first_name . ' ' . $emp->last_name,
                            'position' => $emp->position,
                            'avatar' => $emp->profile_picture ?? null,
                            'shift_date' => $emp->shift_date,
                            'specific_time' => isset($emp->start_time, $emp->end_time) ? 
                                date('g:i A', strtotime($emp->start_time)) . ' - ' . date('g:i A', strtotime($emp->end_time)) : null
                        ];
                    })->toArray()
                ]);
                
                // Night Shift (shift_type_id = 3) - only show if there are actual assignments
                $nightEmployees = $employeesByShift->get(3, collect());
                if ($nightEmployees->isNotEmpty()) {
                    $defaultShifts->push([
                        'id' => 3,
                        'name' => 'Night Shift',
                        'time_range' => '10:00 PM - 6:00 AM',
                        'employee_count' => $nightEmployees->count(),
                        'employees' => $nightEmployees->map(function ($emp) {
                            return [
                                'id' => $emp->id,
                                'shift_id' => $emp->shift_id,
                                'name' => $emp->first_name . ' ' . $emp->last_name,
                                'position' => $emp->position,
                                'avatar' => $emp->profile_picture ?? null,
                                'shift_date' => $emp->shift_date,
                                'specific_time' => isset($emp->start_time, $emp->end_time) ? 
                                    date('g:i A', strtotime($emp->start_time)) . ' - ' . date('g:i A', strtotime($emp->end_time)) : null
                            ];
                        })->toArray()
                    ]);
                }
                
                return $defaultShifts;
            }

            return $shifts;
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error in getTodayShifts: ' . $e->getMessage());
            
            // Fallback: try to get employees from shifts table with their actual assignments
            try {
                $allShiftAssignments = DB::table('shifts')
                    ->join('employees', 'shifts.employee_id', '=', 'employees.id')
                    ->where('employees.status', 'active')
                    ->select(
                        'employees.id',
                        'employees.first_name',
                        'employees.last_name', 
                        'employees.position',
                        'employees.profile_picture',
                        'shifts.shift_type_id',
                        'shifts.id as shift_id',
                        'shifts.shift_date',
                        'shifts.start_time',
                        'shifts.end_time'
                    )
                    ->orderBy('shifts.shift_date', 'desc')
                    ->orderBy('shifts.start_time')
                    ->get();
                    // DO NOT use unique('id') - we want all shift assignments
                
                $employeesByShift = $allShiftAssignments->groupBy('shift_type_id');
                
                if ($employeesByShift->isNotEmpty()) {
                    $fallbackShifts = collect();
                    
                    // Morning Shift
                    $morningEmployees = $employeesByShift->get(1, collect());
                    $fallbackShifts->push([
                        'id' => 1,
                        'name' => 'Morning Shift',
                        'time_range' => '8:00 AM - 4:00 PM',
                        'employee_count' => $morningEmployees->count(),
                        'employees' => $morningEmployees->map(function ($emp) {
                            return [
                                'id' => $emp->id,
                                'shift_id' => $emp->shift_id,
                                'name' => $emp->first_name . ' ' . $emp->last_name,
                                'position' => $emp->position,
                                'avatar' => $emp->profile_picture ?? null,
                                'shift_date' => $emp->shift_date,
                                'specific_time' => isset($emp->start_time, $emp->end_time) ? 
                                    date('g:i A', strtotime($emp->start_time)) . ' - ' . date('g:i A', strtotime($emp->end_time)) : null
                            ];
                        })->toArray()
                    ]);
                    
                    // Afternoon Shift
                    $afternoonEmployees = $employeesByShift->get(2, collect());
                    $fallbackShifts->push([
                        'id' => 2,
                        'name' => 'Afternoon Shift',
                        'time_range' => '2:00 PM - 10:00 PM',
                        'employee_count' => $afternoonEmployees->count(),
                        'employees' => $afternoonEmployees->map(function ($emp) {
                            return [
                                'id' => $emp->id,
                                'shift_id' => $emp->shift_id,
                                'name' => $emp->first_name . ' ' . $emp->last_name,
                                'position' => $emp->position,
                                'avatar' => $emp->profile_picture ?? null,
                                'shift_date' => $emp->shift_date,
                                'specific_time' => isset($emp->start_time, $emp->end_time) ? 
                                    date('g:i A', strtotime($emp->start_time)) . ' - ' . date('g:i A', strtotime($emp->end_time)) : null
                            ];
                        })->toArray()
                    ]);
                    
                    // Night Shift - only if there are actual assignments
                    $nightEmployees = $employeesByShift->get(3, collect());
                    if ($nightEmployees->isNotEmpty()) {
                        $fallbackShifts->push([
                            'id' => 3,
                            'name' => 'Night Shift',
                            'time_range' => '10:00 PM - 6:00 AM',
                            'employee_count' => $nightEmployees->count(),
                            'employees' => $nightEmployees->map(function ($emp) {
                                return [
                                    'id' => $emp->id,
                                    'shift_id' => $emp->shift_id,
                                    'name' => $emp->first_name . ' ' . $emp->last_name,
                                    'position' => $emp->position,
                                    'avatar' => $emp->profile_picture ?? null,
                                    'shift_date' => $emp->shift_date,
                                    'specific_time' => isset($emp->start_time, $emp->end_time) ? 
                                        date('g:i A', strtotime($emp->start_time)) . ' - ' . date('g:i A', strtotime($emp->end_time)) : null
                                ];
                            })->toArray()
                        ]);
                    }
                    
                    return $fallbackShifts;
                }
            } catch (\Exception $e2) {
                \Log::error('Fallback query also failed: ' . $e2->getMessage());
            }
            
            // Final fallback - only show Morning and Afternoon shifts (most common)
            return collect([
                [
                    'id' => 1,
                    'name' => 'Morning Shift',
                    'time_range' => '8:00 AM - 4:00 PM',
                    'employee_count' => 0,
                    'employees' => []
                ],
                [
                    'id' => 2,
                    'name' => 'Afternoon Shift',
                    'time_range' => '2:00 PM - 10:00 PM',
                    'employee_count' => 0,
                    'employees' => []
                ]
                // Note: Night Shift removed from final fallback - only show if there are actual assignments
            ]);
        }
    }
    
    private function getRecentTimeEntries()
    {
        \Log::info('=== Starting getRecentTimeEntries ===');
        
        // First, let's check if tables exist
        try {
            $attendanceTableExists = \Schema::hasTable('attendances');
            $timeEntriesTableExists = \Schema::hasTable('time_entries');
            $employeesTableExists = \Schema::hasTable('employees');
            
            \Log::info('Table existence check:', [
                'attendances' => $attendanceTableExists,
                'time_entries' => $timeEntriesTableExists,
                'employees' => $employeesTableExists
            ]);
            
            if (!$employeesTableExists) {
                \Log::error('Employees table does not exist!');
                return collect([]);
            }
            
        } catch (\Exception $e) {
            \Log::error('Error checking table existence: ' . $e->getMessage());
            return collect([]);
        }
        
        // Try attendances table first
        if ($attendanceTableExists) {
            try {
                \Log::info('Querying attendances table...');
                
                // First check total count
                $totalAttendances = DB::table('attendances')->count();
                \Log::info('Total attendances in table: ' . $totalAttendances);
                
                if ($totalAttendances > 0) {
                    // Get recent attendances with employee info
                    // Based on your DB screenshot, the date column is 'date', not 'attendance_date'
                    $attendances = DB::table('attendances')
                        ->join('employees', 'attendances.employee_id', '=', 'employees.id')
                        ->select(
                            'attendances.id',
                            'attendances.employee_id',
                            'attendances.date as attendance_date',  // Use 'date' column from your DB
                            'attendances.clock_in_time',
                            'attendances.clock_out_time',
                            'attendances.total_hours as stored_total_hours', // Use stored total_hours
                            'attendances.status',
                            'employees.first_name',
                            'employees.last_name',
                            'employees.profile_picture'
                        )
                        ->orderBy('attendances.date', 'desc')
                        ->orderBy('attendances.id', 'desc')
                        ->limit(3)
                        ->get();
                    
                    \Log::info('Found ' . $attendances->count() . ' attendance records with employee data');
                    
                    if ($attendances->isNotEmpty()) {
                        $result = $attendances->map(function ($entry) {
                            \Log::info('Processing attendance entry:', [
                                'id' => $entry->id,
                                'employee' => $entry->first_name . ' ' . $entry->last_name,
                                'date' => $entry->attendance_date,
                                'clock_in' => $entry->clock_in_time,
                                'clock_out' => $entry->clock_out_time,
                                'stored_total_hours' => $entry->stored_total_hours
                            ]);
                            
                            $calculatedHours = $entry->stored_total_hours ?? $this->calculateTotalHours($entry->clock_in_time, $entry->clock_out_time, $entry->attendance_date);
                            
                            \Log::info('Final total_hours for entry ' . $entry->id . ': ' . ($calculatedHours ?? 'NULL'));
                            
                            return (object) [
                                'id' => $entry->id,
                                'employee_name' => $entry->first_name . ' ' . $entry->last_name,
                                'profile_picture' => $entry->profile_picture,
                                'work_date' => $entry->attendance_date ? Carbon::parse($entry->attendance_date) : null,
                                'formatted_clock_in' => $entry->clock_in_time ? date('g:i A', strtotime($entry->clock_in_time)) : '--',
                                'formatted_clock_out' => $entry->clock_out_time ? date('g:i A', strtotime($entry->clock_out_time)) : '--',
                                'total_hours' => $calculatedHours,
                                'status' => $entry->status ?? 'present'
                            ];
                        });
                        
                        \Log::info('Returning ' . $result->count() . ' processed attendance records');
                        return $result;
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Attendances query failed: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
            }
        }
        
        // Try time_entries table as fallback
        if ($timeEntriesTableExists) {
            try {
                \Log::info('Trying time_entries table as fallback...');
                
                $totalTimeEntries = DB::table('time_entries')->count();
                \Log::info('Total time entries in table: ' . $totalTimeEntries);
                
                if ($totalTimeEntries > 0) {
                    $timeEntries = DB::table('time_entries')
                        ->join('employees', 'time_entries.employee_id', '=', 'employees.id')
                        ->select(
                            'time_entries.*',
                            'employees.first_name',
                            'employees.last_name',
                            'employees.profile_picture'
                        )
                        ->orderBy('time_entries.created_at', 'desc')
                        ->limit(3)
                        ->get();
                    
                    \Log::info('Found ' . $timeEntries->count() . ' time entry records');
                    
                    if ($timeEntries->isNotEmpty()) {
                        $result = $timeEntries->map(function ($entry) {
                            return (object) [
                                'id' => $entry->id,
                                'employee_name' => $entry->first_name . ' ' . $entry->last_name,
                                'profile_picture' => $entry->profile_picture,
                                'work_date' => $entry->work_date ? Carbon::parse($entry->work_date) : null,
                                'formatted_clock_in' => $entry->clock_in_time ? date('g:i A', strtotime($entry->clock_in_time)) : '--',
                                'formatted_clock_out' => $entry->clock_out_time ? date('g:i A', strtotime($entry->clock_out_time)) : '--',
                                'total_hours' => $entry->hours_worked ?? $this->calculateTotalHours($entry->clock_in_time, $entry->clock_out_time, $entry->work_date),
                                'status' => $entry->status ?? 'pending'
                            ];
                        });
                        
                        \Log::info('Returning ' . $result->count() . ' processed time entry records');
                        return $result;
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Time entries query failed: ' . $e->getMessage());
            }
        }
        
        \Log::warning('No data found in any table, returning empty collection');
        return collect([]);
    }
    
    /**
     * Calculate total hours between clock-in and clock-out times
     */
    private function calculateTotalHours($clockInTime, $clockOutTime, $date = null)
    {
        // Return null if either time is missing
        if (!$clockInTime || !$clockOutTime) {
            return null;
        }
        
        try {
            // Parse datetime strings directly (since they're stored as DATETIME in DB)
            $clockIn = Carbon::parse($clockInTime);
            $clockOut = Carbon::parse($clockOutTime);
            
            // Handle overnight shifts (clock-out is next day)
            if ($clockOut->lt($clockIn)) {
                $clockOut->addDay();
            }
            
            // Calculate difference in hours
            $totalHours = $clockOut->diffInMinutes($clockIn) / 60;
            
            // Round to 2 decimal places and ensure it's not negative
            $totalHours = max(0, round($totalHours, 2));
            
            \Log::info('Calculated hours:', [
                'clock_in' => $clockInTime,
                'clock_out' => $clockOutTime,
                'total_hours' => $totalHours
            ]);
            
            return $totalHours;
            
        } catch (\Exception $e) {
            \Log::error('Error calculating total hours: ' . $e->getMessage(), [
                'clock_in' => $clockInTime,
                'clock_out' => $clockOutTime,
                'date' => $date
            ]);
            return null;
        }
    }
}
