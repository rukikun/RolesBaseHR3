<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\TimeEntry;
use App\Models\LeaveRequest;
use App\Models\Claim;
use App\Models\Shift;
use App\Models\ShiftType;
use App\Models\Attendance;

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
        
        return view('dashboard.index', compact(
            'stats',
            'todayShifts', 
            'recentTimeEntries',
            'employeeStats'
        ));
    }
    
    /**
     * Get recent time entries as JSON for AJAX refresh
     */
    public function getRecentTimeEntriesJson()
    {
        try {
            \Log::info('=== Starting getRecentTimeEntriesJson (Simple Version) ===');
            
            // Simple direct query to avoid complex relationship issues
            $attendances = DB::table('attendances')
                ->join('employees', 'attendances.employee_id', '=', 'employees.id')
                ->select(
                    'attendances.id',
                    'attendances.date',
                    'attendances.clock_in_time',
                    'attendances.clock_out_time',
                    'attendances.total_hours',
                    'employees.first_name',
                    'employees.last_name',
                    'employees.profile_picture'
                )
                ->orderBy('attendances.date', 'desc')
                ->orderBy('attendances.id', 'desc')
                ->limit(10)
                ->get();
            
            \Log::info('Retrieved ' . $attendances->count() . ' attendance records');
            
            $processedEntries = $attendances->map(function($attendance) {
                try {
                    // Handle total_hours safely and format as time
                    $totalHours = null;
                    $formattedTotalTime = '--';
                    if ($attendance->total_hours !== null) {
                        $hours = abs(floatval($attendance->total_hours)); // Ensure positive number
                        $totalHours = $hours;
                        
                        // Format as "Xh Ym" instead of "X.XX hrs"
                        $wholeHours = floor($hours);
                        $minutes = round(($hours - $wholeHours) * 60);
                        
                        if ($wholeHours > 0 && $minutes > 0) {
                            $formattedTotalTime = $wholeHours . 'h ' . $minutes . 'm';
                        } elseif ($wholeHours > 0) {
                            $formattedTotalTime = $wholeHours . 'h';
                        } elseif ($minutes > 0) {
                            $formattedTotalTime = $minutes . 'm';
                        } else {
                            $formattedTotalTime = '0m';
                        }
                    }
                    
                    // Format date
                    $workDate = '--';
                    if ($attendance->date) {
                        try {
                            $workDate = \Carbon\Carbon::parse($attendance->date)->format('M d, Y');
                        } catch (\Exception $e) {
                            \Log::warning('Error formatting date: ' . $e->getMessage());
                            $workDate = '--';
                        }
                    }
                    
                    // Format times
                    $clockIn = '--';
                    $clockOut = '--';
                    
                    if ($attendance->clock_in_time) {
                        try {
                            $clockIn = \Carbon\Carbon::parse($attendance->clock_in_time)->format('g:i A');
                        } catch (\Exception $e) {
                            $clockIn = '--';
                        }
                    }
                    
                    if ($attendance->clock_out_time) {
                        try {
                            $clockOut = \Carbon\Carbon::parse($attendance->clock_out_time)->format('g:i A');
                        } catch (\Exception $e) {
                            $clockOut = '--';
                        }
                    }
                    
                    // Employee name
                    $employeeName = trim(($attendance->first_name ?? '') . ' ' . ($attendance->last_name ?? ''));
                    if (empty($employeeName)) {
                        $employeeName = 'Unknown Employee';
                    }
                    
                    return [
                        'id' => $attendance->id,
                        'employee_name' => $employeeName,
                        'profile_picture' => $attendance->profile_picture,
                        'work_date' => $workDate,
                        'formatted_clock_in' => $clockIn,
                        'formatted_clock_out' => $clockOut,
                        'total_hours' => $totalHours,
                        'formatted_total_time' => $formattedTotalTime,
                        'status' => ($totalHours !== null && $totalHours >= 8) ? 'success' : 'secondary'
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error processing individual attendance record: ' . $e->getMessage());
                    return [
                        'id' => $attendance->id ?? null,
                        'employee_name' => 'Error Loading',
                        'profile_picture' => null,
                        'work_date' => '--',
                        'formatted_clock_in' => '--',
                        'formatted_clock_out' => '--',
                        'total_hours' => null,
                        'formatted_total_time' => '--',
                        'status' => 'secondary'
                    ];
                }
            });
            
            \Log::info('Successfully processed ' . $processedEntries->count() . ' entries');
            
            return response()->json([
                'success' => true,
                'entries' => $processedEntries,
                'count' => $processedEntries->count()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getRecentTimeEntriesJson: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred while fetching time entries',
                'debug_message' => $e->getMessage(),
                'entries' => []
            ], 500);
        }
    }
    
    private function getDashboardStats()
    {
        try {
            return [
                'total_employees' => Employee::where('status', 'active')->count(),
                'pending_timesheets' => TimeEntry::where('status', 'pending')->count(),
                'leave_requests' => LeaveRequest::where('status', 'pending')->count(),
                'active_claims' => Claim::whereIn('status', ['pending', 'approved'])->count()
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
            // Try to get present count from attendance logs first using Eloquent
            $presentToday = 0;
            try {
                $presentToday = Attendance::whereHas('employee', function($query) {
                        $query->where('status', 'active');
                    })
                    ->whereDate('date', today())
                    ->whereNotNull('clock_in_time')
                    ->distinct('employee_id')
                    ->count();
            } catch (\Exception $e) {
                // Fallback to time_entries if attendances doesn't exist
                $presentToday = TimeEntry::whereHas('employee', function($query) {
                        $query->where('status', 'active');
                    })
                    ->whereDate('work_date', today())
                    ->whereNotNull('clock_in_time')
                    ->distinct('employee_id')
                    ->count();
            }
            
            return [
                'active_employees' => Employee::where('status', 'active')->count(),
                'present_today' => $presentToday,
                'on_leave_today' => LeaveRequest::whereHas('employee', function($query) {
                        $query->where('status', 'active');
                    })
                    ->where('status', 'approved')
                    ->whereDate('start_date', '<=', today())
                    ->whereDate('end_date', '>=', today())
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
            // Get shift types with employee assignments using Eloquent
            $shiftTypes = ShiftType::where('is_active', true)
                ->orderBy('name')
                ->get();

            $shifts = collect();
            
            foreach ($shiftTypes as $shiftType) {
                // Get all scheduled shifts (not just today's) using Eloquent relationships
                $allShifts = Shift::with('employee')
                    ->where('shift_type_id', $shiftType->id)
                    ->whereHas('employee', function($query) {
                        $query->where('status', 'active');
                    })
                    ->orderBy('shift_date', 'desc')
                    ->orderBy('start_time')
                    ->limit(50) // Increased limit to show more shifts
                    ->get();

                $employees = $allShifts->map(function($shift) {
                    return (object) [
                        'id' => $shift->employee->id,
                        'first_name' => $shift->employee->first_name,
                        'last_name' => $shift->employee->last_name,
                        'position' => $shift->employee->position,
                        'profile_picture' => $shift->employee->profile_picture,
                        'shift_id' => $shift->id,
                        'shift_date' => $shift->shift_date,
                        'start_time' => $shift->start_time,
                        'end_time' => $shift->end_time
                    ];
                });

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
        try {
            \Log::info('=== Starting getRecentTimeEntries (Fixed Version) ===');
            
            // Simple direct query to avoid relationship issues
            $attendances = DB::table('attendances')
                ->join('employees', 'attendances.employee_id', '=', 'employees.id')
                ->select(
                    'attendances.id',
                    'attendances.date',
                    'attendances.clock_in_time',
                    'attendances.clock_out_time',
                    'attendances.total_hours',
                    'employees.first_name',
                    'employees.last_name',
                    'employees.profile_picture'
                )
                ->orderBy('attendances.date', 'desc')
                ->orderBy('attendances.id', 'desc')
                ->limit(10)
                ->get();
            
            \Log::info('Retrieved ' . $attendances->count() . ' attendance records for original method');
            
            return $attendances->map(function($attendance) {
                // Format times
                $clockIn = '--';
                $clockOut = '--';
                
                if ($attendance->clock_in_time) {
                    try {
                        $clockIn = \Carbon\Carbon::parse($attendance->clock_in_time)->format('g:i A');
                    } catch (\Exception $e) {
                        $clockIn = '--';
                    }
                }
                
                if ($attendance->clock_out_time) {
                    try {
                        $clockOut = \Carbon\Carbon::parse($attendance->clock_out_time)->format('g:i A');
                    } catch (\Exception $e) {
                        $clockOut = '--';
                    }
                }
                
                // Employee name
                $employeeName = trim(($attendance->first_name ?? '') . ' ' . ($attendance->last_name ?? ''));
                if (empty($employeeName)) {
                    $employeeName = 'Unknown Employee';
                }
                
                // Format date
                $workDate = null;
                if ($attendance->date) {
                    try {
                        $workDate = \Carbon\Carbon::parse($attendance->date);
                    } catch (\Exception $e) {
                        $workDate = null;
                    }
                }
                
                // Fix negative hours and format as time
                $totalHours = null;
                $formattedTotalTime = '--';
                if ($attendance->total_hours !== null) {
                    $hours = abs(floatval($attendance->total_hours)); // Remove negative sign
                    $totalHours = $hours;
                    
                    // Format as "Xh Ym" instead of "X.XX hrs"
                    $wholeHours = floor($hours);
                    $minutes = round(($hours - $wholeHours) * 60);
                    
                    if ($wholeHours > 0 && $minutes > 0) {
                        $formattedTotalTime = $wholeHours . 'h ' . $minutes . 'm';
                    } elseif ($wholeHours > 0) {
                        $formattedTotalTime = $wholeHours . 'h';
                    } elseif ($minutes > 0) {
                        $formattedTotalTime = $minutes . 'm';
                    } else {
                        $formattedTotalTime = '0m';
                    }
                }
                
                return (object) [
                    'id' => $attendance->id,
                    'employee_name' => $employeeName,
                    'profile_picture' => $attendance->profile_picture,
                    'work_date' => $workDate,
                    'formatted_clock_in' => $clockIn,
                    'formatted_clock_out' => $clockOut,
                    'total_hours' => $totalHours, // Now positive
                    'formatted_total_time' => $formattedTotalTime,
                ];
            });
            
        } catch (\Exception $e) {
            \Log::error('Error in getRecentTimeEntries: ' . $e->getMessage());
            return collect([]);
        }
    }
}
