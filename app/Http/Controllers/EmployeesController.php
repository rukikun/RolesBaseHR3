<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Employee;
use App\Models\TimeEntry;
use Carbon\Carbon;

class EmployeesController extends Controller
{
    /**
     * Display employees page with proper MVC structure
     */
    public function index(Request $request)
    {
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
                
                Log::info('Eloquent - Retrieved ' . $employees->count() . ' employees');
            } catch (\Exception $e) {
                Log::warning('Eloquent failed, falling back to raw queries: ' . $e->getMessage());
                
                // Fallback to raw PDO queries with table creation
                try {
                    // Use environment database configuration
                    $dbName = env('DB_DATABASE', 'hr3_hr3systemdb');
                    $dbHost = env('DB_HOST', '127.0.0.1');
                    $dbUser = env('DB_USERNAME', 'root');
                    $dbPass = env('DB_PASSWORD', '');
                    
                    $pdo = new \PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    
                    // Auto-create employees table if not exists with proper structure
                    $pdo->exec("CREATE TABLE IF NOT EXISTS employees (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        first_name VARCHAR(100) NOT NULL,
                        last_name VARCHAR(100) NOT NULL,
                        email VARCHAR(255) NOT NULL UNIQUE,
                        phone VARCHAR(20) NULL,
                        position VARCHAR(100) NOT NULL,
                        department VARCHAR(100) NOT NULL,
                        hire_date DATE NOT NULL,
                        salary DECIMAL(10,2) DEFAULT 0.00,
                        status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
                        online_status ENUM('online', 'offline') DEFAULT 'offline',
                        last_activity TIMESTAMP NULL,
                        password VARCHAR(255) NULL,
                        profile_picture VARCHAR(255) NULL,
                        address TEXT NULL,
                        date_of_birth DATE NULL,
                        gender ENUM('male', 'female', 'other') NULL,
                        emergency_contact_name VARCHAR(100) NULL,
                        emergency_contact_phone VARCHAR(20) NULL,
                        bank_account_number VARCHAR(50) NULL,
                        tax_id VARCHAR(50) NULL,
                        remember_token VARCHAR(100) NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_email (email),
                        INDEX idx_status (status),
                        INDEX idx_department (department)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                    
                    // Insert sample employees if table is empty
                    $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
                    if ($stmt->fetchColumn() == 0) {
                        $pdo->exec("INSERT IGNORE INTO employees (first_name, last_name, email, phone, position, department, hire_date, salary, status) VALUES
                            ('Alex', 'Mcqueen', 'alex.mcqueen@jetlouge.com', '+1234567890', 'Scheduler', 'Human Resources', '2023-01-15', 75000.00, 'active'),
                            ('David', 'Brown', 'david.brown@jetlouge.com', '+1234567891', 'Sales Representative', 'Sales', '2022-03-10', 85000.00, 'active'),
                            ('Jane', 'Smith', 'jane.smith@jetlouge.com', '+1234567892', 'HR Manager', 'Human Resources', '2023-06-01', 65000.00, 'active'),
                            ('John', 'Doe', 'john.doe@jetlouge.com', '+1234567893', 'Software Developer', 'IT', '2023-08-20', 60000.00, 'active'),
                            ('Mike', 'Johnson', 'mike.johnson@jetlouge.com', '+1234567894', 'Accountant', 'Human Resources', '2022-11-05', 80000.00, 'active'),
                            ('Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', '+1234567895', 'Marketing Specialist', 'Marketing', '2023-09-15', 70000.00, 'active')");
                    }
                    
                    // Build query with filters
                    $baseQuery = "SELECT * FROM employees";
                    $whereConditions = [];
                    
                    if ($request->has('status')) {
                        $whereConditions[] = "status = '" . $request->get('status') . "'";
                    }
                    
                    if ($request->has('view') && $request->get('view') === 'departments') {
                        $whereConditions[] = "department IS NOT NULL AND department != ''";
                    }
                    
                    $finalQuery = $baseQuery;
                    if (!empty($whereConditions)) {
                        $finalQuery .= " WHERE " . implode(' AND ', $whereConditions);
                    }
                    $finalQuery .= " ORDER BY first_name, last_name";
                    
                    $stmt = $pdo->query($finalQuery);
                    $employeesData = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    $employees = collect($employeesData);
                    
                    Log::info('Raw PDO - Retrieved ' . count($employeesData) . ' employees');
                } catch (\Exception $e2) {
                    Log::error('Raw PDO also failed: ' . $e2->getMessage());
                    $employees = collect([]);
                }
            }
            
            // Calculate statistics for dashboard cards
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
                
                // Employees with timesheets today
                try {
                    $stats['employees_with_timesheets'] = Employee::whereHas('timeEntries', function($query) {
                        $query->whereDate('work_date', today());
                    })->count();
                } catch (\Exception $e) {
                    $stats['employees_with_timesheets'] = 0;
                }
                
                Log::info('Eloquent - Retrieved employee statistics');
            } catch (\Exception $e) {
                Log::warning('Eloquent stats failed, falling back to raw queries: ' . $e->getMessage());
                
                // Fallback to raw queries for statistics
                try {
                    $dbName = env('DB_DATABASE', 'hr3_hr3systemdb');
                    $dbHost = env('DB_HOST', '127.0.0.1');
                    $dbUser = env('DB_USERNAME', 'root');
                    $dbPass = env('DB_PASSWORD', '');
                    
                    $pdo = new \PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM employees");
                    $stats['total_employees'] = $stmt->fetch(\PDO::FETCH_OBJ)->total;
                    
                    $stmt = $pdo->query("SELECT COUNT(*) as active FROM employees WHERE status = 'active'");
                    $stats['active_employees'] = $stmt->fetch(\PDO::FETCH_OBJ)->active;
                    
                    $stmt = $pdo->query("SELECT COUNT(*) as inactive FROM employees WHERE status = 'inactive'");
                    $stats['inactive_employees'] = $stmt->fetch(\PDO::FETCH_OBJ)->inactive;
                    
                    $stmt = $pdo->query("SELECT COUNT(*) as terminated FROM employees WHERE status = 'terminated'");
                    $stats['terminated_employees'] = $stmt->fetch(\PDO::FETCH_OBJ)->terminated;
                    
                    $stmt = $pdo->query("SELECT COUNT(DISTINCT department) as departments FROM employees WHERE department IS NOT NULL AND department != ''");
                    $stats['departments'] = $stmt->fetch(\PDO::FETCH_OBJ)->departments;
                    
                    $stats['employees_with_timesheets'] = 0; // Default fallback
                } catch (\Exception $e) {
                    // Final fallback stats
                    $stats = [
                        'total_employees' => 0,
                        'active_employees' => 0,
                        'inactive_employees' => 0,
                        'terminated_employees' => 0,
                        'departments' => 0,
                        'employees_with_timesheets' => 0
                    ];
                }
            }
                
            return view('admin.employees.index', compact('employees', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Employees index error: ' . $e->getMessage());
            
            return view('admin.employees.index', [
                'employees' => collect([]),
                'stats' => [
                    'total_employees' => 0,
                    'active_employees' => 0,
                    'inactive_employees' => 0,
                    'terminated_employees' => 0,
                    'departments' => 0,
                    'employees_with_timesheets' => 0
                ]
            ])->with('error', 'Error loading employee data: ' . $e->getMessage());
        }
    }

    /**
     * Store new employee with proper validation
     */
    public function store(Request $request)
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
            'hire_date.before_or_equal' => 'Hire date cannot be in the future.',
            'status.required' => 'Status is required.'
        ]);

        if ($validator->fails()) {
            return redirect()->route('employees.index')
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            // Try Eloquent first
            try {
                $employee = Employee::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'position' => $request->position,
                    'department' => $request->department,
                    'hire_date' => $request->hire_date,
                    'salary' => $request->salary ?? 0.00,
                    'status' => $request->status
                ]);

                return redirect()->route('employees.index')
                    ->with('success', 'Employee "' . $employee->first_name . ' ' . $employee->last_name . '" created successfully!');
                    
            } catch (\Exception $e) {
                Log::warning('Eloquent failed, using PDO fallback: ' . $e->getMessage());
                
                // Fallback to raw PDO
                $dbName = env('DB_DATABASE', 'hr3_hr3systemdb');
                $dbHost = env('DB_HOST', '127.0.0.1');
                $dbUser = env('DB_USERNAME', 'root');
                $dbPass = env('DB_PASSWORD', '');
                
                $pdo = new \PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                // Ensure table exists with proper structure
                $this->ensureEmployeesTableExists($pdo);
                
                $stmt = $pdo->prepare("
                    INSERT INTO employees (first_name, last_name, email, phone, position, department, hire_date, salary, status, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([
                    $request->first_name,
                    $request->last_name,
                    $request->email,
                    $request->phone,
                    $request->position,
                    $request->department,
                    $request->hire_date,
                    $request->salary ?? 0.00,
                    $request->status
                ]);
                
                return redirect()->route('employees.index')
                    ->with('success', 'Employee "' . $request->first_name . ' ' . $request->last_name . '" created successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Employee store error: ' . $e->getMessage());
            return redirect()->route('employees.index')
                ->with('error', 'Error creating employee: ' . $e->getMessage());
        }
    }

    /**
     * Get employee details for view modal (API endpoint)
     */
    public function view($id)
    {
        try {
            // Try Eloquent first
            try {
                $employee = Employee::findOrFail($id);
                
                return response()->json([
                    'success' => true,
                    'employee' => $employee
                ]);
                
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
                $stmt->execute([$id]);
                $employee = $stmt->fetch(\PDO::FETCH_OBJ);
                
                if (!$employee) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Employee not found'
                    ], 404);
                }
                
                return response()->json([
                    'success' => true,
                    'employee' => $employee
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Employee view error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading employee: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update employee
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:employees,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'hire_date' => 'required|date|before_or_equal:today',
            'salary' => 'nullable|numeric|min:0|max:9999999.99',
            'status' => 'required|in:active,inactive,terminated'
        ]);

        if ($validator->fails()) {
            return redirect()->route('employees.index')
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            // Try Eloquent first
            try {
                $employee = Employee::findOrFail($id);
                
                $employee->update([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'position' => $request->position,
                    'department' => $request->department,
                    'hire_date' => $request->hire_date,
                    'salary' => $request->salary ?? 0.00,
                    'status' => $request->status
                ]);

                return redirect()->route('employees.index')
                    ->with('success', 'Employee updated successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("
                    UPDATE employees 
                    SET first_name = ?, last_name = ?, email = ?, phone = ?, position = ?, department = ?, hire_date = ?, salary = ?, status = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $request->first_name,
                    $request->last_name,
                    $request->email,
                    $request->phone,
                    $request->position,
                    $request->department,
                    $request->hire_date,
                    $request->salary ?? 0.00,
                    $request->status,
                    $id
                ]);
                
                return redirect()->route('employees.index')
                    ->with('success', 'Employee updated successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Employee update error: ' . $e->getMessage());
            return redirect()->route('employees.index')
                ->with('error', 'Error updating employee: ' . $e->getMessage());
        }
    }

    /**
     * Delete employee
     */
    public function destroy($id)
    {
        try {
            // Try Eloquent first
            try {
                $employee = Employee::findOrFail($id);
                
                // Check for related records
                $hasTimeEntries = $employee->timeEntries()->exists();
                if ($hasTimeEntries) {
                    return redirect()->route('employees.index')
                        ->with('error', 'Cannot delete employee with existing time entries.');
                }
                
                $employeeName = $employee->first_name . ' ' . $employee->last_name;
                $employee->delete();

                return redirect()->route('employees.index')
                    ->with('success', "Employee '{$employeeName}' deleted successfully!");
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                // Check for related records
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM time_entries WHERE employee_id = ?");
                $stmt->execute([$id]);
                $timeEntriesCount = $stmt->fetch(\PDO::FETCH_OBJ)->count;
                
                if ($timeEntriesCount > 0) {
                    return redirect()->route('employees.index')
                        ->with('error', 'Cannot delete employee with existing time entries.');
                }
                
                $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
                $stmt->execute([$id]);
                
                return redirect()->route('employees.index')
                    ->with('success', 'Employee deleted successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Employee delete error: ' . $e->getMessage());
            return redirect()->route('employees.index')
                ->with('error', 'Error deleting employee: ' . $e->getMessage());
        }
    }

    /**
     * Ensure employees table exists with proper structure
     */
    private function ensureEmployeesTableExists($pdo)
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS employees (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            phone VARCHAR(20) NULL,
            position VARCHAR(100) NOT NULL,
            department VARCHAR(100) NOT NULL,
            hire_date DATE NOT NULL,
            salary DECIMAL(10,2) DEFAULT 0.00,
            status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
            online_status ENUM('online', 'offline') DEFAULT 'offline',
            last_activity TIMESTAMP NULL,
            password VARCHAR(255) NULL,
            profile_picture VARCHAR(255) NULL,
            address TEXT NULL,
            date_of_birth DATE NULL,
            gender ENUM('male', 'female', 'other') NULL,
            emergency_contact_name VARCHAR(100) NULL,
            emergency_contact_phone VARCHAR(20) NULL,
            bank_account_number VARCHAR(50) NULL,
            tax_id VARCHAR(50) NULL,
            remember_token VARCHAR(100) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_status (status),
            INDEX idx_department (department)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}
