<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    // Get all employees (Web view)
    public function index(Request $request)
    {
        // Check if this is an API request
        if ($request->wantsJson() || $request->is('api/*')) {
            return $this->apiIndex($request);
        }

        // Web view - return Blade template with statistics
        try {
            // Use Eloquent models with fallback to raw queries
            $employees = collect([]);
            
            try {
                // Try using Eloquent first
                $query = Employee::query();
                
                // Apply filters based on query parameters
                if ($request->has('status')) {
                    $query->where('status', $request->get('status'));
                }
                
                if ($request->has('filter') && $request->get('filter') === 'with_timesheets') {
                    // Use relationship to filter employees with timesheets today
                    $query->whereHas('timeEntries', function($q) {
                        $q->whereDate('work_date', today());
                    });
                }
                
                if ($request->has('view') && $request->get('view') === 'departments') {
                    $query->whereNotNull('department')->where('department', '!=', '');
                }
                
                $employees = $query->orderBy('first_name')->orderBy('last_name')->get();
                
                // Add computed name property for blade compatibility
                $employees = $employees->map(function($employee) {
                    $employee->name = $employee->first_name . ' ' . $employee->last_name;
                    return $employee;
                });
                
                \Log::info('Eloquent - Retrieved ' . $employees->count() . ' employees');
            } catch (\Exception $e) {
                \Log::warning('Eloquent failed, falling back to raw queries: ' . $e->getMessage());
                
                // Fallback to raw queries
                try {
                    // Ensure we're using the correct database
                    \Illuminate\Support\Facades\Config::set('database.connections.mysql.database', 'hr3systemdb');
                    DB::purge('mysql');
                    
                    // Check if employees table exists
                    $tablesExist = DB::select("SHOW TABLES LIKE 'employees'");
                    if (empty($tablesExist)) {
                        // Return empty data if table doesn't exist
                        $stats = [
                            'total_employees' => 0,
                            'active_employees' => 0,
                            'departments' => 0,
                            'employees_with_timesheets' => 0,
                            'online_employees' => 0,
                            'inactive_employees' => 0,
                            'terminated_employees' => 0,
                            'recent_hires' => 0
                        ];
                        return view('admin.employees.index', ['employees' => [], 'stats' => $stats]);
                    }
                    
                    // Handle filtering from timesheet management cards
                    $baseQuery = "SELECT *, CONCAT(first_name, ' ', last_name) as name FROM employees";
                    $whereConditions = [];
                    $orderBy = " ORDER BY first_name, last_name";
                    
                    // Apply filters based on query parameters
                    if ($request->has('status')) {
                        $whereConditions[] = "status = '" . $request->get('status') . "'";
                    }
                    
                    if ($request->has('filter') && $request->get('filter') === 'with_timesheets') {
                        // Check if time_entries table exists
                        $timeEntriesExist = DB::select("SHOW TABLES LIKE 'time_entries'");
                        if (!empty($timeEntriesExist)) {
                            $baseQuery = "SELECT DISTINCT e.*, CONCAT(e.first_name, ' ', e.last_name) as name FROM employees e INNER JOIN time_entries t ON e.id = t.employee_id WHERE DATE(t.work_date) = CURDATE()";
                        }
                    }
                    
                    if ($request->has('view') && $request->get('view') === 'departments') {
                        $whereConditions[] = "department IS NOT NULL AND department != ''";
                    }
                    
                    // Build final query
                    $finalQuery = $baseQuery;
                    if (!empty($whereConditions) && !$request->has('filter')) {
                        $finalQuery .= " WHERE " . implode(' AND ', $whereConditions);
                    } elseif (!empty($whereConditions) && $request->has('filter')) {
                        $finalQuery .= " AND " . implode(' AND ', $whereConditions);
                    }
                    $finalQuery .= $orderBy;
                    
                    $employees = DB::select($finalQuery);
                    
                    // Convert to collection for consistent handling
                    $employees = collect($employees);
                } catch (\Exception $e) {
                    \Log::error('Database connection failed: ' . $e->getMessage());
                    $employees = collect([]);
                }
            }
            
            // Get comprehensive statistics using Eloquent with fallback
            $stats = [];
            
            try {
                // Try using Eloquent first for statistics
                $stats['total_employees'] = Employee::count();
                $stats['active_employees'] = Employee::where('status', 'active')->count();
                $stats['inactive_employees'] = Employee::where('status', 'inactive')->count();
                $stats['terminated_employees'] = Employee::where('status', 'terminated')->count();
                
                // Department count
                $stats['departments'] = Employee::whereNotNull('department')
                    ->where('department', '!=', '')
                    ->distinct('department')
                    ->count();
                
                // Recent hires (last 30 days)
                $stats['recent_hires'] = Employee::where('hire_date', '>=', now()->subDays(30))->count();
                
                // Online employees
                try {
                    $stats['online_employees'] = Employee::where('online_status', 'online')->count();
                } catch (\Exception $e) {
                    $stats['online_employees'] = 0;
                }
                
                // Employees with timesheets today using relationship
                try {
                    $stats['employees_with_timesheets'] = Employee::whereHas('timeEntries', function($query) {
                        $query->whereDate('work_date', today());
                    })->count();
                } catch (\Exception $e) {
                    // Fallback to raw query if relationship doesn't exist
                    try {
                        $timeEntriesExist = DB::select("SHOW TABLES LIKE 'time_entries'");
                        if (!empty($timeEntriesExist)) {
                            $stats['employees_with_timesheets'] = DB::table('employees')
                                ->join('time_entries', 'employees.id', '=', 'time_entries.employee_id')
                                ->whereDate('time_entries.work_date', today())
                                ->distinct('employees.id')
                                ->count();
                        } else {
                            $stats['employees_with_timesheets'] = 0;
                        }
                    } catch (\Exception $e) {
                        $stats['employees_with_timesheets'] = 0;
                    }
                }
                
                \Log::info('Eloquent - Retrieved employee statistics');
            } catch (\Exception $e) {
                \Log::warning('Eloquent stats failed, falling back to raw queries: ' . $e->getMessage());
                
                // Fallback to raw queries for statistics
                try {
                    $stats['total_employees'] = DB::table('employees')->count();
                    $stats['active_employees'] = DB::table('employees')->where('status', 'active')->count();
                    $stats['inactive_employees'] = DB::table('employees')->where('status', 'inactive')->count();
                    $stats['terminated_employees'] = DB::table('employees')->where('status', 'terminated')->count();
                    
                    // Department count
                    $stats['departments'] = DB::table('employees')
                        ->select('department')
                        ->distinct()
                        ->whereNotNull('department')
                        ->where('department', '!=', '')
                        ->count();
                    
                    // Recent hires (last 30 days)
                    $stats['recent_hires'] = DB::table('employees')
                        ->where('hire_date', '>=', now()->subDays(30))
                        ->count();
                    
                    // Online employees
                    try {
                        $stats['online_employees'] = DB::table('employees')
                            ->where('online_status', 'online')
                            ->count();
                    } catch (\Exception $e) {
                        $stats['online_employees'] = 0;
                    }
                    
                    // Employees with timesheets today
                    try {
                        $timeEntriesExist = DB::select("SHOW TABLES LIKE 'time_entries'");
                        if (!empty($timeEntriesExist)) {
                            $stats['employees_with_timesheets'] = DB::table('employees')
                                ->join('time_entries', 'employees.id', '=', 'time_entries.employee_id')
                                ->whereDate('time_entries.work_date', today())
                                ->distinct('employees.id')
                                ->count();
                        } else {
                            $stats['employees_with_timesheets'] = 0;
                        }
                    } catch (\Exception $e) {
                        $stats['employees_with_timesheets'] = 0;
                    }
                } catch (\Exception $e) {
                    // Fallback stats if all queries fail
                    $stats = [
                        'total_employees' => 0,
                        'active_employees' => 0,
                        'departments' => 0,
                        'employees_with_timesheets' => 0,
                        'online_employees' => 0,
                        'inactive_employees' => 0,
                        'terminated_employees' => 0,
                        'recent_hires' => 0
                    ];
                }
            }
            
            return view('admin.employees.index', compact('employees', 'stats'));
        } catch (\Exception $e) {
            $stats = [
                'total_employees' => 0,
                'active_employees' => 0,
                'departments' => 0,
                'employees_with_timesheets' => 0,
                'online_employees' => 0,
                'inactive_employees' => 0,
                'terminated_employees' => 0,
                'recent_hires' => 0
            ];
            return view('admin.employees.index', ['employees' => collect([]), 'stats' => $stats]);
        }
    }

    // API endpoint for employees
    public function apiIndex(Request $request)
    {
        try {
            // Check if tables exist
            $tablesExist = DB::select("SHOW TABLES LIKE 'employees'");
            if (empty($tablesExist)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employees table does not exist. Please run the database setup SQL first.',
                    'data' => []
                ]);
            }

            $query = "SELECT *, CONCAT(first_name, ' ', last_name) as name FROM employees WHERE 1=1";
            $params = [];

            // Filter by status if provided
            if ($request->filled('status')) {
                $query .= " AND status = ?";
                $params[] = $request->status;
            }

            // Filter by department if provided
            if ($request->filled('department')) {
                $query .= " AND department = ?";
                $params[] = $request->department;
            }

            // Search by name or email
            if ($request->filled('search')) {
                $query .= " AND (CONCAT(first_name, ' ', last_name) LIKE ? OR email LIKE ?)";
                $searchTerm = '%' . $request->search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $query .= " ORDER BY first_name, last_name";

            $employees = DB::select($query, $params);

            return response()->json([
                'success' => true,
                'data' => $employees,
                'count' => count($employees)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    // Create new employee
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:employees',
            'phone' => 'nullable|string|max:20',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'hire_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,terminated'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employeeId = DB::table('employees')->insertGetId([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'department' => $request->department,
                'hire_date' => $request->hire_date,
                'salary' => $request->salary ?? 0,
                'status' => $request->status,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $employee = DB::table('employees')->find($employeeId);

            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully',
                'data' => $employee
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create employee: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get single employee
    public function show($id)
    {
        try {
            $employee = DB::table('employees')
                ->where('id', $id)
                ->first();
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $employee
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employee: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update employee
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:employees,email,'.$id,
            'phone' => 'nullable|string|max:20',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'hire_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,terminated'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $affected = DB::update(
                "UPDATE employees 
                 SET first_name = ?, last_name = ?, email = ?, phone = ?, position = ?, 
                     department = ?, hire_date = ?, salary = ?, status = ?, updated_at = NOW() 
                 WHERE id = ?",
                [
                    $request->first_name,
                    $request->last_name,
                    $request->email,
                    $request->phone,
                    $request->position,
                    $request->department,
                    $request->hire_date,
                    $request->salary ?? 0,
                    $request->status,
                    $id
                ]
            );

            if ($affected === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating employee: ' . $e->getMessage()
            ], 500);
        }
    }

    // Delete employee
    public function destroy($id)
    {
        try {
            // Check if employee has related records using Eloquent with fallback
            $hasTimeEntries = false;
            try {
                $hasTimeEntries = \App\Models\TimeEntry::where('employee_id', $id)->exists();
            } catch (\Exception $e) {
                // Fallback to raw query
                $timeEntriesCount = DB::selectOne("SELECT COUNT(*) as count FROM time_entries WHERE employee_id = ?", [$id]);
                $hasTimeEntries = $timeEntriesCount && $timeEntriesCount->count > 0;
            }
            
            if ($hasTimeEntries) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete employee with existing time entries'
                ], 409);
            }

            $affected = DB::delete("DELETE FROM employees WHERE id = ?", [$id]);

            if ($affected === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting employee: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get employee statistics
    public function getStats()
    {
        try {
            $totalEmployees = DB::table('employees')->count();
            $activeEmployees = DB::table('employees')->where('status', 'active')->count();
            $inactiveEmployees = DB::table('employees')->where('status', 'inactive')->count();
            $terminatedEmployees = DB::table('employees')->where('status', 'terminated')->count();
            
            // Department breakdown
            $departmentStats = DB::table('employees')
                ->select('department', DB::raw('count(*) as count'))
                ->where('status', 'active')
                ->groupBy('department')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_employees' => $totalEmployees,
                    'active_employees' => $activeEmployees,
                    'inactive_employees' => $inactiveEmployees,
                    'terminated_employees' => $terminatedEmployees,
                    'department_breakdown' => $departmentStats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employee statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get departments list
    public function getDepartments()
    {
        try {
            $departments = collect([]);
            
            try {
                // Try using Eloquent first
                $departments = Employee::whereNotNull('department')
                    ->where('department', '!=', '')
                    ->distinct()
                    ->orderBy('department')
                    ->pluck('department')
                    ->map(function($dept) {
                        return (object)['department' => $dept];
                    });
            } catch (\Exception $e) {
                // Fallback to raw query
                $departments = DB::select("SELECT DISTINCT department FROM employees WHERE department IS NOT NULL ORDER BY department");
            }
            
            return response()->json([
                'success' => true,
                'data' => $departments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving departments: ' . $e->getMessage()
            ]);
        }
    }

    // Get employees for dropdown (replaces users)
    public function getUsers()
    {
        try {
            // Use employees instead of users
            $employees = Employee::select('id', DB::raw("CONCAT(first_name, ' ', last_name) as name"), 'email')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $employees,
                'count' => $employees->count()
            ]);
        } catch (\Exception $e) {
            // Fallback to raw query if Eloquent fails
            try {
                $employees = DB::select("SELECT id, CONCAT(first_name, ' ', last_name) as name, email FROM employees ORDER BY first_name, last_name");
                
                return response()->json([
                    'success' => true,
                    'data' => $employees,
                    'count' => count($employees)
                ]);
            } catch (\Exception $e2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database error: ' . $e2->getMessage(),
                    'data' => []
                ]);
            }
        }
    }

    // Web-based methods for server-side handling
    public function create()
    {
        return redirect()->route('employees.index')->with('info', 'Please use the Add Employee button to create new employees.');
    }

    // View employee details
    public function viewEmployee($id)
    {
        try {
            $employee = DB::table('employees')->where('id', $id)->first();
            
            if (!$employee) {
                return redirect()->route('employees.index')->with('error', 'Employee not found.');
            }

            // Get additional employee data
            $timesheetCount = 0;
            try {
                $timesheetCount = DB::table('time_entries')
                    ->where('employee_id', $id)
                    ->count();
            } catch (\Exception $e) {
                // time_entries table may not exist
            }

            // Get stats using the same logic as index method
            $stats = [];
            $stats['total_employees'] = DB::table('employees')->count();
            $stats['active_employees'] = DB::table('employees')->where('status', 'active')->count();
            $stats['inactive_employees'] = DB::table('employees')->where('status', 'inactive')->count();
            $stats['terminated_employees'] = DB::table('employees')->where('status', 'terminated')->count();
            $stats['departments'] = DB::table('employees')->distinct('department')->whereNotNull('department')->where('department', '!=', '')->count();
            $stats['recent_hires'] = DB::table('employees')->where('hire_date', '>=', now()->subDays(30))->count();
            $stats['online_employees'] = 0;
            try {
                $stats['employees_with_timesheets'] = DB::table('employees')->join('time_entries', 'employees.id', '=', 'time_entries.employee_id')->whereDate('time_entries.work_date', today())->distinct('employees.id')->count();
            } catch (\Exception $e) {
                $stats['employees_with_timesheets'] = 0;
            }

            return view('admin.employees.index', [
                'employees' => collect([$employee]),
                'stats' => $stats,
                'viewEmployee' => $employee,
                'timesheetCount' => $timesheetCount
            ]);
        } catch (\Exception $e) {
            return redirect()->route('employees.index')->with('error', 'Error loading employee details.');
        }
    }

    // Edit employee
    public function edit($id)
    {
        try {
            $employee = DB::table('employees')->where('id', $id)->first();
            
            if (!$employee) {
                return redirect()->route('employees.index')->with('error', 'Employee not found.');
            }

            // Store employee data in session for modal population
            session(['edit_employee' => $employee]);

            return redirect()->route('employees.index')->with('success', 'Employee loaded for editing.');
        } catch (\Exception $e) {
            return redirect()->route('employees.index')->with('error', 'Error loading employee for editing.');
        }
    }

    public function storeWeb(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'hire_date' => 'required|date|before_or_equal:today',
            'salary' => 'nullable|numeric|min:0|max:9999999.99',
            'status' => 'required|in:active,inactive,terminated'
        ], [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'position.required' => 'Position is required.',
            'department.required' => 'Department is required.',
            'hire_date.required' => 'Hire date is required.',
            'hire_date.date' => 'Please enter a valid hire date.',
            'hire_date.before_or_equal' => 'Hire date cannot be in the future.',
            'salary.numeric' => 'Salary must be a valid number.',
            'salary.min' => 'Salary cannot be negative.',
            'salary.max' => 'Salary cannot exceed 9,999,999.99.',
            'status.required' => 'Status is required.',
            'status.in' => 'Please select a valid status.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors and try again.');
        }

        try {
            // Ensure we're using the correct database
            \Illuminate\Support\Facades\Config::set('database.connections.mysql.database', 'hr3systemdb');
            DB::purge('mysql');
            
            // Ensure the employees table exists
            $this->ensureEmployeesTableExists();
            
            // Prepare employee data - simplified date handling
            $employeeData = [
                'first_name' => trim($request->first_name),
                'last_name' => trim($request->last_name),
                'email' => strtolower(trim($request->email)),
                'phone' => $request->phone ? trim($request->phone) : null,
                'position' => trim($request->position),
                'department' => trim($request->department),
                'hire_date' => $request->hire_date,
                'salary' => $request->salary ? floatval($request->salary) : 0.00,
                'status' => $request->status,
                'online_status' => 'offline',
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // Validate that all required fields are present
            $requiredFields = ['first_name', 'last_name', 'email', 'position', 'department', 'hire_date', 'status'];
            foreach ($requiredFields as $field) {
                if (empty($employeeData[$field])) {
                    throw new \Exception("Required field '{$field}' is missing or empty");
                }
            }
            
            // Insert the employee with explicit field specification
            $employeeId = DB::table('employees')->insertGetId($employeeData);

            return redirect()->route('employees.index')->with('success', 'Employee "' . $employeeData['first_name'] . ' ' . $employeeData['last_name'] . '" created successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Employee creation failed: ' . $e->getMessage());
            
            // More specific error messages
            $errorMessage = 'Failed to create employee. ';
            if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'email') !== false) {
                $errorMessage .= 'This email address is already registered.';
            } elseif (strpos($e->getMessage(), 'Data too long') !== false) {
                $errorMessage .= 'One or more fields exceed the maximum length.';
            } else {
                $errorMessage .= 'Please check all fields and try again. Error: ' . $e->getMessage();
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }
    
    /**
     * Ensure the employees table exists with proper structure
     */
    private function ensureEmployeesTableExists()
    {
        try {
            $tableExists = DB::select("SHOW TABLES LIKE 'employees'");
            if (empty($tableExists)) {
                \Log::info('Creating employees table...');
                
                // Create the table with full structure
                $createTableSQL = "
                CREATE TABLE `employees` (
                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `position` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `department` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `hire_date` date NOT NULL,
                  `salary` decimal(10,2) NOT NULL DEFAULT 0.00,
                  `status` enum('active','inactive','terminated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
                  `online_status` enum('online','offline') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'offline',
                  `last_activity` timestamp NULL DEFAULT NULL,
                  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `created_at` timestamp NULL DEFAULT NULL,
                  `updated_at` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `employees_email_unique` (`email`),
                  KEY `employees_status_index` (`status`),
                  KEY `employees_department_index` (`department`),
                  KEY `employees_online_status_index` (`online_status`),
                  KEY `employees_hire_date_index` (`hire_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ";
                
                DB::statement($createTableSQL);
                \Log::info('Employees table created successfully');
            }
        } catch (\Exception $e) {
            \Log::error('Error checking/creating employees table: ' . $e->getMessage());
            throw new \Exception('Database table setup failed: ' . $e->getMessage());
        }
    }


    public function updateWeb(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:employees,email,'.$id,
            'phone' => 'nullable|string|max:20',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'hire_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,terminated'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors and try again.');
        }

        try {
            $affected = DB::update(
                "UPDATE employees 
                 SET first_name = ?, last_name = ?, email = ?, phone = ?, position = ?, 
                     department = ?, hire_date = ?, salary = ?, status = ?, updated_at = NOW() 
                 WHERE id = ?",
                [
                    $request->first_name,
                    $request->last_name,
                    $request->email,
                    $request->phone,
                    $request->position,
                    $request->department,
                    $request->hire_date,
                    $request->salary ?? 0,
                    $request->status,
                    $id
                ]
            );

            if ($affected === 0) {
                return redirect()->route('employees.index')->with('error', 'Employee not found');
            }

            return redirect()->route('employees.index')->with('success', 'Employee updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Error updating employee: ' . $e->getMessage()])
                ->withInput()
                ->with('error', 'Failed to update employee.');
        }
    }

    // Delete employee
    public function destroyWeb($id)
    {
        try {
            $affected = DB::table('employees')->where('id', $id)->delete();

            if ($affected === 0) {
                return redirect()->route('employees.index')->with('error', 'Employee not found');
            }

            return redirect()->route('employees.index')->with('success', 'Employee deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('employees.index')->with('error', 'Error deleting employee: ' . $e->getMessage());
        }
    }

    // API endpoint for viewing employee details (for AJAX calls)
    public function viewEmployeeAPI($id)
    {
        try {
            $employee = DB::table('employees')->where('id', $id)->first();
            
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee not found'], 404);
            }

            return response()->json([
                'success' => true,
                'employee' => $employee
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error loading employee details'], 500);
        }
    }
}
