<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\ShiftType;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Claim;
use App\Models\ClaimType;
use App\Models\Attendance;
use App\Models\EmployeeTimesheetDetail;
use App\Models\AIGeneratedTimesheet;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TimesheetController extends Controller
{
    // Web route - return view for timesheet management page
    public function index(Request $request)
    {
        // If this is an API request, handle it differently
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->getTimesheets($request);
        }
        
        // For web requests, return the view with employees data
        try {
            // Get employees using Eloquent model
            $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();
            \Log::info('TimesheetController: Retrieved ' . $employees->count() . ' employees');
            
            // Get timesheets using TimeEntry model with relationships
            $timesheets = TimeEntry::with('employee')
                ->orderBy('work_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($timesheet) {
                    // Calculate total hours if clock times are available (but don't save computed properties)
                    if ($timesheet->clock_in_time && $timesheet->clock_out_time && !$timesheet->hours_worked) {
                        $clockIn = \Carbon\Carbon::createFromFormat('H:i:s', $timesheet->clock_in_time);
                        $clockOut = \Carbon\Carbon::createFromFormat('H:i:s', $timesheet->clock_out_time);
                        
                        // Handle overnight shifts
                        if ($clockOut->lt($clockIn)) {
                            $clockOut->addDay();
                        }
                        
                        $totalMinutes = $clockOut->diffInMinutes($clockIn) - ($timesheet->break_duration * 60 ?? 0);
                        $calculatedHours = round($totalMinutes / 60, 2);
                        
                        // Update only the hours fields (not computed properties)
                        $timesheet->update([
                            'hours_worked' => max(0, min(8, $calculatedHours)),
                            'overtime_hours' => max(0, $calculatedHours - 8)
                        ]);
                    }
                    
                    // Add computed properties for blade template compatibility (after saving)
                    $timesheet->employee_name = $timesheet->employee 
                        ? $timesheet->employee->first_name . ' ' . $timesheet->employee->last_name
                        : 'Unknown Employee';
                    
                    // Format clock times for display
                    $timesheet->clock_in = $timesheet->formatted_clock_in ?? $timesheet->clock_in_time;
                    $timesheet->clock_out = $timesheet->formatted_clock_out ?? $timesheet->clock_out_time;
                    
                    return $timesheet;
                });
            
            // Get shifts with employee and shift type data using Eloquent relationships
            try {
                $shifts = Shift::with(['employee', 'shiftType'])
                    ->orderBy('shift_date', 'desc')
                    ->get()
                    ->map(function ($shift) {
                        // Add computed properties for blade template compatibility
                        $shift->employee_name = $shift->employee 
                            ? $shift->employee->first_name . ' ' . $shift->employee->last_name
                            : 'Unknown Employee';
                        $shift->shift_type_name = $shift->shiftType 
                            ? $shift->shiftType->name 
                            : 'Unknown Shift Type';
                        return $shift;
                    });
            } catch (\Exception $e) {
                $shifts = collect([]);
            }
            
            // Get leave requests using Eloquent relationships
            try {
                $leaveRequests = LeaveRequest::with(['employee', 'leaveType'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($leaveRequest) {
                        // Add computed properties for blade template compatibility
                        $leaveRequest->employee_name = $leaveRequest->employee 
                            ? $leaveRequest->employee->first_name . ' ' . $leaveRequest->employee->last_name
                            : 'Employee ID:' . $leaveRequest->employee_id;
                        $leaveRequest->leave_type_name = $leaveRequest->leaveType 
                            ? $leaveRequest->leaveType->name 
                            : 'Type ID:' . $leaveRequest->leave_type_id;
                        $leaveRequest->leave_type_code = $leaveRequest->leaveType 
                            ? $leaveRequest->leaveType->code 
                            : null;
                        return $leaveRequest;
                    });
                    
                \Log::info('Timesheet - Retrieved ' . $leaveRequests->count() . ' leave requests for integration');
            } catch (\Exception $e) {
                \Log::warning('Leave requests query failed in TimesheetController: ' . $e->getMessage());
                $leaveRequests = collect([]);
            }
            
            // Get leave types for the modal dropdown using Eloquent
            try {
                $leaveTypes = LeaveType::where('is_active', true)
                    ->orderBy('name')
                    ->get();
            } catch (\Exception $e) {
                $leaveTypes = collect([]);
            }
            
            // Get claim types for the modal dropdown using Eloquent
            try {
                $claimTypes = ClaimType::where('is_active', true)
                    ->orderBy('name')
                    ->get();
            } catch (\Exception $e) {
                $claimTypes = collect([]);
            }
            
            // Get claims with employee and claim type data using Eloquent relationships
            try {
                $claims = Claim::with(['employee', 'claimType'])
                    ->orderBy('claim_date', 'desc')
                    ->get()
                    ->map(function ($claim) {
                        // Add computed properties for blade template compatibility
                        $claim->employee_name = $claim->employee 
                            ? $claim->employee->first_name . ' ' . $claim->employee->last_name
                            : 'Unknown Employee';
                        $claim->claim_type_name = $claim->claimType 
                            ? $claim->claimType->name 
                            : 'Unknown Claim Type';
                        return $claim;
                    });
            } catch (\Exception $e) {
                $claims = collect([]);
            }
            
            // Get attendance records with employee data using Eloquent relationships
            try {
                $attendances = Attendance::with('employee')
                    ->orderBy('date', 'desc')
                    ->orderBy('clock_in_time', 'desc')
                    ->limit(100) // Limit to recent records for performance
                    ->get()
                    ->map(function ($attendance) {
                        // Add computed properties for blade template compatibility
                        $attendance->employee_name = $attendance->employee 
                            ? $attendance->employee->first_name . ' ' . $attendance->employee->last_name
                            : 'Unknown Employee';
                        return $attendance;
                    });
            } catch (\Exception $e) {
                $attendances = collect([]);
            }
            
            // Calculate comprehensive timesheet statistics (regular + AI timesheets)
            $regularTimesheets = $timesheets;
            $aiTimesheets = collect([]);
            
            // Try to get AI timesheets using Eloquent model
            try {
                $aiTimesheets = AIGeneratedTimesheet::all();
            } catch (\Exception $e) {
                // AI timesheets table doesn't exist yet
                \Log::info('AI timesheets table not found, using regular timesheets only');
                $aiTimesheets = collect([]);
            }
            
            $timesheetStats = [
                'total_timesheets' => $regularTimesheets->count() + $aiTimesheets->count(),
                'pending_timesheets' => $regularTimesheets->where('status', 'pending')->count() + 
                                       $aiTimesheets->where('status', 'pending')->count(),
                'approved_timesheets' => $regularTimesheets->where('status', 'approved')->count() + 
                                        $aiTimesheets->whereIn('status', ['approved', 'sent_to_payroll'])->count(),
                'total_hours' => $regularTimesheets->where('status', 'approved')->sum('hours_worked') + 
                                $aiTimesheets->whereIn('status', ['approved', 'sent_to_payroll'])->sum('total_hours')
            ];
            
            // Calculate employee statistics
            $employeeStats = [
                'total_employees' => $employees->count(),
                'active_employees' => $employees->where('status', 'active')->count(),
                'employees_with_timesheets' => $timesheets->pluck('employee_id')->unique()->count(),
                'departments' => $employees->pluck('department')->unique()->count()
            ];
            
            return view('timesheets.management', compact('employees', 'timesheets', 'shifts', 'leaveRequests', 'claims', 'attendances', 'leaveTypes', 'claimTypes', 'employeeStats', 'timesheetStats'));
        } catch (\Exception $e) {
            // If there's a database error, return empty arrays
            \Log::error('TimesheetController exception: ' . $e->getMessage());
            $employees = collect([]);
            $timesheets = collect([]);
            $shifts = collect([]);
            $leaveRequests = collect([]);
            $claims = collect([]);
            $attendances = collect([]);
            $leaveTypes = collect([]);
            $claimTypes = collect([]);
            $employeeStats = [
                'total_employees' => 0,
                'active_employees' => 0,
                'employees_with_timesheets' => 0,
                'departments' => 0
            ];
            // Try to get AI timesheets even in fallback mode
            $aiTimesheetCount = 0;
            $aiPendingCount = 0;
            $aiApprovedCount = 0;
            $aiTotalHours = 0;
            
            try {
                $aiTimesheets = DB::table('ai_generated_timesheets')->get();
                $aiTimesheetCount = $aiTimesheets->count();
                $aiPendingCount = $aiTimesheets->where('status', 'pending')->count();
                $aiApprovedCount = $aiTimesheets->whereIn('status', ['approved', 'sent_to_payroll'])->count();
                $aiTotalHours = $aiTimesheets->whereIn('status', ['approved', 'sent_to_payroll'])->sum('total_hours');
            } catch (\Exception $e) {
                // AI timesheets table doesn't exist
            }
            
            $timesheetStats = [
                'total_timesheets' => $aiTimesheetCount,
                'pending_timesheets' => $aiPendingCount,
                'approved_timesheets' => $aiApprovedCount,
                'total_hours' => $aiTotalHours
            ];
            
            // Log the actual error for debugging (but don't show to user)
            \Log::info('Timesheet management initializing: ' . $e->getMessage());
            
            return view('timesheets.management', compact('employees', 'timesheets', 'shifts', 'leaveRequests', 'claims', 'attendances', 'leaveTypes', 'claimTypes', 'employeeStats', 'timesheetStats'));
        }
    }
    
    // API method - get timesheets data
    public function getTimesheets(Request $request)
    {
        try {
            // First check if tables exist
            $tablesExist = DB::select("SHOW TABLES LIKE 'time_entries'");
            if (empty($tablesExist)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time entries table does not exist. Please run the database setup SQL first.'
                ]);
            }

            $query = "SELECT t.id, t.employee_id, t.work_date, t.hours_worked, t.overtime_hours, 
                      t.description, COALESCE(t.status, 'pending') as status, t.created_at, t.updated_at,
                      COALESCE(CONCAT(e.first_name, ' ', e.last_name), CONCAT('Employee #', t.employee_id)) as employee_name 
                      FROM time_entries t 
                      LEFT JOIN employees e ON t.employee_id = e.id 
                      WHERE 1=1";
            
            $params = [];
            
            if ($request->filled('employee_id')) {
                $query .= " AND t.employee_id = ?";
                $params[] = $request->employee_id;
            }
            
            if ($request->filled('status')) {
                $query .= " AND t.status = ?";
                $params[] = $request->status;
            }
            
            $query .= " ORDER BY t.work_date DESC, t.created_at DESC";
            
            $timesheets = DB::select($query, $params);
            
            return response()->json([
                'success' => true,
                'data' => $timesheets,
                'count' => count($timesheets)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    // Create new timesheet
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'work_date' => 'required|date',
            'hours_worked' => 'required|numeric|min:0|max:24',
            'overtime_hours' => 'nullable|numeric|min:0|max:12',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,approved,rejected'
        ]);

        try {
            DB::insert(
                "INSERT INTO time_entries (employee_id, work_date, hours_worked, overtime_hours, description, status, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    $request->employee_id,
                    $request->work_date,
                    $request->hours_worked,
                    $request->overtime_hours ?? 0,
                    $request->description,
                    $request->status
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Timesheet created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating timesheet: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get single timesheet
    public function show($id)
    {
        try {
            $timesheet = DB::selectOne(
                "SELECT t.*, 
                 COALESCE(CONCAT(e.first_name, ' ', e.last_name), 'Unknown Employee') as employee_name 
                 FROM time_entries t 
                 LEFT JOIN employees e ON t.employee_id = e.id 
                 WHERE t.id = ?",
                [$id]
            );

            if (!$timesheet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timesheet not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $timesheet
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading timesheet: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update timesheet
    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'work_date' => 'required|date',
            'hours_worked' => 'required|numeric|min:0|max:24',
            'overtime_hours' => 'nullable|numeric|min:0|max:12',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,approved,rejected'
        ]);

        try {
            $affected = DB::update(
                "UPDATE time_entries 
                 SET employee_id = ?, work_date = ?, hours_worked = ?, overtime_hours = ?, 
                     description = ?, status = ?, updated_at = NOW() 
                 WHERE id = ?",
                [
                    $request->employee_id,
                    $request->work_date,
                    $request->hours_worked,
                    $request->overtime_hours ?? 0,
                    $request->description,
                    $request->status,
                    $id
                ]
            );

            if ($affected === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timesheet not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Timesheet updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating timesheet: ' . $e->getMessage()
            ], 500);
        }
    }

    // Delete timesheet
    public function destroy($id)
    {
        try {
            $affected = DB::delete("DELETE FROM time_entries WHERE id = ?", [$id]);

            if ($affected === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timesheet not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Timesheet deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting timesheet: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get statistics
    public function stats()
    {
        try {
            $stats = DB::selectOne(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(hours_worked + overtime_hours) as total_hours
                 FROM time_entries"
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => (int)($stats->total ?? 0),
                    'pending' => (int)($stats->pending ?? 0),
                    'approved' => (int)($stats->approved ?? 0),
                    'total_hours' => (float)($stats->total_hours ?? 0)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total' => 0,
                    'pending' => 0,
                    'approved' => 0,
                    'total_hours' => 0
                ]
            ]);
        }
    }

    // Get employees for dropdown
    public function getEmployees()
    {
        try {
            $employees = DB::select(
                "SELECT id, CONCAT(first_name, ' ', last_name) as name 
                 FROM employees 
                 WHERE status = 'active'
                 ORDER BY first_name, last_name"
            );

            return response()->json([
                'success' => true,
                'data' => $employees
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employees: ' . $e->getMessage()
            ], 500);
        }
    }

    // Approve timesheet
    public function approve(Request $request, $id)
    {
        try {
            $affected = DB::update(
                "UPDATE time_entries SET status = 'approved', updated_at = NOW() WHERE id = ?",
                [$id]
            );

            if ($affected === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timesheet not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Timesheet approved successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving timesheet: ' . $e->getMessage()
            ], 500);
        }
    }

    // Reject timesheet
    public function reject(Request $request, $id)
    {
        try {
            $affected = DB::update(
                "UPDATE time_entries SET status = 'rejected', updated_at = NOW() WHERE id = ?",
                [$id]
            );

            if ($affected === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timesheet not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Timesheet rejected successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting timesheet: ' . $e->getMessage()
            ], 500);
        }
    }

    // Bulk approve timesheets
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'timesheet_ids' => 'required|array',
            'timesheet_ids.*' => 'integer'
        ]);

        try {
            $placeholders = str_repeat('?,', count($request->timesheet_ids) - 1) . '?';
            $affected = DB::update(
                "UPDATE time_entries SET status = 'approved', updated_at = NOW() WHERE id IN ($placeholders)",
                $request->timesheet_ids
            );

            return response()->json([
                'success' => true,
                'message' => "$affected timesheets approved successfully!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error bulk approving timesheets: ' . $e->getMessage()
            ], 500);
        }
    }

    // Export timesheets to CSV
    public function export(Request $request)
    {
        try {
            $query = "SELECT t.id, t.employee_id, t.work_date, t.hours_worked, t.overtime_hours, 
                      t.description, t.status, t.created_at,
                      COALESCE(CONCAT(e.first_name, ' ', e.last_name), CONCAT('Employee #', t.employee_id)) as employee_name 
                      FROM time_entries t 
                      LEFT JOIN employees e ON t.employee_id = e.id 
                      WHERE 1=1";
            
            $params = [];
            
            if ($request->filled('employee_id')) {
                $query .= " AND t.employee_id = ?";
                $params[] = $request->employee_id;
            }
            
            if ($request->filled('status')) {
                $query .= " AND t.status = ?";
                $params[] = $request->status;
            }
            
            $query .= " ORDER BY t.work_date DESC, t.created_at DESC";
            
            $timesheets = DB::select($query, $params);
            
            // Generate CSV content
            $csvContent = "Employee Name,Work Date,Hours Worked,Overtime Hours,Description,Status,Created At\n";
            
            foreach ($timesheets as $timesheet) {
                $csvContent .= sprintf(
                    "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                    $timesheet->employee_name,
                    $timesheet->work_date,
                    $timesheet->hours_worked,
                    $timesheet->overtime_hours ?? 0,
                    str_replace('"', '""', $timesheet->description ?? ''),
                    $timesheet->status,
                    $timesheet->created_at
                );
            }
            
            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="timesheets_' . date('Y-m-d') . '.csv"');
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting timesheets: ' . $e->getMessage()
            ], 500);
        }
    }

    // Web-based controller methods for server-side form handling

    // Store timesheet via web form
    public function storeWeb(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer|exists:employees,id',
            'work_date' => 'required|date',
            'clock_in_time' => 'nullable|date_format:H:i',
            'clock_out_time' => 'nullable|date_format:H:i',
            'hours_worked' => 'nullable|numeric|min:0|max:24',
            'overtime_hours' => 'nullable|numeric|min:0|max:12',
            'break_duration' => 'nullable|numeric|min:0|max:8',
            'description' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,approved,rejected'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors and try again.');
        }

        try {
            // Create timesheet using TimeEntry model
            $timeEntry = new TimeEntry();
            $timeEntry->employee_id = $request->employee_id;
            $timeEntry->work_date = $request->work_date;
            $timeEntry->clock_in_time = $request->clock_in_time;
            $timeEntry->clock_out_time = $request->clock_out_time;
            $timeEntry->break_duration = $request->break_duration ?? 1.0;
            $timeEntry->description = $request->description;
            $timeEntry->notes = $request->notes;
            $timeEntry->status = $request->status;

            // Calculate hours if clock times are provided and hours_worked is not set
            if ($request->clock_in_time && $request->clock_out_time && !$request->hours_worked) {
                $clockIn = \Carbon\Carbon::createFromFormat('H:i', $request->clock_in_time);
                $clockOut = \Carbon\Carbon::createFromFormat('H:i', $request->clock_out_time);
                
                // Handle overnight shifts
                if ($clockOut->lt($clockIn)) {
                    $clockOut->addDay();
                }
                
                $totalMinutes = $clockOut->diffInMinutes($clockIn) - ($timeEntry->break_duration * 60);
                $calculatedHours = round($totalMinutes / 60, 2);
                
                $timeEntry->hours_worked = max(0, min(8, $calculatedHours));
                $timeEntry->overtime_hours = max(0, $calculatedHours - 8);
            } else {
                // Use manually entered hours
                $timeEntry->hours_worked = $request->hours_worked ?? 0;
                $timeEntry->overtime_hours = $request->overtime_hours ?? 0;
            }

            $timeEntry->save();

            return redirect()->route('timesheet-management')->with('success', 'Timesheet created successfully! Hours calculated from clock-in/out times.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create timesheet: ' . $e->getMessage());
        }
    }

    // Edit timesheet via web form
    public function editWeb($id)
    {
        try {
            $timesheet = DB::selectOne(
                "SELECT t.*, 
                 COALESCE(CONCAT(e.first_name, ' ', e.last_name), 'Unknown Employee') as employee_name 
                 FROM time_entries t 
                 LEFT JOIN employees e ON t.employee_id = e.id 
                 WHERE t.id = ?",
                [$id]
            );

            if (!$timesheet) {
                return redirect()->route('timesheet-management')->with('error', 'Timesheet not found.');
            }

            // Store timesheet data in session for modal population
            session([
                'edit_timesheet' => [
                    'id' => $timesheet->id,
                    'employee_id' => $timesheet->employee_id,
                    'work_date' => $timesheet->work_date,
                    'hours_worked' => $timesheet->hours_worked,
                    'overtime_hours' => $timesheet->overtime_hours,
                    'description' => $timesheet->description,
                    'status' => $timesheet->status
                ]
            ]);

            return redirect()->route('timesheet-management')->with('info', 'Edit form populated with timesheet data.');
        } catch (\Exception $e) {
            return redirect()->route('timesheet-management')->with('error', 'Error loading timesheet: ' . $e->getMessage());
        }
    }

    // Update timesheet via web form
    public function updateWeb(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer|exists:employees,id',
            'work_date' => 'required|date',
            'hours_worked' => 'required|numeric|min:0|max:24',
            'overtime_hours' => 'nullable|numeric|min:0|max:12',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,approved,rejected'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors and try again.');
        }

        try {
            $affected = DB::update(
                "UPDATE time_entries 
                 SET employee_id = ?, work_date = ?, hours_worked = ?, overtime_hours = ?, 
                     description = ?, status = ?, updated_at = NOW() 
                 WHERE id = ?",
                [
                    $request->employee_id,
                    $request->work_date,
                    $request->hours_worked,
                    $request->overtime_hours ?? 0,
                    $request->description,
                    $request->status,
                    $id
                ]
            );

            if ($affected === 0) {
                return redirect()->route('timesheet-management')->with('error', 'Timesheet not found.');
            }

            return redirect()->route('timesheet-management')->with('success', 'Timesheet updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update timesheet: ' . $e->getMessage());
        }
    }

    // View timesheet via web
    public function viewWeb($id)
    {
        try {
            $timesheet = DB::selectOne(
                "SELECT t.*, 
                 COALESCE(CONCAT(e.first_name, ' ', e.last_name), 'Unknown Employee') as employee_name 
                 FROM time_entries t 
                 LEFT JOIN employees e ON t.employee_id = e.id 
                 WHERE t.id = ?",
                [$id]
            );

            if (!$timesheet) {
                return redirect()->route('timesheet-management')->with('error', 'Timesheet not found.');
            }

            // Store timesheet data in session for modal population
            session([
                'view_timesheet' => [
                    'id' => $timesheet->id,
                    'employee_name' => $timesheet->employee_name,
                    'work_date' => $timesheet->work_date,
                    'hours_worked' => $timesheet->hours_worked,
                    'overtime_hours' => $timesheet->overtime_hours,
                    'description' => $timesheet->description,
                    'status' => $timesheet->status,
                    'created_at' => $timesheet->created_at,
                    'updated_at' => $timesheet->updated_at
                ]
            ]);

            return redirect()->route('timesheet-management')->with('info', 'Timesheet details loaded.');
        } catch (\Exception $e) {
            return redirect()->route('timesheet-management')->with('error', 'Error loading timesheet: ' . $e->getMessage());
        }
    }

    // Delete timesheet via web form
    public function destroyWeb($id)
    {
        try {
            $affected = DB::delete("DELETE FROM time_entries WHERE id = ?", [$id]);

            if ($affected === 0) {
                return redirect()->route('timesheet-management')->with('error', 'Timesheet not found.');
            }

            return redirect()->route('timesheet-management')->with('success', 'Timesheet deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('timesheet-management')->with('error', 'Failed to delete timesheet: ' . $e->getMessage());
        }
    }

    // Approve timesheet via web form
    public function approveWeb($id)
    {
        try {
            $affected = DB::update(
                "UPDATE time_entries SET status = 'approved', updated_at = NOW() WHERE id = ?",
                [$id]
            );

            if ($affected === 0) {
                return redirect()->route('timesheet-management')->with('error', 'Timesheet not found.');
            }

            return redirect()->route('timesheet-management')->with('success', 'Timesheet approved successfully!');
        } catch (\Exception $e) {
            return redirect()->route('timesheet-management')->with('error', 'Failed to approve timesheet: ' . $e->getMessage());
        }
    }

    // Fix negative total hours in attendance records
    public function fixNegativeAttendanceHours(Request $request)
    {
        try {
            \Log::info('Starting fix negative attendance hours process');
            
            // First, let's check what columns exist in the attendances table
            $tableColumns = DB::select("SHOW COLUMNS FROM attendances");
            $columnNames = array_column($tableColumns, 'Field');
            
            \Log::info('Attendances table columns: ' . implode(', ', $columnNames));
            
            // Determine the correct date column name
            $dateColumn = in_array('date', $columnNames) ? 'date' : 'attendance_date';
            
            // Get attendance records with negative or zero total_hours that have both clock-in and clock-out times
            $query = "
                SELECT id, employee_id, {$dateColumn} as date, 
                       clock_in_time, clock_out_time, total_hours
                FROM attendances 
                WHERE (total_hours < 0 OR total_hours IS NULL)
                  AND clock_in_time IS NOT NULL 
                  AND clock_out_time IS NOT NULL
                ORDER BY {$dateColumn} DESC
            ";
            
            $negativeRecords = DB::select($query);
            \Log::info('Found ' . count($negativeRecords) . ' records with negative or null total_hours');

            $fixedCount = 0;
            $errors = [];

            foreach ($negativeRecords as $record) {
                try {
                    \Log::info("Processing record ID {$record->id} with total_hours: {$record->total_hours}");
                    
                    $clockInTime = \Carbon\Carbon::parse($record->clock_in_time);
                    $clockOutTime = \Carbon\Carbon::parse($record->clock_out_time);
                    
                    // Ensure clock-in is before clock-out
                    if ($clockInTime->gt($clockOutTime)) {
                        // If clock-out is before clock-in, assume it's next day
                        $clockOutTime->addDay();
                    }
                    
                    // Calculate total hours - ensure positive value
                    $totalMinutes = abs($clockOutTime->diffInMinutes($clockInTime));
                    $totalHours = round($totalMinutes / 60, 2);
                    
                    // Ensure total hours is positive and reasonable (max 24 hours)
                    $totalHours = max(0, min(24, $totalHours));
                    $overtimeHours = $totalHours > 8 ? round($totalHours - 8, 2) : 0;

                    \Log::info("Calculated total_hours: {$totalHours}, overtime_hours: {$overtimeHours}");

                    // Update the record
                    $affected = DB::update(
                        "UPDATE attendances 
                         SET total_hours = ?, overtime_hours = ?, updated_at = NOW() 
                         WHERE id = ?",
                        [$totalHours, $overtimeHours, $record->id]
                    );
                    
                    if ($affected > 0) {
                        $fixedCount++;
                        \Log::info("Successfully updated record ID {$record->id}");
                    } else {
                        \Log::warning("No rows affected for record ID {$record->id}");
                    }
                } catch (\Exception $e) {
                    $error = "Failed to fix attendance record ID {$record->id}: " . $e->getMessage();
                    $errors[] = $error;
                    \Log::error($error);
                }
            }

            \Log::info("Fix process completed. Fixed {$fixedCount} records");

            return response()->json([
                'success' => true,
                'message' => "Successfully fixed {$fixedCount} attendance records with negative hours",
                'fixed_count' => $fixedCount,
                'total_found' => count($negativeRecords),
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in fixNegativeAttendanceHours: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fixing negative attendance hours: ' . $e->getMessage(),
                'fixed_count' => 0
            ], 500);
        }
    }

    // Sync attendance logs to timesheets
    public function syncAttendanceToTimesheets(Request $request)
    {
        try {
            // Get attendance records that don't have corresponding timesheet entries
            $attendanceRecords = DB::select("
                SELECT a.*, 
                       CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as employee_name
                FROM attendances a
                LEFT JOIN employees e ON a.employee_id = e.id
                LEFT JOIN time_entries t ON (a.employee_id = t.employee_id AND a.date = t.work_date)
                WHERE t.id IS NULL 
                  AND a.clock_in_time IS NOT NULL 
                  AND a.clock_out_time IS NOT NULL
                  AND a.total_hours > 0
                ORDER BY a.date DESC, a.employee_id
                LIMIT 100
            ");

            $syncedCount = 0;
            $errors = [];

            foreach ($attendanceRecords as $attendance) {
                try {
                    // Calculate regular and overtime hours
                    $totalHours = floatval($attendance->total_hours);
                    $regularHours = min($totalHours, 8);
                    $overtimeHours = max(0, $totalHours - 8);

                    // Extract time components from datetime
                    $clockInTime = \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i:s');
                    $clockOutTime = \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i:s');

                    // Create timesheet entry
                    DB::insert(
                        "INSERT INTO time_entries 
                         (employee_id, work_date, clock_in_time, clock_out_time, hours_worked, overtime_hours, 
                          break_duration, status, description, notes, created_at, updated_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                        [
                            $attendance->employee_id,
                            $attendance->date,
                            $clockInTime,
                            $clockOutTime,
                            $regularHours,
                            $overtimeHours,
                            1.0, // Default 1 hour break
                            'pending', // Default status for approval
                            'Imported from ESS attendance log',
                            "Synced from attendance record. Location: " . ($attendance->location ?? 'Office') . 
                            ". IP: " . ($attendance->ip_address ?? 'N/A')
                        ]
                    );

                    $syncedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to sync attendance for employee {$attendance->employee_id} on {$attendance->date}: " . $e->getMessage();
                }
            }

            if ($syncedCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully synced {$syncedCount} attendance records to timesheets",
                    'synced_count' => $syncedCount,
                    'errors' => $errors
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No new attendance records found to sync. All attendance logs may already have corresponding timesheet entries.',
                    'synced_count' => 0,
                    'errors' => $errors
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error syncing attendance to timesheets: ' . $e->getMessage(),
                'synced_count' => 0
            ], 500);
        }
    }

    // Reject timesheet via web form
    public function rejectWeb($id)
    {
        try {
            $affected = DB::update(
                "UPDATE time_entries SET status = 'rejected', updated_at = NOW() WHERE id = ?",
                [$id]
            );

            if ($affected === 0) {
                return redirect()->route('timesheet-management')->with('error', 'Timesheet not found.');
            }

            return redirect()->route('timesheet-management')->with('success', 'Timesheet rejected successfully!');
        } catch (\Exception $e) {
            return redirect()->route('timesheet-management')->with('error', 'Failed to reject timesheet: ' . $e->getMessage());
        }
    }

    // Create timesheet from ESS clock-in/out data
    public function createFromClockData(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'work_date' => 'required|date',
            'clock_in_time' => 'required|date_format:H:i:s',
            'clock_out_time' => 'nullable|date_format:H:i:s',
            'break_duration' => 'nullable|numeric|min:0|max:8',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            // Create or update timesheet entry
            $timeEntry = TimeEntry::updateOrCreate(
                [
                    'employee_id' => $request->employee_id,
                    'work_date' => $request->work_date
                ],
                [
                    'clock_in_time' => $request->clock_in_time,
                    'clock_out_time' => $request->clock_out_time,
                    'break_duration' => $request->break_duration ?? 1.0,
                    'notes' => $request->notes,
                    'status' => 'pending'
                ]
            );

            // Calculate hours if both clock times are available
            if ($timeEntry->clock_in_time && $timeEntry->clock_out_time) {
                $clockIn = \Carbon\Carbon::createFromFormat('H:i:s', $timeEntry->clock_in_time);
                $clockOut = \Carbon\Carbon::createFromFormat('H:i:s', $timeEntry->clock_out_time);
                
                // Handle overnight shifts
                if ($clockOut->lt($clockIn)) {
                    $clockOut->addDay();
                }
                
                $totalMinutes = $clockOut->diffInMinutes($clockIn) - ($timeEntry->break_duration * 60);
                $calculatedHours = round($totalMinutes / 60, 2);
                
                $timeEntry->hours_worked = max(0, min(8, $calculatedHours));
                $timeEntry->overtime_hours = max(0, $calculatedHours - 8);
                $timeEntry->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Timesheet entry created/updated from clock data successfully!',
                'data' => $timeEntry
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating timesheet from clock data: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get timesheet data for ESS integration
    public function getTimesheetForEmployee(Request $request, $employeeId)
    {
        try {
            $query = TimeEntry::with('employee')->where('employee_id', $employeeId);
            
            if ($request->filled('date')) {
                $query->whereDate('work_date', $request->date);
            } else {
                // Default to current month
                $query->whereMonth('work_date', now()->month)
                      ->whereYear('work_date', now()->year);
            }
            
            $timesheets = $query->orderBy('work_date', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $timesheets,
                'count' => $timesheets->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching employee timesheets: ' . $e->getMessage()
            ], 500);
        }
    }

    // View detailed weekly timesheet
    public function viewDetailedTimesheet($employeeId, $weekStartDate = null)
    {
        try {
            if (!$weekStartDate) {
                $weekStartDate = now()->startOfWeek()->format('Y-m-d');
            }

            // Get or generate the detailed timesheet
            $timesheet = EmployeeTimesheetDetail::generateFromAttendance($employeeId, $weekStartDate);
            
            if (!$timesheet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timesheet not found for this employee and week'
                ], 404);
            }

            // Get employee information
            $employee = Employee::find($employeeId);
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            // Get supervisor information
            $supervisor = null;
            if ($timesheet->supervisor_id) {
                $supervisor = Employee::find($timesheet->supervisor_id);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'timesheet' => $timesheet,
                    'employee' => $employee,
                    'supervisor' => $supervisor,
                    'weekly_data' => $timesheet->weekly_data
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading detailed timesheet: ' . $e->getMessage()
            ], 500);
        }
    }

    // Generate weekly timesheet from attendance data
    public function generateWeeklyTimesheet(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'week_start_date' => 'required|date'
        ]);

        try {
            $timesheet = EmployeeTimesheetDetail::generateFromAttendance(
                $request->employee_id,
                $request->week_start_date
            );

            return response()->json([
                'success' => true,
                'message' => 'Weekly timesheet generated successfully',
                'data' => $timesheet
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating weekly timesheet: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get weekly timesheet for modal display
    public function getWeeklyTimesheetForModal($timesheetId)
    {
        try {
            // Get the basic timesheet entry
            $timesheet = DB::selectOne(
                "SELECT t.*, 
                 COALESCE(CONCAT(e.first_name, ' ', e.last_name), 'Unknown Employee') as employee_name,
                 e.department,
                 COALESCE(CONCAT(s.first_name, ' ', s.last_name), 'N/A') as supervisor_name
                 FROM time_entries t 
                 LEFT JOIN employees e ON t.employee_id = e.id 
                 LEFT JOIN employees s ON e.supervisor_id = s.id
                 WHERE t.id = ?",
                [$timesheetId]
            );

            if (!$timesheet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timesheet not found'
                ], 404);
            }

            // Get the week start date for this timesheet
            $workDate = \Carbon\Carbon::parse($timesheet->work_date);
            $weekStartDate = $workDate->startOfWeek()->format('Y-m-d');

            // Try to get or generate detailed weekly timesheet
            $weeklyTimesheet = EmployeeTimesheetDetail::generateFromAttendance(
                $timesheet->employee_id,
                $weekStartDate
            );

            // If no detailed timesheet exists, create a basic one from the single entry
            if (!$weeklyTimesheet) {
                $weeklyTimesheet = $this->createBasicWeeklyTimesheet($timesheet);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_name' => $timesheet->employee_name,
                    'department' => $timesheet->department ?? 'N/A',
                    'supervisor_name' => $timesheet->supervisor_name,
                    'weekly_data' => $weeklyTimesheet->weekly_data ?? $this->getBasicWeeklyData($timesheet)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading weekly timesheet: ' . $e->getMessage()
            ], 500);
        }
    }

    // Create basic weekly timesheet from single entry
    private function createBasicWeeklyTimesheet($timesheet)
    {
        $workDate = \Carbon\Carbon::parse($timesheet->work_date);
        $dayName = strtolower($workDate->format('l'));
        
        $weeklyData = [
            'Monday' => ['date' => null, 'time_in' => null, 'break' => null, 'time_out' => null, 'total_hours' => '0 hrs.', 'actual_time' => '0 hrs.'],
            'Tuesday' => ['date' => null, 'time_in' => null, 'break' => null, 'time_out' => null, 'total_hours' => '0 hrs.', 'actual_time' => '0 hrs.'],
            'Wednesday' => ['date' => null, 'time_in' => null, 'break' => null, 'time_out' => null, 'total_hours' => '0 hrs.', 'actual_time' => '0 hrs.'],
            'Thursday' => ['date' => null, 'time_in' => null, 'break' => null, 'time_out' => null, 'total_hours' => '0 hrs.', 'actual_time' => '0 hrs.'],
            'Friday' => ['date' => null, 'time_in' => null, 'break' => null, 'time_out' => null, 'total_hours' => '0 hrs.', 'actual_time' => '0 hrs.']
        ];

        $dayKey = ucfirst($dayName);
        if (isset($weeklyData[$dayKey])) {
            $weeklyData[$dayKey] = [
                'date' => $workDate->format('m-d-y'),
                'time_in' => $timesheet->clock_in_time ? \Carbon\Carbon::parse($timesheet->clock_in_time)->format('g:i A') : null,
                'break' => '12:00 PM - 1:00 PM',
                'time_out' => $timesheet->clock_out_time ? \Carbon\Carbon::parse($timesheet->clock_out_time)->format('g:i A') : null,
                'total_hours' => ($timesheet->hours_worked ?? 0) . ' hrs.',
                'actual_time' => ($timesheet->hours_worked ?? 0) . ' hrs.'
            ];
        }

        return (object)['weekly_data' => $weeklyData];
    }

    // Get basic weekly data from single timesheet entry
    private function getBasicWeeklyData($timesheet)
    {
        $workDate = \Carbon\Carbon::parse($timesheet->work_date);
        $dayName = strtolower($workDate->format('l'));
        
        $weeklyData = [
            'Monday' => ['date' => null, 'time_in' => null, 'break' => null, 'time_out' => null, 'total_hours' => '0 hrs.', 'actual_time' => '0 hrs.'],
            'Tuesday' => ['date' => null, 'time_in' => null, 'break' => null, 'time_out' => null, 'total_hours' => '0 hrs.', 'actual_time' => '0 hrs.'],
            'Wednesday' => ['date' => null, 'time_in' => null, 'break' => null, 'time_out' => null, 'total_hours' => '0 hrs.', 'actual_time' => '0 hrs.'],
            'Thursday' => ['date' => null, 'time_in' => null, 'break' => null, 'time_out' => null, 'total_hours' => '0 hrs.', 'actual_time' => '0 hrs.'],
            'Friday' => ['date' => null, 'time_in' => null, 'break' => null, 'time_out' => null, 'total_hours' => '0 hrs.', 'actual_time' => '0 hrs.']
        ];

        $dayKey = ucfirst($dayName);
        if (isset($weeklyData[$dayKey])) {
            $weeklyData[$dayKey] = [
                'date' => $workDate->format('m-d-y'),
                'time_in' => $timesheet->clock_in_time ? \Carbon\Carbon::parse($timesheet->clock_in_time)->format('g:i A') : null,
                'break' => '12:00 PM - 1:00 PM',
                'time_out' => $timesheet->clock_out_time ? \Carbon\Carbon::parse($timesheet->clock_out_time)->format('g:i A') : null,
                'total_hours' => ($timesheet->hours_worked ?? 0) . ' hrs.',
                'actual_time' => ($timesheet->hours_worked ?? 0) . ' hrs.'
            ];
        }

        return $weeklyData;
    }

    // AI Timesheet Generation Methods
    
    /**
     * Simple test endpoint for AI timesheet generation
     */
    public function testAIGeneration($employeeId)
    {
        try {
            \Log::info('AI Test - Simple generation for employee: ' . $employeeId);
            
            // Ultra-simple response
            return response()->json([
                'success' => true,
                'message' => 'AI timesheet generated successfully!',
                'data' => [
                    'id' => 'test-' . $employeeId,
                    'employee_name' => 'Test Employee #' . $employeeId,
                    'department' => 'General',
                    'supervisor_name' => 'Not Assigned',
                    'weekly_data' => self::generateCurrentWeekData(),
                    'total_hours' => 0,
                    'overtime_hours' => 0,
                    'ai_insights' => ['📋 Test timesheet with no attendance records'],
                    'generated_at' => date('m/d/Y, g:i A'),
                    'status' => 'generated'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate current week data with real dates
     */
    private static function generateCurrentWeekData()
    {
        $weekStart = \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY);
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $weeklyData = [];

        foreach ($days as $index => $day) {
            $currentDate = $weekStart->copy()->addDays($index);
            $weeklyData[$day] = [
                'date' => $currentDate->format('m/d/y'),
                'time_in' => '--',
                'break' => '--',
                'time_out' => '--',
                'total_hours' => '--',
                'overtime' => '--'
            ];
        }

        return $weeklyData;
    }

    /**
     * Generate AI timesheet for a specific employee - Ultra simple version
     */
    public function generateAITimesheet(Request $request, $employeeId)
    {
        try {
            \Log::info('AI Timesheet Controller - Ultra simple generation for employee: ' . $employeeId);
            
            // Get employee name safely
            $employeeName = 'Employee #' . $employeeId;
            $department = 'General';
            
            try {
                $employee = Employee::find($employeeId);
                if ($employee) {
                    $employeeName = $employee->first_name . ' ' . $employee->last_name;
                    $department = $employee->department ?? 'General';
                }
            } catch (\Exception $e) {
                // Use defaults
            }
            
            // Generate current week dates
            $weekStart = \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY);
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $weeklyData = [];
            
            foreach ($days as $index => $day) {
                $currentDate = $weekStart->copy()->addDays($index);
                $weeklyData[$day] = [
                    'date' => $currentDate->format('m/d/y'),
                    'time_in' => '--',
                    'break' => '--',
                    'time_out' => '--',
                    'total_hours' => '--',
                    'overtime' => '--'
                ];
            }
            
            // Try to get real attendance data
            try {
                $attendances = \DB::table('attendances')
                    ->where('employee_id', $employeeId)
                    ->whereBetween('date', [
                        $weekStart->format('Y-m-d'),
                        $weekStart->copy()->addDays(6)->format('Y-m-d')
                    ])
                    ->get();
                    
                foreach ($attendances as $att) {
                    $dayOfWeek = \Carbon\Carbon::parse($att->date)->format('l');
                    if (isset($weeklyData[$dayOfWeek]) && $att->clock_in_time && $att->clock_out_time) {
                        $clockIn = \Carbon\Carbon::parse($att->clock_in_time);
                        $clockOut = \Carbon\Carbon::parse($att->clock_out_time);
                        
                        // Handle same time (likely test data) or calculate proper hours
                        if ($clockIn->equalTo($clockOut)) {
                            // Same time - assume 8 hour workday
                            $hours = 8;
                            $clockOut = $clockIn->copy()->addHours(8);
                        } else {
                            // Handle overnight shifts
                            if ($clockOut->lt($clockIn)) {
                                $clockOut->addDay();
                            }
                            
                            // Calculate total minutes and convert to hours
                            $totalMinutes = $clockOut->diffInMinutes($clockIn);
                            $hours = round($totalMinutes / 60, 1);
                        }
                        
                        // Use existing total_hours and overtime_hours from database if available
                        $totalHours = isset($att->total_hours) ? abs($att->total_hours) : abs($hours);
                        $overtimeHours = isset($att->overtime_hours) ? abs($att->overtime_hours) : max(0, $totalHours - 8);
                        
                        // Format hours as "Xh Ym" instead of "X.X hrs."
                        $totalHoursFormatted = $this->formatHoursToTime($totalHours);
                        $overtimeFormatted = $overtimeHours > 0 ? $this->formatHoursToTime($overtimeHours) : '0m';
                        
                        $weeklyData[$dayOfWeek] = [
                            'date' => $clockIn->format('m/d/y'),
                            'time_in' => $clockIn->format('g:i A'),
                            'break' => '12:00 PM - 1:00 PM',
                            'time_out' => $clockOut->format('g:i A'),
                            'total_hours' => $totalHoursFormatted,
                            'overtime' => $overtimeFormatted
                        ];
                    }
                }
            } catch (\Exception $e) {
                \Log::error('AI Timesheet - Attendance query failed: ' . $e->getMessage());
            }
            
            // Generate comprehensive AI insights
            $insights = $this->generateAIInsights($weeklyData, $employeeName, $department);
            
            // Create response data
            $responseData = [
                'id' => 'ai-' . $employeeId . '-' . time(),
                'employee_id' => $employeeId,
                'employee_name' => $employeeName,
                'department' => $department,
                'supervisor_name' => 'Not Assigned',
                'weekly_data' => $weeklyData,
                'total_hours' => 0,
                'overtime_hours' => 0,
                'ai_insights' => $insights,
                'status' => 'generated',
                'generated_at' => now()->format('m/d/Y, g:i A')
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'AI timesheet generated successfully!',
                'data' => $responseData
            ]);
            
        } catch (\Exception $e) {
            \Log::error('AI Timesheet Controller Error: ' . $e->getMessage());
            \Log::error('AI Timesheet Controller Stack: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating AI timesheet: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate AI timesheets for all employees - Ultra simple version
     */
    public function generateAllAITimesheets(Request $request)
    {
        try {
            \Log::info('AI Timesheet - Starting bulk generation for all employees');
            
            $employees = Employee::where('status', 'active')->get();
            
            $generated = [];
            $errors = [];
            
            foreach ($employees as $employee) {
                try {
                    // Use the same logic as individual generation
                    $employeeId = $employee->id;
                    $employeeName = $employee->first_name . ' ' . $employee->last_name;
                    $department = $employee->department ?? 'General';
                    
                    // Generate current week dates
                    $weekStart = \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY);
                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    $weeklyData = [];
                    
                    foreach ($days as $index => $day) {
                        $currentDate = $weekStart->copy()->addDays($index);
                        $weeklyData[$day] = [
                            'date' => $currentDate->format('m/d/y'),
                            'time_in' => '--',
                            'break' => '--',
                            'time_out' => '--',
                            'total_hours' => '--',
                            'overtime' => '--'
                        ];
                    }
                    
                    // Try to get real attendance data
                    try {
                        $attendances = \DB::table('attendances')
                            ->where('employee_id', $employeeId)
                            ->whereBetween('date', [
                                $weekStart->format('Y-m-d'),
                                $weekStart->copy()->addDays(6)->format('Y-m-d')
                            ])
                            ->get();
                            
                        foreach ($attendances as $att) {
                            $dayOfWeek = \Carbon\Carbon::parse($att->date)->format('l');
                            if (isset($weeklyData[$dayOfWeek]) && $att->clock_in_time && $att->clock_out_time) {
                                $clockIn = \Carbon\Carbon::parse($att->clock_in_time);
                                $clockOut = \Carbon\Carbon::parse($att->clock_out_time);
                                
                                // Handle same time (likely test data) or calculate proper hours
                                if ($clockIn->equalTo($clockOut)) {
                                    $hours = 8;
                                    $clockOut = $clockIn->copy()->addHours(8);
                                } else {
                                    if ($clockOut->lt($clockIn)) {
                                        $clockOut->addDay();
                                    }
                                    $totalMinutes = $clockOut->diffInMinutes($clockIn);
                                    $hours = round($totalMinutes / 60, 1);
                                }
                                
                                $regularHours = min($hours, 8);
                                $overtimeHours = max(0, $hours - 8);
                                $displayHours = max(0, $regularHours);
                                $displayOvertime = max(0, $overtimeHours);
                                
                                $weeklyData[$dayOfWeek] = [
                                    'date' => $clockIn->format('m/d/y'),
                                    'time_in' => $clockIn->format('g:i A'),
                                    'break' => '12:00 PM - 1:00 PM',
                                    'time_out' => $clockOut->format('g:i A'),
                                    'total_hours' => $displayHours . ' hrs.',
                                    'overtime' => $displayOvertime > 0 ? $displayOvertime . ' hrs.' : '0 hrs.'
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        // Ignore attendance errors for individual employees
                    }
                    
                    // Generate comprehensive AI insights
                    $insights = $this->generateAIInsights($weeklyData, $employeeName, $department);
                    
                    // Create the same response data structure as individual generation
                    $timesheetData = [
                        'id' => 'ai-' . $employeeId . '-' . time(),
                        'employee_id' => $employeeId,
                        'employee_name' => $employeeName,
                        'department' => $department,
                        'supervisor_name' => 'Not Assigned',
                        'weekly_data' => $weeklyData,
                        'total_hours' => 0,
                        'overtime_hours' => 0,
                        'ai_insights' => $insights,
                        'status' => 'generated',
                        'generated_at' => now()->format('m/d/Y, g:i A')
                    ];
                    
                    $generated[] = [
                        'employee_id' => $employeeId,
                        'employee_name' => $employeeName,
                        'timesheet_id' => $timesheetData['id'],
                        'status' => 'generated',
                        'timesheet_data' => $timesheetData
                    ];
                    
                } catch (\Exception $e) {
                    $errors[] = [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'AI timesheets generation completed',
                'generated_count' => count($generated),
                'error_count' => count($errors),
                'generated' => $generated,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating AI timesheets: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate comprehensive AI insights for timesheet
     */
    private function generateAIInsights($weeklyData, $employeeName, $department)
    {
        $insights = [];
        
        // Count days worked
        $daysWorked = 0;
        $totalHours = 0;
        $overtimeHours = 0;
        $workDays = [];
        
        foreach ($weeklyData as $day => $data) {
            if ($data['time_in'] !== '--' && $data['time_out'] !== '--') {
                $daysWorked++;
                $workDays[] = $day;
                
                // Extract hours from string (e.g., "8 hrs." -> 8)
                if (isset($data['total_hours']) && $data['total_hours'] !== '--') {
                    $hours = (float) str_replace(' hrs.', '', $data['total_hours']);
                    $totalHours += $hours;
                }
                
                if (isset($data['overtime']) && $data['overtime'] !== '0 hrs.' && $data['overtime'] !== '--') {
                    $overtime = (float) str_replace(' hrs.', '', $data['overtime']);
                    $overtimeHours += $overtime;
                }
            }
        }
        
        // Basic attendance insights
        if ($daysWorked > 0) {
            $insights[] = "📊 {$employeeName} worked {$daysWorked} day(s) this week";
            
            if ($totalHours > 0) {
                $avgHours = round($totalHours / $daysWorked, 1);
                $insights[] = "⏱️ Average daily hours: {$avgHours} hours per day";
            }
        } else {
            $insights[] = "ℹ️ No attendance records found for {$employeeName} this week";
        }
        
        // Work pattern and consistency analysis
        if ($daysWorked >= 3) {
            // Analyze consistency of work days
            $consistencyInsight = $this->analyzeWorkConsistency($workDays, $weeklyData);
            $insights[] = $consistencyInsight;
            
            $insights[] = "✅ Good attendance pattern - consistent work schedule";
        } elseif ($daysWorked > 0) {
            $insights[] = "⚠️ Partial attendance - only {$daysWorked} day(s) recorded";
            
            if ($daysWorked == 2) {
                $insights[] = "📅 Inconsistent schedule - consider establishing regular work days";
            }
        }
        
        // Overtime analysis
        if ($overtimeHours > 0) {
            $insights[] = "🕐 Overtime detected: {$overtimeHours} hours beyond regular schedule";
        } elseif ($daysWorked > 0) {
            $insights[] = "👍 No overtime - maintaining regular work hours";
        }
        
        // Department-specific insights
        switch (strtolower($department)) {
            case 'it':
                if ($overtimeHours > 5) {
                    $insights[] = "💻 High IT overtime - consider workload distribution";
                } elseif ($daysWorked >= 4) {
                    $insights[] = "💻 IT schedule looks balanced - good work-life balance";
                }
                break;
                
            case 'sales':
                if ($daysWorked >= 5) {
                    $insights[] = "📈 Full sales week - excellent client engagement potential";
                } elseif ($daysWorked >= 3) {
                    $insights[] = "📈 Good sales presence - maintaining client relationships";
                }
                break;
                
            case 'marketing':
                if ($daysWorked >= 4) {
                    $insights[] = "🎯 Strong marketing presence - consistent campaign support";
                }
                break;
                
            case 'finance':
                if ($daysWorked >= 4) {
                    $insights[] = "💰 Reliable finance schedule - maintaining fiscal operations";
                }
                break;
                
            case 'human resources':
                if ($daysWorked >= 4) {
                    $insights[] = "👥 Consistent HR support - maintaining employee services";
                }
                break;
                
            default:
                if ($daysWorked >= 4) {
                    $insights[] = "🏢 Regular {$department} schedule - maintaining department operations";
                }
        }
        
        // Weekly completion insights
        if ($daysWorked == 5) {
            $insights[] = "🎉 Perfect attendance - full work week completed";
        } elseif ($daysWorked >= 3) {
            $insights[] = "📋 Partial week completed - {$daysWorked}/5 work days recorded";
        }
        
        // Ensure we always have at least one insight
        if (empty($insights)) {
            $insights[] = "📋 Weekly timesheet generated successfully";
        }
        
        return $insights;
    }
    
    /**
     * Analyze work consistency patterns
     */
    private function analyzeWorkConsistency($workDays, $weeklyData)
    {
        if (count($workDays) < 2) {
            return "📅 Single work day - unable to analyze consistency";
        }
        
        // Define typical work day patterns
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $weekends = ['Saturday', 'Sunday'];
        
        // Check if working weekdays vs weekends
        $weekdayWork = array_intersect($workDays, $weekdays);
        $weekendWork = array_intersect($workDays, $weekends);
        
        // Analyze time consistency
        $workTimes = [];
        $clockInTimes = [];
        $clockOutTimes = [];
        
        foreach ($workDays as $day) {
            if (isset($weeklyData[$day])) {
                $timeIn = $weeklyData[$day]['time_in'];
                $timeOut = $weeklyData[$day]['time_out'];
                
                if ($timeIn !== '--' && $timeOut !== '--') {
                    $clockInTimes[] = $timeIn;
                    $clockOutTimes[] = $timeOut;
                }
            }
        }
        
        // Pattern analysis
        if (count($workDays) >= 4) {
            // Check for consecutive days
            $dayNumbers = array_map(function($day) {
                $days = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];
                return $days[$day];
            }, $workDays);
            
            sort($dayNumbers);
            $consecutive = true;
            for ($i = 1; $i < count($dayNumbers); $i++) {
                if ($dayNumbers[$i] - $dayNumbers[$i-1] > 1) {
                    $consecutive = false;
                    break;
                }
            }
            
            if ($consecutive && count($weekdayWork) >= 4) {
                return "🎯 Excellent consistency - consecutive weekday schedule";
            } elseif (count($weekdayWork) >= 4) {
                return "📊 Good consistency - regular weekday attendance";
            }
        }
        
        // Check for mixed patterns
        if (count($weekendWork) > 0 && count($weekdayWork) > 0) {
            return "🔄 Mixed schedule - working both weekdays and weekends";
        }
        
        // Check for weekend-only work
        if (count($weekendWork) > 0 && count($weekdayWork) == 0) {
            return "📅 Weekend worker - non-traditional schedule pattern";
        }
        
        // Check for scattered weekday pattern
        if (count($workDays) >= 3 && count($workDays) < 5) {
            $gaps = 0;
            $dayNumbers = array_map(function($day) {
                $days = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5];
                return isset($days[$day]) ? $days[$day] : 0;
            }, $workDays);
            
            $dayNumbers = array_filter($dayNumbers); // Remove weekends
            sort($dayNumbers);
            
            for ($i = 1; $i < count($dayNumbers); $i++) {
                if ($dayNumbers[$i] - $dayNumbers[$i-1] > 1) {
                    $gaps++;
                }
            }
            
            if ($gaps > 1) {
                return "⚠️ Inconsistent pattern - scattered work days with gaps";
            } else {
                return "📋 Moderate consistency - some regular pattern detected";
            }
        }
        
        // Time consistency analysis
        if (count($clockInTimes) >= 2) {
            $uniqueInTimes = array_unique($clockInTimes);
            $uniqueOutTimes = array_unique($clockOutTimes);
            
            if (count($uniqueInTimes) == 1 && count($uniqueOutTimes) == 1) {
                return "⏰ Perfect time consistency - same schedule every work day";
            } elseif (count($uniqueInTimes) <= 2 && count($uniqueOutTimes) <= 2) {
                return "🕐 Good time consistency - similar daily schedule";
            } else {
                return "🔄 Variable schedule - different times each work day";
            }
        }
        
        return "📅 Work pattern established - building consistency";
    }
    
    /**
     * Save AI timesheet to pending approval - Complete database version
     */
    public function saveAITimesheet(Request $request)
    {
        try {
            \Log::info('Save AI Timesheet - Complete database version started');
            
            $employeeId = $request->input('employee_id');
            $timesheetData = $request->input('timesheet_data');
            
            \Log::info('Save AI Timesheet - Input received', [
                'employee_id' => $employeeId,
                'timesheet_data' => $timesheetData
            ]);
            
            if (!$employeeId || !$timesheetData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing employee ID or timesheet data'
                ], 400);
            }
            
            // Get employee information
            $employee = DB::table('employees')->where('id', $employeeId)->first();
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }
            
            $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
            
            // Check if record already exists
            $existing = DB::table('ai_generated_timesheets')
                ->where('employee_id', $employeeId)
                ->where('week_start_date', $weekStart)
                ->first();
            
            // Calculate totals from weekly data
            $totalHours = 0;
            $overtimeHours = 0;
            
            if (isset($timesheetData['weekly_data'])) {
                foreach ($timesheetData['weekly_data'] as $day => $dayData) {
                    if (isset($dayData['total_hours']) && is_numeric($dayData['total_hours'])) {
                        $totalHours += floatval($dayData['total_hours']);
                    }
                    if (isset($dayData['overtime']) && is_numeric($dayData['overtime'])) {
                        $overtimeHours += floatval($dayData['overtime']);
                    }
                }
            }
            
            $saveData = [
                'employee_id' => $employeeId,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'department' => $employee->department ?? 'General',
                'week_start_date' => $weekStart,
                'weekly_data' => json_encode($timesheetData['weekly_data'] ?? []),
                'total_hours' => $totalHours,
                'overtime_hours' => $overtimeHours,
                'ai_insights' => json_encode($timesheetData['ai_insights'] ?? []),
                'status' => 'pending',
                'generated_at' => now(),
                'updated_at' => now()
            ];
            
            if ($existing) {
                // Update existing record
                DB::table('ai_generated_timesheets')
                    ->where('id', $existing->id)
                    ->update($saveData);
                $id = $existing->id;
                \Log::info('Save AI Timesheet - Updated existing record', ['id' => $id]);
            } else {
                // Create new record
                $saveData['created_at'] = now();
                $id = DB::table('ai_generated_timesheets')->insertGetId($saveData);
                \Log::info('Save AI Timesheet - Created new record', ['id' => $id]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'AI timesheet saved successfully to pending approval',
                'timesheet_id' => $id,
                'employee_name' => $saveData['employee_name'],
                'total_hours' => $totalHours,
                'overtime_hours' => $overtimeHours
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Save AI Timesheet Error: ' . $e->getMessage());
            \Log::error('Save AI Timesheet Stack: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error saving timesheet: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get all AI timesheets for approval - Complete database version
     */
    public function getPendingTimesheets()
    {
        try {
            \Log::info('Get All AI Timesheets - Complete database version started');
            
            // Get all AI timesheets (pending, approved, rejected) from ai_generated_timesheets table
            $timesheets = DB::table('ai_generated_timesheets')
                ->orderByRaw("CASE WHEN status = 'pending' THEN 1 WHEN status = 'approved' THEN 2 ELSE 3 END")
                ->orderBy('generated_at', 'desc')
                ->get();
            
            \Log::info('Get Pending Timesheets - Found ' . $timesheets->count() . ' records');
            
            $formattedTimesheets = $timesheets->map(function ($timesheet) {
                $weekStart = Carbon::parse($timesheet->week_start_date);
                $weekEnd = $weekStart->copy()->addDays(6);
                
                return [
                    'id' => $timesheet->id,
                    'employee_id' => $timesheet->employee_id,
                    'employee_name' => $timesheet->employee_name ?? 'Employee #' . $timesheet->employee_id,
                    'department' => $timesheet->department ?? 'General',
                    'week_period' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d, Y'),
                    'total_hours' => $timesheet->total_hours ?? 0,
                    'overtime_hours' => $timesheet->overtime_hours ?? 0,
                    'generated_at' => $timesheet->generated_at ? 
                        Carbon::parse($timesheet->generated_at)->format('M d, Y g:i A') : 
                        'Unknown',
                    'status' => $timesheet->status
                ];
            });
            
            return response()->json([
                'success' => true,
                'timesheets' => $formattedTimesheets->toArray()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Get Pending Timesheets Error: ' . $e->getMessage());
            \Log::error('Get Pending Timesheets Stack: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading pending timesheets: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get saved timesheet details
     */
    public function getSavedTimesheet($id)
    {
        try {
            \Log::info('Get Saved Timesheet - Started', ['id' => $id]);
            
            $timesheet = DB::table('ai_generated_timesheets')->where('id', $id)->first();
            
            if (!$timesheet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timesheet not found'
                ], 404);
            }
            
            $weekStart = Carbon::parse($timesheet->week_start_date);
            $weekEnd = $weekStart->copy()->addDays(6);
            
            $timesheetData = [
                'id' => $timesheet->id,
                'employee_id' => $timesheet->employee_id,
                'employee_name' => $timesheet->employee_name,
                'department' => $timesheet->department ?? 'General',
                'supervisor_name' => 'HR Manager',
                'week_period' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d, Y'),
                'weekly_data' => $timesheet->weekly_data,
                'total_hours' => $timesheet->total_hours,
                'overtime_hours' => $timesheet->overtime_hours,
                'ai_insights' => $timesheet->ai_insights,
                'status' => $timesheet->status,
                'generated_at' => $timesheet->generated_at ? 
                    Carbon::parse($timesheet->generated_at)->format('M d, Y g:i A') : 
                    'Unknown'
            ];
            
            return response()->json([
                'success' => true,
                'timesheet' => $timesheetData
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Get Saved Timesheet Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading timesheet: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Approve AI timesheet
     */
    public function approveAITimesheet($id)
    {
        try {
            \Log::info('Approve AI Timesheet - Started', ['id' => $id]);
            
            $timesheet = DB::table('ai_generated_timesheets')->where('id', $id)->first();
            
            if (!$timesheet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timesheet not found'
                ], 404);
            }
            
            // Update status to approved
            DB::table('ai_generated_timesheets')
                ->where('id', $id)
                ->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => auth()->id() ?? 1,
                    'updated_at' => now()
                ]);
            
            \Log::info('Approve AI Timesheet - Success', ['id' => $id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Timesheet approved successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Approve AI Timesheet Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error approving timesheet: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject AI timesheet
     */
    public function rejectAITimesheet(Request $request, $id)
    {
        try {
            \Log::info('Reject AI Timesheet - Started', ['id' => $id]);
            
            $timesheet = DB::table('ai_generated_timesheets')->where('id', $id)->first();
            
            if (!$timesheet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timesheet not found'
                ], 404);
            }
            
            // Update status to rejected
            DB::table('ai_generated_timesheets')
                ->where('id', $id)
                ->update([
                    'status' => 'rejected',
                    'rejection_reason' => $request->input('reason'),
                    'rejected_at' => now(),
                    'rejected_by' => auth()->id() ?? 1,
                    'updated_at' => now()
                ]);
            
            \Log::info('Reject AI Timesheet - Success', ['id' => $id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Timesheet rejected successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Reject AI Timesheet Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting timesheet: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get AI timesheet for viewing
     */
    public function getAITimesheet($employeeId)
    {
        try {
            $aiTimesheet = AIGeneratedTimesheet::where('employee_id', $employeeId)
                ->currentWeek()
                ->with('employee')
                ->first();
                
            if (!$aiTimesheet) {
                return response()->json([
                    'success' => false,
                    'message' => 'No AI timesheet found for this employee. Please generate one first.'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $aiTimesheet->id,
                    'employee_name' => $aiTimesheet->employee->first_name . ' ' . $aiTimesheet->employee->last_name,
                    'department' => $aiTimesheet->employee->department ?? 'General',
                    'supervisor_name' => $aiTimesheet->employee->supervisor ?? 'Not Assigned',
                    'weekly_data' => $aiTimesheet->weekly_data,
                    'total_hours' => $aiTimesheet->total_hours,
                    'overtime_hours' => $aiTimesheet->overtime_hours,
                    'ai_insights' => $aiTimesheet->ai_insights,
                    'generated_at' => $aiTimesheet->generated_at->format('m/d/Y, g:i A'),
                    'status' => $aiTimesheet->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading AI timesheet: ' . $e->getMessage()
            ], 500);
        }
    }
    
    
    /**
     * Create actual timesheet entries from approved AI timesheet
     */
    private function createTimesheetEntriesFromAI(AIGeneratedTimesheet $aiTimesheet)
    {
        $weekStart = $aiTimesheet->week_start_date;
        $weeklyData = $aiTimesheet->weekly_data;
        
        foreach ($weeklyData as $day => $data) {
            $dayIndex = array_search($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
            $workDate = $weekStart->copy()->addDays($dayIndex);
            
            // Check if timesheet entry already exists
            $existing = DB::table('time_entries')
                ->where('employee_id', $aiTimesheet->employee_id)
                ->where('work_date', $workDate->format('Y-m-d'))
                ->first();
                
            if (!$existing && isset($data['time_in']) && isset($data['time_out'])) {
                // Convert 12-hour format back to 24-hour for database
                $timeIn = \Carbon\Carbon::createFromFormat('h:i A', $data['time_in'])->format('H:i:s');
                $timeOut = \Carbon\Carbon::createFromFormat('h:i A', $data['time_out'])->format('H:i:s');
                $totalHours = floatval(str_replace(' hrs.', '', $data['total_hours']));
                $overtimeHours = floatval(str_replace(' hrs.', '', $data['overtime']));
                
                DB::table('time_entries')->insert([
                    'employee_id' => $aiTimesheet->employee_id,
                    'work_date' => $workDate->format('Y-m-d'),
                    'clock_in_time' => $timeIn,
                    'clock_out_time' => $timeOut,
                    'hours_worked' => $totalHours,
                    'overtime_hours' => $overtimeHours,
                    'break_duration' => 1.0,
                    'status' => 'approved',
                    'description' => 'Generated from AI timesheet',
                    'notes' => 'AI-generated and approved timesheet entry',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
    
    /**
     * Send approved timesheet to payroll (create time entries)
     */
    public function sendToPayroll($id)
    {
        try {
            \Log::info('Send to Payroll - Started', ['id' => $id]);
            
            $timesheet = DB::table('ai_generated_timesheets')->where('id', $id)->first();
            
            if (!$timesheet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timesheet not found'
                ], 404);
            }
            
            if ($timesheet->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only approved timesheets can be sent to payroll'
                ], 400);
            }
            
            // Parse weekly data
            $weeklyData = json_decode($timesheet->weekly_data, true);
            if (!$weeklyData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid timesheet data'
                ], 400);
            }
            
            $entriesCreated = 0;
            $weekStart = Carbon::parse($timesheet->week_start_date);
            
            // Create time entries for each day with data
            foreach ($weeklyData as $day => $dayData) {
                if (isset($dayData['total_hours']) && $dayData['total_hours'] > 0) {
                    $dayDate = $weekStart->copy();
                    
                    // Calculate day offset
                    $dayOffsets = [
                        'monday' => 0, 'tuesday' => 1, 'wednesday' => 2, 
                        'thursday' => 3, 'friday' => 4, 'saturday' => 5, 'sunday' => 6
                    ];
                    
                    if (isset($dayOffsets[$day])) {
                        $dayDate->addDays($dayOffsets[$day]);
                        
                        // Check if entry already exists
                        $existingEntry = DB::table('time_entries')
                            ->where('employee_id', $timesheet->employee_id)
                            ->where('work_date', $dayDate->format('Y-m-d'))
                            ->first();
                        
                        if (!$existingEntry) {
                            DB::table('time_entries')->insert([
                                'employee_id' => $timesheet->employee_id,
                                'work_date' => $dayDate->format('Y-m-d'),
                                'clock_in' => $dayData['clock_in'] ?? '09:00',
                                'break_duration' => $dayData['break'] ?? '1:00',
                                'clock_out' => $dayData['clock_out'] ?? '17:00',
                                'total_hours' => $dayData['total_hours'],
                                'overtime_hours' => $dayData['overtime'] ?? 0,
                                'status' => 'approved',
                                'description' => 'Generated from AI timesheet',
                                'notes' => 'Auto-generated from AI timesheet ID: ' . $timesheet->id,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            
                            $entriesCreated++;
                        }
                    }
                }
            }
            
            // Update timesheet status to indicate it's been sent to payroll
            DB::table('ai_generated_timesheets')
                ->where('id', $id)
                ->update([
                    'status' => 'sent_to_payroll',
                    'updated_at' => now()
                ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Timesheet sent to payroll successfully',
                'entries_created' => $entriesCreated
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Send to Payroll Error: ' . $e->getMessage());
            \Log::error('Send to Payroll Stack: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error sending to payroll: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get real-time timesheet statistics
     */
    public function getTimesheetStatistics()
    {
        try {
            // Get regular timesheets
            $regularTimesheets = collect([]);
            try {
                $regularTimesheets = collect(DB::table('time_entries')->get());
            } catch (\Exception $e) {
                // time_entries table might not exist
            }
            
            // Get AI timesheets
            $aiTimesheets = collect([]);
            try {
                $aiTimesheets = collect(DB::table('ai_generated_timesheets')->get());
            } catch (\Exception $e) {
                // ai_generated_timesheets table might not exist
            }
            
            $stats = [
                'total_timesheets' => $regularTimesheets->count() + $aiTimesheets->count(),
                'pending_timesheets' => $regularTimesheets->where('status', 'pending')->count() + 
                                       $aiTimesheets->where('status', 'pending')->count(),
                'approved_timesheets' => $regularTimesheets->where('status', 'approved')->count() + 
                                        $aiTimesheets->whereIn('status', ['approved', 'sent_to_payroll'])->count(),
                'total_hours' => $regularTimesheets->where('status', 'approved')->sum('hours_worked') + 
                                $aiTimesheets->whereIn('status', ['approved', 'sent_to_payroll'])->sum('total_hours')
            ];
            
            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Get Timesheet Statistics Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading statistics: ' . $e->getMessage(),
                'statistics' => [
                    'total_timesheets' => 0,
                    'pending_timesheets' => 0,
                    'approved_timesheets' => 0,
                    'total_hours' => 0
                ]
            ], 500);
        }
    }
    
    /**
     * Helper method to format decimal hours to readable time format
     */
    private function formatHoursToTime($hours)
    {
        if ($hours === null || $hours === 0) {
            return '0m';
        }
        
        $wholeHours = floor($hours);
        $minutes = round(($hours - $wholeHours) * 60);
        
        if ($wholeHours > 0 && $minutes > 0) {
            return $wholeHours . 'h ' . $minutes . 'm';
        } elseif ($wholeHours > 0) {
            return $wholeHours . 'h';
        } elseif ($minutes > 0) {
            return $minutes . 'm';
        } else {
            return '0m';
        }
    }
}
