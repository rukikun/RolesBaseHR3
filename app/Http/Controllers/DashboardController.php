<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\TimeEntry;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Claim;
use App\Models\Shift;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get the authenticated employee directly
        $employee = Auth::guard('employee')->user() ?? Employee::find(1); // Fallback for testing
        
        // Dashboard statistics
        $stats = [
            'total_employees' => DB::table('employees')->where('status', 'active')->count(),
            'online_employees' => DB::table('time_entries')
                ->join('employees', 'time_entries.employee_id', '=', 'employees.id')
                ->where('employees.status', 'active')
                ->whereDate('time_entries.work_date', today())
                ->whereNotNull('time_entries.clock_in_time')
                ->distinct('employees.id')
                ->count(),
            'employees_with_timesheets' => DB::table('time_entries')
                ->join('employees', 'time_entries.employee_id', '=', 'employees.id')
                ->where('employees.status', 'active')
                ->where('time_entries.status', 'pending')
                ->distinct('employees.id')
                ->count(),
            'departments' => DB::table('employees')
                ->where('status', 'active')
                ->whereNotNull('department')
                ->where('department', '!=', '')
                ->distinct('department')
                ->count()
        ];

        // Recent time entries
        $recentEntries = DB::table('time_entries')
            ->join('employees', 'time_entries.employee_id', '=', 'employees.id')
            ->select(
                'time_entries.*',
                DB::raw('CONCAT(employees.first_name, " ", employees.last_name) as employee_name'),
                DB::raw('DATE_FORMAT(time_entries.clock_in_time, "%h:%i %p") as formatted_clock_in'),
                DB::raw('DATE_FORMAT(time_entries.clock_out_time, "%h:%i %p") as formatted_clock_out'),
                DB::raw('CASE 
                    WHEN time_entries.clock_in_time IS NOT NULL AND time_entries.clock_out_time IS NOT NULL 
                    THEN ROUND(TIME_TO_SEC(TIMEDIFF(time_entries.clock_out_time, time_entries.clock_in_time)) / 3600, 2)
                    ELSE NULL 
                END as total_hours')
            )
            ->orderBy('time_entries.work_date', 'desc')
            ->orderBy('time_entries.created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($entry) {
                return (object) [
                    'id' => $entry->id,
                    'employee_name' => $entry->employee_name,
                    'entry_date' => $entry->work_date ? Carbon::parse($entry->work_date) : null,
                    'formatted_clock_in' => $entry->formatted_clock_in,
                    'formatted_clock_out' => $entry->formatted_clock_out,
                    'total_hours' => $entry->total_hours,
                    'status' => $entry->status ?? 'pending'
                ];
            });

        // Get today's shift schedule
        $shiftData = $this->getTodayShifts();
        $todayShifts = $shiftData['shifts'] ?? collect();
        $shiftDate = $shiftData['date'] ?? today()->toDateString();
        $isToday = $shiftData['is_today'] ?? true;
        
        // Get leave requests for stats
        $leaveRequests = DB::table('leave_requests')
            ->where('status', 'pending')
            ->count();
            
        // If no leave requests table exists, set to 0
        if (!$leaveRequests) {
            $leaveRequests = 0;
        }

        // Get employees and other data for dropdowns
        $employees = DB::table('employees')
            ->where('status', 'active')
            ->select('id', 'first_name', 'last_name')
            ->get();

        $leaveTypes = DB::table('leave_types')
            ->where('is_active', 1)
            ->select('id', 'name')
            ->get();

        $claimTypes = DB::table('claim_types')
            ->select('id', 'name')
            ->get();

        // Current user's clock status
        $todayTimeEntry = null;
        if ($employee && $employee->id) {
            $todayTimeEntry = DB::table('time_entries')
                ->where('employee_id', $employee->id)
                ->whereDate('work_date', today())
                ->first();
        }

        $isClockedIn = $todayTimeEntry && $todayTimeEntry->clock_in_time && !$todayTimeEntry->clock_out_time;

        return view('dashboard.index', compact(
            'stats',
            'employeeStats',
            'recentEntries',
            'employees',
            'leaveTypes',
            'claimTypes',
            'todayShifts',
            'shiftDate',
            'isToday',
            'leaveRequests'
        ));
    }

    public function clockIn(Request $request)
    {
        $employee = $this->resolveAttendanceEmployee();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'No employee found for clock in'
            ], 404);
        }
        
        $todayEntry = $employee->getTodayTimeEntry();
        
        if ($todayEntry && $todayEntry->clock_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Already clocked in today'
            ]);
        }

        if (!$todayEntry) {
            $todayEntry = new TimeEntry([
                'employee_id' => $employee->id,
                'work_date' => today(),
                'status' => 'pending'
            ]);
        }

        $todayEntry->clockIn();

        // Sync attendance table
        $clockInTime = Carbon::now();
        $attendance = Attendance::getTodayAttendance($employee->id);
        $standardStartTime = Carbon::today()->setTime(9, 0, 0);
        $status = $clockInTime->gt($standardStartTime) ? 'late' : 'present';

        if ($attendance) {
            $attendance->update([
                'clock_in_time' => $clockInTime,
                'status' => $status,
                'location' => 'HR Dashboard',
                'ip_address' => $request->ip()
            ]);
        } else {
            Attendance::create([
                'employee_id' => $employee->id,
                'date' => Carbon::today(),
                'clock_in_time' => $clockInTime,
                'status' => $status,
                'location' => 'HR Dashboard',
                'ip_address' => $request->ip()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Clocked in successfully',
            'clock_in_time' => $todayEntry->formatted_clock_in,
            'entry_date' => $todayEntry->work_date ? $todayEntry->work_date->format('M d, Y') : date('M d, Y')
        ]);
    }

    public function clockOut(Request $request)
    {
        $employee = $this->resolveAttendanceEmployee();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'No employee found for clock out'
            ], 404);
        }
        
        $todayEntry = $employee->getTodayTimeEntry();
        
        if (!$todayEntry || !$todayEntry->clock_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Must clock in first'
            ]);
        }

        if ($todayEntry->clock_out_time) {
            return response()->json([
                'success' => false,
                'message' => 'Already clocked out today'
            ]);
        }

        $todayEntry->clockOut();

        // Sync attendance table
        $attendance = Attendance::getTodayAttendance($employee->id);
        if ($attendance && !$attendance->clock_out_time) {
            $attendance->clock_out_time = Carbon::now();
            $attendance->status = 'clocked_out';
            $attendance->total_hours = $attendance->calculateTotalHours();
            $attendance->overtime_hours = $attendance->calculateOvertimeHours();
            $attendance->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Clocked out successfully',
            'clock_out_time' => $todayEntry->formatted_clock_out,
            'total_hours' => $todayEntry->total_hours . ' hrs'
        ]);
    }

    public function getStats()
    {
        $stats = [
            'total_employees' => DB::table('employees')->where('status', 'active')->count(),
            'online_employees' => DB::table('employees')
                ->where('status', 'active')
                ->count(), // For now, show active employees as online
            'employees_with_timesheets' => DB::table('employees')
                ->join('time_entries', 'employees.id', '=', 'time_entries.employee_id')
                ->where('employees.status', 'active')
                ->whereDate('time_entries.work_date', today())
                ->distinct('employees.id')
                ->count(),
            'departments' => DB::table('employees')
                ->where('status', 'active')
                ->distinct('department')
                ->count()
        ];

        return response()->json($stats);
    }

    public function getAttendanceStatus()
    {
        try {
            $employee = $this->resolveAttendanceEmployee();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            $attendance = Attendance::getTodayAttendance($employee->id);

            if (!$attendance) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'employee_id' => $employee->id,
                        'clocked_in' => false,
                        'last_clock_in' => null,
                        'last_clock_out' => null,
                        'status' => 'not_clocked_in'
                    ]
                ]);
            }

            $lastClockIn = $attendance->clock_in_time
                ? Carbon::parse($attendance->clock_in_time)->format('h:i A')
                : null;
            $lastClockOut = $attendance->clock_out_time
                ? Carbon::parse($attendance->clock_out_time)->format('h:i A')
                : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_id' => $employee->id,
                    'clocked_in' => (bool) $attendance->clock_in_time && !$attendance->clock_out_time,
                    'last_clock_in' => $lastClockIn,
                    'last_clock_out' => $lastClockOut,
                    'status' => $attendance->status,
                    'total_hours' => $attendance->total_hours
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading attendance status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load attendance status'
            ], 500);
        }
    }

    private function resolveAttendanceEmployee(): ?Employee
    {
        $employee = Auth::guard('employee')->user();

        if ($employee instanceof Employee) {
            return $employee;
        }

        $user = Auth::guard('web')->user();

        if ($user) {
            try {
                if (method_exists($user, 'employee')) {
                    $linkedEmployee = $user->employee;
                    if ($linkedEmployee instanceof Employee) {
                        return $linkedEmployee;
                    }
                }
            } catch (\Exception $e) {
                // Ignore relation lookup issues
            }

            if (!empty($user->email)) {
                $employeeByEmail = Employee::where('email', $user->email)->first();
                if ($employeeByEmail) {
                    return $employeeByEmail;
                }
            }
        }

        return Employee::active()->first() ?? Employee::first();
    }

    private function buildAttendanceLogQuery(Request $request)
    {
        $query = Attendance::with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $period = $request->input('period', 'today');

        switch ($period) {
            case 'yesterday':
                $query->whereDate('date', Carbon::yesterday());
                break;
            case 'current-week':
                $query->whereBetween('date', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'last-week':
                $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
                $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();
                $query->whereBetween('date', [$lastWeekStart, $lastWeekEnd]);
                break;
            case 'current-month':
                $query->whereMonth('date', Carbon::now()->month)
                    ->whereYear('date', Carbon::now()->year);
                break;
            case 'last-month':
                $lastMonth = Carbon::now()->subMonth();
                $query->whereMonth('date', $lastMonth->month)
                    ->whereYear('date', $lastMonth->year);
                break;
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $query->whereBetween('date', [$request->start_date, $request->end_date]);
                } elseif ($request->filled('start_date')) {
                    $query->whereDate('date', '>=', $request->start_date);
                } elseif ($request->filled('end_date')) {
                    $query->whereDate('date', '<=', $request->end_date);
                }
                break;
            case 'today':
            default:
                $query->whereDate('date', Carbon::today());
                break;
        }

        return $query;
    }

    private function formatAttendanceRecord(Attendance $attendance): array
    {
        $employeeName = $attendance->employee
            ? $attendance->employee->full_name
            : 'Unknown Employee';

        $totalHours = $attendance->total_hours;
        if (($totalHours === null || $totalHours < 0) && $attendance->clock_in_time && $attendance->clock_out_time) {
            $totalHours = $attendance->calculateTotalHours();
        }

        $breakMinutes = null;
        if ($attendance->break_start_time && $attendance->break_end_time) {
            $breakMinutes = abs(
                Carbon::parse($attendance->break_end_time)
                    ->diffInMinutes(Carbon::parse($attendance->break_start_time))
            );
        }

        return [
            'id' => $attendance->id,
            'employee_name' => $employeeName,
            'date' => $attendance->date ? Carbon::parse($attendance->date)->format('Y-m-d') : null,
            'clock_in' => $attendance->clock_in_time ? Carbon::parse($attendance->clock_in_time)->format('h:i A') : null,
            'clock_out' => $attendance->clock_out_time ? Carbon::parse($attendance->clock_out_time)->format('h:i A') : null,
            'total_hours' => $totalHours !== null ? (float) $totalHours : null,
            'break_time' => $breakMinutes,
            'status' => $attendance->status ?? 'unknown',
            'notes' => $attendance->notes,
            'location' => $attendance->location,
        ];
    }

    private function buildAttendanceTimeline(Attendance $attendance): array
    {
        $timeline = [];

        if ($attendance->clock_in_time) {
            $timeline[] = [
                'time' => Carbon::parse($attendance->clock_in_time)->format('h:i A'),
                'action' => 'Clocked in'
            ];
        }

        if ($attendance->break_start_time) {
            $timeline[] = [
                'time' => Carbon::parse($attendance->break_start_time)->format('h:i A'),
                'action' => 'Break started'
            ];
        }

        if ($attendance->break_end_time) {
            $timeline[] = [
                'time' => Carbon::parse($attendance->break_end_time)->format('h:i A'),
                'action' => 'Break ended'
            ];
        }

        if ($attendance->clock_out_time) {
            $timeline[] = [
                'time' => Carbon::parse($attendance->clock_out_time)->format('h:i A'),
                'action' => 'Clocked out'
            ];
        }

        return $timeline;
    }

    public function getAttendanceStats()
    {
        try {
            $employee = $this->resolveAttendanceEmployee();

            if (!$employee) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'today_hours' => 0,
                        'week_hours' => 0,
                        'month_hours' => 0,
                        'attendance_rate' => 100
                    ]
                ]);
            }

            $todayAttendance = Attendance::getTodayAttendance($employee->id);
            $todayHours = 0;

            if ($todayAttendance && $todayAttendance->clock_in_time && $todayAttendance->clock_out_time) {
                if (!$todayAttendance->total_hours) {
                    $todayAttendance->total_hours = $todayAttendance->calculateTotalHours();
                    $todayAttendance->overtime_hours = $todayAttendance->calculateOvertimeHours();
                    $todayAttendance->save();
                }
                $todayHours = (float) $todayAttendance->total_hours;
            }

            $weekHours = (float) Attendance::where('employee_id', $employee->id)
                ->thisWeek()
                ->sum('total_hours');

            $monthRecords = Attendance::where('employee_id', $employee->id)
                ->thisMonth()
                ->get();

            $monthHours = (float) $monthRecords->sum('total_hours');
            $totalDays = $monthRecords->count();
            $presentDays = $monthRecords
                ->whereIn('status', ['present', 'late', 'clocked_out'])
                ->count();
            $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 100;

            return response()->json([
                'success' => true,
                'data' => [
                    'today_hours' => $todayHours,
                    'week_hours' => $weekHours,
                    'month_hours' => $monthHours,
                    'attendance_rate' => $attendanceRate
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading attendance stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load attendance stats'
            ], 500);
        }
    }

    public function getAttendanceLog(Request $request)
    {
        try {
            $query = $this->buildAttendanceLogQuery($request);
            $attendances = $query->orderBy('date', 'desc')
                ->orderBy('clock_in_time', 'desc')
                ->limit(200)
                ->get();

            $records = $attendances->map(function (Attendance $attendance) {
                return $this->formatAttendanceRecord($attendance);
            });

            return response()->json([
                'success' => true,
                'data' => $records
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading attendance log: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load attendance log'
            ], 500);
        }
    }

    public function getAttendanceDetail($id)
    {
        try {
            $attendance = Attendance::with('employee')->find($id);

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record not found'
                ], 404);
            }

            $record = $this->formatAttendanceRecord($attendance);
            $record['timeline'] = $this->buildAttendanceTimeline($attendance);

            return response()->json([
                'success' => true,
                'data' => $record
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading attendance detail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load attendance detail'
            ], 500);
        }
    }

    public function exportAttendance(Request $request)
    {
        try {
            $query = $this->buildAttendanceLogQuery($request);
            $attendances = $query->orderBy('date', 'desc')
                ->orderBy('clock_in_time', 'desc')
                ->get();

            $records = $attendances->map(function (Attendance $attendance) {
                return $this->formatAttendanceRecord($attendance);
            });

            return response()->json([
                'success' => true,
                'data' => $records
            ]);
        } catch (\Exception $e) {
            \Log::error('Error exporting attendance: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export attendance data'
            ], 500);
        }
    }

    public function getRecentEntries()
    {
        $entries = TimeEntry::with('employee')
            ->orderBy('work_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'employee_name' => $entry->employee->full_name,
                    'entry_date' => $entry->work_date ? $entry->work_date->format('M d, Y') : '--',
                    'clock_in' => $entry->formatted_clock_in ?? '--',
                    'clock_out' => $entry->formatted_clock_out ?? '--',
                    'total_hours' => $entry->total_hours ? $entry->total_hours . ' hrs' : '--',
                    'status' => $entry->status,
                    'status_badge' => $this->getStatusBadge($entry->status)
                ];
            });

        return response()->json($entries);
    }

    public function getTimesheetData(Request $request)
    {
        $query = TimeEntry::with('employee');

        // Filter by employee if specified
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by period
        switch ($request->period) {
            case 'current-week':
                $query->whereBetween('work_date', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
                break;
            case 'last-week':
                $query->whereBetween('work_date', [
                    now()->subWeek()->startOfWeek(),
                    now()->subWeek()->endOfWeek()
                ]);
                break;
            case 'current-month':
                $query->whereMonth('work_date', now()->month)
                      ->whereYear('work_date', now()->year);
                break;
            case 'last-month':
                $query->whereMonth('work_date', now()->subMonth()->month)
                      ->whereYear('work_date', now()->subMonth()->year);
                break;
            case 'custom':
                if ($request->start_date && $request->end_date) {
                    $query->whereBetween('work_date', [
                        $request->start_date,
                        $request->end_date
                    ]);
                }
                break;
        }

        $entries = $query->orderBy('work_date', 'desc')
                        ->get()
                        ->map(function ($entry) {
                            return [
                                'id' => $entry->id,
                                'employee_name' => $entry->employee->full_name,
                                'entry_date' => $entry->work_date ? $entry->work_date->format('M d, Y') : '--',
                                'clock_in' => $entry->formatted_clock_in ?? '--',
                                'clock_out' => $entry->formatted_clock_out ?? '--',
                                'total_hours' => $entry->total_hours ? $entry->total_hours . ' hrs' : '--',
                                'status' => $entry->status,
                                'status_badge' => $this->getStatusBadge($entry->status)
                            ];
                        });

        return response()->json($entries);
    }

    public function getLeaveData()
    {
        $employee = Auth::guard('employee')->user() ?? Employee::find(1);

        // Leave balances
        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', date('Y'))
            ->get()
            ->map(function ($balance) {
                return [
                    'leave_type' => $balance->leaveType->name,
                    'remaining_days' => $balance->remaining_days
                ];
            });

        // Pending leave requests
        $pendingRequests = LeaveRequest::with('leaveType')
            ->where('employee_id', $employee->id)
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'leave_type' => $request->leaveType->name,
                    'start_date' => $request->start_date ? $request->start_date->format('M d, Y') : '--',
                    'end_date' => $request->end_date ? $request->end_date->format('M d, Y') : '--',
                    'days_requested' => $request->days_requested,
                    'reason' => $request->reason
                ];
            });

        // Leave history
        $leaveHistory = LeaveRequest::with('leaveType')
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'leave_type' => $request->leaveType->name,
                    'start_date' => $request->start_date ? $request->start_date->format('M d, Y') : '--',
                    'end_date' => $request->end_date ? $request->end_date->format('M d, Y') : '--',
                    'days_requested' => $request->days_requested,
                    'status' => $request->status,
                    'status_badge' => $this->getStatusBadge($request->status)
                ];
            });

        return response()->json([
            'balances' => $leaveBalances,
            'pending_requests' => $pendingRequests,
            'history' => $leaveHistory
        ]);
    }

    public function getClaimsData()
    {
        $employee = Auth::guard('employee')->user() ?? Employee::find(1);

        // Pending claims
        $pendingClaims = Claim::with('claimType')
            ->where('employee_id', $employee->id)
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($claim) {
                return [
                    'id' => $claim->id,
                    'type' => $claim->claimType->name,
                    'amount' => '$' . number_format($claim->amount, 2),
                    'description' => $claim->description,
                    'claim_date' => $claim->claim_date ? $claim->claim_date->format('M d, Y') : '--'
                ];
            });

        // Approved claims
        $approvedClaims = Claim::with('claimType')
            ->where('employee_id', $employee->id)
            ->approved()
            ->orderBy('approved_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($claim) {
                return [
                    'id' => $claim->id,
                    'type' => $claim->claimType->name,
                    'amount' => '$' . number_format($claim->amount, 2),
                    'description' => $claim->description,
                    'claim_date' => $claim->claim_date ? $claim->claim_date->format('M d, Y') : '--'
                ];
            });

        // Claims history
        $claimsHistory = Claim::with('claimType')
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($claim) {
                return [
                    'id' => $claim->id,
                    'type' => $claim->claimType->name,
                    'amount' => '$' . number_format($claim->amount, 2),
                    'description' => $claim->description,
                    'claim_date' => $claim->claim_date ? $claim->claim_date->format('M d, Y') : '--',
                    'status' => $claim->status,
                    'status_badge' => $this->getStatusBadge($claim->status)
                ];
            });

        return response()->json([
            'pending_claims' => $pendingClaims,
            'approved_claims' => $approvedClaims,
            'history' => $claimsHistory
        ]);
    }

    private function getTodayShifts()
    {
        try {
            \Log::info('Getting today shifts for date: ' . today()->toDateString());
            
            // Get shift types with employee assignments for today
            $shiftTypes = DB::table('shift_types')
                ->where('is_active', 1)
                ->get();

            \Log::info('Found shift types: ' . $shiftTypes->count());

            $shifts = collect();
            
            // First, check if there are any shifts for today
            $todayShiftsCount = DB::table('shifts')
                ->whereDate('shift_date', today())
                ->count();
                
            \Log::info('Shifts for today (' . today()->toDateString() . '): ' . $todayShiftsCount);
            
            // If no shifts for today, get the most recent shift date
            $targetDate = today();
            if ($todayShiftsCount == 0) {
                $mostRecentDate = DB::table('shifts')
                    ->orderBy('shift_date', 'desc')
                    ->value('shift_date');
                    
                if ($mostRecentDate) {
                    $targetDate = \Carbon\Carbon::parse($mostRecentDate);
                    \Log::info('No shifts for today, using most recent date: ' . $targetDate->toDateString());
                }
            }

            foreach ($shiftTypes as $shiftType) {
                \Log::info('Processing shift type: ' . $shiftType->name . ' (ID: ' . $shiftType->id . ')');
                
                // Get employees assigned to this shift type for the target date
                $employees = DB::table('shifts')
                    ->join('employees', 'shifts.employee_id', '=', 'employees.id')
                    ->where('shifts.shift_type_id', $shiftType->id)
                    ->whereDate('shifts.shift_date', $targetDate)
                    ->where('employees.status', 'active')
                    ->select(
                        'employees.id',
                        'employees.first_name',
                        'employees.last_name',
                        'employees.position',
                        'shifts.shift_date'
                    )
                    ->get();

                \Log::info('Found employees for shift type ' . $shiftType->id . ': ' . $employees->count());

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
                            'name' => $emp->first_name . ' ' . $emp->last_name,
                            'position' => $emp->position
                        ];
                    })->toArray()
                ]);
            }

            // If no shifts found, return default shifts with sample assignments
            if ($shifts->isEmpty()) {
                $allEmployees = DB::table('employees')
                    ->where('status', 'active')
                    ->select('id', 'first_name', 'last_name', 'position')
                    ->get();
                
                // Distribute employees across shifts
                $employeeChunks = $allEmployees->chunk(ceil($allEmployees->count() / 3));
                
                return collect([
                    [
                        'id' => 1,
                        'name' => 'Morning Shift',
                        'time_range' => '8:00 AM - 4:00 PM',
                        'employee_count' => $employeeChunks->get(0, collect())->count(),
                        'employees' => $employeeChunks->get(0, collect())->map(function ($emp) {
                            return [
                                'id' => $emp->id,
                                'name' => $emp->first_name . ' ' . $emp->last_name,
                                'position' => $emp->position
                            ];
                        })->toArray()
                    ],
                    [
                        'id' => 2,
                        'name' => 'Afternoon Shift',
                        'time_range' => '2:00 PM - 10:00 PM',
                        'employee_count' => $employeeChunks->get(1, collect())->count(),
                        'employees' => $employeeChunks->get(1, collect())->map(function ($emp) {
                            return [
                                'id' => $emp->id,
                                'name' => $emp->first_name . ' ' . $emp->last_name,
                                'position' => $emp->position
                            ];
                        })->toArray()
                    ],
                    [
                        'id' => 3,
                        'name' => 'Night Shift',
                        'time_range' => '10:00 PM - 6:00 AM',
                        'employee_count' => $employeeChunks->get(2, collect())->count(),
                        'employees' => $employeeChunks->get(2, collect())->map(function ($emp) {
                            return [
                                'id' => $emp->id,
                                'name' => $emp->first_name . ' ' . $emp->last_name,
                                'position' => $emp->position
                            ];
                        })->toArray()
                    ]
                ]);
            }

            \Log::info('Returning ' . $shifts->count() . ' shifts for date: ' . $targetDate->toDateString());
            
            return [
                'shifts' => $shifts,
                'date' => $targetDate->toDateString(),
                'is_today' => $targetDate->isToday()
            ];

        } catch (\Exception $e) {
            \Log::error('Error getting today shifts: ' . $e->getMessage());
            return [
                'shifts' => collect(),
                'date' => today()->toDateString(),
                'is_today' => true
            ];
        }
    }

    private function getStatusBadge($status)
    {
        switch ($status) {
            case 'approved':
                return 'bg-success';
            case 'pending':
                return 'bg-warning';
            case 'rejected':
                return 'bg-danger';
            case 'paid':
                return 'bg-info';
            default:
                return 'bg-secondary';
        }
    }

    // Submit attendance correction
    public function submitAttendanceCorrection(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:500'
        ]);

        $employee = Auth::guard('employee')->user() ?? Employee::find(1);

        try {
            // Check if entry exists for this date
            $existingEntry = DB::selectOne(
                "SELECT * FROM time_entries WHERE employee_id = ? AND work_date = ?",
                [$employee->id, $request->date]
            );

            if ($existingEntry) {
                // Update existing entry
                DB::update(
                    "UPDATE time_entries SET clock_in_time = ?, clock_out_time = ?, status = 'pending', updated_at = NOW() WHERE id = ?",
                    [$request->clock_in, $request->clock_out, $existingEntry->id]
                );
            } else {
                // Create new entry
                DB::insert(
                    "INSERT INTO time_entries (employee_id, work_date, clock_in_time, clock_out_time, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'pending', NOW(), NOW())",
                    [$employee->id, $request->date, $request->clock_in, $request->clock_out]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance correction submitted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting attendance correction: ' . $e->getMessage()
            ], 500);
        }
    }

    // Submit overtime request
    public function submitOvertimeRequest(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'overtime_hours' => 'required|numeric|min:0.5|max:12',
            'reason' => 'required|string|max:500'
        ]);

        $employee = Auth::guard('employee')->user() ?? Employee::find(1);

        try {
            // Check if entry exists for this date
            $existingEntry = DB::selectOne(
                "SELECT * FROM time_entries WHERE employee_id = ? AND work_date = ?",
                [$employee->id, $request->date]
            );

            if ($existingEntry) {
                // Update existing entry with overtime
                DB::update(
                    "UPDATE time_entries SET overtime_hours = ?, description = CONCAT(COALESCE(description, ''), ' - Overtime: ', ?), status = 'pending', updated_at = NOW() WHERE id = ?",
                    [$request->overtime_hours, $request->reason, $existingEntry->id]
                );
            } else {
                // Create new overtime entry
                DB::insert(
                    "INSERT INTO time_entries (employee_id, work_date, overtime_hours, description, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'pending', NOW(), NOW())",
                    [$employee->id, $request->date, $request->overtime_hours, 'Overtime: ' . $request->reason]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Overtime request submitted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting overtime request: ' . $e->getMessage()
            ], 500);
        }
    }

    // Manual time entry
    public function manualTimeEntry(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'date' => 'required|date',
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'hours_worked' => 'nullable|numeric|min:0|max:24',
            'overtime_hours' => 'nullable|numeric|min:0|max:12',
            'description' => 'nullable|string|max:1000'
        ]);

        try {
            DB::insert(
                "INSERT INTO time_entries (employee_id, work_date, clock_in_time, clock_out_time, hours_worked, overtime_hours, description, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())",
                [
                    $request->employee_id,
                    $request->date,
                    $request->clock_in,
                    $request->clock_out,
                    $request->hours_worked ?? 0,
                    $request->overtime_hours ?? 0,
                    $request->description
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Manual time entry created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating manual time entry: ' . $e->getMessage()
            ], 500);
        }
    }
}




