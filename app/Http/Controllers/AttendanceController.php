<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display attendance records
     */
    public function index(Request $request)
    {
        try {
            // Get employees for dropdowns
            $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();
            
            // Get attendance records with employee relationships
            $attendances = Attendance::with('employee')
                ->orderBy('date', 'desc')
                ->orderBy('clock_in_time', 'desc')
                ->get()
                ->map(function ($attendance) {
                    // Add computed properties for blade template compatibility
                    $attendance->employee_name = $attendance->employee 
                        ? $attendance->employee->first_name . ' ' . $attendance->employee->last_name
                        : 'Unknown Employee';
                    
                    // Calculate and update total hours if not set
                    if (!$attendance->total_hours && $attendance->clock_in_time && $attendance->clock_out_time) {
                        $attendance->total_hours = $attendance->calculateTotalHours();
                        $attendance->overtime_hours = $attendance->calculateOvertimeHours();
                        $attendance->save();
                    }
                    
                    return $attendance;
                });

            // Calculate statistics
            $attendanceStats = [
                'total_records' => $attendances->count(),
                'present_today' => Attendance::today()->where('status', 'present')->count(),
                'on_break' => Attendance::today()->where('status', 'on_break')->count(),
                'total_hours_today' => Attendance::today()->sum('total_hours'),
            ];

            return view('attendance_management', compact('employees', 'attendances', 'attendanceStats'));
            
        } catch (\Exception $e) {
            \Log::error('Error in AttendanceController@index: ' . $e->getMessage());
            
            // Fallback with empty data
            return view('attendance_management', [
                'employees' => collect([]),
                'attendances' => collect([]),
                'attendanceStats' => [
                    'total_records' => 0,
                    'present_today' => 0,
                    'on_break' => 0,
                    'total_hours_today' => 0,
                ]
            ]);
        }
    }

    /**
     * Clock in an employee
     */
    public function clockIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employeeId = $request->employee_id;
            $today = Carbon::today();
            
            // Check if employee already has attendance record for today
            $existingAttendance = Attendance::getTodayAttendance($employeeId);
            
            if ($existingAttendance && $existingAttendance->clock_in_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee already clocked in today'
                ], 400);
            }

            $clockInTime = Carbon::now();
            
            // Determine if employee is late (assuming 9:00 AM is standard start time)
            $standardStartTime = Carbon::today()->setTime(9, 0, 0);
            $status = $clockInTime->gt($standardStartTime) ? 'late' : 'present';

            if ($existingAttendance) {
                // Update existing record
                $existingAttendance->update([
                    'clock_in_time' => $clockInTime,
                    'status' => $status,
                    'location' => $request->location ?? 'Office',
                    'ip_address' => $request->ip(),
                    'notes' => $request->notes,
                ]);
                $attendance = $existingAttendance;
            } else {
                // Create new attendance record
                $attendance = Attendance::create([
                    'employee_id' => $employeeId,
                    'date' => $today,
                    'clock_in_time' => $clockInTime,
                    'status' => $status,
                    'location' => $request->location ?? 'Office',
                    'ip_address' => $request->ip(),
                    'notes' => $request->notes,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully clocked in',
                'data' => [
                    'id' => $attendance->id,
                    'employee_id' => $attendance->employee_id,
                    'clock_in_time' => $attendance->formatted_clock_in,
                    'status' => $attendance->status,
                    'location' => $attendance->location,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in AttendanceController@clockIn: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to clock in: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clock out an employee
     */
    public function clockOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employeeId = $request->employee_id;
            $attendance = Attendance::getTodayAttendance($employeeId);
            
            if (!$attendance || !$attendance->clock_in_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee has not clocked in today'
                ], 400);
            }

            if ($attendance->clock_out_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee already clocked out today'
                ], 400);
            }

            $clockOutTime = Carbon::now();
            
            // Update attendance record
            $attendance->update([
                'clock_out_time' => $clockOutTime,
                'status' => 'clocked_out',
                'notes' => $request->notes ? $attendance->notes . ' | ' . $request->notes : $attendance->notes,
            ]);

            // Calculate total hours and overtime
            $attendance->total_hours = $attendance->calculateTotalHours();
            $attendance->overtime_hours = $attendance->calculateOvertimeHours();
            $attendance->save();

            return response()->json([
                'success' => true,
                'message' => 'Successfully clocked out',
                'data' => [
                    'id' => $attendance->id,
                    'employee_id' => $attendance->employee_id,
                    'clock_out_time' => $attendance->formatted_clock_out,
                    'total_hours' => $attendance->total_hours,
                    'overtime_hours' => $attendance->overtime_hours,
                    'status' => $attendance->status,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in AttendanceController@clockOut: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to clock out: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start break
     */
    public function startBreak(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employeeId = $request->employee_id;
            $attendance = Attendance::getTodayAttendance($employeeId);
            
            if (!$attendance || !$attendance->clock_in_time || $attendance->clock_out_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee must be clocked in to start break'
                ], 400);
            }

            if ($attendance->break_start_time && !$attendance->break_end_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee is already on break'
                ], 400);
            }

            $attendance->update([
                'break_start_time' => Carbon::now(),
                'status' => 'on_break',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Break started successfully',
                'data' => [
                    'break_start_time' => $attendance->break_start_time->format('h:i A'),
                    'status' => $attendance->status,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in AttendanceController@startBreak: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to start break: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * End break
     */
    public function endBreak(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employeeId = $request->employee_id;
            $attendance = Attendance::getTodayAttendance($employeeId);
            
            if (!$attendance || !$attendance->break_start_time || $attendance->break_end_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee is not currently on break'
                ], 400);
            }

            $attendance->update([
                'break_end_time' => Carbon::now(),
                'status' => 'present',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Break ended successfully',
                'data' => [
                    'break_end_time' => $attendance->break_end_time->format('h:i A'),
                    'status' => $attendance->status,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in AttendanceController@endBreak: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to end break: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employee's current attendance status
     */
    public function getStatus(Request $request, $employeeId)
    {
        try {
            $attendance = Attendance::getTodayAttendance($employeeId);
            
            if (!$attendance) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'status' => 'not_clocked_in',
                        'message' => 'Employee has not clocked in today'
                    ]
                ]);
            }

            $status = [
                'id' => $attendance->id,
                'employee_id' => $attendance->employee_id,
                'date' => $attendance->date->format('Y-m-d'),
                'clock_in_time' => $attendance->formatted_clock_in,
                'clock_out_time' => $attendance->formatted_clock_out,
                'break_start_time' => $attendance->break_start_time ? $attendance->break_start_time->format('h:i A') : null,
                'break_end_time' => $attendance->break_end_time ? $attendance->break_end_time->format('h:i A') : null,
                'total_hours' => $attendance->total_hours,
                'overtime_hours' => $attendance->overtime_hours,
                'status' => $attendance->status,
                'location' => $attendance->location,
                'is_clocked_in' => $attendance->isClockedIn(),
                'is_clocked_out' => $attendance->isClockedOut(),
                'is_on_break' => $attendance->isOnBreak(),
            ];

            return response()->json([
                'success' => true,
                'data' => $status
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in AttendanceController@getStatus: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get attendance status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance statistics
     */
    public function getStats(Request $request)
    {
        try {
            $today = Carbon::today();
            $thisWeek = [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            $thisMonth = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];

            $stats = [
                'today' => [
                    'total_present' => Attendance::today()->where('status', '!=', 'absent')->count(),
                    'on_break' => Attendance::today()->where('status', 'on_break')->count(),
                    'clocked_out' => Attendance::today()->where('status', 'clocked_out')->count(),
                    'late_arrivals' => Attendance::today()->where('status', 'late')->count(),
                    'total_hours' => Attendance::today()->sum('total_hours'),
                ],
                'this_week' => [
                    'total_records' => Attendance::thisWeek()->count(),
                    'total_hours' => Attendance::thisWeek()->sum('total_hours'),
                    'overtime_hours' => Attendance::thisWeek()->sum('overtime_hours'),
                ],
                'this_month' => [
                    'total_records' => Attendance::thisMonth()->count(),
                    'total_hours' => Attendance::thisMonth()->sum('total_hours'),
                    'overtime_hours' => Attendance::thisMonth()->sum('overtime_hours'),
                    'average_hours_per_day' => Attendance::thisMonth()->avg('total_hours'),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in AttendanceController@getStats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get attendance statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance records for API
     */
    public function getAttendances(Request $request)
    {
        try {
            $query = Attendance::with('employee');

            // Apply filters
            if ($request->has('employee_id') && $request->employee_id) {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('date', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('date', '<=', $request->date_to);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            $attendances = $query->orderBy('date', 'desc')
                                ->orderBy('clock_in_time', 'desc')
                                ->paginate($request->per_page ?? 15);

            return response()->json([
                'success' => true,
                'data' => $attendances
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in AttendanceController@getAttendances: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get attendance records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new attendance record (manual entry)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'clock_in_time' => 'required|date_format:H:i',
            'clock_out_time' => 'nullable|date_format:H:i',
            'break_start_time' => 'nullable|date_format:H:i',
            'break_end_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $date = Carbon::parse($request->date);
            $clockInTime = $date->copy()->setTimeFromTimeString($request->clock_in_time);
            $clockOutTime = $request->clock_out_time ? $date->copy()->setTimeFromTimeString($request->clock_out_time) : null;

            // Handle overnight shifts
            if ($clockOutTime && $clockOutTime->lt($clockInTime)) {
                $clockOutTime->addDay();
            }

            $attendance = Attendance::create([
                'employee_id' => $request->employee_id,
                'date' => $date,
                'clock_in_time' => $clockInTime,
                'clock_out_time' => $clockOutTime,
                'break_start_time' => $request->break_start_time ? $date->copy()->setTimeFromTimeString($request->break_start_time) : null,
                'break_end_time' => $request->break_end_time ? $date->copy()->setTimeFromTimeString($request->break_end_time) : null,
                'location' => $request->location ?? 'Office',
                'ip_address' => $request->ip(),
                'notes' => $request->notes,
                'status' => $clockOutTime ? 'clocked_out' : 'present',
            ]);

            // Calculate hours
            if ($clockOutTime) {
                $attendance->total_hours = $attendance->calculateTotalHours();
                $attendance->overtime_hours = $attendance->calculateOvertimeHours();
                $attendance->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance record created successfully',
                'data' => $attendance->load('employee')
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in AttendanceController@store: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create attendance record: ' . $e->getMessage()
            ], 500);
        }
    }
}
