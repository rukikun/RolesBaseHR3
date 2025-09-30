<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\TimeEntry;
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
        $user = Auth::user();
        $employee = $user->employee ?? Employee::find(1); // Fallback for testing
        
        // Dashboard statistics
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

        return view('admin_dashboard', compact(
            'stats',
            'recentEntries',
            'todayTimeEntry',
            'isClockedIn',
            'employee',
            'employees',
            'leaveTypes',
            'claimTypes'
        ));
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee ?? Employee::find(1);
        
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

        return response()->json([
            'success' => true,
            'message' => 'Clocked in successfully',
            'clock_in_time' => $todayEntry->formatted_clock_in,
            'entry_date' => $todayEntry->work_date ? $todayEntry->work_date->format('M d, Y') : date('M d, Y')
        ]);
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee ?? Employee::find(1);
        
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
        $user = Auth::user();
        $employee = $user->employee ?? Employee::find(1);

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
        $user = Auth::user();
        $employee = $user->employee ?? Employee::find(1);

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
        $shifts = DB::table('employee_shifts')
            ->join('shifts', 'employee_shifts.shift_id', '=', 'shifts.id')
            ->select('shifts.name', 'shifts.start_time', 'shifts.end_time', 
                    DB::raw('COUNT(employee_shifts.id) as employee_count'))
            ->where('employee_shifts.shift_date', today())
            ->groupBy('shifts.id', 'shifts.name', 'shifts.start_time', 'shifts.end_time')
            ->get()
            ->map(function ($shift) {
                return [
                    'name' => $shift->name,
                    'time_range' => Carbon::createFromFormat('H:i:s', $shift->start_time)->format('g:i A') . 
                                  ' - ' . 
                                  Carbon::createFromFormat('H:i:s', $shift->end_time)->format('g:i A'),
                    'employee_count' => $shift->employee_count
                ];
            });

        return $shifts;
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

        $user = Auth::user();
        $employee = $user->employee ?? Employee::find(1);

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

        $user = Auth::user();
        $employee = $user->employee ?? Employee::find(1);

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
