<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\TimeEntry;

class EmployeeESSController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:employee')->except(['showLogin', 'login']);
    }

    /**
     * Show the employee dashboard
     */
    public function dashboard()
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            // Get employee statistics
            $stats = $this->getEmployeeStats($employee->id);
            
            // Get notifications
            $notifications = $this->getNotifications($employee->id);
            
            // Get upcoming trainings
            $upcomingTrainingsList = $this->getUpcomingTrainings($employee->id);
            
            // Get recent requests
            $recentRequests = $this->getRecentRequests($employee->id);
            
            // Get attendance logs for dashboard
            $attendanceLogs = $this->getAttendanceLogsForDashboard($employee->id);
            
            // Ensure attendanceLogs is always a collection
            if (!$attendanceLogs) {
                $attendanceLogs = collect([]);
            }
            
            return view('employee_ess_modules.employee_dashboard', compact(
                'employee',
                'notifications',
                'upcomingTrainingsList',
                'recentRequests',
                'attendanceLogs'
            ) + $stats);
            
        } catch (\Exception $e) {
            \Log::error('Employee dashboard error: ' . $e->getMessage());
            
            // Return with default values if there's an error
            return view('employee_ess_modules.employee_dashboard', [
                'employee' => $employee,
                'notifications' => collect([]),
                'upcomingTrainingsList' => [],
                'recentRequests' => [],
                'attendanceLogs' => collect([]),
                'pendingLeaveRequests' => 0,
                'attendanceRate' => 0,
                'latestPayslip' => 0,
                'payslipMonth' => 'N/A',
                'upcomingTrainings' => 0,
                'competencyGoalsAchieved' => 0,
                'totalCompetencies' => 1,
                'competencyProgress' => 0,
                'completedTrainings' => 0,
                'totalTrainings' => 1,
                'trainingCompletionRate' => 0
            ]);
        }
    }

    /**
     * Show employee login page
     */
    public function showLogin()
    {
        return view('employee_ess_modules.employee_login');
    }

    /**
     * Handle employee login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('employee')->attempt($credentials)) {
            $employee = Auth::guard('employee')->user();
            
            // Update online status
            DB::table('employees')
                ->where('id', $employee->id)
                ->update([
                    'online_status' => 'online',
                    'last_activity' => now()
                ]);

            return redirect()->route('employee.dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Handle employee logout
     */
    public function logout()
    {
        $employee = Auth::guard('employee')->user();
        
        if ($employee) {
            // Update offline status
            DB::table('employees')
                ->where('id', $employee->id)
                ->update([
                    'online_status' => 'offline',
                    'last_activity' => now()
                ]);
        }

        Auth::guard('employee')->logout();
        return redirect('/employee/login');
    }

    /**
     * Show employee profile
     */
    public function profile()
    {
        $employee = Auth::guard('employee')->user();
        return view('employee_ess_modules.employee_profile', compact('employee'));
    }

    /**
     * Update employee profile
     */
    public function updateProfile(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'phone_number' => 'nullable|string|max:20',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $updateData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'updated_at' => now()
        ];

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($employee->profile_picture) {
                Storage::disk('public')->delete($employee->profile_picture);
            }

            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $updateData['profile_picture'] = $path;
        }

        DB::table('employees')
            ->where('id', $employee->id)
            ->update($updateData);

        return response()->json(['success' => true, 'message' => 'Profile updated successfully']);
    }

    /**
     * Log attendance
     */
    public function logAttendance(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        $timestamp = $request->input('timestamp', now());

        try {
            DB::table('time_entries')->insert([
                'employee_id' => $employee->id,
                'work_date' => Carbon::parse($timestamp)->toDateString(),
                'clock_in' => Carbon::parse($timestamp)->toTimeString(),
                'hours_worked' => 0,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Attendance logged successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error logging attendance: ' . $e->getMessage()]);
        }
    }

    /**
     * Submit leave application
     */
    public function submitLeaveApplication(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        
        $request->validate([
            'leave_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string'
        ]);

        try {
            // Get leave type ID
            $leaveType = DB::table('leave_types')
                ->where('name', $request->leave_type)
                ->first();

            if (!$leaveType) {
                // Create leave type if it doesn't exist
                $leaveTypeId = DB::table('leave_types')->insertGetId([
                    'name' => $request->leave_type,
                    'days_allowed' => 15,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $leaveTypeId = $leaveType->id;
            }

            DB::table('leave_requests')->insert([
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveTypeId,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'reason' => $request->reason,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Leave application submitted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error submitting leave application: ' . $e->getMessage()]);
        }
    }

    /**
     * Submit request form
     */
    public function submitRequestForm(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        
        $request->validate([
            'request_type' => 'required|string',
            'reason' => 'required|string',
            'requested_date' => 'nullable|date'
        ]);

        try {
            DB::table('employee_requests')->insert([
                'employee_id' => $employee->id,
                'request_type' => $request->request_type,
                'reason' => $request->reason,
                'requested_date' => $request->requested_date,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Request submitted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error submitting request: ' . $e->getMessage()]);
        }
    }

    /**
     * Get latest payslip
     */
    public function getLatestPayslip()
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            $payslip = DB::table('payslips')
                ->where('employee_id', $employee->id)
                ->orderBy('pay_period_end', 'desc')
                ->first();

            if ($payslip) {
                return response()->download(storage_path('app/payslips/' . $payslip->file_path));
            } else {
                return response()->json(['error' => 'No payslip found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving payslip'], 500);
        }
    }

    /**
     * Get CSRF token for AJAX requests
     */
    public function getCsrfToken()
    {
        return response()->json(['csrf_token' => csrf_token()]);
    }

    /**
     * Show timesheet management page
     */
    public function timesheet()
    {
        $employee = Auth::guard('employee')->user();
        
        // Get employee's timesheets
        $timesheets = DB::table('time_entries')
            ->where('employee_id', $employee->id)
            ->orderBy('work_date', 'desc')
            ->get();
            
        return view('employee_ess_modules.timesheet_management', compact('employee', 'timesheets'));
    }

    /**
     * Show timesheet history page
     */
    public function timesheetHistory()
    {
        $employee = Auth::guard('employee')->user();
        
        // Get employee's timesheet history with pagination
        $timesheets = DB::table('time_entries')
            ->where('employee_id', $employee->id)
            ->orderBy('work_date', 'desc')
            ->get();
            
        // Get timesheet statistics
        $stats = [
            'totalEntries' => $timesheets->count(),
            'totalHours' => $timesheets->sum('hours_worked'),
            'approvedEntries' => $timesheets->where('status', 'approved')->count(),
            'pendingEntries' => $timesheets->where('status', 'pending')->count()
        ];
            
        return view('employee_ess_modules.timesheet_history', compact('employee', 'timesheets', 'stats'));
    }

    /**
     * Show employee schedule page
     */
    public function schedule()
    {
        $employee = Auth::guard('employee')->user();
        
        // Get employee's upcoming shifts - using try/catch to handle missing table/columns
        try {
            $upcomingShifts = DB::table('shifts as s')
                ->leftJoin('shift_types as st', 's.shift_type_id', '=', 'st.id')
                ->where('s.employee_id', $employee->id)
                ->select('s.*', 'st.name as shift_type_name')
                ->orderBy('s.id', 'desc')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            // If shifts table doesn't exist or has different structure, create empty collection
            $upcomingShifts = collect([]);
        }
            
        // Get employee's leave requests
        $leaveRequests = DB::table('leave_requests as lr')
            ->leftJoin('leave_types as lt', 'lr.leave_type_id', '=', 'lt.id')
            ->where('lr.employee_id', $employee->id)
            ->where('lr.start_date', '>=', now()->toDateString())
            ->select('lr.*', 'lt.name as leave_type_name')
            ->orderBy('lr.start_date', 'asc')
            ->limit(5)
            ->get();
            
        // Calculate statistics
        $stats = [
            'totalShifts' => $upcomingShifts->count(),
            'upcomingShifts' => $upcomingShifts->where('shift_date', '>=', now()->toDateString())->count(),
            'thisWeekShifts' => $upcomingShifts->whereBetween('shift_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'totalHours' => $upcomingShifts->sum('hours') ?: 0
        ];
        
        return view('employee_ess_modules.shift_schedule', [
            'employee' => $employee,
            'shifts' => $upcomingShifts,
            'stats' => $stats
        ]);
    }

    /**
     * Show shift schedule page
     */
    public function shiftSchedule()
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            // Auto-create shifts table if it doesn't exist
            $this->ensureShiftsTableExists();
            
            // Get employee's shifts
            $shifts = DB::table('shifts as s')
                ->leftJoin('shift_types as st', 's.shift_type_id', '=', 'st.id')
                ->where('s.employee_id', $employee->id)
                ->select('s.*', 'st.name as shift_type_name', 'st.start_time', 'st.end_time')
                ->orderBy('s.shift_date', 'desc')
                ->get();
                
            $stats = [
                'totalShifts' => $shifts->count(),
                'upcomingShifts' => $shifts->where('shift_date', '>=', now()->toDateString())->count(),
                'thisWeekShifts' => $shifts->whereBetween('shift_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'totalHours' => $shifts->sum('hours') ?: 0
            ];
            
            return view('employee_ess_modules.shift_schedule', compact('employee', 'shifts', 'stats'));
            
        } catch (\Exception $e) {
            \Log::error('Shift schedule error: ' . $e->getMessage());
            
            return view('employee_ess_modules.shift_schedule', [
                'employee' => $employee,
                'shifts' => collect([]),
                'stats' => [
                    'totalShifts' => 0,
                    'upcomingShifts' => 0,
                    'thisWeekShifts' => 0,
                    'totalHours' => 0
                ]
            ]);
        }
    }

    /**
     * Show shift request page
     */
    public function shiftRequest(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        
        // Build query for employee's shift requests
        $query = DB::table('shift_requests as sr')
            ->leftJoin('shift_types as st', 'sr.shift_type_id', '=', 'st.id')
            ->where('sr.employee_id', $employee->id)
            ->select('sr.*', 'st.name as shift_type_name');
            
        // Apply filters if provided
        if ($request->has('status') && $request->status != '') {
            $query->where('sr.status', $request->status);
        }
        
        if ($request->has('type') && $request->type != '') {
            $query->where('sr.request_type', $request->type);
        }
        
        $shiftRequests = $query->orderBy('sr.created_at', 'desc')->get();
            
        // Get available shift types
        $shiftTypes = DB::table('shift_types')->get();
        
        return view('employee_ess_modules.shift_request', compact('employee', 'shiftRequests', 'shiftTypes'));
    }

    /**
     * Show leave applications page
     */
    public function leaveApplications()
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            // Auto-create leave tables if they don't exist
            $this->ensureLeaveTablesExist();
            
            // Get employee's leave requests
            $leaveRequests = DB::table('leave_requests as lr')
                ->leftJoin('leave_types as lt', 'lr.leave_type_id', '=', 'lt.id')
                ->where('lr.employee_id', $employee->id)
                ->select('lr.*', 'lt.name as leave_type_name')
                ->orderBy('lr.created_at', 'desc')
                ->get();
                
            // Get leave types
            $leaveTypes = DB::table('leave_types')->get();
            
            $stats = [
                'totalRequests' => $leaveRequests->count(),
                'pendingRequests' => $leaveRequests->where('status', 'pending')->count(),
                'approvedRequests' => $leaveRequests->where('status', 'approved')->count(),
                'rejectedRequests' => $leaveRequests->where('status', 'rejected')->count()
            ];
            
            return view('employee_ess_modules.leave_applications', compact('employee', 'leaveRequests', 'leaveTypes', 'stats'));
            
        } catch (\Exception $e) {
            \Log::error('Leave applications error: ' . $e->getMessage());
            
            return view('employee_ess_modules.leave_applications', [
                'employee' => $employee,
                'leaveRequests' => collect([]),
                'leaveTypes' => collect([]),
                'stats' => [
                    'totalRequests' => 0,
                    'pendingRequests' => 0,
                    'approvedRequests' => 0,
                    'rejectedRequests' => 0
                ]
            ]);
        }
    }

    /**
     * Show leave balance page
     */
    public function leaveBalance()
    {
        $employee = Auth::guard('employee')->user();
        
        // Get employee's leave balances
        $leaveBalances = DB::table('leave_balances as lb')
            ->leftJoin('leave_types as lt', 'lb.leave_type_id', '=', 'lt.id')
            ->where('lb.employee_id', $employee->id)
            ->select('lb.*', 'lt.name as leave_type_name', 'lt.days_allowed')
            ->get();
            
        // Get leave usage statistics
        $leaveUsage = DB::table('leave_requests as lr')
            ->leftJoin('leave_types as lt', 'lr.leave_type_id', '=', 'lt.id')
            ->where('lr.employee_id', $employee->id)
            ->where('lr.status', 'approved')
            ->selectRaw('lr.leave_type_id, lt.name as leave_type_name, 
                        COUNT(*) as total_requests,
                        SUM(DATEDIFF(lr.end_date, lr.start_date) + 1) as days_used')
            ->groupBy('lr.leave_type_id', 'lt.name')
            ->get();
            
        return view('employee_ess_modules.leave_balance', compact('employee', 'leaveBalances', 'leaveUsage'));
    }


    /**
     * Show claims history page
     */
    public function claimsHistory()
    {
        $employee = Auth::guard('employee')->user();
        
        // Get employee's claims history
        $claims = DB::table('claims as c')
            ->leftJoin('claim_types as ct', 'c.claim_type_id', '=', 'ct.id')
            ->where('c.employee_id', $employee->id)
            ->select('c.*', 'ct.name as claim_type_name')
            ->orderBy('c.created_at', 'desc')
            ->get();
            
        // Get claims statistics
        $stats = [
            'totalClaims' => $claims->count(),
            'totalAmount' => $claims->sum('amount'),
            'approvedClaims' => $claims->where('status', 'approved')->count(),
            'pendingClaims' => $claims->where('status', 'pending')->count(),
            'rejectedClaims' => $claims->where('status', 'rejected')->count(),
            'approvedAmount' => $claims->where('status', 'approved')->sum('amount')
        ];
            
        return view('employee_ess_modules.claims_history', compact('employee', 'claims', 'stats'));
    }

    /**
     * Get employee statistics
     */
    private function getEmployeeStats($employeeId)
    {
        // Pending leave requests
        $pendingLeaveRequests = DB::table('leave_requests')
            ->where('employee_id', $employeeId)
            ->where('status', 'pending')
            ->count();

        // Attendance rate (current month)
        $currentMonth = Carbon::now()->format('Y-m');
        $workingDays = Carbon::now()->daysInMonth;
        $attendedDays = DB::table('time_entries')
            ->where('employee_id', $employeeId)
            ->where('work_date', 'like', $currentMonth . '%')
            ->count();
        
        $attendanceRate = $workingDays > 0 ? round(($attendedDays / $workingDays) * 100) : 0;

        // Latest payslip
        $latestPayslip = DB::table('payslips')
            ->where('employee_id', $employeeId)
            ->orderBy('pay_period_end', 'desc')
            ->value('net_pay') ?? 0;

        $payslipMonth = DB::table('payslips')
            ->where('employee_id', $employeeId)
            ->orderBy('pay_period_end', 'desc')
            ->value('pay_period_end');
        
        $payslipMonth = $payslipMonth ? Carbon::parse($payslipMonth)->format('M Y') : 'N/A';

        // Upcoming trainings
        $upcomingTrainings = DB::table('employee_trainings')
            ->where('employee_id', $employeeId)
            ->where('status', 'assigned')
            ->where('start_date', '>', now())
            ->count();

        // Competency progress
        $totalCompetencies = DB::table('competency_assessments')
            ->where('employee_id', $employeeId)
            ->count();
        
        $competencyGoalsAchieved = DB::table('competency_assessments')
            ->where('employee_id', $employeeId)
            ->where('score', '>=', 80)
            ->count();

        $competencyProgress = $totalCompetencies > 0 ? round(($competencyGoalsAchieved / $totalCompetencies) * 100) : 0;

        // Training completion
        $totalTrainings = DB::table('employee_trainings')
            ->where('employee_id', $employeeId)
            ->count();
        
        $completedTrainings = DB::table('employee_trainings')
            ->where('employee_id', $employeeId)
            ->where('status', 'completed')
            ->count();

        $trainingCompletionRate = $totalTrainings > 0 ? round(($completedTrainings / $totalTrainings) * 100) : 0;

        return [
            'pendingLeaveRequests' => $pendingLeaveRequests,
            'attendanceRate' => $attendanceRate,
            'latestPayslip' => $latestPayslip,
            'payslipMonth' => $payslipMonth,
            'upcomingTrainings' => $upcomingTrainings,
            'competencyGoalsAchieved' => $competencyGoalsAchieved,
            'totalCompetencies' => max(1, $totalCompetencies),
            'competencyProgress' => $competencyProgress,
            'completedTrainings' => $completedTrainings,
            'totalTrainings' => max(1, $totalTrainings),
            'trainingCompletionRate' => $trainingCompletionRate
        ];
    }

    /**
     * Get notifications for employee
     */
    private function getNotifications($employeeId)
    {
        return DB::table('employee_notifications')
            ->where('employee_id', $employeeId)
            ->where('read_at', null)
            ->orderBy('sent_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get upcoming trainings
     */
    private function getUpcomingTrainings($employeeId)
    {
        $trainings = DB::table('employee_trainings as et')
            ->join('training_programs as tp', 'et.training_id', '=', 'tp.id')
            ->where('et.employee_id', $employeeId)
            ->where('et.status', 'assigned')
            ->where('et.start_date', '>', now())
            ->select('et.*', 'tp.title as training_title')
            ->orderBy('et.start_date')
            ->limit(10)
            ->get();

        return $trainings->map(function($training) {
            return [
                'id' => $training->id,
                'upcoming_id' => $training->id,
                'training_title' => $training->training_title,
                'start_date' => Carbon::parse($training->start_date)->format('M d, Y'),
                'end_date' => Carbon::parse($training->end_date)->format('M d, Y'),
                'status' => ucfirst($training->status),
                'status_class' => $this->getStatusClass($training->status),
                'source' => 'Employee Training Dashboard',
                'assigned_by' => 'HR Department',
                'delivery_mode' => 'Online'
            ];
        })->toArray();
    }

    /**
     * Get recent requests
     */
    private function getRecentRequests($employeeId)
    {
        $requests = collect();

        // Leave requests
        $leaveRequests = DB::table('leave_requests as lr')
            ->join('leave_types as lt', 'lr.leave_type_id', '=', 'lt.id')
            ->where('lr.employee_id', $employeeId)
            ->select('lr.created_at', 'lr.status', 'lt.name as type')
            ->orderBy('lr.created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($leaveRequests as $request) {
            $requests->push([
                'type' => 'Leave Request - ' . $request->type,
                'date' => Carbon::parse($request->created_at)->format('M d, Y'),
                'status' => ucfirst($request->status),
                'remarks' => $request->type . ' request'
            ]);
        }

        // Employee requests
        $employeeRequests = DB::table('employee_requests')
            ->where('employee_id', $employeeId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($employeeRequests as $request) {
            $requests->push([
                'type' => $request->request_type,
                'date' => Carbon::parse($request->created_at)->format('M d, Y'),
                'status' => ucfirst($request->status),
                'remarks' => substr($request->reason, 0, 50) . '...'
            ]);
        }

        return $requests->sortByDesc('date')->take(10)->values()->toArray();
    }

    /**
     * Get status class for badges
     */
    private function getStatusClass($status)
    {
        return match(strtolower($status)) {
            'completed', 'approved' => 'bg-success',
            'pending', 'assigned' => 'bg-warning',
            'in_progress' => 'bg-info',
            'rejected', 'cancelled' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    /**
     * Show attendance logs page
     */
    public function attendanceLogs()
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            // Get employee attendance records
            $attendanceRecords = DB::table('time_entries')
                ->where('employee_id', $employee->id)
                ->orderBy('work_date', 'desc')
                ->limit(50)
                ->get();
            
            // Calculate statistics
            $stats = [
                'totalDays' => $attendanceRecords->count(),
                'totalHours' => $attendanceRecords->sum('hours_worked'),
                'averageHours' => $attendanceRecords->count() > 0 ? round($attendanceRecords->avg('hours_worked'), 2) : 0,
                'thisMonth' => $attendanceRecords->where('work_date', '>=', now()->startOfMonth())->count()
            ];
            
            return view('employee_ess_modules.attendance_logs', compact('employee', 'attendanceRecords', 'stats'));
            
        } catch (\Exception $e) {
            \Log::error('Attendance logs error: ' . $e->getMessage());
            
            return view('employee_ess_modules.attendance_logs', [
                'employee' => $employee,
                'attendanceRecords' => collect([]),
                'stats' => [
                    'totalDays' => 0,
                    'totalHours' => 0,
                    'averageHours' => 0,
                    'thisMonth' => 0
                ]
            ]);
        }
    }

    /**
     * Show claims page
     */
    public function claims()
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            // Auto-create claims table if it doesn't exist
            $this->ensureClaimsTableExists();
            
            $claims = DB::table('claims as c')
                ->leftJoin('claim_types as ct', 'c.claim_type_id', '=', 'ct.id')
                ->where('c.employee_id', $employee->id)
                ->select('c.*', 'ct.name as claim_type_name')
                ->orderBy('c.created_at', 'desc')
                ->get();
            
            $stats = [
                'totalClaims' => $claims->count(),
                'pendingClaims' => $claims->where('status', 'pending')->count(),
                'approvedClaims' => $claims->where('status', 'approved')->count(),
                'totalAmount' => $claims->sum('amount')
            ];
            
            return view('employee_ess_modules.claims_reimbursement', compact('employee', 'claims', 'stats'));
            
        } catch (\Exception $e) {
            \Log::error('Claims error: ' . $e->getMessage());
            
            return view('employee_ess_modules.claims_reimbursement', [
                'employee' => $employee,
                'claims' => collect([]),
                'stats' => [
                    'totalClaims' => 0,
                    'pendingClaims' => 0,
                    'approvedClaims' => 0,
                    'totalAmount' => 0
                ]
            ]);
        }
    }

    /**
     * Store claim submission
     */
    public function storeClaim(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        
        $request->validate([
            'claim_type' => 'required|string|max:100',
            'claim_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|min:10',
            'business_purpose' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048'
        ]);
        
        try {
            $receiptPath = null;
            if ($request->hasFile('receipt')) {
                $receiptPath = $request->file('receipt')->store('receipts', 'public');
            }
            
            DB::table('claims')->insert([
                'employee_id' => $employee->id,
                'claim_number' => 'CLM-' . str_pad(DB::table('claims')->count() + 1, 4, '0', STR_PAD_LEFT),
                'claim_type' => $request->claim_type,
                'claim_date' => $request->claim_date,
                'amount' => $request->amount,
                'description' => $request->description,
                'business_purpose' => $request->business_purpose,
                'receipt_path' => $receiptPath,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return redirect()->route('employee.claim_reimbursements.index')
                ->with('success', 'Claim submitted successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Store claim error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to submit claim. Please try again.')
                ->withInput();
        }
    }

    /**
     * Update claim submission
     */
    public function updateClaim(Request $request, $id)
    {
        $employee = Auth::guard('employee')->user();
        
        $request->validate([
            'claim_type' => 'required|string|max:100',
            'claim_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|min:10',
            'business_purpose' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048'
        ]);
        
        try {
            $claim = DB::table('claims')
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->first();
                
            if (!$claim) {
                return redirect()->route('employee.claim_reimbursements.index')
                    ->with('error', 'Claim not found or cannot be edited.');
            }
            
            $updateData = [
                'claim_type' => $request->claim_type,
                'claim_date' => $request->claim_date,
                'amount' => $request->amount,
                'description' => $request->description,
                'business_purpose' => $request->business_purpose,
                'updated_at' => now()
            ];
            
            if ($request->hasFile('receipt')) {
                $updateData['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
            }
            
            DB::table('claims')
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->update($updateData);
            
            return redirect()->route('employee.claim_reimbursements.index')
                ->with('success', 'Claim updated successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Update claim error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update claim. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show requests page
     */
    public function requests()
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            $requests = DB::table('employee_requests')
                ->where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            $stats = [
                'totalRequests' => $requests->count(),
                'pendingRequests' => $requests->where('status', 'pending')->count(),
                'approvedRequests' => $requests->where('status', 'approved')->count(),
                'thisMonth' => $requests->where('created_at', '>=', now()->startOfMonth())->count()
            ];
            
            return view('employee_ess_modules.requests', compact('employee', 'requests', 'stats'));
            
        } catch (\Exception $e) {
            \Log::error('Requests error: ' . $e->getMessage());
            
            return view('employee_ess_modules.requests', [
                'employee' => $employee,
                'requests' => collect([]),
                'stats' => [
                    'totalRequests' => 0,
                    'pendingRequests' => 0,
                    'approvedRequests' => 0,
                    'thisMonth' => 0
                ]
            ]);
        }
    }

    /**
     * Store request submission
     */
    public function storeRequest(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        
        $request->validate([
            'request_type' => 'required|string|max:100',
            'subject' => 'required|string|max:200',
            'description' => 'required|string|min:20',
            'priority' => 'required|in:low,medium,high',
            'justification' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048'
        ]);
        
        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('request_attachments', 'public');
            }
            
            DB::table('employee_requests')->insert([
                'employee_id' => $employee->id,
                'request_number' => 'REQ-' . str_pad(DB::table('employee_requests')->count() + 1, 4, '0', STR_PAD_LEFT),
                'request_type' => $request->request_type,
                'subject' => $request->subject,
                'description' => $request->description,
                'priority' => $request->priority,
                'justification' => $request->justification,
                'attachment_path' => $attachmentPath,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return redirect()->route('employee.requests.index')
                ->with('success', 'Request submitted successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Store request error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to submit request. Please try again.')
                ->withInput();
        }
    }

    /**
     * Update request submission
     */
    public function updateRequest(Request $request, $id)
    {
        $employee = Auth::guard('employee')->user();
        
        $request->validate([
            'request_type' => 'required|string|max:100',
            'subject' => 'required|string|max:200',
            'description' => 'required|string|min:20',
            'priority' => 'required|in:low,medium,high',
            'justification' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048'
        ]);
        
        try {
            $employeeRequest = DB::table('employee_requests')
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->first();
                
            if (!$employeeRequest) {
                return redirect()->route('employee.requests.index')
                    ->with('error', 'Request not found or cannot be edited.');
            }
            
            $updateData = [
                'request_type' => $request->request_type,
                'subject' => $request->subject,
                'description' => $request->description,
                'priority' => $request->priority,
                'justification' => $request->justification,
                'updated_at' => now()
            ];
            
            if ($request->hasFile('attachment')) {
                $updateData['attachment_path'] = $request->file('attachment')->store('request_attachments', 'public');
            }
            
            DB::table('employee_requests')
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->update($updateData);
            
            return redirect()->route('employee.requests.index')
                ->with('success', 'Request updated successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Update request error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update request. Please try again.')
                ->withInput();
        }
    }

    /**
     * Store timesheet submission
     */
    public function storeTimesheet(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        
        $request->validate([
            'work_date' => 'required|date',
            'clock_in_time' => 'required',
            'clock_out_time' => 'required',
            'break_time' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000'
        ]);
        
        try {
            // Calculate hours worked
            $clockIn = \Carbon\Carbon::parse($request->work_date . ' ' . $request->clock_in_time);
            $clockOut = \Carbon\Carbon::parse($request->work_date . ' ' . $request->clock_out_time);
            $breakTime = $request->break_time ?? 0;
            
            $hoursWorked = $clockOut->diffInHours($clockIn) - $breakTime;
            
            // Insert timesheet entry
            DB::table('time_entries')->insert([
                'employee_id' => $employee->id,
                'work_date' => $request->work_date,
                'clock_in_time' => $clockIn,
                'clock_out_time' => $clockOut,
                'break_time' => $breakTime,
                'hours_worked' => $hoursWorked,
                'description' => $request->description,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return redirect()->route('employee.timesheet')->with('success', 'Timesheet submitted successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Timesheet submission error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error submitting timesheet. Please try again.']);
        }
    }

    /**
     * Ensure claims table exists with auto-creation
     */
    private function ensureClaimsTableExists()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('claims')) {
                DB::statement("
                    CREATE TABLE claims (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        employee_id INT NOT NULL,
                        claim_number VARCHAR(50) UNIQUE,
                        claim_type VARCHAR(100) NOT NULL,
                        claim_date DATE NOT NULL,
                        amount DECIMAL(10,2) NOT NULL,
                        description TEXT,
                        business_purpose TEXT,
                        receipt_path VARCHAR(255),
                        attachment_path VARCHAR(255),
                        status ENUM('pending', 'approved', 'rejected', 'paid') DEFAULT 'pending',
                        approved_by INT NULL,
                        approved_at TIMESTAMP NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
                        FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL
                    )
                ");
            }

            if (!DB::getSchemaBuilder()->hasTable('claim_types')) {
                DB::statement("
                    CREATE TABLE claim_types (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL UNIQUE,
                        description TEXT,
                        max_amount DECIMAL(10,2) DEFAULT NULL,
                        requires_receipt BOOLEAN DEFAULT TRUE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ");

                // Insert sample claim types
                DB::table('claim_types')->insert([
                    ['name' => 'Travel Expense', 'description' => 'Business travel expenses', 'max_amount' => 1000.00],
                    ['name' => 'Meal Allowance', 'description' => 'Business meal expenses', 'max_amount' => 100.00],
                    ['name' => 'Office Supplies', 'description' => 'Office supplies and materials', 'max_amount' => 500.00],
                    ['name' => 'Training & Development', 'description' => 'Training and certification costs', 'max_amount' => 2000.00],
                    ['name' => 'Communication', 'description' => 'Phone and internet expenses', 'max_amount' => 200.00]
                ]);
            }

            // Insert sample claims if table is empty
            if (DB::table('claims')->count() == 0) {
                $employees = DB::table('employees')->limit(3)->get();
                foreach ($employees as $employee) {
                    DB::table('claims')->insert([
                        [
                            'employee_id' => $employee->id,
                            'claim_number' => 'CLM-' . str_pad(1, 4, '0', STR_PAD_LEFT),
                            'claim_type' => 'Travel Expense',
                            'claim_date' => now()->subDays(5),
                            'amount' => 150.00,
                            'description' => 'Business trip to client meeting',
                            'business_purpose' => 'Client consultation and project discussion',
                            'status' => 'approved',
                            'created_at' => now()->subDays(5),
                            'updated_at' => now()->subDays(3)
                        ],
                        [
                            'employee_id' => $employee->id,
                            'claim_number' => 'CLM-' . str_pad(2, 4, '0', STR_PAD_LEFT),
                            'claim_type' => 'Meal Allowance',
                            'claim_date' => now()->subDays(2),
                            'amount' => 45.00,
                            'description' => 'Business lunch with stakeholders',
                            'business_purpose' => 'Project planning meeting',
                            'status' => 'pending',
                            'created_at' => now()->subDays(2),
                            'updated_at' => now()->subDays(2)
                        ]
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Claims table creation error: ' . $e->getMessage());
        }
    }

    /**
     * Ensure shifts table exists with auto-creation
     */
    private function ensureShiftsTableExists()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('shift_types')) {
                DB::statement("
                    CREATE TABLE shift_types (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        start_time TIME NOT NULL,
                        end_time TIME NOT NULL,
                        hours DECIMAL(4,2) NOT NULL,
                        description TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ");

                // Insert sample shift types
                DB::table('shift_types')->insert([
                    ['name' => 'Morning Shift', 'start_time' => '08:00:00', 'end_time' => '16:00:00', 'hours' => 8.00],
                    ['name' => 'Evening Shift', 'start_time' => '16:00:00', 'end_time' => '00:00:00', 'hours' => 8.00],
                    ['name' => 'Night Shift', 'start_time' => '00:00:00', 'end_time' => '08:00:00', 'hours' => 8.00],
                    ['name' => 'Part-time', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'hours' => 4.00]
                ]);
            }

            if (!DB::getSchemaBuilder()->hasTable('shifts')) {
                DB::statement("
                    CREATE TABLE shifts (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        employee_id INT NOT NULL,
                        shift_type_id INT NOT NULL,
                        shift_date DATE NOT NULL,
                        start_time TIME,
                        end_time TIME,
                        hours DECIMAL(4,2) DEFAULT 0,
                        status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
                        notes TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
                        FOREIGN KEY (shift_type_id) REFERENCES shift_types(id) ON DELETE CASCADE
                    )
                ");
            }

            // Insert sample shifts if table is empty
            if (DB::table('shifts')->count() == 0) {
                $employees = DB::table('employees')->limit(2)->get();
                $shiftTypes = DB::table('shift_types')->get();
                
                foreach ($employees as $employee) {
                    foreach ($shiftTypes as $shiftType) {
                        DB::table('shifts')->insert([
                            'employee_id' => $employee->id,
                            'shift_type_id' => $shiftType->id,
                            'shift_date' => now()->addDays(rand(1, 14)),
                            'start_time' => $shiftType->start_time,
                            'end_time' => $shiftType->end_time,
                            'hours' => $shiftType->hours,
                            'status' => 'scheduled'
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Shifts table creation error: ' . $e->getMessage());
        }
    }

    /**
     * Ensure leave tables exist with auto-creation
     */
    private function ensureLeaveTablesExist()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('leave_types')) {
                DB::statement("
                    CREATE TABLE leave_types (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL UNIQUE,
                        days_allowed INT NOT NULL DEFAULT 15,
                        description TEXT,
                        requires_approval BOOLEAN DEFAULT TRUE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ");

                // Insert sample leave types
                DB::table('leave_types')->insert([
                    ['name' => 'Annual Leave', 'days_allowed' => 21, 'description' => 'Yearly vacation leave'],
                    ['name' => 'Sick Leave', 'days_allowed' => 10, 'description' => 'Medical leave for illness'],
                    ['name' => 'Personal Leave', 'days_allowed' => 5, 'description' => 'Personal time off'],
                    ['name' => 'Maternity Leave', 'days_allowed' => 90, 'description' => 'Maternity leave for mothers'],
                    ['name' => 'Emergency Leave', 'days_allowed' => 3, 'description' => 'Emergency situations']
                ]);
            }

            if (!DB::getSchemaBuilder()->hasTable('leave_requests')) {
                DB::statement("
                    CREATE TABLE leave_requests (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        employee_id INT NOT NULL,
                        leave_type_id INT NOT NULL,
                        start_date DATE NOT NULL,
                        end_date DATE NOT NULL,
                        days_requested INT NOT NULL,
                        reason TEXT NOT NULL,
                        status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
                        approved_by INT NULL,
                        approved_at TIMESTAMP NULL,
                        rejection_reason TEXT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
                        FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE,
                        FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL
                    )
                ");
            }

            // Insert sample leave requests if table is empty
            if (DB::table('leave_requests')->count() == 0) {
                $employees = DB::table('employees')->limit(2)->get();
                $leaveTypes = DB::table('leave_types')->get();
                
                foreach ($employees as $employee) {
                    DB::table('leave_requests')->insert([
                        [
                            'employee_id' => $employee->id,
                            'leave_type_id' => $leaveTypes->first()->id,
                            'start_date' => now()->addDays(10),
                            'end_date' => now()->addDays(12),
                            'days_requested' => 3,
                            'reason' => 'Family vacation trip',
                            'status' => 'pending'
                        ],
                        [
                            'employee_id' => $employee->id,
                            'leave_type_id' => $leaveTypes->skip(1)->first()->id,
                            'start_date' => now()->subDays(5),
                            'end_date' => now()->subDays(3),
                            'days_requested' => 3,
                            'reason' => 'Medical appointment and recovery',
                            'status' => 'approved'
                        ]
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Leave tables creation error: ' . $e->getMessage());
        }
    }

    /**
     * Store shift request submission
     */
    public function storeShift(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        
        $request->validate([
            'shift_type' => 'required|string|max:100',
            'shift_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:500'
        ]);
        
        try {
            // Calculate hours
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);
            $hours = $endTime->diffInHours($startTime);
            
            DB::table('shifts')->insert([
                'employee_id' => $employee->id,
                'shift_type' => $request->shift_type,
                'shift_date' => $request->shift_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'hours' => $hours,
                'status' => 'pending',
                'notes' => $request->reason,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return redirect()->route('employee.shift_schedule.index')
                ->with('success', 'Shift request submitted successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Store shift error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to submit shift request. Please try again.')
                ->withInput();
        }
    }

    /**
     * Update shift request
     */
    public function updateShift(Request $request, $id)
    {
        $employee = Auth::guard('employee')->user();
        
        $request->validate([
            'shift_type' => 'required|string|max:100',
            'shift_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:500'
        ]);
        
        try {
            $shift = DB::table('shifts')
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->first();
                
            if (!$shift) {
                return redirect()->route('employee.shift_schedule.index')
                    ->with('error', 'Shift request not found or cannot be edited.');
            }
            
            // Calculate hours
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);
            $hours = $endTime->diffInHours($startTime);
            
            DB::table('shifts')
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->update([
                    'shift_type' => $request->shift_type,
                    'shift_date' => $request->shift_date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'hours' => $hours,
                    'notes' => $request->reason,
                    'updated_at' => now()
                ]);
            
            return redirect()->route('employee.shift_schedule.index')
                ->with('success', 'Shift request updated successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Update shift error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update shift request. Please try again.')
                ->withInput();
        }
    }

    /**
     * Store leave request submission
     */
    public function storeLeave(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        
        $request->validate([
            'leave_type' => 'required|string|max:100',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500'
        ]);
        
        try {
            // Calculate days requested
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $daysRequested = $startDate->diffInDays($endDate) + 1;
            
            DB::table('leave_requests')->insert([
                'employee_id' => $employee->id,
                'leave_type' => $request->leave_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'days_requested' => $daysRequested,
                'reason' => $request->reason,
                'status' => 'pending',
                'applied_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return redirect()->route('employee.leave_applications.index')
                ->with('success', 'Leave request submitted successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Store leave error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to submit leave request. Please try again.')
                ->withInput();
        }
    }

    /**
     * Update leave request
     */
    public function updateLeave(Request $request, $id)
    {
        $employee = Auth::guard('employee')->user();
        
        $request->validate([
            'leave_type' => 'required|string|max:100',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500'
        ]);
        
        try {
            $leaveRequest = DB::table('leave_requests')
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->first();
                
            if (!$leaveRequest) {
                return redirect()->route('employee.leave_applications.index')
                    ->with('error', 'Leave request not found or cannot be edited.');
            }
            
            // Calculate days requested
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $daysRequested = $startDate->diffInDays($endDate) + 1;
            
            DB::table('leave_requests')
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->update([
                    'leave_type' => $request->leave_type,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'days_requested' => $daysRequested,
                    'reason' => $request->reason,
                    'updated_at' => now()
                ]);
            
            return redirect()->route('employee.leave_applications.index')
                ->with('success', 'Leave request updated successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Update leave error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update leave request. Please try again.')
                ->withInput();
        }
    }

    /**
     * Edit timesheet entry (handles both modal and page editing)
     */
    public function editTimesheet($id, Request $request)
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            $timeEntry = DB::table('time_entries')
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->first();
                
            if (!$timeEntry) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Timesheet not found'], 404);
                }
                return redirect()->route('employee.timesheet')
                    ->with('error', 'Time entry not found.');
            }
            
            // For AJAX requests (modal), return JSON
            if ($request->expectsJson()) {
                return response()->json($timeEntry);
            }
            
            // For modal editing, store in session and redirect back
            if ($request->has('modal')) {
                session()->flash('edit_timesheet', $timeEntry);
                return redirect()->back()->with('info', 'Timesheet loaded for editing.');
            }
            
            // For regular page editing, return view
            return view('employee_ess_modules.edit_timesheet', compact('employee', 'timeEntry'));
            
        } catch (\Exception $e) {
            \Log::error('Edit timesheet error: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Error loading timesheet details'], 500);
            }
            return redirect()->route('employee.timesheet')
                ->with('error', 'Failed to load time entry for editing.');
        }
    }



    /**
     * Ensure time_entries table exists
     */
    private function ensureTimeEntriesTableExists()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('time_entries')) {
                DB::statement("
                    CREATE TABLE time_entries (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        employee_id INT NOT NULL,
                        date DATE NOT NULL,
                        clock_in TIME,
                        clock_out TIME,
                        total_hours DECIMAL(4,2),
                        break_duration DECIMAL(4,2) DEFAULT 1.00,
                        overtime DECIMAL(4,2) DEFAULT 0.00,
                        status ENUM('present', 'late', 'absent', 'half_day') DEFAULT 'present',
                        description TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        UNIQUE KEY unique_employee_date (employee_id, date),
                        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
                    )
                ");
                
                // Insert sample time entries if table is empty
                $employees = DB::table('employees')->limit(2)->get();
                foreach ($employees as $employee) {
                    DB::table('time_entries')->insert([
                        [
                            'employee_id' => $employee->id,
                            'date' => now()->toDateString(),
                            'clock_in' => '08:00:00',
                            'clock_out' => '17:00:00',
                            'total_hours' => 8.00,
                            'break_duration' => 1.00,
                            'status' => 'present',
                            'description' => 'Regular work day'
                        ],
                        [
                            'employee_id' => $employee->id,
                            'date' => now()->subDay()->toDateString(),
                            'clock_in' => '08:15:00',
                            'clock_out' => '17:00:00',
                            'total_hours' => 7.75,
                            'break_duration' => 1.00,
                            'status' => 'late',
                            'description' => 'Late arrival due to traffic'
                        ]
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Time entries table creation error: ' . $e->getMessage());
        }
    }

    /**
     * View shift request details
     */
    public function viewShiftRequest($id)
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            $shiftRequest = DB::table('shift_requests as sr')
                ->leftJoin('shift_types as st', 'sr.shift_type_id', '=', 'st.id')
                ->where('sr.id', $id)
                ->where('sr.employee_id', $employee->id)
                ->select('sr.*', 'st.name as shift_type_name')
                ->first();
                
            if (!$shiftRequest) {
                return redirect()->route('employee.shift.request')
                    ->with('error', 'Shift request not found.');
            }

            // Create a simple view page with shift request details
            return view('employee_ess_modules.shift_request', [
                'employee' => $employee,
                'shiftRequests' => collect([$shiftRequest]),
                'shiftTypes' => DB::table('shift_types')->get(),
                'viewingRequest' => $shiftRequest
            ]);
            
        } catch (\Exception $e) {
            \Log::error('View shift request error: ' . $e->getMessage());
            return redirect()->route('employee.shift.request')
                ->with('error', 'Error loading shift request details.');
        }
    }

    /**
     * Cancel shift request
     */
    public function cancelShiftRequest($id)
    {
        $employee = Auth::guard('employee')->user();

        try {
            $updated = DB::table('shift_requests')
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => now()
                ]);

            if ($updated) {
                return redirect()->route('employee.shift.request')
                    ->with('success', 'Shift request cancelled successfully!');
            } else {
                return redirect()->route('employee.shift.request')
                    ->with('error', 'Shift request not found or cannot be cancelled.');
            }

        } catch (\Exception $e) {
            \Log::error('Cancel shift request error: ' . $e->getMessage());
            return redirect()->route('employee.shift.request')
                ->with('error', 'Failed to cancel shift request. Please try again.');
        }
    }

    /**
     * View leave request details (for ESS modal)
     */
    public function viewLeaveRequest($id)
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            $leaveRequest = DB::table('leave_requests as lr')
                ->leftJoin('leave_types as lt', 'lr.leave_type_id', '=', 'lt.id')
                ->where('lr.id', $id)
                ->where('lr.employee_id', $employee->id)
                ->select('lr.*', 'lt.name as leave_type_name')
                ->first();
                
            if (!$leaveRequest) {
                return response()->json(['error' => 'Leave request not found'], 404);
            }
            
            return response()->json($leaveRequest);
            
        } catch (\Exception $e) {
            \Log::error('View leave request error: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading leave request details'], 500);
        }
    }

    /**
     * Edit leave request (for ESS modal)
     */
    public function editLeaveRequest($id)
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            $leaveRequest = DB::table('leave_requests as lr')
                ->leftJoin('leave_types as lt', 'lr.leave_type_id', '=', 'lt.id')
                ->where('lr.id', $id)
                ->where('lr.employee_id', $employee->id)
                ->where('lr.status', 'pending')
                ->select('lr.*', 'lt.name as leave_type_name')
                ->first();
                
            if (!$leaveRequest) {
                return redirect()->back()->with('error', 'Leave request not found or cannot be edited.');
            }
            
            // Store data in session for modal population
            session()->flash('edit_leave_request', $leaveRequest);
            
            return redirect()->back()->with('info', 'Leave request loaded for editing.');
            
        } catch (\Exception $e) {
            \Log::error('Edit leave request error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading leave request for editing.');
        }
    }

    /**
     * View claim details (for ESS modal)
     */
    public function viewClaim($id)
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            $claim = DB::table('claims as c')
                ->leftJoin('claim_types as ct', 'c.claim_type_id', '=', 'ct.id')
                ->where('c.id', $id)
                ->where('c.employee_id', $employee->id)
                ->select('c.*', 'ct.name as claim_type_name')
                ->first();
                
            if (!$claim) {
                return response()->json(['error' => 'Claim not found'], 404);
            }
            
            return response()->json($claim);
            
        } catch (\Exception $e) {
            \Log::error('View claim error: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading claim details'], 500);
        }
    }

    /**
     * Edit claim (for ESS modal)
     */
    public function editClaim($id)
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            $claim = DB::table('claims as c')
                ->leftJoin('claim_types as ct', 'c.claim_type_id', '=', 'ct.id')
                ->where('c.id', $id)
                ->where('c.employee_id', $employee->id)
                ->where('c.status', 'pending')
                ->select('c.*', 'ct.name as claim_type_name')
                ->first();
                
            if (!$claim) {
                return redirect()->back()->with('error', 'Claim not found or cannot be edited.');
            }
            
            // Store data in session for modal population
            session()->flash('edit_claim', $claim);
            
            return redirect()->back()->with('info', 'Claim loaded for editing.');
            
        } catch (\Exception $e) {
            \Log::error('Edit claim error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading claim for editing.');
        }
    }

    /**
     * View timesheet details (for ESS modal)
     */
    public function viewTimesheet($id)
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            $timesheet = DB::table('time_entries')
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->first();
                
            if (!$timesheet) {
                return response()->json(['error' => 'Timesheet not found'], 404);
            }
            
            return response()->json($timesheet);
            
        } catch (\Exception $e) {
            \Log::error('View timesheet error: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading timesheet details'], 500);
        }
    }

    /**
     * Store a new shift request
     */
    public function storeShiftRequest(Request $request)
    {
        $request->validate([
            'request_type' => 'required|string|in:shift_change,shift_swap,overtime,time_off',
            'requested_date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:500',
            'shift_type_id' => 'nullable|integer'
        ]);

        $employee = Auth::guard('employee')->user();

        try {
            DB::table('shift_requests')->insert([
                'employee_id' => $employee->id,
                'request_type' => $request->request_type,
                'shift_type_id' => $request->shift_type_id ?: null,
                'requested_date' => $request->requested_date,
                'reason' => $request->reason,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->route('employee.shift.request')
                ->with('success', 'Shift request submitted successfully!');

        } catch (\Exception $e) {
            \Log::error('Store shift request error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to submit shift request. Please try again.');
        }
    }

    /**
     * Edit shift request (prepare data for modal)
     */
    public function editShiftRequest($id)
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            $shiftRequest = DB::table('shift_requests')
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->first();
                
            if (!$shiftRequest) {
                return redirect()->route('employee.shift.request')
                    ->with('error', 'Shift request not found or cannot be edited.');
            }

            // Store edit data in session for modal population
            session(['edit_request' => $shiftRequest]);

            return redirect()->route('employee.shift.request')
                ->with('info', 'Edit form opened. Make your changes and submit.');
            
        } catch (\Exception $e) {
            \Log::error('Edit shift request error: ' . $e->getMessage());
            return redirect()->route('employee.shift.request')
                ->with('error', 'Error loading shift request for editing.');
        }
    }

    /**
     * Update shift request
     */
    public function updateShiftRequest($id, Request $request)
    {
        $request->validate([
            'request_type' => 'required|string|in:shift_change,shift_swap,overtime,time_off',
            'requested_date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:500',
            'shift_type_id' => 'nullable|integer'
        ]);

        $employee = Auth::guard('employee')->user();

        try {
            $updated = DB::table('shift_requests')
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->update([
                    'request_type' => $request->request_type,
                    'shift_type_id' => $request->shift_type_id ?: null,
                    'requested_date' => $request->requested_date,
                    'reason' => $request->reason,
                    'updated_at' => now()
                ]);

            if ($updated) {
                // Clear edit session data
                session()->forget('edit_request');
                
                return redirect()->route('employee.shift.request')
                    ->with('success', 'Shift request updated successfully!');
            } else {
                return redirect()->route('employee.shift.request')
                    ->with('error', 'Shift request not found or cannot be updated.');
            }

        } catch (\Exception $e) {
            \Log::error('Update shift request error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update shift request. Please try again.');
        }
    }

    /**
     * Clock in employee (ESS)
     */
    public function clockIn(Request $request)
    {
        try {
            $employee = Auth::guard('employee')->user();
            $today = Carbon::today();
            
            // Check if employee already has attendance record for today using model
            $existingAttendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->first();
            
            if ($existingAttendance && $existingAttendance->clock_in_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already clocked in today'
                ], 400);
            }

            $clockInTime = Carbon::now('Asia/Manila');
            
            // Determine if employee is late (assuming 9:00 AM is standard start time)
            $standardStartTime = Carbon::today('Asia/Manila')->setTime(9, 0, 0);
            $status = $clockInTime->gt($standardStartTime) ? 'late' : 'present';

            if ($existingAttendance) {
                // Update existing record using model
                $existingAttendance->update([
                    'clock_in_time' => $clockInTime,
                    'status' => $status,
                    'location' => 'ESS Portal',
                    'ip_address' => $request->ip(),
                ]);
                $attendanceId = $existingAttendance->id;
            } else {
                // Create new attendance record using model with fallback
                try {
                    $attendance = Attendance::create([
                        'employee_id' => $employee->id,
                        'date' => $today,
                        'clock_in_time' => $clockInTime,
                        'status' => $status,
                        'location' => 'ESS Portal',
                        'ip_address' => $request->ip(),
                    ]);
                    $attendanceId = $attendance->id;
                } catch (\Exception $modelError) {
                    // Fallback to raw DB insert if model fails
                    \Log::warning('Attendance model creation failed, using raw DB insert: ' . $modelError->getMessage());
                    
                    $attendanceId = DB::table('attendances')->insertGetId([
                        'employee_id' => $employee->id,
                        'date' => $today->format('Y-m-d'),
                        'clock_in_time' => $clockInTime->format('Y-m-d H:i:s'),
                        'status' => $status,
                        'location' => 'ESS Portal',
                        'ip_address' => $request->ip(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // Also create/update time entry for timesheet management using model
            $existingTimeEntry = TimeEntry::where('employee_id', $employee->id)
                ->whereDate('work_date', $today)
                ->first();

            if ($existingTimeEntry) {
                // Update existing time entry
                $existingTimeEntry->update([
                    'clock_in_time' => $clockInTime->format('H:i:s'),
                    'status' => 'pending',
                ]);
            } else {
                // Create new time entry with fallback
                try {
                    TimeEntry::create([
                        'employee_id' => $employee->id,
                        'work_date' => $today,
                        'clock_in_time' => $clockInTime->format('H:i:s'),
                        'status' => 'pending',
                    ]);
                } catch (\Exception $timeEntryError) {
                    // Fallback to raw DB insert if model fails
                    \Log::warning('TimeEntry model creation failed, using raw DB insert: ' . $timeEntryError->getMessage());
                    
                    DB::table('time_entries')->insert([
                        'employee_id' => $employee->id,
                        'work_date' => $today->format('Y-m-d'),
                        'clock_in_time' => $clockInTime->format('H:i:s'),
                        'status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully clocked in',
                'data' => [
                    'clock_in_time' => $clockInTime->format('h:i A'),
                    'status' => $status,
                    'clocked_in' => true
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('ESS Clock In Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to clock in: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clock out employee (ESS)
     */
    public function clockOut(Request $request)
    {
        try {
            $employee = Auth::guard('employee')->user();
            $today = Carbon::today();
            
            \Log::info('Clock-out attempt', [
                'employee_id' => $employee->id,
                'date' => $today->toDateString()
            ]);
            
            // Get today's attendance record using model
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->first();
                
            \Log::info('Attendance record found', [
                'attendance' => $attendance ? 'exists' : 'not found',
                'clock_in_time' => $attendance->clock_in_time ?? null,
                'clock_out_time' => $attendance->clock_out_time ?? null
            ]);
            
            if (!$attendance || !$attendance->clock_in_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have not clocked in today'
                ], 400);
            }

            if ($attendance->clock_out_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already clocked out today'
                ], 400);
            }

            $clockOutTime = Carbon::now('Asia/Manila');
            $clockInTime = Carbon::parse($attendance->clock_in_time);
            
            // Ensure clock-in time is before clock-out time
            if ($clockInTime->gt($clockOutTime)) {
                // Handle case where clock-in is after clock-out (shouldn't happen, but safety check)
                $clockInTime = $clockOutTime->copy()->subHours(8); // Default to 8 hour shift
            }
            
            // Calculate total hours - ensure positive value
            $totalMinutes = abs($clockOutTime->diffInMinutes($clockInTime));
            $totalHours = round($totalMinutes / 60, 2);
            
            // Ensure total hours is positive and reasonable (max 24 hours)
            $totalHours = max(0, min(24, $totalHours));
            $overtimeHours = $totalHours > 8 ? round($totalHours - 8, 2) : 0;
            
            // Update attendance record using model
            $attendance->update([
                'clock_out_time' => $clockOutTime,
                'total_hours' => $totalHours,
                'overtime_hours' => $overtimeHours,
                'status' => 'clocked_out',
            ]);

            // Update time entry record using model
            $timeEntry = TimeEntry::where('employee_id', $employee->id)
                ->whereDate('work_date', $today)
                ->first();

            if ($timeEntry) {
                $timeEntry->update([
                    'clock_out_time' => $clockOutTime->format('H:i:s'),
                    'hours_worked' => min(8, $totalHours), // Regular hours capped at 8
                    'overtime_hours' => $overtimeHours,
                    'status' => 'pending',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully clocked out',
                'data' => [
                    'clock_out_time' => $clockOutTime->format('h:i A'),
                    'total_hours' => $totalHours,
                    'overtime_hours' => $overtimeHours,
                    'clocked_in' => false
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('ESS Clock Out Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to clock out: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current clock status for employee
     */
    public function getClockStatus(Request $request)
    {
        try {
            $employee = Auth::guard('employee')->user();
            $today = Carbon::today();
            
            $attendance = DB::table('attendances')
                ->where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->first();
            
            if (!$attendance) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'clocked_in' => false,
                        'status' => 'not_clocked_in',
                        'message' => 'Not clocked in today'
                    ]
                ]);
            }

            $clockedIn = $attendance->clock_in_time && !$attendance->clock_out_time;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'clocked_in' => $clockedIn,
                    'clock_in_time' => $attendance->clock_in_time,
                    'clock_out_time' => $attendance->clock_out_time,
                    'total_hours' => $attendance->total_hours ?? 0,
                    'status' => $attendance->status,
                    'location' => $attendance->location
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('ESS Clock Status Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get clock status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance logs for dashboard view (server-side rendering)
     */
    private function getAttendanceLogsForDashboard($employeeId)
    {
        try {
            // Ensure attendance table exists and has sample data
            $this->ensureAttendanceDataExists($employeeId);
            
            // Get attendance records using the Attendance model
            $attendanceRecords = Attendance::where('employee_id', $employeeId)
                ->orderBy('date', 'desc')
                ->limit(10)
                ->get();
                
            \Log::info('ESS Attendance Logs: Found ' . $attendanceRecords->count() . ' records for employee ' . $employeeId);
            
            // Format the data for the dashboard view
            $formattedLogs = $attendanceRecords->map(function ($attendance) {
                return (object) [
                    'id' => $attendance->id,
                    'date' => $attendance->date,
                    'clock_in_time' => $attendance->clock_in_time,
                    'clock_out_time' => $attendance->clock_out_time,
                    'total_hours' => $attendance->total_hours ?? 0,
                    'overtime_hours' => $attendance->overtime_hours ?? 0,
                    'status' => $attendance->status ?? 'unknown',
                    'location' => $attendance->location ?? 'Office',
                    // Add formatted accessors for easy display
                    'formatted_clock_in' => $attendance->formatted_clock_in,
                    'formatted_clock_out' => $attendance->formatted_clock_out,
                    'status_badge' => $attendance->status_badge
                ];
            });
            
            return $formattedLogs;
            
        } catch (\Exception $e) {
            \Log::error('ESS Attendance Logs Dashboard Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return empty collection as fallback
            return collect([]);
        }
    }

    /**
     * Ensure attendance table exists and has sample data for testing
     */
    private function ensureAttendanceDataExists($employeeId)
    {
        try {
            // Check if attendance table exists
            if (!DB::getSchemaBuilder()->hasTable('attendances')) {
                $this->createAttendanceTable();
            }
            
            // Check if employee has any attendance records
            $existingRecords = DB::table('attendances')
                ->where('employee_id', $employeeId)
                ->count();
                
            // If no records exist, create sample data
            if ($existingRecords == 0) {
                $this->createSampleAttendanceData($employeeId);
            }
            
        } catch (\Exception $e) {
            \Log::error('Ensure attendance data error: ' . $e->getMessage());
        }
    }

    /**
     * Create attendance table if it doesn't exist
     */
    private function createAttendanceTable()
    {
        try {
            DB::statement("
                CREATE TABLE IF NOT EXISTS attendances (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    employee_id INT NOT NULL,
                    date DATE NOT NULL,
                    clock_in_time DATETIME NULL,
                    clock_out_time DATETIME NULL,
                    break_start_time DATETIME NULL,
                    break_end_time DATETIME NULL,
                    total_hours DECIMAL(5,2) DEFAULT 0.00,
                    overtime_hours DECIMAL(5,2) DEFAULT 0.00,
                    status ENUM('present', 'absent', 'late', 'on_break', 'clocked_out') DEFAULT 'present',
                    location VARCHAR(255) DEFAULT 'Office',
                    ip_address VARCHAR(45) NULL,
                    notes TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_employee_date (employee_id, date),
                    INDEX idx_employee_date (employee_id, date)
                )
            ");
            
            \Log::info('Attendance table created successfully');
            
        } catch (\Exception $e) {
            \Log::error('Create attendance table error: ' . $e->getMessage());
        }
    }

    /**
     * Create sample attendance data for employee
     */
    private function createSampleAttendanceData($employeeId)
    {
        try {
            $sampleData = [];
            
            // Create 7 days of sample attendance data
            for ($i = 0; $i < 7; $i++) {
                $date = Carbon::now()->subDays($i);
                $clockIn = $date->copy()->setTime(8, rand(0, 30), 0); // 8:00-8:30 AM
                $clockOut = $clockIn->copy()->addHours(8)->addMinutes(rand(0, 60)); // 8 hours + 0-60 minutes
                
                $totalHours = $clockOut->diffInHours($clockIn, true);
                $overtimeHours = $totalHours > 8 ? $totalHours - 8 : 0;
                
                $status = $clockIn->minute > 15 ? 'late' : 'present';
                if ($clockOut) {
                    $status = 'clocked_out';
                }
                
                $sampleData[] = [
                    'employee_id' => $employeeId,
                    'date' => $date->toDateString(),
                    'clock_in_time' => $clockIn->toDateTimeString(),
                    'clock_out_time' => $clockOut->toDateTimeString(),
                    'total_hours' => round($totalHours, 2),
                    'overtime_hours' => round($overtimeHours, 2),
                    'status' => $status,
                    'location' => 'ESS Portal',
                    'ip_address' => '127.0.0.1',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            DB::table('attendances')->insert($sampleData);
            \Log::info('Sample attendance data created for employee: ' . $employeeId);
            
        } catch (\Exception $e) {
            \Log::error('Create sample attendance data error: ' . $e->getMessage());
        }
    }

    /**
     * Get attendance log for employee dashboard (AJAX)
     */
    public function getAttendanceLog(Request $request)
    {
        try {
            $employee = Auth::guard('employee')->user();
            
            $attendances = Attendance::where('employee_id', $employee->id)
                ->orderBy('date', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($attendance) {
                    return [
                        'date' => $attendance->date->format('M d, Y'),
                        'clock_in' => $attendance->formatted_clock_in ?? '--',
                        'clock_out' => $attendance->formatted_clock_out ?? '--',
                        'hours' => $attendance->total_hours ? number_format($attendance->total_hours, 2) : '0.00',
                        'status' => $attendance->status
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $attendances
            ]);

        } catch (\Exception $e) {
            \Log::error('ESS Attendance Log Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load attendance log: ' . $e->getMessage()
            ], 500);
        }
    }


}
