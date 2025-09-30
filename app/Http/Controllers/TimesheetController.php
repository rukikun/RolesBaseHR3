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
            
            return view('timesheet_management', compact('employees', 'timesheets', 'shifts', 'leaveRequests', 'claims', 'leaveTypes', 'claimTypes', 'employeeStats', 'timesheetStats'));
        } catch (\Exception $e) {
            // If there's a database error, return empty arrays
            $employees = collect([]);
            $timesheets = collect([]);
            $shifts = collect([]);
            $leaveRequests = collect([]);
            $claims = collect([]);
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
            
            return view('timesheet_management', compact('employees', 'timesheets', 'shifts', 'leaveRequests', 'claims', 'leaveTypes', 'claimTypes', 'employeeStats', 'timesheetStats'));
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
}
