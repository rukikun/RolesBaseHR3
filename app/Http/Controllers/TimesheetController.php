<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Employee;
use App\Models\TimeEntry;
use App\Models\Shift;
use App\Models\LeaveRequest;
use App\Models\Claim;
use App\Models\EmployeeTimesheetDetail;
use App\Models\AIGeneratedTimesheet;

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
            
            // Get timesheets using TimeEntry model with relationships
            $timesheets = TimeEntry::with('employee')
                ->orderBy('work_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($timesheet) {
                    // Add computed properties for blade template compatibility
                    $timesheet->employee_name = $timesheet->employee 
                        ? $timesheet->employee->first_name . ' ' . $timesheet->employee->last_name
                        : 'Unknown Employee';
                    
                    // Calculate total hours if clock times are available
                    if ($timesheet->clock_in_time && $timesheet->clock_out_time && !$timesheet->hours_worked) {
                        $clockIn = \Carbon\Carbon::createFromFormat('H:i:s', $timesheet->clock_in_time);
                        $clockOut = \Carbon\Carbon::createFromFormat('H:i:s', $timesheet->clock_out_time);
                        
                        // Handle overnight shifts
                        if ($clockOut->lt($clockIn)) {
                            $clockOut->addDay();
                        }
                        
                        $totalMinutes = $clockOut->diffInMinutes($clockIn) - ($timesheet->break_duration * 60 ?? 0);
                        $calculatedHours = round($totalMinutes / 60, 2);
                        
                        // Update the timesheet with calculated hours
                        $timesheet->hours_worked = max(0, min(8, $calculatedHours));
                        $timesheet->overtime_hours = max(0, $calculatedHours - 8);
                        $timesheet->save();
                    }
                    
                    // Format clock times for display
                    $timesheet->clock_in = $timesheet->formatted_clock_in ?? $timesheet->clock_in_time;
                    $timesheet->clock_out = $timesheet->formatted_clock_out ?? $timesheet->clock_out_time;
                    
                    return $timesheet;
                });
            
            // Get shifts with employee and shift type data (fallback to raw query if needed)
            try {
                $shifts = DB::table('shifts as s')
                    ->leftJoin('employees as e', 's.employee_id', '=', 'e.id')
                    ->leftJoin('shift_types as st', 's.shift_type_id', '=', 'st.id')
                    ->select(
                        's.*',
                        DB::raw("CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as employee_name"),
                        'st.name as shift_type_name'
                    )
                    ->orderBy('s.shift_date', 'desc')
                    ->get();
            } catch (\Exception $e) {
                $shifts = collect([]);
            }
            
            // Get leave requests using the same logic as LeaveController for consistency
            try {
                // Try using the same approach as LeaveController
                $leaveRequests = DB::table('leave_requests as lr')
                    ->leftJoin('employees as e', 'lr.employee_id', '=', 'e.id')
                    ->leftJoin('leave_types as lt', 'lr.leave_type_id', '=', 'lt.id')
                    ->select(
                        'lr.*',
                        'e.first_name',
                        'e.last_name', 
                        DB::raw("CONCAT(COALESCE(e.first_name, 'Employee'), ' ', COALESCE(e.last_name, CONCAT('ID:', lr.employee_id))) as employee_name"),
                        DB::raw("COALESCE(lt.name, CONCAT('Type ID:', lr.leave_type_id)) as leave_type_name"),
                        'lt.code as leave_type_code'
                    )
                    ->orderBy('lr.created_at', 'desc')
                    ->get();
                    
                \Log::info('Timesheet - Retrieved ' . $leaveRequests->count() . ' leave requests for integration');
            } catch (\Exception $e) {
                \Log::warning('Leave requests query failed in TimesheetController: ' . $e->getMessage());
                $leaveRequests = collect([]);
            }
            
            // Get leave types for the modal dropdown
            try {
                $leaveTypes = DB::table('leave_types')
                    ->where('is_active', 1)
                    ->orderBy('name')
                    ->get();
            } catch (\Exception $e) {
                $leaveTypes = collect([]);
            }
            
            // Get claim types for the modal dropdown
            try {
                $claimTypes = DB::table('claim_types')
                    ->where('is_active', 1)
                    ->orderBy('name')
                    ->get();
            } catch (\Exception $e) {
                $claimTypes = collect([]);
            }
            
            // Get claims with employee and claim type data (fallback to raw query if needed)
            try {
                $claims = DB::table('claims as c')
                    ->leftJoin('employees as e', 'c.employee_id', '=', 'e.id')
                    ->leftJoin('claim_types as ct', 'c.claim_type_id', '=', 'ct.id')
                    ->select(
                        'c.*',
                        DB::raw("CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as employee_name"),
                        'ct.name as claim_type_name'
                    )
                    ->orderBy('c.claim_date', 'desc')
                    ->get();
            } catch (\Exception $e) {
                $claims = collect([]);
            }
            
            // Get attendance records with employee data
            try {
                $attendances = DB::table('attendances as a')
                    ->leftJoin('employees as e', 'a.employee_id', '=', 'e.id')
                    ->select(
                        'a.*',
                        DB::raw("CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as employee_name")
                    )
                    ->orderBy('a.date', 'desc')
                    ->orderBy('a.clock_in_time', 'desc')
                    ->limit(100) // Limit to recent records for performance
                    ->get();
            } catch (\Exception $e) {
                $attendances = collect([]);
            }
            
            // Calculate timesheet statistics
            $timesheetStats = [
                'total_timesheets' => $timesheets->count(),
                'pending_timesheets' => $timesheets->where('status', 'pending')->count(),
                'approved_timesheets' => $timesheets->where('status', 'approved')->count(),
                'total_hours' => $timesheets->where('status', 'approved')->sum('hours_worked')
            ];
            
            // Calculate employee statistics
            $employeeStats = [
                'total_employees' => $employees->count(),
                'active_employees' => $employees->where('status', 'active')->count(),
                'employees_with_timesheets' => $timesheets->pluck('employee_id')->unique()->count(),
                'departments' => $employees->pluck('department')->unique()->count()
            ];
            
            return view('timesheet_management', compact('employees', 'timesheets', 'shifts', 'leaveRequests', 'claims', 'attendances', 'leaveTypes', 'claimTypes', 'employeeStats', 'timesheetStats'));
        } catch (\Exception $e) {
            // If there's a database error, return empty arrays
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
            $timesheetStats = [
                'total_timesheets' => 0,
                'pending_timesheets' => 0,
                'approved_timesheets' => 0,
                'total_hours' => 0
            ];
            
            // Log the actual error for debugging (but don't show to user)
            \Log::info('Timesheet management initializing: ' . $e->getMessage());
            
            return view('timesheet_management', compact('employees', 'timesheets', 'shifts', 'leaveRequests', 'claims', 'attendances', 'leaveTypes', 'claimTypes', 'employeeStats', 'timesheetStats'));
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
     * Generate AI timesheet for a specific employee
     */
    public function generateAITimesheet(Request $request, $employeeId)
    {
        try {
            $weekStartDate = $request->input('week_start_date');
            
            $aiTimesheet = AIGeneratedTimesheet::generateForEmployee($employeeId, $weekStartDate);
            
            // Handle both Eloquent model and fallback object
            $employeeName = '';
            $department = 'General';
            $supervisorName = 'Not Assigned';
            $generatedAt = '';
            
            if (is_object($aiTimesheet->employee)) {
                // Eloquent model
                $employeeName = $aiTimesheet->employee->first_name . ' ' . $aiTimesheet->employee->last_name;
                $department = $aiTimesheet->employee->department ?? 'General';
                $supervisorName = $aiTimesheet->employee->supervisor ?? 'Not Assigned';
            } else {
                // Fallback mode - get employee data directly
                $employee = Employee::find($employeeId);
                if ($employee) {
                    $employeeName = $employee->first_name . ' ' . $employee->last_name;
                    $department = $employee->department ?? 'General';
                    $supervisorName = $employee->supervisor ?? 'Not Assigned';
                }
            }
            
            if (is_object($aiTimesheet->generated_at) && method_exists($aiTimesheet->generated_at, 'format')) {
                $generatedAt = $aiTimesheet->generated_at->format('m/d/Y, g:i A');
            } else {
                $generatedAt = date('m/d/Y, g:i A');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'AI timesheet generated successfully!',
                'data' => [
                    'id' => $aiTimesheet->id,
                    'employee_name' => $employeeName,
                    'department' => $department,
                    'supervisor_name' => $supervisorName,
                    'weekly_data' => $aiTimesheet->weekly_data,
                    'total_hours' => $aiTimesheet->total_hours,
                    'overtime_hours' => $aiTimesheet->overtime_hours,
                    'ai_insights' => $aiTimesheet->ai_insights,
                    'generated_at' => $generatedAt,
                    'status' => $aiTimesheet->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating AI timesheet: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate AI timesheets for all employees
     */
    public function generateAllAITimesheets(Request $request)
    {
        try {
            $weekStartDate = $request->input('week_start_date');
            $employees = Employee::where('status', 'active')->get();
            
            $generated = [];
            $errors = [];
            
            foreach ($employees as $employee) {
                try {
                    $aiTimesheet = AIGeneratedTimesheet::generateForEmployee($employee->id, $weekStartDate);
                    $generated[] = [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                        'timesheet_id' => $aiTimesheet->id
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
     * Approve AI timesheet
     */
    public function approveAITimesheet(Request $request, $id)
    {
        try {
            $aiTimesheet = AIGeneratedTimesheet::findOrFail($id);
            
            $aiTimesheet->update([
                'status' => 'approved',
                'approved_by' => auth()->user()->id ?? 1,
                'approved_at' => now(),
                'notes' => $request->input('notes')
            ]);
            
            // Create actual timesheet entries
            $this->createTimesheetEntriesFromAI($aiTimesheet);
            
            return response()->json([
                'success' => true,
                'message' => 'AI timesheet approved and saved successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving AI timesheet: ' . $e->getMessage()
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
}
